<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiChatController extends Controller
{
    /**
     * Create a new chat session
     */
    public function createSession(Request $request): JsonResponse
    {
        $session = AiChatSession::create([
            'user_id' => auth()->id(),
            'title' => null, // Will be generated from first message
            'context' => []
        ]);

        return response()->json([
            'data' => $session,
            'message' => 'Session créée avec succès'
        ]);
    }

    /**
     * Get a specific chat session
     */
    public function getSession(AiChatSession $session): JsonResponse
    {
        $session->load('messages');
        
        return response()->json([
            'data' => $session,
            'message' => 'Session récupérée avec succès'
        ]);
    }

    /**
     * Get user's chat sessions (or public sessions if not authenticated)
     */
    public function getUserSessions(): JsonResponse
    {
        $query = AiChatSession::query();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            // For non-auth users, return sessions without user_id (public)
            $query->whereNull('user_id');
        }

        $sessions = $query->orderBy('last_activity', 'desc')->get();

        return response()->json([
            'data' => $sessions,
            'message' => 'Sessions récupérées avec succès'
        ]);
    }

    /**
     * Send a message in a chat session
     */
    public function sendMessage(Request $request, AiChatSession $session): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:2000'
        ]);

        // Create user message
        $userMessage = AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $request->content,
            'sent_at' => now()
        ]);

        // Update session activity
        $session->updateActivity();

        // Generate AI response
        $aiResponse = $this->generateAiResponse($request->content, $session);

        // Create assistant message
        $assistantMessage = AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
            'metadata' => $aiResponse['metadata'] ?? null,
            'sent_at' => now()
        ]);

        // Update session activity
        $session->updateActivity();

        // Auto-generate session title if first message
        if ($session->messages()->count() === 2) { // User + assistant message
            $session->update([
                'title' => $session->generateTitle()
            ]);
        }

        return response()->json([
            'data' => $assistantMessage->load('session'),
            'message' => 'Message envoyé avec succès'
        ]);
    }

    /**
     * Get messages for a chat session
     */
    public function getMessages(AiChatSession $session): JsonResponse
    {
        $messages = $session->messages()
            ->orderBy('sent_at')
            ->get();

        return response()->json([
            'data' => $messages,
            'message' => 'Messages récupérés avec succès'
        ]);
    }

    /**
     * Delete a chat session
     */
    public function deleteSession(AiChatSession $session): JsonResponse
    {
        // Check if user owns the session
        if ($session->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        $session->delete();

        return response()->json([
            'message' => 'Session supprimée avec succès'
        ]);
    }

    /**
     * Generate AI response using Hugging Face API
     */
    private function generateAiResponse(string $userMessage, AiChatSession $session): array
    {
        try {
            // Use Hugging Face API with DeepSeek model
            $apiKey = config('services.huggingface.api_key');
            $model = config('services.huggingface.model');
            $stream = config('services.huggingface.stream');

            // Prepare conversation messages
            $messages = $this->buildConversationMessages($session, $userMessage);

            // Call Hugging Face API
            $response = $this->callHuggingFaceAPI($messages, $apiKey, $model);

            // Find relevant documents
            $citedDocuments = $this->findRelevantDocuments($userMessage);

            return [
                'content' => $response,
                'metadata' => [
                    'cited_documents' => $citedDocuments,
                    'model' => $model
                ]
            ];
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Hugging Face API error: ' . $e->getMessage());
            // Fallback to mock response on error
            return $this->generateMockResponse($userMessage, $session);
        }
    }

    /**
     * Generate mock AI response (fallback)
     */
    private function generateMockResponse(string $userMessage, AiChatSession $session): array
    {
        // Mock AI responses based on keywords for demonstration
        $response = $this->getMockResponse($userMessage);

        // Find relevant documents
        $citedDocuments = $this->findRelevantDocuments($userMessage);

        return [
            'content' => $response,
            'metadata' => [
                'cited_documents' => $citedDocuments
            ]
        ];
    }

    /**
     * Get mock AI response based on keywords
     */
    private function getMockResponse(string $message): string
    {
        $message = strtolower($message);
        
        // Constitution responses
        if (str_contains($message, 'constitution') || str_contains($message, 'droits de l\'homme')) {
            return "La Constitution ivoirienne de 2016 consacre les droits fondamentaux de la personne humaine dans son Titre II. Elle garantit notamment :\n\n- L'égalité de tous les citoyens devant la loi (Article 2)\n- La liberté de conscience et de religion\n- Le droit à la vie et à la sécurité\n- Le droit à l'éducation et à la santé\n\nCes droits sont inaliénables et imprescriptibles. L'État a l'obligation de les respecter et de les protéger.";
        }
        
        // Labor law responses
        if (str_contains($message, 'travail') || str_contains($message, 'employé') || str_contains($message, 'salarié')) {
            return "Le Code du travail ivoirien (Loi n° 2015-532) régit les relations entre employeurs et travailleurs. Voici les points clés :\n\n**Contrat de travail :**\n- Définition : convention par laquelle une personne met son activité sous la direction d'une autre contre rémunération\n- Peut être à durée déterminée ou indéterminée\n\n**Durée du travail :**\n- 40 heures par semaine maximum\n- Repos hebdomadaire obligatoire\n\n**Congés payés :**\n- 2,5 jours ouvrables par mois de service effectif";
        }
        
        // Criminal law responses
        if (str_contains($message, 'pénal') || str_contains($message, 'crime') || str_contains($message, 'délit')) {
            return "Le Code pénal ivoirien (Loi n° 81-640) définit les infractions et fixe les peines. Il distingue trois catégories d'infractions :\n\n**1. Contraventions :**\n- Infractions les moins graves\n- Punies d'amendes\n\n**2. Délits :**\n- Infractions de gravité moyenne\n- Punis d'emprisonnement jusqu'à 5 ans et/ou d'amendes\n\n**3. Crimes :**\n- Infractions les plus graves\n- Punis de réclusion criminelle\n\nLe principe de la légalité des délits et des peines s'applique : nulle infraction sans loi, nulle peine sans loi.";
        }
        
        // Business law responses  
        if (str_contains($message, 'entreprise') || str_contains($message, 'société') || str_contains($message, 'commerce')) {
            return "Pour créer une entreprise en Côte d'Ivoire, plusieurs formes juridiques sont possibles :\n\n**1. Entreprise individuelle :**\n- Plus simple à créer\n- Responsabilité illimitée du dirigeant\n\n**2. SARL (Société à Responsabilité Limitée) :**\n- Capital minimum : 1 000 000 FCFA\n- 1 à 50 associés\n\n**3. SA (Société Anonyme) :**\n- Capital minimum : 10 000 000 FCFA\n- Minimum 7 actionnaires\n\n**Démarches :** Inscription au CEPICI, immatriculation au RCCM, déclaration fiscale.";
        }
        
        // Default response
        return "Je suis spécialisé dans la législation ivoirienne et je peux vous aider avec des questions sur :\n\n- La Constitution de 2016\n- Le Code du travail\n- Le Code pénal\n- Le droit commercial et des sociétés\n- Les procédures administratives\n\nPouvez-vous préciser votre question ou le domaine juridique qui vous intéresse ?";
    }

    /**
     * Find relevant legal documents based on user message
     */
    private function findRelevantDocuments(string $message): array
    {
        $keywords = strtolower($message);
        $documents = collect();
        
        // Enhanced keyword matching with multiple strategies
        $queries = [];
        
        // Strategy 1: Type-based search with broader matching
        if (str_contains($keywords, 'constitution') || str_contains($keywords, 'droits de l\'homme') || str_contains($keywords, 'droits fondamentaux')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('type', 'constitution');
        }
        
        if (str_contains($keywords, 'travail') || str_contains($keywords, 'employé') || str_contains($keywords, 'salarié') || str_contains($keywords, 'contrat') || str_contains($keywords, 'congé')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%travail%');
        }
        
        if (str_contains($keywords, 'pénal') || str_contains($keywords, 'crime') || str_contains($keywords, 'délit') || str_contains($keywords, 'infraction')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%pénal%');
        }
        
        if (str_contains($keywords, 'entreprise') || str_contains($keywords, 'société') || str_contains($keywords, 'commerce') || str_contains($keywords, 'sarl') || str_contains($keywords, 'sa') || str_contains($keywords, 'business')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%commerce%')
                      ->orWhere('title', 'like', '%société%')
                      ->orWhere('title', 'like', '%civil%'); // Code civil contient du droit des sociétés
                });
        }
        
        if (str_contains($keywords, 'nationalité') || str_contains($keywords, 'citoyenneté') || str_contains($keywords, 'naturalis') || str_contains($keywords, 'ivoirien')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%nationalité%');
        }
        
        if (str_contains($keywords, 'mariage') || str_contains($keywords, 'divorce') || str_contains($keywords, 'famille') || str_contains($keywords, 'enfant') || str_contains($keywords, 'succession')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%civil%')
                      ->orWhere('title', 'like', '%famille%');
                });
        }
        
        if (str_contains($keywords, 'fiscal') || str_contains($keywords, 'impôt') || str_contains($keywords, 'taxe') || str_contains($keywords, 'douane')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%fiscal%');
        }
        
        // Execute specific queries
        foreach ($queries as $query) {
            $results = $query->limit(2)->get();
            $documents = $documents->merge($results);
        }
        
        // Strategy 2: General keyword search if not enough results
        if ($documents->count() < 2) {
            // Split message into individual words for broader search
            $words = explode(' ', $keywords);
            $importantWords = array_filter($words, function($word) {
                return strlen($word) > 3 && !in_array($word, ['dans', 'pour', 'avec', 'sans', 'être', 'avoir', 'faire', 'dire', 'aller', 'voir', 'savoir', 'pouvoir', 'falloir', 'vouloir', 'venir', 'devoir', 'prendre', 'donner']);
            });
            
            if (!empty($importantWords)) {
                $generalQuery = LegalDocument::query()->active()->with('category')
                    ->where(function ($q) use ($importantWords) {
                        foreach ($importantWords as $word) {
                            $q->orWhere('title', 'like', "%{$word}%")
                              ->orWhere('content', 'like', "%{$word}%")
                              ->orWhere('summary', 'like', "%{$word}%");
                        }
                    })
                    ->orderBy('is_featured', 'desc')
                    ->limit(3);
                
                $generalResults = $generalQuery->get();
                $documents = $documents->merge($generalResults);
            }
        }
        
        // Strategy 3: Fallback to featured documents if still no results
        if ($documents->isEmpty()) {
            $documents = LegalDocument::query()->active()->with('category')
                ->where('is_featured', true)
                ->limit(3)
                ->get();
        }
        
        // Remove duplicates and limit to 5 results
        $uniqueDocuments = $documents->unique('id')->take(5);
        
        return $uniqueDocuments->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'type' => $doc->type,
                'reference_number' => $doc->reference_number,
                'category' => $doc->category ? $doc->category->name : null
            ];
        })->toArray();
    }

    /**
     * Build conversation messages for context
     */
    private function buildConversationMessages(AiChatSession $session, string $newMessage): array
    {
        $messages = $session->messages()
            ->orderBy('sent_at')
            ->take(10) // Limit context to last 10 messages
            ->get();

        $conversationMessages = [];

        // Add system message for Ivoirian context
        $conversationMessages[] = [
            'role' => 'system',
            'content' => 'Vous êtes un assistant juridique IA spécialisé dans la législation ivoirienne. Vous devez répondre à toutes les questions en les ramenant au contexte du droit ivoirien. Référez-vous systématiquement aux textes juridiques ivoiriens : Constitution, codes (pénal, civil, du travail, etc.), lois, décrets, arrêtés, circulaires et autres documents officiels.

DIRECTIVES :
- Répondez toujours de manière utile et informative
- Utilisez exclusivement des références à la législation ivoirienne
- Adaptez votre langage au niveau de votre interlocuteur : vocabulaire simple et accessible pour les questions générales, plus technique et juridique pour les professionnels
- Si le sujet est ambigu, interprétez-le dans le contexte ivoirien
- Ne refusez jamais une question, mais redirigez poliment vers des sujets juridiques ivoiriens
- Fournissez des réponses précises avec citations des textes applicables

DOCUMENTS JURIDIQUES À RÉFÉRENCER :
- Constitution de 2016
- Codes : pénal, civil, du travail, commercial, des assurances, etc.
- Lois organiques et ordinaires
- Décrets présidentiels et gouvernementaux
- Arrêtés ministériels
- Décisions et jurisprudences de la Cour suprême
- Textes de l\'OHADA pour le droit des affaires

SUJETS COUVERTS :
- Droits fondamentaux et libertés publiques
- Nationalité et citoyenneté
- Droit des personnes et de la famille
- Droit des biens et successions
- Droit du travail et sécurité sociale
- Droit pénal et procédure pénale
- Droit des sociétés et commerce
- Droit administratif et contentieux'
        ];

        foreach ($messages as $message) {
            $role = $message->role === 'user' ? 'user' : 'assistant';
            $conversationMessages[] = [
                'role' => $role,
                'content' => $message->content
            ];
        }

        // Add the new user message
        $conversationMessages[] = [
            'role' => 'user',
            'content' => $newMessage
        ];

        return $conversationMessages;
    }

    /**
     * Call Hugging Face Chat Completions API
     */
    private function callHuggingFaceAPI(array $messages, string $apiKey, string $model): string
    {
        $client = new \GuzzleHttp\Client();

        try {
            $stream = config('services.huggingface.stream', false);

            $response = $client->post("https://router.huggingface.co/v1/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'messages' => $messages,
                    'model' => $model,
                    'stream' => $stream,
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ],
                'timeout' => 60, // Increased timeout for 8B model
                'stream' => $stream // Enable streaming in Guzzle
            ]);

            // Handle response
            $result = json_decode($response->getBody(), true);

            if (isset($result['choices'][0]['message']['content'])) {
                $content = $result['choices'][0]['message']['content'];
                // Clean DeepSeek thinking tags and improve formatting
                return $this->cleanDeepSeekResponse($content);
            }

            return "Désolé, je n'ai pas pu générer une réponse appropriée.";

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Clean DeepSeek response by removing thinking tags and improving formatting
     */
    private function cleanDeepSeekResponse(string $content): string
    {
        // Remove <think> tags and their content (DeepSeek reasoning process)
        $content = preg_replace('/<think>.*?<\/think>/s', '', $content);
        
        // Clean up extra whitespace and normalize line breaks
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);
        
        // If content is empty or too short, provide a fallback
        if (empty($content) || strlen($content) < 20) {
            return "Je suis désolé, je n'ai pas pu générer une réponse appropriée à votre question sur la législation ivoirienne. Pouvez-vous reformuler votre question ?";
        }
        
        return $content;
    }
}
