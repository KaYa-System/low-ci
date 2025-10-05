<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;

class DocumentAnalysisService
{
    private UserTrackingService $trackingService;

    public function __construct(UserTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Analyse automatiquement un document PDF avec l'IA
     */
    public function analyzeDocument(UploadedFile $pdfFile): array
    {
        try {
            // 1. Extraire le texte du PDF
            $extractedText = $this->extractPdfText($pdfFile);
            
            if (!$extractedText) {
                throw new \Exception('Impossible d\'extraire le texte du PDF');
            }

            // 2. Analyser avec l'IA
            $aiAnalysis = $this->analyzeWithAI($extractedText);

            // 3. Nettoyer et valider les résultats
            return $this->processAnalysisResults($aiAnalysis, $pdfFile->getClientOriginalName());

        } catch (\Exception $e) {
            \Log::error('Document analysis failed: ' . $e->getMessage());
            
            // Fallback: analyse basique sans IA
            return $this->basicAnalysis($pdfFile);
        }
    }

    /**
     * Extrait le texte d'un PDF
     */
    protected function extractPdfText(UploadedFile $pdfFile): ?string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($pdfFile->getPathname());
            $text = $pdf->getText();
            
            // Nettoyer le texte extrait
            return $this->cleanExtractedText($text);
        } catch (\Exception $e) {
            \Log::warning('PDF text extraction failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyse le contenu avec l'IA
     */
    protected function analyzeWithAI(string $content): array
    {
        try {
            $apiKey = config('services.huggingface.api_key');
            $model = config('services.huggingface.model');

            // Prompt système pour l'analyse de documents juridiques
            $systemPrompt = "Tu es un expert en législation ivoirienne. Analyse ce document juridique et extrait les informations suivantes au format JSON strict :

{
  \"title\": \"titre du document (max 200 caractères)\",
  \"type\": \"un de: constitution|loi|decret|arrete|code|ordonnance\",
  \"reference_number\": \"numéro de référence/identification du document\",
  \"summary\": \"résumé détaillé du document (max 800 caractères)\",
  \"publication_date\": \"date au format YYYY-MM-DD si trouvée, sinon null\",
  \"effective_date\": \"date d'entrée en vigueur au format YYYY-MM-DD si trouvée, sinon null\",
  \"category_suggestion\": \"suggestion de catégorie (ex: droit civil, droit pénal, etc.)\",
  \"confidence_score\": \"score de confiance entre 0 et 1\"
}

IMPORTANT: 
- Réponds UNIQUEMENT avec le JSON, rien d'autre
- Si tu n'es pas sûr d'une valeur, utilise null
- Le type doit être exactement un de: constitution, loi, decret, arrete, code, ordonnance
- Concentre-toi sur la législation ivoirienne";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Analyse ce document juridique ivoirien :\n\n" . substr($content, 0, 8000)] // Limite à 8000 caractères pour éviter les timeouts
            ];

            $response = $this->callHuggingFaceAPI($messages, $apiKey, $model);
            
            // Parser la réponse JSON
            $analysisData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Réponse IA invalide: ' . $response);
            }

            return $analysisData;

        } catch (\Exception $e) {
            \Log::error('AI analysis failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Appelle l'API Hugging Face
     */
    protected function callHuggingFaceAPI(array $messages, string $apiKey, string $model): string
    {
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post("https://router.huggingface.co/v1/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => $messages,
                    'model' => $model,
                    'temperature' => 0.1, // Faible température pour plus de précision
                    'max_tokens' => 1000,
                    'stream' => false
                ],
                'timeout' => 60
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['choices'][0]['message']['content'])) {
                $content = $result['choices'][0]['message']['content'];
                
                // Nettoyer les balises de réflexion DeepSeek
                $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
                $content = trim($content);
                
                return $content;
            }

            throw new \Exception('Réponse API invalide');

        } catch (\Exception $e) {
            throw new \Exception('Erreur API Hugging Face: ' . $e->getMessage());
        }
    }

    /**
     * Traite et valide les résultats de l'analyse IA
     */
    protected function processAnalysisResults(array $aiAnalysis, string $originalFileName): array
    {
        return [
            'title' => $this->validateTitle($aiAnalysis['title'] ?? null, $originalFileName),
            'type' => $this->validateType($aiAnalysis['type'] ?? null),
            'reference_number' => $this->validateReferenceNumber($aiAnalysis['reference_number'] ?? null),
            'summary' => $this->validateSummary($aiAnalysis['summary'] ?? null),
            'publication_date' => $this->validateDate($aiAnalysis['publication_date'] ?? null),
            'effective_date' => $this->validateDate($aiAnalysis['effective_date'] ?? null),
            'category_suggestion' => $aiAnalysis['category_suggestion'] ?? null,
            'confidence_score' => floatval($aiAnalysis['confidence_score'] ?? 0),
            'analysis_source' => 'ai',
            'analysis_timestamp' => now(),
        ];
    }

    /**
     * Analyse basique sans IA (fallback)
     */
    protected function basicAnalysis(UploadedFile $pdfFile): array
    {
        $fileName = $pdfFile->getClientOriginalName();
        $extractedText = $this->extractPdfText($pdfFile);

        return [
            'title' => $this->guessTitle($fileName, $extractedText),
            'type' => $this->guessType($fileName, $extractedText),
            'reference_number' => $this->guessReferenceNumber($extractedText),
            'summary' => $extractedText ? Str::limit($extractedText, 500) : null,
            'publication_date' => null,
            'effective_date' => null,
            'category_suggestion' => null,
            'confidence_score' => 0.3, // Faible confiance pour l'analyse basique
            'analysis_source' => 'basic',
            'analysis_timestamp' => now(),
        ];
    }

    /**
     * Fonctions de validation
     */
    protected function validateTitle(?string $title, string $fallback): string
    {
        if (!$title || strlen(trim($title)) < 10) {
            return Str::limit(pathinfo($fallback, PATHINFO_FILENAME), 200);
        }
        return Str::limit(trim($title), 200);
    }

    protected function validateType(?string $type): string
    {
        $validTypes = ['constitution', 'loi', 'decret', 'arrete', 'code', 'ordonnance'];
        return in_array(strtolower($type), $validTypes) ? strtolower($type) : 'loi';
    }

    protected function validateReferenceNumber(?string $refNumber): ?string
    {
        return $refNumber ? Str::limit(trim($refNumber), 100) : null;
    }

    protected function validateSummary(?string $summary): ?string
    {
        return $summary ? Str::limit(trim($summary), 1000) : null;
    }

    protected function validateDate(?string $date): ?string
    {
        if (!$date) return null;
        
        try {
            $parsedDate = \Carbon\Carbon::parse($date);
            return $parsedDate->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fonctions d'analyse basique (fallback)
     */
    protected function guessTitle(string $fileName, ?string $content): string
    {
        // Essayer d'extraire le titre du début du contenu
        if ($content) {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 20 && strlen($line) < 200) {
                    return $line;
                }
            }
        }
        
        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    protected function guessType(string $fileName, ?string $content): string
    {
        $fileName = strtolower($fileName);
        $content = strtolower($content ?? '');

        if (str_contains($fileName, 'constitution') || str_contains($content, 'constitution')) {
            return 'constitution';
        }
        if (str_contains($fileName, 'code') || str_contains($content, 'code')) {
            return 'code';
        }
        if (str_contains($fileName, 'decret') || str_contains($content, 'décret')) {
            return 'decret';
        }
        if (str_contains($fileName, 'arrete') || str_contains($content, 'arrêté')) {
            return 'arrete';
        }
        if (str_contains($fileName, 'ordonnance') || str_contains($content, 'ordonnance')) {
            return 'ordonnance';
        }
        
        return 'loi'; // Par défaut
    }

    protected function guessReferenceNumber(?string $content): ?string
    {
        if (!$content) return null;

        // Chercher des patterns de numéros de référence courants
        $patterns = [
            '/n°?\s*(\d{4}[-\/]\d+)/i',
            '/loi\s+n°?\s*([\d\-\/]+)/i',
            '/décret\s+n°?\s*([\d\-\/]+)/i',
            '/arrêté\s+n°?\s*([\d\-\/]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Nettoie le texte extrait du PDF
     */
    protected function cleanExtractedText(string $text): string
    {
        // Supprimer les caractères de contrôle et normaliser les espaces
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
}