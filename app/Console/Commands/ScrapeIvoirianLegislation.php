<?php

namespace App\Console\Commands;

use App\Models\LegalCategory;
use App\Models\LegalDocument;
use App\Models\LegalSection;
use App\Models\LegalArticle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScrapeIvoirianLegislation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legislation:scrape 
                            {--source=mock : Source to scrape from (mock, gouv, jo)}
                            {--limit=10 : Maximum number of documents to scrape}
                            {--force : Force overwrite existing documents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Ivorian legislation from official sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = $this->option('source');
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $this->info("Démarrage du scraping de la législation ivoirienne...");
        $this->info("Source: {$source}, Limite: {$limit}");

        switch ($source) {
            case 'mock':
                $this->scrapeMockData($limit, $force);
                break;
            case 'gouv':
                $this->scrapeGovernmentSite($limit, $force);
                break;
            case 'jo':
                $this->scrapeJournalOfficiel($limit, $force);
                break;
            default:
                $this->error("Source non reconnue: {$source}");
                return 1;
        }

        $this->info("✓ Scraping terminé avec succès!");
        return 0;
    }

    /**
     * Scrape mock data for development
     */
    private function scrapeMockData(int $limit, bool $force): void
    {
        $this->info("Génération de données de démonstration...");

        $mockDocuments = [
            [
                'title' => 'Décret n° 2023-001 portant application du Code du travail',
                'type' => 'decret',
                'reference_number' => 'Décret n° 2023-001',
                'summary' => 'Décret d\'application précisant les modalités d\'application du nouveau Code du travail.',
                'publication_date' => '2023-01-15',
                'category' => 'Droit du travail',
                'content' => $this->getMockDecretContent()
            ],
            [
                'title' => 'Arrêté n° 2023-045 fixant les modalités de création d\'entreprise',
                'type' => 'arrete',
                'reference_number' => 'Arrêté n° 2023-045',
                'summary' => 'Arrêté définissant les procédures simplifiées de création d\'entreprise.',
                'publication_date' => '2023-03-10',
                'category' => 'Droit civil et commercial',
                'content' => $this->getMockArreteContent()
            ],
            [
                'title' => 'Loi n° 2023-123 portant protection de l\'environnement',
                'type' => 'loi',
                'reference_number' => 'Loi n° 2023-123',
                'summary' => 'Loi renforçant le cadre juridique de protection de l\'environnement.',
                'publication_date' => '2023-06-20',
                'category' => 'Droit de l\'environnement',
                'content' => $this->getMockLoiContent()
            ],
            [
                'title' => 'Ordonnance n° 2023-067 relative à la digitalisation de l\'administration',
                'type' => 'ordonnance',
                'reference_number' => 'Ordonnance n° 2023-067',
                'summary' => 'Ordonnance encadrant la transformation digitale des services publics.',
                'publication_date' => '2023-09-05',
                'category' => 'Droit administratif',
                'content' => $this->getMockOrdonnanceContent()
            ]
        ];

        $bar = $this->output->createProgressBar(min($limit, count($mockDocuments)));
        $bar->start();

        $created = 0;
        foreach (array_slice($mockDocuments, 0, $limit) as $docData) {
            // Find or create category
            $category = LegalCategory::firstOrCreate(
                ['name' => $docData['category']],
                [
                    'description' => "Catégorie {$docData['category']}",
                    'color' => $this->getRandomColor(),
                    'icon' => 'scale'
                ]
            );

            // Check if document exists
            $existing = LegalDocument::where('reference_number', $docData['reference_number'])->first();
            
            if ($existing && !$force) {
                $bar->advance();
                continue;
            }

            // Create or update document
            $document = LegalDocument::updateOrCreate(
                ['reference_number' => $docData['reference_number']],
                [
                    'title' => $docData['title'],
                    'summary' => $docData['summary'],
                    'content' => $docData['content'],
                    'type' => $docData['type'],
                    'publication_date' => Carbon::parse($docData['publication_date']),
                    'effective_date' => Carbon::parse($docData['publication_date']),
                    'status' => 'active',
                    'category_id' => $category->id,
                    'journal_officiel' => "JO n° " . rand(1, 52) . " du " . Carbon::parse($docData['publication_date'])->format('d M Y'),
                    'is_featured' => rand(0, 1) == 1
                ]
            );

            // Create sample articles for the document
            $this->createMockArticles($document, $docData['type']);

            $created++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$created} documents créés/mis à jour");
    }

    /**
     * Scrape from government website (placeholder)
     */
    private function scrapeGovernmentSite(int $limit, bool $force): void
    {
        $this->info("Scraping depuis le site gouvernemental...");
        
        // Placeholder for actual government site scraping
        // This would use HTTP requests to scrape real legislation
        $this->warn("Fonctionnalité non implémentée: Scraping du site gouvernemental");
        $this->info("URLs potentielles:");
        $this->line("- https://www.gouv.ci/");
        $this->line("- https://assembleenationale.ci/");
        
        // For now, fall back to mock data
        $this->scrapeMockData($limit, $force);
    }

    /**
     * Scrape from Journal Officiel (placeholder)
     */
    private function scrapeJournalOfficiel(int $limit, bool $force): void
    {
        $this->info("Scraping depuis le Journal Officiel...");
        
        // Placeholder for actual JO scraping
        $this->warn("Fonctionnalité non implémentée: Scraping du Journal Officiel");
        
        // For now, fall back to mock data
        $this->scrapeMockData($limit, $force);
    }

    /**
     * Create mock articles for a document
     */
    private function createMockArticles(LegalDocument $document, string $type): void
    {
        $articleCount = rand(3, 8);
        
        for ($i = 1; $i <= $articleCount; $i++) {
            LegalArticle::create([
                'number' => (string) $i,
                'title' => $this->getMockArticleTitle($type, $i),
                'content' => $this->getMockArticleContent($type, $i),
                'document_id' => $document->id,
                'sort_order' => $i
            ]);
        }
    }

    // Mock content generators
    private function getMockDecretContent(): string
    {
        return "CHAPITRE I : DISPOSITIONS GÉNÉRALES\n\nLe présent décret fixe les modalités d'application des dispositions du Code du travail relatives aux conditions de travail, à la durée du travail et aux congés payés.\n\nCHAPITRE II : CONDITIONS DE TRAVAIL\n\nLes conditions de travail doivent respecter les normes de sécurité et de santé au travail définies par les textes en vigueur.";
    }

    private function getMockArreteContent(): string
    {
        return "TITRE I : CRÉATION D'ENTREPRISE\n\nArticle 1er : Les formalités de création d'entreprise peuvent être accomplies selon la procédure simplifiée définie par le présent arrêté.\n\nTITRE II : PIÈCES JUSTIFICATIVES\n\nLes pièces requises sont définies selon le type d'entreprise à créer.";
    }

    private function getMockLoiContent(): string
    {
        return "TITRE PREMIER : PRINCIPES GÉNÉRAUX\n\nLa protection de l'environnement est d'intérêt général. Toute personne a le droit de vivre dans un environnement sain.\n\nTITRE II : OBLIGATIONS\n\nToute activité susceptible de porter atteinte à l'environnement doit faire l'objet d'une étude d'impact environnemental.";
    }

    private function getMockOrdonnanceContent(): string
    {
        return "CHAPITRE I : OBJECTIFS\n\nLa présente ordonnance vise à accélérer la transformation digitale de l'administration publique ivoirienne.\n\nCHAPITRE II : MISE EN ŒUVRE\n\nLes services publics doivent progressivement dématérialiser leurs procédures.";
    }

    private function getMockArticleTitle(string $type, int $number): string
    {
        $titles = [
            'decret' => ["Champ d'application", "Modalités d'exécution", "Dispositions transitoires"],
            'arrete' => ["Procédures", "Documents requis", "Délais"],
            'loi' => ["Principes", "Droits et obligations", "Sanctions"],
            'ordonnance' => ["Objectifs", "Mise en application", "Suivi et évaluation"]
        ];
        
        return $titles[$type][($number - 1) % count($titles[$type])] ?? "Disposition générale";
    }

    private function getMockArticleContent(string $type, int $number): string
    {
        return "Contenu de l'article {$number} du {$type}. Cette disposition précise les modalités d'application et les conditions spécifiques requises pour la mise en œuvre des mesures prévues.";
    }

    private function getRandomColor(): string
    {
        $colors = ['#dc2626', '#2563eb', '#059669', '#7c3aed', '#ea580c', '#0891b2'];
        return $colors[array_rand($colors)];
    }
}
