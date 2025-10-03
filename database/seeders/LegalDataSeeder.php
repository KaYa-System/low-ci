<?php

namespace Database\Seeders;

use App\Models\LegalCategory;
use App\Models\LegalDocument;
use App\Models\LegalSection;
use App\Models\LegalArticle;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LegalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Catégories principales
        $categories = [
            [
                'name' => 'Droit constitutionnel',
                'description' => 'Constitution et lois fondamentales de la République de Côte d\'Ivoire',
                'color' => '#dc2626',
                'icon' => 'scale',
                'children' => [
                    'Constitution',
                    'Lois organiques',
                    'Droits de l\'homme'
                ]
            ],
            [
                'name' => 'Droit civil et commercial',
                'description' => 'Code civil, commercial et des affaires',
                'color' => '#2563eb',
                'icon' => 'briefcase',
                'children' => [
                    'Code civil',
                    'Code de commerce',
                    'Droit des sociétés',
                    'Droit des contrats'
                ]
            ],
            [
                'name' => 'Droit pénal',
                'description' => 'Code pénal et procédure pénale',
                'color' => '#7c2d12',
                'icon' => 'shield',
                'children' => [
                    'Code pénal',
                    'Procédure pénale',
                    'Exécution des peines'
                ]
            ],
            [
                'name' => 'Droit du travail',
                'description' => 'Législation du travail et de la sécurité sociale',
                'color' => '#059669',
                'icon' => 'users',
                'children' => [
                    'Code du travail',
                    'Sécurité sociale',
                    'Conventions collectives'
                ]
            ],
            [
                'name' => 'Droit fiscal',
                'description' => 'Code général des impôts et fiscalité',
                'color' => '#7c3aed',
                'icon' => 'calculator',
                'children' => [
                    'Impôts directs',
                    'Impôts indirects',
                    'Douanes'
                ]
            ],
            [
                'name' => 'Droit de la nationalité',
                'description' => 'Acquisition et perte de la nationalité ivoirienne',
                'color' => '#ea580c',
                'icon' => 'flag',
                'children' => [
                    'Code de la nationalité',
                    'Naturalisation',
                    'Double nationalité'
                ]
            ],
            [
                'name' => 'Droit de la famille',
                'description' => 'Mariage, divorce, filiation et successions',
                'color' => '#be185d',
                'icon' => 'heart',
                'children' => [
                    'Mariage et divorce',
                    'Filiation',
                    'Successions'
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = LegalCategory::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'color' => $categoryData['color'],
                'icon' => $categoryData['icon'],
                'sort_order' => array_search($categoryData, $categories)
            ]);

            foreach ($categoryData['children'] as $index => $childName) {
                LegalCategory::create([
                    'name' => $childName,
                    'parent_id' => $category->id,
                    'sort_order' => $index
                ]);
            }
        }

        // Création de quelques documents exemples
        $this->createConstitution();
        $this->createCodeTravail();
        $this->createCodePenal();
        $this->createCodeNationalite();
        $this->createCodeCivil();
    }

    private function createConstitution(): void
    {
        $constitutionCategory = LegalCategory::where('name', 'Constitution')->first();
        
        $constitution = LegalDocument::create([
            'title' => 'Constitution de la République de Côte d\'Ivoire',
            'summary' => 'Loi fondamentale de la République de Côte d\'Ivoire adoptée le 8 novembre 2016',
            'content' => $this->getConstitutionContent(),
            'type' => 'constitution',
            'reference_number' => 'Constitution 2016',
            'publication_date' => Carbon::create(2016, 11, 8),
            'effective_date' => Carbon::create(2016, 11, 8),
            'journal_officiel' => 'JO n° 47 du 8 novembre 2016',
            'status' => 'active',
            'category_id' => $constitutionCategory?->id,
            'is_featured' => true
        ]);

        // Créer des sections de la Constitution
        $sections = [
            ['title' => 'TITRE PREMIER : DE L\'ÉTAT ET DE LA SOUVERAINETÉ', 'number' => 'Titre I'],
            ['title' => 'TITRE II : DES DROITS ET DES DEVOIRS DE LA PERSONNE HUMAINE', 'number' => 'Titre II'],
            ['title' => 'TITRE III : DU PRÉSIDENT DE LA RÉPUBLIQUE', 'number' => 'Titre III'],
            ['title' => 'TITRE IV : DU POUVOIR LÉGISLATIF', 'number' => 'Titre IV'],
            ['title' => 'TITRE V : DES RAPPORTS ENTRE LE POUVOIR EXÉCUTIF ET LE POUVOIR LÉGISLATIF', 'number' => 'Titre V']
        ];

        foreach ($sections as $index => $sectionData) {
            LegalSection::create([
                'title' => $sectionData['title'],
                'number' => $sectionData['number'],
                'document_id' => $constitution->id,
                'sort_order' => $index
            ]);
        }

        // Créer quelques articles
        $articles = [
            [
                'number' => '1',
                'content' => 'La Côte d\'Ivoire est une République souveraine, indépendante, laïque, démocratique et sociale. Son principe est : Gouvernement du peuple, par le peuple et pour le peuple.'
            ],
            [
                'number' => '2',
                'content' => 'La République de Côte d\'Ivoire est une et indivisible, laïque, démocratique et sociale. Elle assure l\'égalité devant la loi de tous les citoyens sans distinction d\'origine, de race, d\'ethnie, de sexe, de religion ou de croyance.'
            ],
            [
                'number' => '3',
                'content' => 'La souveraineté nationale appartient au peuple qui l\'exerce par ses représentants élus ou par voie de référendum. Aucune fraction du peuple, ni aucun individu ne peut s\'en attribuer l\'exercice.'
            ]
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'content' => $articleData['content'],
                'document_id' => $constitution->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodeTravail(): void
    {
        $travailCategory = LegalCategory::where('name', 'Code du travail')->first();
        
        $codeTravail = LegalDocument::create([
            'title' => 'Loi n° 2015-532 du 20 juillet 2015 portant Code du travail',
            'summary' => 'Code du travail de la République de Côte d\'Ivoire',
            'content' => 'Le présent Code régit les rapports de travail entre employeurs et travailleurs...',
            'type' => 'code',
            'reference_number' => 'Loi n° 2015-532',
            'publication_date' => Carbon::create(2015, 7, 20),
            'effective_date' => Carbon::create(2015, 7, 20),
            'journal_officiel' => 'JO n° 29 du 20 juillet 2015',
            'status' => 'active',
            'category_id' => $travailCategory?->id,
            'is_featured' => true
        ]);

        // Articles du Code du travail
        $articles = [
            [
                'number' => '1.1',
                'title' => 'Champ d\'application',
                'content' => 'Les dispositions du présent Code s\'appliquent aux relations de travail entre les travailleurs et les employeurs ainsi qu\'aux travailleurs et employeurs eux-mêmes, dans tous les secteurs d\'activités, qu\'ils relèvent du secteur public, parapublic, privé ou du secteur informel.'
            ],
            [
                'number' => '2.1',
                'title' => 'Contrat de travail',
                'content' => 'Le contrat de travail est une convention par laquelle une personne s\'engage à mettre son activité professionnelle sous la direction et l\'autorité d\'une autre personne, moyennant rémunération.'
            ]
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'title' => $articleData['title'] ?? null,
                'content' => $articleData['content'],
                'document_id' => $codeTravail->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodePenal(): void
    {
        $penalCategory = LegalCategory::where('name', 'Code pénal')->first();

        $codePenal = LegalDocument::create([
            'title' => 'Loi n° 81-640 du 31 juillet 1981 instituant le Code pénal',
            'summary' => 'Code pénal de la République de Côte d\'Ivoire',
            'content' => 'Le présent Code définit les infractions et fixe les peines applicables aux personnes physiques et morales...',
            'type' => 'code',
            'reference_number' => 'Loi n° 81-640',
            'publication_date' => Carbon::create(1981, 7, 31),
            'effective_date' => Carbon::create(1981, 7, 31),
            'journal_officiel' => 'JO du 31 juillet 1981',
            'status' => 'active',
            'category_id' => $penalCategory?->id
        ]);

        // Articles du Code pénal
        $articles = [
            [
                'number' => '1',
                'content' => 'Nul n\'est punissable que d\'une peine légalement établie par une loi antérieure au délit.'
            ],
            [
                'number' => '2',
                'content' => 'Nulle peine ne peut être établie ni appliquée qu\'en vertu d\'une loi.'
            ]
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'content' => $articleData['content'],
                'document_id' => $codePenal->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodeNationalite(): void
    {
        $nationaliteCategory = LegalCategory::where('name', 'Code de la nationalité')->first();

        $codeNationalite = LegalDocument::create([
            'title' => 'Loi n° 61-415 du 14 décembre 1961 portant Code de la nationalité ivoirienne',
            'summary' => 'Code régissant l\'acquisition et la perte de la nationalité ivoirienne',
            'content' => 'Le présent Code détermine les conditions dans lesquelles une personne acquiert, conserve ou perd la nationalité ivoirienne...',
            'type' => 'code',
            'reference_number' => 'Loi n° 61-415',
            'publication_date' => Carbon::create(1961, 12, 14),
            'effective_date' => Carbon::create(1961, 12, 14),
            'journal_officiel' => 'JO du 14 décembre 1961',
            'status' => 'active',
            'category_id' => $nationaliteCategory?->id,
            'is_featured' => true
        ]);

        // Articles clés du Code de la nationalité
        $articles = [
            [
                'number' => '5',
                'title' => 'Nationalité par filiation',
                'content' => 'Est Ivoirien l\'enfant légitime né d\'un père ivoirien ou d\'une mère ivoirienne, que la naissance ait lieu en Côte d\'Ivoire ou à l\'étranger.'
            ],
            [
                'number' => '6',
                'title' => 'Nationalité par naissance sur le territoire',
                'content' => 'Est Ivoirien tout enfant né en Côte d\'Ivoire d\'au moins un parent né en Côte d\'Ivoire.'
            ],
            [
                'number' => '9',
                'title' => 'Nationalité par mariage',
                'content' => 'L\'étranger ou l\'étrangère qui épouse un Ivoirien ou une Ivoirienne acquiert la nationalité ivoirienne après deux ans de vie commune effective.'
            ],
            [
                'number' => '10',
                'title' => 'Naturalisation',
                'content' => 'Peut être naturalisé ivoirien tout étranger âgé de 21 ans révolus qui justifie d\'une résidence habituelle et continue de 5 ans en Côte d\'Ivoire.'
            ]
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'title' => $articleData['title'] ?? null,
                'content' => $articleData['content'],
                'document_id' => $codeNationalite->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodeCivil(): void
    {
        $civilCategory = LegalCategory::where('name', 'Code civil')->first();

        $codeCivil = LegalDocument::create([
            'title' => 'Loi n° 64-376 du 7 octobre 1964 portant Code civil',
            'summary' => 'Code civil de la République de Côte d\'Ivoire',
            'content' => 'Le présent Code régit les droits et obligations des personnes physiques et morales...',
            'type' => 'code',
            'reference_number' => 'Loi n° 64-376',
            'publication_date' => Carbon::create(1964, 10, 7),
            'effective_date' => Carbon::create(1964, 10, 7),
            'journal_officiel' => 'JO du 7 octobre 1964',
            'status' => 'active',
            'category_id' => $civilCategory?->id,
            'is_featured' => true
        ]);

        // Articles du Code civil
        $articles = [
            [
                'number' => '1',
                'content' => 'Les lois civiles obligent tous ceux qui habitent le territoire de la Côte d\'Ivoire, sans distinction d\'origine, de race, de sexe, de religion ou de croyance.'
            ],
            [
                'number' => '2',
                'content' => 'Les biens sont meubles ou immeubles. Sont meubles les biens susceptibles de mouvement propre ou qui peuvent être mus par une force étrangère.'
            ]
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'content' => $articleData['content'],
                'document_id' => $codeCivil->id,
                'sort_order' => $index
            ]);
        }
    }

    private function getConstitutionContent(): string
    {
        return "PRÉAMBULE\n\nNous, Peuple de Côte d'Ivoire,\n\nAffirmons notre volonté de créer un État de droit et de démocratie pluraliste dans lequel les droits fondamentaux de l'homme, les libertés publiques et la dignité de la personne humaine sont garantis, protégés et promus ;\n\nRéaffirmons notre attachement aux principes et droits fondamentaux tels qu'ils sont définis dans la Déclaration universelle des Droits de l'Homme de 1948 et dans la Charte africaine des Droits de l'Homme et des Peuples de 1981 ;\n\nExprimons notre engagement à œuvrer pour la paix, la solidarité et l'unité entre les peuples du monde entier et ceux d'Afrique en particulier ;\n\nDéclarons que la présente Constitution est l'expression de notre volonté souveraine ;\n\nAdoptons solennellement la présente Constitution.";
    }
}
