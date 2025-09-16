<template>
  <LegalLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Breadcrumb -->
      <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
          <nav class="flex items-center space-x-2 text-sm text-gray-500">
            <button @click="router.visit('/')" class="hover:text-gray-700">Accueil</button>
            <ChevronRight class="w-4 h-4" />
            <button @click="router.visit('/procedures')" class="hover:text-gray-700">Procédures</button>
            <ChevronRight class="w-4 h-4" />
            <span class="text-gray-900">{{ procedure?.title }}</span>
          </nav>
        </div>
      </div>

      <div class="container mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
              <div class="flex items-start justify-between mb-6">
                <div class="flex-1">
                  <div class="flex items-center mb-4">
                    <component :is="getCategoryIcon(procedure?.category)" class="w-6 h-6 text-blue-600 mr-3" />
                    <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                      {{ getCategoryName(procedure?.category) }}
                    </span>
                  </div>
                  <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ procedure?.title }}
                  </h1>
                  <p class="text-lg text-gray-600 mb-6">
                    {{ procedure?.description }}
                  </p>
                  
                  <!-- Meta Info -->
                  <div class="flex flex-wrap items-center gap-4">
                    <span :class="getDifficultyBadge(procedure?.difficulty)">
                      {{ getDifficultyText(procedure?.difficulty) }}
                    </span>
                    <div class="flex items-center text-gray-600">
                      <Clock class="w-4 h-4 mr-2" />
                      <span>{{ procedure?.duration }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                      <MapPin class="w-4 h-4 mr-2" />
                      <span>{{ procedure?.location }}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                      <Eye class="w-4 h-4 mr-2" />
                      <span>{{ procedure?.views_count }} consultations</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Steps -->
            <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <FileCheck class="w-6 h-6 mr-3 text-blue-600" />
                Étapes de la procédure
              </h2>
              
              <div class="space-y-6">
                <div 
                  v-for="(step, index) in procedure?.steps" 
                  :key="index"
                  class="flex items-start"
                >
                  <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold mr-4">
                    {{ index + 1 }}
                  </div>
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                      {{ step.title }}
                    </h3>
                    <p class="text-gray-600 mb-3">
                      {{ step.description }}
                    </p>
                    <div v-if="step.duration" class="flex items-center text-sm text-gray-500 mb-2">
                      <Clock class="w-4 h-4 mr-1" />
                      Durée estimée: {{ step.duration }}
                    </div>
                    <div v-if="step.cost" class="flex items-center text-sm text-gray-500">
                      <CreditCard class="w-4 h-4 mr-1" />
                      Coût: {{ step.cost }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Required Documents -->
            <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
              <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <FileText class="w-6 h-6 mr-3 text-blue-600" />
                Documents requis
              </h2>
              
              <div class="grid md:grid-cols-2 gap-4">
                <div 
                  v-for="(document, index) in procedure?.documents" 
                  :key="index"
                  class="flex items-start p-4 border border-gray-200 rounded-xl"
                >
                  <div class="flex-shrink-0 w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                    <FileText class="w-5 h-5 text-gray-600" />
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 mb-1">
                      {{ document.name }}
                    </h4>
                    <p class="text-sm text-gray-600 mb-2">
                      {{ document.description }}
                    </p>
                    <div class="flex items-center text-xs text-gray-500">
                      <span :class="document.required ? 'text-red-600' : 'text-orange-600'">
                        {{ document.required ? 'Obligatoire' : 'Optionnel' }}
                      </span>
                      <span v-if="document.copies" class="ml-2">
                        • {{ document.copies }} copie(s)
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tips and Warnings -->
            <div class="bg-white rounded-2xl shadow-sm p-8">
              <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <AlertCircle class="w-6 h-6 mr-3 text-blue-600" />
                Conseils et avertissements
              </h2>
              
              <div class="space-y-4">
                <div v-if="procedure?.tips?.length" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                  <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                    <Lightbulb class="w-4 h-4 mr-2" />
                    Conseils utiles
                  </h3>
                  <ul class="space-y-2">
                    <li v-for="(tip, index) in procedure?.tips" :key="index" class="text-sm text-blue-800">
                      • {{ tip }}
                    </li>
                  </ul>
                </div>

                <div v-if="procedure?.warnings?.length" class="bg-red-50 border border-red-200 rounded-xl p-4">
                  <h3 class="font-semibold text-red-900 mb-3 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-2" />
                    Points d'attention
                  </h3>
                  <ul class="space-y-2">
                    <li v-for="(warning, index) in procedure?.warnings" :key="index" class="text-sm text-red-800">
                      • {{ warning }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <!-- Quick Summary -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé rapide</h3>
              <div class="space-y-3">
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Durée:</span>
                  <span class="font-medium">{{ procedure?.duration }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Difficulté:</span>
                  <span class="font-medium">{{ getDifficultyText(procedure?.difficulty) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Étapes:</span>
                  <span class="font-medium">{{ procedure?.steps?.length }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Documents:</span>
                  <span class="font-medium">{{ procedure?.documents?.length }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                  <span class="text-gray-600">Coût estimé:</span>
                  <span class="font-medium">{{ procedure?.estimatedCost || 'Variable' }}</span>
                </div>
              </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact et localisation</h3>
              <div class="space-y-3">
                <div class="flex items-start">
                  <MapPin class="w-4 h-4 text-gray-400 mt-1 mr-2" />
                  <div class="text-sm">
                    <p class="font-medium text-gray-900">{{ procedure?.location }}</p>
                    <p class="text-gray-600">{{ procedure?.address || 'Adresse non spécifiée' }}</p>
                  </div>
                </div>
                <div v-if="procedure?.phone" class="flex items-center">
                  <Phone class="w-4 h-4 text-gray-400 mr-2" />
                  <span class="text-sm text-gray-600">{{ procedure.phone }}</span>
                </div>
                <div v-if="procedure?.email" class="flex items-center">
                  <Mail class="w-4 h-4 text-gray-400 mr-2" />
                  <span class="text-sm text-gray-600">{{ procedure.email }}</span>
                </div>
                <div v-if="procedure?.website" class="flex items-center">
                  <Globe class="w-4 h-4 text-gray-400 mr-2" />
                  <a :href="procedure.website" target="_blank" class="text-sm text-blue-600 hover:text-blue-800">
                    Site web officiel
                  </a>
                </div>
              </div>
            </div>

            <!-- Related Procedures -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Procédures similaires</h3>
              <div class="space-y-3">
                <div 
                  v-for="related in relatedProcedures" 
                  :key="related.id"
                  class="p-3 border border-gray-200 rounded-lg hover:border-blue-300 cursor-pointer transition-colors"
                  @click="router.visit(`/procedures/${related.slug}`)"
                >
                  <h4 class="font-medium text-gray-900 text-sm mb-1">{{ related.title }}</h4>
                  <p class="text-xs text-gray-600 line-clamp-2">{{ related.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </LegalLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  ChevronRight,
  FileText, 
  FileCheck,
  Clock, 
  MapPin, 
  Eye, 
  Building2,
  CreditCard,
  Users,
  Home,
  Car,
  AlertCircle,
  Lightbulb,
  AlertTriangle,
  Phone,
  Mail,
  Globe
} from 'lucide-vue-next'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Props
const props = defineProps<{
  procedureSlug: string
}>()

// Sample data - In real app, this would come from API
const procedure = ref({
  id: 1,
  title: "Création d'entreprise individuelle",
  description: "Guide complet pour créer votre entreprise individuelle en Côte d'Ivoire",
  category: "entreprise",
  difficulty: "moyen",
  duration: "2-3 semaines",
  location: "CEPICI - Centre de Promotion des Investissements",
  address: "Plateau, Avenue Chardy, Immeuble CEPICI, Abidjan",
  phone: "+225 20 20 00 20",
  email: "info@cepici.ci",
  website: "https://www.cepici.ci",
  views_count: 1250,
  estimatedCost: "150 000 - 300 000 FCFA",
  slug: "creation-entreprise-individuelle",
  steps: [
    {
      title: "Réservation de la dénomination sociale",
      description: "Vérifiez la disponibilité et réservez le nom de votre entreprise au Greffe du Tribunal de Commerce.",
      duration: "1-2 jours",
      cost: "15 000 FCFA"
    },
    {
      title: "Dépôt du dossier au CEPICI",
      description: "Rassemblez tous les documents requis et déposez votre dossier complet au guichet unique du CEPICI.",
      duration: "1 jour",
      cost: "50 000 FCFA"
    },
    {
      title: "Obtention du Numéro de Compte Contribuable (NCC)",
      description: "Votre NCC sera automatiquement généré lors de l'enregistrement de votre entreprise.",
      duration: "Immédiat",
      cost: "Inclus"
    },
    {
      title: "Inscription au Registre de Commerce",
      description: "Votre entreprise sera automatiquement inscrite au RCCM suite à la validation de votre dossier.",
      duration: "7-14 jours",
      cost: "Inclus"
    }
  ],
  documents: [
    {
      name: "Copie de la pièce d'identité",
      description: "CNI, Passeport ou Carte consulaire en cours de validité",
      required: true,
      copies: 2
    },
    {
      name: "Justificatif de domicile",
      description: "Facture d'électricité, d'eau ou attestation de domicile de moins de 3 mois",
      required: true,
      copies: 1
    },
    {
      name: "Formulaire de création d'entreprise",
      description: "Formulaire dûment rempli et signé, disponible au CEPICI",
      required: true,
      copies: 1
    },
    {
      name: "Certificat médical",
      description: "Certificat médical de moins de 3 mois pour certaines activités",
      required: false,
      copies: 1
    }
  ],
  tips: [
    "Préparez tous vos documents avant de vous rendre au CEPICI pour éviter les allers-retours",
    "Vérifiez la disponibilité du nom de votre entreprise en ligne avant de faire la demande",
    "Gardez des copies de tous les documents soumis pour vos archives",
    "Le paiement peut se faire par chèque certifié ou virement bancaire"
  ],
  warnings: [
    "La réservation du nom de l'entreprise est valable pendant 3 mois seulement",
    "Certaines activités nécessitent des autorisations spéciales avant l'enregistrement",
    "Les frais peuvent varier selon le type d'activité et le capital social",
    "Un dossier incomplet entraînera des retards dans le traitement"
  ]
})

const relatedProcedures = ref([
  {
    id: 2,
    title: "Création de SARL",
    description: "Procédure de création d'une Société à Responsabilité Limitée",
    slug: "creation-sarl"
  },
  {
    id: 3,
    title: "Obtention de licence d'exploitation",
    description: "Demande de licence pour exercer certaines activités commerciales",
    slug: "licence-exploitation"
  }
])

// Methods
const getCategoryIcon = (category: string) => {
  const icons = {
    entreprise: Building2,
    identite: Users,
    urbanisme: Home,
    transport: Car,
    finance: CreditCard
  }
  return icons[category as keyof typeof icons] || FileCheck
}

const getCategoryName = (category: string) => {
  const names = {
    entreprise: "Entreprise",
    identite: "Identité",
    urbanisme: "Urbanisme",
    transport: "Transport",
    finance: "Finance"
  }
  return names[category as keyof typeof names] || category
}

const getDifficultyBadge = (difficulty: string) => {
  const badges = {
    facile: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800',
    moyen: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    difficile: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800'
  }
  return badges[difficulty as keyof typeof badges] || badges.moyen
}

const getDifficultyText = (difficulty: string) => {
  const texts = {
    facile: 'Facile',
    moyen: 'Moyen',
    difficile: 'Difficile'
  }
  return texts[difficulty as keyof typeof texts] || 'Moyen'
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>