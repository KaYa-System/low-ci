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

        // Création de documents juridiques étendus
        $this->createConstitution();
        $this->createCodeTravail();
        $this->createCodePenal();
        $this->createCodeNationalite();
        $this->createCodeCivil();
        $this->createCodeCommerce();
        $this->createCodeFiscal();
        $this->createCodeFamille();
        $this->createLoisEnvironnement();
        $this->createLoisAdministratives();
        $this->createLoisSanteTravail();
        $this->createLoisEducation();
        $this->createDecretsPratiques();
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
            ],
            [
                'number' => '3',
                'content' => 'Le mariage est contracté par deux personnes de sexe différent. Il est conclu publiquement devant l\'officier de l\'état civil.'
            ],
            [
                'number' => '4',
                'content' => 'La filiation s\'établit par l\'acte de naissance, par la reconnaissance volontaire ou par la décision judiciaire.'
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

    private function createCodeCommerce(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Code de commerce'
        ], [
            'description' => 'Règles applicables au commerce et aux sociétés',
            'color' => '#0ea5e9',
            'icon' => 'briefcase',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Code de commerce ivoirien (extraits OHADA)',
            'summary' => 'Dispositions relatives aux sociétés commerciales et au registre du commerce',
            'content' => 'Le présent Code fixe les règles relatives aux actes de commerce, aux commerçants et aux sociétés commerciales...',
            'type' => 'code',
            'reference_number' => 'OHADA-CC',
            'publication_date' => Carbon::create(2014, 1, 1),
            'effective_date' => Carbon::create(2014, 1, 1),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id,
            'is_featured' => true
        ]);

        $articles = [
            [ 'number' => '1', 'title' => 'Actes de commerce par nature', 'content' => 'Sont commerçants ceux qui accomplissent des actes de commerce et en font leur profession habituelle.' ],
            [ 'number' => '2', 'title' => 'Registre du commerce et du crédit mobilier (RCCM)', 'content' => 'Toute personne physique ou morale exerçant une activité commerciale doit être immatriculée au RCCM.' ],
            [ 'number' => '3', 'title' => 'Sociétés commerciales', 'content' => 'Les sociétés commerciales comprennent la SA, la SARL, la SNC et la SCS.' ],
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'title' => $articleData['title'],
                'content' => $articleData['content'],
                'document_id' => $doc->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodeFiscal(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Code général des impôts'
        ], [
            'description' => 'Régime fiscal ivoirien',
            'color' => '#7c3aed',
            'icon' => 'calculator',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Code général des impôts (extraits)',
            'summary' => 'Dispositions en matière de TVA, IS et IRPP',
            'content' => 'Le présent Code fixe les règles relatives à l\'assiette, au recouvrement et au contentieux des impôts...',
            'type' => 'code',
            'reference_number' => 'CGI-2019',
            'publication_date' => Carbon::create(2019, 1, 1),
            'effective_date' => Carbon::create(2019, 1, 1),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        $articles = [
            [ 'number' => 'TVA-1', 'title' => 'Champ d\'application de la TVA', 'content' => 'La TVA s\'applique sur les opérations de vente de biens et de services.' ],
            [ 'number' => 'IS-1', 'title' => 'Impôt sur les sociétés', 'content' => 'L\'IS est dû par les personnes morales réalisant des bénéfices en Côte d\'Ivoire.' ],
            [ 'number' => 'IRPP-1', 'title' => 'Impôt sur le revenu des personnes physiques', 'content' => 'L\'IRPP est établi d\'après le montant total des revenus perçus par le contribuable.' ],
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'title' => $articleData['title'],
                'content' => $articleData['content'],
                'document_id' => $doc->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createCodeFamille(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Droit de la famille'
        ], [
            'description' => 'Mariage, divorce, filiation et successions',
            'color' => '#be185d',
            'icon' => 'heart',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Code de la famille (extraits)',
            'summary' => 'Principes relatifs au mariage, filiation et successions',
            'content' => 'Le présent Code fixe les règles relatives à la famille, la filiation, le mariage et les successions...',
            'type' => 'code',
            'reference_number' => 'CF-2016',
            'publication_date' => Carbon::create(2016, 6, 1),
            'effective_date' => Carbon::create(2016, 6, 1),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id,
            'is_featured' => true
        ]);

        $articles = [
            [ 'number' => 'M-1', 'title' => 'Conditions du mariage', 'content' => 'Le mariage est célébré publiquement par l\'officier de l\'état civil.' ],
            [ 'number' => 'D-1', 'title' => 'Divorce', 'content' => 'Le divorce peut être prononcé pour faute, altération définitive du lien conjugal ou consentement mutuel.' ],
            [ 'number' => 'S-1', 'title' => 'Successions', 'content' => 'Les successions s\'ouvrent par la mort au dernier domicile du défunt.' ],
        ];

        foreach ($articles as $index => $articleData) {
            LegalArticle::create([
                'number' => $articleData['number'],
                'title' => $articleData['title'],
                'content' => $articleData['content'],
                'document_id' => $doc->id,
                'sort_order' => $index
            ]);
        }
    }

    private function createLoisEnvironnement(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Droit de l\'environnement'
        ], [
            'description' => 'Protection de l\'environnement et développement durable',
            'color' => '#16a34a',
            'icon' => 'leaf',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Loi sur la protection de l\'environnement (extraits)',
            'summary' => 'Principes généraux et études d\'impact environnemental',
            'content' => 'La protection de l\'environnement est une obligation de l\'État et des citoyens...',
            'type' => 'loi',
            'reference_number' => 'Loi ENV-2018',
            'publication_date' => Carbon::create(2018, 3, 15),
            'effective_date' => Carbon::create(2018, 3, 15),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        LegalArticle::create([
            'number' => 'EIE-1',
            'title' => 'Études d\'impact environnemental',
            'content' => 'Les projets susceptibles d\'avoir des effets sur l\'environnement sont soumis à des études d\'impact environnemental.',
            'document_id' => $doc->id,
            'sort_order' => 0
        ]);
    }

    private function createLoisAdministratives(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Droit administratif'
        ], [
            'description' => 'Organisation administrative et procédures',
            'color' => '#334155',
            'icon' => 'scale',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Loi sur les procédures administratives (extraits)',
            'summary' => 'Règles relatives aux actes administratifs et recours',
            'content' => 'Les actes administratifs doivent être motivés et peuvent faire l\'objet de recours...',
            'type' => 'loi',
            'reference_number' => 'Loi ADM-2017',
            'publication_date' => Carbon::create(2017, 9, 10),
            'effective_date' => Carbon::create(2017, 9, 10),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        LegalArticle::create([
            'number' => 'REC-1',
            'title' => 'Recours pour excès de pouvoir',
            'content' => 'Tout administré peut contester devant le juge administratif un acte administratif illégal.',
            'document_id' => $doc->id,
            'sort_order' => 0
        ]);
    }

    private function createLoisSanteTravail(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Santé au travail'
        ], [
            'description' => 'Sécurité et santé des travailleurs',
            'color' => '#ef4444',
            'icon' => 'shield',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Loi sur la santé et sécurité au travail (extraits)',
            'summary' => 'Obligations de l\'employeur et du travailleur',
            'content' => 'L\'employeur prend les mesures nécessaires pour assurer la sécurité et protéger la santé des travailleurs...',
            'type' => 'loi',
            'reference_number' => 'Loi SAT-2020',
            'publication_date' => Carbon::create(2020, 2, 20),
            'effective_date' => Carbon::create(2020, 2, 20),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        LegalArticle::create([
            'number' => 'SST-1',
            'title' => 'Équipements de protection individuelle (EPI)',
            'content' => 'L\'employeur doit fournir gratuitement les EPI adaptés aux risques.',
            'document_id' => $doc->id,
            'sort_order' => 0
        ]);
    }

    private function createLoisEducation(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Droit de l\'éducation'
        ], [
            'description' => 'Organisation du système éducatif',
            'color' => '#f59e0b',
            'icon' => 'book',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Loi d\'orientation de l\'éducation (extraits)',
            'summary' => 'Principes de l\'éducation nationale en Côte d\'Ivoire',
            'content' => 'L\'éducation est un droit fondamental garanti par l\'État...',
            'type' => 'loi',
            'reference_number' => 'Loi EDU-2015',
            'publication_date' => Carbon::create(2015, 1, 20),
            'effective_date' => Carbon::create(2015, 1, 20),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        LegalArticle::create([
            'number' => 'EDU-1',
            'title' => 'Obligation scolaire',
            'content' => 'L\'instruction est obligatoire pour les enfants des deux sexes jusqu\'à l\'âge de 16 ans.',
            'document_id' => $doc->id,
            'sort_order' => 0
        ]);
    }

    private function createDecretsPratiques(): void
    {
        $category = LegalCategory::firstOrCreate([
            'name' => 'Décrets et arrêtés'
        ], [
            'description' => 'Textes réglementaires d\'application',
            'color' => '#0ea5e9',
            'icon' => 'file-text',
        ]);

        $doc = LegalDocument::create([
            'title' => 'Décret relatif aux marchés publics (extraits)',
            'summary' => 'Procédures passation et exécution des marchés publics',
            'content' => 'Le présent décret fixe les procédures applicables aux marchés publics et délégations de service public...',
            'type' => 'decret',
            'reference_number' => 'Décret MP-2021-001',
            'publication_date' => Carbon::create(2021, 5, 10),
            'effective_date' => Carbon::create(2021, 5, 10),
            'journal_officiel' => 'JO de la République de Côte d\'Ivoire',
            'status' => 'active',
            'category_id' => $category?->id
        ]);

        LegalArticle::create([
            'number' => 'MP-1',
            'title' => 'Appel d\'offres ouvert',
            'content' => 'L\'appel d\'offres ouvert est la procédure par laquelle toute personne peut soumissionner.',
            'document_id' => $doc->id,
            'sort_order' => 0
        ]);
    }

    private function getConstitutionContent(): string
    {
        return "PRÉAMBULE\n\nNous, Peuple de Côte d'Ivoire,\n\nAffirmons notre volonté de créer un État de droit et de démocratie pluraliste dans lequel les droits fondamentaux de l'homme, les libertés publiques et la dignité de la personne humaine sont garantis, protégés et promus ;\n\nRéaffirmons notre attachement aux principes et droits fondamentaux tels qu'ils sont définis dans la Déclaration universelle des Droits de l'Homme de 1948 et dans la Charte africaine des Droits de l'Homme et des Peuples de 1981 ;\n\nExprimons notre engagement à œuvrer pour la paix, la solidarité et l'unité entre les peuples du monde entier et ceux d'Afrique en particulier ;\n\nDéclarons que la présente Constitution est l'expression de notre volonté souveraine ;\n\nAdoptons solennellement la présente Constitution.";
    }
}
