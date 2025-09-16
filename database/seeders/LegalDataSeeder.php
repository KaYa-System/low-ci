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
    }

    private function getConstitutionContent(): string
    {
        return "PRÉAMBULE\n\nNous, Peuple de Côte d'Ivoire,\n\nAffirmons notre volonté de créer un État de droit et de démocratie pluraliste dans lequel les droits fondamentaux de l'homme, les libertés publiques et la dignité de la personne humaine sont garantis, protégés et promus ;\n\nRéaffirmons notre attachement aux principes et droits fondamentaux tels qu'ils sont définis dans la Déclaration universelle des Droits de l'Homme de 1948 et dans la Charte africaine des Droits de l'Homme et des Peuples de 1981 ;\n\nExprimons notre engagement à œuvrer pour la paix, la solidarité et l'unité entre les peuples du monde entier et ceux d'Afrique en particulier ;\n\nDéclarons que la présente Constitution est l'expression de notre volonté souveraine ;\n\nAdoptons solennellement la présente Constitution.";
    }
}
