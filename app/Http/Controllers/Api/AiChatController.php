<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\LegalDocument;
use App\Services\UserTrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiChatController extends Controller
{
    protected UserTrackingService $trackingService;

    public function __construct(UserTrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }
    /**
     * Create a new chat session
     */
    public function createSession(Request $request): JsonResponse
    {
        // Collecte des données client optionnelles depuis le frontend
        $clientData = $request->input('client_data', []);
        
        // Collecte des données de tracking
        $trackingData = $this->trackingService->collectTrackingData($request, $clientData);
        
        $sessionData = array_merge([
            'user_id' => auth()->id(),
            'title' => null, // Will be generated from first message
            'context' => []
        ], $trackingData);
        
        $session = AiChatSession::create($sessionData);

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
        
        // Strategy 1: Type-based search with extensive keyword matching
        
        // Constitution & Droits fondamentaux
        if (str_contains($keywords, 'constitution') || str_contains($keywords, 'droits de l\'homme') || str_contains($keywords, 'droits fondamentaux') 
            || str_contains($keywords, 'liberté') || str_contains($keywords, 'libertés') || str_contains($keywords, 'république') 
            || str_contains($keywords, 'souveraineté') || str_contains($keywords, 'démocratie') || str_contains($keywords, 'égalité')
            || str_contains($keywords, 'justice') || str_contains($keywords, 'dignité') || str_contains($keywords, 'citoyen')
            || str_contains($keywords, 'citoyens') || str_contains($keywords, 'préambule') || str_contains($keywords, 'fondamental')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('type', 'constitution');
        }
        
        // Droit du travail étendu
        if (str_contains($keywords, 'travail') || str_contains($keywords, 'employé') || str_contains($keywords, 'salarié') 
            || str_contains($keywords, 'contrat') || str_contains($keywords, 'congé') || str_contains($keywords, 'emploi')
            || str_contains($keywords, 'embauche') || str_contains($keywords, 'licenciement') || str_contains($keywords, 'salaire')
            || str_contains($keywords, 'rémunération') || str_contains($keywords, 'patron') || str_contains($keywords, 'employeur')
            || str_contains($keywords, 'syndicat') || str_contains($keywords, 'grève') || str_contains($keywords, 'horaire')
            || str_contains($keywords, 'pause') || str_contains($keywords, 'repos') || str_contains($keywords, 'maladie')
            || str_contains($keywords, 'accident') || str_contains($keywords, 'sécurité sociale') || str_contains($keywords, 'cnps')
            || str_contains($keywords, 'retraite') || str_contains($keywords, 'apprentissage') || str_contains($keywords, 'formation')
            || str_contains($keywords, 'stage') || str_contains($keywords, 'stagiaire') || str_contains($keywords, 'démission')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%travail%');
        }
        
        // Droit pénal étendu
        if (str_contains($keywords, 'pénal') || str_contains($keywords, 'crime') || str_contains($keywords, 'délit') 
            || str_contains($keywords, 'infraction') || str_contains($keywords, 'prison') || str_contains($keywords, 'amende')
            || str_contains($keywords, 'tribunal') || str_contains($keywords, 'juge') || str_contains($keywords, 'procès')
            || str_contains($keywords, 'avocat') || str_contains($keywords, 'police') || str_contains($keywords, 'gendarmerie')
            || str_contains($keywords, 'arrestation') || str_contains($keywords, 'garde à vue') || str_contains($keywords, 'enquête')
            || str_contains($keywords, 'vol') || str_contains($keywords, 'meurtre') || str_contains($keywords, 'agression')
            || str_contains($keywords, 'violence') || str_contains($keywords, 'viol') || str_contains($keywords, 'corruption')
            || str_contains($keywords, 'fraude') || str_contains($keywords, 'escroquerie') || str_contains($keywords, 'blanchiment')
            || str_contains($keywords, 'stupéfiant') || str_contains($keywords, 'drogue') || str_contains($keywords, 'trafic')
            || str_contains($keywords, 'contrebande') || str_contains($keywords, 'peine') || str_contains($keywords, 'condamnation')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%pénal%');
        }
        
        // Droit commercial et des affaires étendu
        if (str_contains($keywords, 'entreprise') || str_contains($keywords, 'société') || str_contains($keywords, 'commerce') 
            || str_contains($keywords, 'sarl') || str_contains($keywords, 'sa') || str_contains($keywords, 'business')
            || str_contains($keywords, 'commercial') || str_contains($keywords, 'commerçant') || str_contains($keywords, 'boutique')
            || str_contains($keywords, 'magasin') || str_contains($keywords, 'vente') || str_contains($keywords, 'achat')
            || str_contains($keywords, 'client') || str_contains($keywords, 'fournisseur') || str_contains($keywords, 'facture')
            || str_contains($keywords, 'crédit') || str_contains($keywords, 'banque') || str_contains($keywords, 'prêt')
            || str_contains($keywords, 'investissement') || str_contains($keywords, 'capital') || str_contains($keywords, 'actionnaire')
            || str_contains($keywords, 'associé') || str_contains($keywords, 'partenaire') || str_contains($keywords, 'cepici')
            || str_contains($keywords, 'rccm') || str_contains($keywords, 'immatriculation') || str_contains($keywords, 'création')
            || str_contains($keywords, 'startup') || str_contains($keywords, 'entrepreneur') || str_contains($keywords, 'auto-entrepreneur')
            || str_contains($keywords, 'ohada') || str_contains($keywords, 'concurrence') || str_contains($keywords, 'monopole')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%commerce%')
                      ->orWhere('title', 'like', '%société%')
                      ->orWhere('title', 'like', '%civil%')
                      ->orWhere('title', 'like', '%ohada%');
                });
        }
        
        // Nationalité et citoyenneté étendu
        if (str_contains($keywords, 'nationalité') || str_contains($keywords, 'citoyenneté') || str_contains($keywords, 'naturalis') 
            || str_contains($keywords, 'ivoirien') || str_contains($keywords, 'ivoirienne') || str_contains($keywords, 'passeport')
            || str_contains($keywords, 'carte d\'identité') || str_contains($keywords, 'cni') || str_contains($keywords, 'étranger')
            || str_contains($keywords, 'étrangère') || str_contains($keywords, 'immigration') || str_contains($keywords, 'émigration')
            || str_contains($keywords, 'visa') || str_contains($keywords, 'résidence') || str_contains($keywords, 'expatrié')
            || str_contains($keywords, 'diaspora') || str_contains($keywords, 'binational') || str_contains($keywords, 'apatride')
            || str_contains($keywords, 'naissance') || str_contains($keywords, 'filiation') || str_contains($keywords, 'adoption')) {
            $queries[] = LegalDocument::query()->active()->with('category')->where('title', 'like', '%nationalité%');
        }
        
        // Droit de la famille étendu
        if (str_contains($keywords, 'mariage') || str_contains($keywords, 'divorce') || str_contains($keywords, 'famille') 
            || str_contains($keywords, 'enfant') || str_contains($keywords, 'succession') || str_contains($keywords, 'époux')
            || str_contains($keywords, 'épouse') || str_contains($keywords, 'conjoint') || str_contains($keywords, 'mari')
            || str_contains($keywords, 'femme') || str_contains($keywords, 'père') || str_contains($keywords, 'mère')
            || str_contains($keywords, 'parent') || str_contains($keywords, 'tutelle') || str_contains($keywords, 'curatelle')
            || str_contains($keywords, 'héritage') || str_contains($keywords, 'testament') || str_contains($keywords, 'héritier')
            || str_contains($keywords, 'veuve') || str_contains($keywords, 'veuf') || str_contains($keywords, 'orphelin')
            || str_contains($keywords, 'pension') || str_contains($keywords, 'alimentaire') || str_contains($keywords, 'garde')
            || str_contains($keywords, 'custody') || str_contains($keywords, 'mineur') || str_contains($keywords, 'majeur')
            || str_contains($keywords, 'émancipation') || str_contains($keywords, 'dot') || str_contains($keywords, 'polygamie')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%civil%')
                      ->orWhere('title', 'like', '%famille%')
                      ->orWhere('title', 'like', '%mariage%')
                      ->orWhere('title', 'like', '%succession%');
                });
        }
        
        // Droit fiscal étendu
        if (str_contains($keywords, 'fiscal') || str_contains($keywords, 'impôt') || str_contains($keywords, 'taxe') 
            || str_contains($keywords, 'douane') || str_contains($keywords, 'tva') || str_contains($keywords, 'irpp')
            || str_contains($keywords, 'is') || str_contains($keywords, 'contribution') || str_contains($keywords, 'redevance')
            || str_contains($keywords, 'déclaration') || str_contains($keywords, 'contrôle') || str_contains($keywords, 'redressement')
            || str_contains($keywords, 'contentieux') || str_contains($keywords, 'recours') || str_contains($keywords, 'exonération')
            || str_contains($keywords, 'déduction') || str_contains($keywords, 'abattement') || str_contains($keywords, 'crédit d\'impôt')
            || str_contains($keywords, 'patente') || str_contains($keywords, 'foncier') || str_contains($keywords, 'enregistrement')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%fiscal%')
                      ->orWhere('title', 'like', '%impôt%')
                      ->orWhere('title', 'like', '%douane%')
                      ->orWhere('title', 'like', '%taxe%');
                });
        }
        
        // Droit administratif
        if (str_contains($keywords, 'administratif') || str_contains($keywords, 'administration') || str_contains($keywords, 'préfet')
            || str_contains($keywords, 'maire') || str_contains($keywords, 'ministre') || str_contains($keywords, 'fonctionnaire')
            || str_contains($keywords, 'service public') || str_contains($keywords, 'permis') || str_contains($keywords, 'autorisation')
            || str_contains($keywords, 'agrément') || str_contains($keywords, 'concession') || str_contains($keywords, 'marché public')
            || str_contains($keywords, 'adjudication') || str_contains($keywords, 'appel d\'offres') || str_contains($keywords, 'urbanisme')
            || str_contains($keywords, 'construction') || str_contains($keywords, 'bâtiment') || str_contains($keywords, 'environnement')) {
            $queries[] = LegalDocument::query()->active()->with('category')
                ->where(function($q) {
                    $q->where('title', 'like', '%administratif%')
                      ->orWhere('title', 'like', '%administration%')
                      ->orWhere('title', 'like', '%urbanisme%')
                      ->orWhere('title', 'like', '%environnement%');
                });
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
