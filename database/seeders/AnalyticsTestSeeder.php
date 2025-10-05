<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use Carbon\Carbon;

class AnalyticsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['CI', 'Côte d\'Ivoire', 'Abidjan'],
            ['FR', 'France', 'Paris'],
            ['SN', 'Sénégal', 'Dakar'],
            ['ML', 'Mali', 'Bamako'],
            ['BF', 'Burkina Faso', 'Ouagadougou'],
            ['US', 'États-Unis', 'New York'],
            ['CA', 'Canada', 'Toronto'],
            ['GB', 'Royaume-Uni', 'Londres'],
        ];
        
        $devices = [
            ['mobile', true, false, false],
            ['desktop', false, false, true],
            ['tablet', false, true, false],
        ];
        
        $browsers = [
            ['Chrome', '118.0'],
            ['Safari', '17.0'],
            ['Firefox', '119.0'],
            ['Edge', '118.0'],
        ];
        
        $platforms = [
            ['Windows', 'Windows'],
            ['macOS', 'macOS'],
            ['iOS', 'iOS'],
            ['Android', 'Android'],
            ['Linux', 'Linux'],
        ];
        
        // Créer 100 sessions de test sur les 30 derniers jours
        for ($i = 0; $i < 100; $i++) {
            $country = $countries[array_rand($countries)];
            $device = $devices[array_rand($devices)];
            $browser = $browsers[array_rand($browsers)];
            $platform = $platforms[array_rand($platforms)];
            
            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            $session = AiChatSession::create([
                'session_id' => \Str::uuid(),
                'user_id' => rand(0, 10) > 7 ? null : null, // 100% anonyme pour les tests
                'title' => 'Session de test #' . ($i + 1),
                'context' => [],
                'last_activity' => $createdAt,
                'ip_address' => $this->generateFakeIp(),
                'user_agent' => $this->generateUserAgent($browser[0], $platform[0]),
                'country' => $country[0],
                'country_name' => $country[1],
                'city' => $country[2],
                'device_type' => $device[0],
                'browser' => $browser[0],
                'browser_version' => $browser[1],
                'operating_system' => $platform[0],
                'platform' => $platform[1],
                'is_mobile' => $device[1],
                'is_tablet' => $device[2],
                'is_desktop' => $device[3],
                'language' => $country[0] === 'CI' ? 'fr-CI' : ($country[0] === 'FR' ? 'fr-FR' : 'en-US'),
                'timezone' => $this->getTimezoneForCountry($country[0]),
                'screen_width' => $this->getScreenWidth($device[0]),
                'screen_height' => $this->getScreenHeight($device[0]),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            
            // Ajouter quelques messages à certaines sessions
            if (rand(0, 10) > 3) {
                $messageCount = rand(2, 8);
                for ($j = 0; $j < $messageCount; $j += 2) {
                    // Message utilisateur
                    AiChatMessage::create([
                        'session_id' => $session->id,
                        'role' => 'user',
                        'content' => $this->getRandomUserMessage(),
                        'sent_at' => $createdAt->addMinutes($j * 2),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                    
                    // Réponse assistant
                    if ($j + 1 < $messageCount) {
                        AiChatMessage::create([
                            'session_id' => $session->id,
                            'role' => 'assistant',
                            'content' => $this->getRandomAssistantMessage(),
                            'metadata' => ['model' => 'deepseek-chat'],
                            'sent_at' => $createdAt->addMinutes($j * 2 + 1),
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('✅ 100 sessions de test créées avec succès !');
    }
    
    private function generateFakeIp(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }
    
    private function generateUserAgent(string $browser, string $platform): string
    {
        $userAgents = [
            'Chrome' => "Mozilla/5.0 ({$platform}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36",
            'Safari' => "Mozilla/5.0 ({$platform}) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15",
            'Firefox' => "Mozilla/5.0 ({$platform}) Gecko/20100101 Firefox/119.0",
            'Edge' => "Mozilla/5.0 ({$platform}) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.0.0",
        ];
        
        return $userAgents[$browser] ?? $userAgents['Chrome'];
    }
    
    private function getTimezoneForCountry(string $countryCode): string
    {
        $timezones = [
            'CI' => 'Africa/Abidjan',
            'FR' => 'Europe/Paris',
            'SN' => 'Africa/Dakar',
            'ML' => 'Africa/Bamako',
            'BF' => 'Africa/Ouagadougou',
            'US' => 'America/New_York',
            'CA' => 'America/Toronto',
            'GB' => 'Europe/London',
        ];
        
        return $timezones[$countryCode] ?? 'UTC';
    }
    
    private function getScreenWidth(string $deviceType): int
    {
        return match($deviceType) {
            'mobile' => rand(360, 414),
            'tablet' => rand(768, 1024),
            'desktop' => rand(1366, 1920),
            default => 1366,
        };
    }
    
    private function getScreenHeight(string $deviceType): int
    {
        return match($deviceType) {
            'mobile' => rand(640, 896),
            'tablet' => rand(1024, 1366),
            'desktop' => rand(768, 1080),
            default => 768,
        };
    }
    
    private function getRandomUserMessage(): string
    {
        $messages = [
            "Qu'est-ce que dit la Constitution ivoirienne sur les droits de l'homme ?",
            "Comment fonctionne le Code du travail en Côte d'Ivoire ?",
            "Quelles sont les sanctions prévues par le Code pénal ?",
            "Comment créer une entreprise selon la loi ivoirienne ?",
            "Quels sont les droits des femmes dans la législation ?",
            "Que dit la loi sur le mariage en Côte d'Ivoire ?",
            "Comment obtenir la nationalité ivoirienne ?",
            "Quels sont les congés payés prévus par la loi ?",
            "Comment fonctionne le système judiciaire ivoirien ?",
            "Que dit la loi sur l'éducation en Côte d'Ivoire ?",
        ];
        
        return $messages[array_rand($messages)];
    }
    
    private function getRandomAssistantMessage(): string
    {
        $messages = [
            "La Constitution ivoirienne de 2016 consacre les droits fondamentaux dans son Titre II. Elle garantit l'égalité, la liberté et la dignité.",
            "Le Code du travail ivoirien régit les relations employeur-employé avec des dispositions sur les contrats, les congés et la durée du travail.",
            "Le Code pénal ivoirien distingue les contraventions, délits et crimes avec des sanctions appropriées à chaque catégorie.",
            "Pour créer une entreprise en Côte d'Ivoire, vous devez vous inscrire au CEPICI et respecter les formalités légales.",
            "La législation ivoirienne garantit l'égalité entre hommes et femmes et protège les droits des femmes dans tous les domaines.",
            "Selon le droit ivoirien, le mariage peut être civil ou religieux, avec des conditions d'âge et de consentement.",
            "La nationalité ivoirienne s'acquiert par naissance, naturalisation ou déclaration selon les conditions légales.",
            "Le Code du travail prévoit 2,5 jours ouvrables de congés payés par mois de service effectif.",
            "Le système judiciaire ivoirien comprend les juridictions civiles, pénales et administratives.",
            "L'éducation est un droit garanti par la Constitution avec obligation de scolarisation jusqu'à 16 ans.",
        ];
        
        return $messages[array_rand($messages)];
    }
}
