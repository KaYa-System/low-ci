<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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
     * Get user's chat sessions
     */
    public function getUserSessions(): JsonResponse
    {
        $sessions = AiChatSession::query()
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->get();

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
     * Generate AI response (mock implementation)
     */
    private function generateAiResponse(string $userMessage, AiChatSession $session): array
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
        $documents = [];
        
        // Simple keyword matching - in production, use more sophisticated search
        $query = LegalDocument::query()->active()->with('category');
        
        if (str_contains($keywords, 'constitution')) {
            $query->where('type', 'constitution');
        } elseif (str_contains($keywords, 'travail')) {
            $query->where('title', 'like', '%travail%');
        } elseif (str_contains($keywords, 'pénal')) {
            $query->where('title', 'like', '%pénal%');
        } elseif (str_contains($keywords, 'entreprise') || str_contains($keywords, 'commerce')) {
            $query->where('title', 'like', '%commerce%')
                  ->orWhere('title', 'like', '%société%');
        }
        
        $results = $query->limit(3)->get();
        
        return $results->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'slug' => $doc->slug,
                'type' => $doc->type,
                'reference_number' => $doc->reference_number
            ];
        })->toArray();
    }
}
