<template>
  <LegalLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header Section -->
      <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-8">
          <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-2xl mb-4">
              <FileText class="w-8 h-8" />
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
              Procédures Administratives
            </h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
              Guide complet des procédures administratives en République de Côte d'Ivoire. 
              Découvrez les démarches, documents requis et étapes pour vos formalités.
            </p>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
          <div class="flex flex-col md:flex-row gap-4">
            <!-- Search Bar -->
            <div class="flex-1 relative">
              <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input 
                v-model="searchQuery"
                @input="handleSearch"
                type="text" 
                placeholder="Rechercher une procédure..."
                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            
            <!-- Category Filter -->
            <select 
              v-model="selectedCategory"
              @change="handleCategoryChange"
              class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Toutes les catégories</option>
              <option v-for="category in categories" :key="category.slug" :value="category.slug">
                {{ category.name }}
              </option>
            </select>

            <!-- Difficulty Filter -->
            <select 
              v-model="selectedDifficulty"
              @change="handleDifficultyChange"
              class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Toute difficulté</option>
              <option value="facile">Facile</option>
              <option value="moyen">Moyen</option>
              <option value="difficile">Difficile</option>
            </select>
          </div>
        </div>

        <!-- Procedures Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="procedure in filteredProcedures"
            :key="procedure.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-200 cursor-pointer group"
            @click="viewProcedure(procedure)"
          >
            <!-- Header -->
            <div class="p-6 pb-4">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                  <div class="flex items-center mb-2">
                    <component :is="getCategoryIcon(procedure.category)" class="w-5 h-5 text-blue-600 mr-2" />
                    <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                      {{ procedure.category }}
                    </span>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                    {{ procedure.title }}
                  </h3>
                  <p class="text-gray-600 text-sm line-clamp-2">
                    {{ procedure.description }}
                  </p>
                </div>
              </div>

              <!-- Badges -->
              <div class="flex items-center gap-2 mb-4">
                <span :class="getDifficultyBadge(procedure.difficulty)">
                  {{ getDifficultyText(procedure.difficulty) }}
                </span>
                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-700">
                  <Clock class="w-3 h-3 mr-1" />
                  {{ procedure.duration }}
                </span>
              </div>

              <!-- Steps Preview -->
              <div class="border-t border-gray-100 pt-4">
                <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                  <span>{{ procedure.steps.length }} étapes</span>
                  <span>{{ procedure.documents_count }} documents requis</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                  <MapPin class="w-4 h-4 mr-1" />
                  {{ procedure.location }}
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
              <div class="flex items-center justify-between">
                <div class="flex items-center text-sm text-gray-500">
                  <Eye class="w-4 h-4 mr-1" />
                  {{ procedure.views_count }} consultations
                </div>
                <div class="flex items-center text-blue-600 text-sm font-medium">
                  Voir les détails
                  <ChevronRight class="w-4 h-4 ml-1" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredProcedures.length === 0" class="text-center py-12">
          <FileText class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune procédure trouvée</h3>
          <p class="text-gray-500">
            {{ searchQuery ? 'Essayez d\'ajuster vos critères de recherche.' : 'Les procédures seront bientôt disponibles.' }}
          </p>
        </div>
      </div>
    </div>
  </LegalLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  FileText, 
  Search, 
  Clock, 
  MapPin, 
  Eye, 
  ChevronRight,
  Building2,
  CreditCard,
  Users,
  FileCheck,
  Home,
  Car
} from 'lucide-vue-next'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Reactive data
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedDifficulty = ref('')
const procedures = ref([
  {
    id: 1,
    title: "Création d'entreprise individuelle",
    description: "Guide complet pour créer votre entreprise individuelle en Côte d'Ivoire",
    category: "entreprise",
    difficulty: "moyen",
    duration: "2-3 semaines",
    location: "CEPICI - Centre de Promotion des Investissements",
    steps: [
      "Réservation de la dénomination sociale",
      "Dépôt du dossier au CEPICI",
      "Obtention du Numéro de Compte Contribuable",
      "Inscription au Registre de Commerce"
    ],
    documents_count: 8,
    views_count: 1250,
    slug: "creation-entreprise-individuelle"
  },
  {
    id: 2,
    title: "Obtention du passeport biométrique",
    description: "Procédure pour obtenir ou renouveler votre passeport biométrique ivoirien",
    category: "identite",
    difficulty: "facile",
    duration: "1-2 semaines",
    location: "Préfecture ou Sous-préfecture",
    steps: [
      "Constitution du dossier",
      "Prise de rendez-vous en ligne",
      "Dépôt du dossier et prise biométrique",
      "Retrait du passeport"
    ],
    documents_count: 5,
    views_count: 2180,
    slug: "passeport-biometrique"
  },
  {
    id: 3,
    title: "Demande de permis de construire",
    description: "Étapes pour obtenir un permis de construire pour votre projet immobilier",
    category: "urbanisme",
    difficulty: "difficile",
    duration: "3-6 mois",
    location: "Mairie ou Direction de l'Urbanisme",
    steps: [
      "Étude de faisabilité",
      "Constitution du dossier technique",
      "Dépôt de la demande",
      "Instruction du dossier",
      "Délivrance du permis"
    ],
    documents_count: 12,
    views_count: 890,
    slug: "permis-de-construire"
  },
  {
    id: 4,
    title: "Immatriculation de véhicule",
    description: "Procédure d'immatriculation d'un véhicule neuf ou d'occasion",
    category: "transport",
    difficulty: "moyen",
    duration: "3-5 jours",
    location: "Direction des Transports Terrestres",
    steps: [
      "Contrôle technique",
      "Constitution du dossier",
      "Dépôt à la DTT",
      "Paiement des taxes",
      "Remise de la carte grise"
    ],
    documents_count: 7,
    views_count: 1560,
    slug: "immatriculation-vehicule"
  },
  {
    id: 5,
    title: "Ouverture de compte bancaire",
    description: "Guide pour ouvrir un compte bancaire en Côte d'Ivoire",
    category: "finance",
    difficulty: "facile",
    duration: "1-3 jours",
    location: "Agence bancaire",
    steps: [
      "Choix de la banque",
      "Constitution du dossier",
      "Rendez-vous en agence",
      "Signature des documents",
      "Activation du compte"
    ],
    documents_count: 4,
    views_count: 3200,
    slug: "ouverture-compte-bancaire"
  }
])

const categories = ref([
  { name: "Entreprise", slug: "entreprise" },
  { name: "Identité", slug: "identite" },
  { name: "Urbanisme", slug: "urbanisme" },
  { name: "Transport", slug: "transport" },
  { name: "Finance", slug: "finance" }
])

// Computed
const filteredProcedures = computed(() => {
  let filtered = procedures.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(procedure =>
      procedure.title.toLowerCase().includes(query) ||
      procedure.description.toLowerCase().includes(query) ||
      procedure.category.toLowerCase().includes(query)
    )
  }

  if (selectedCategory.value) {
    filtered = filtered.filter(procedure => procedure.category === selectedCategory.value)
  }

  if (selectedDifficulty.value) {
    filtered = filtered.filter(procedure => procedure.difficulty === selectedDifficulty.value)
  }

  return filtered
})

// Methods
const handleSearch = () => {
  // Search is reactive through computed property
}

const handleCategoryChange = () => {
  // Filter is reactive through computed property
}

const handleDifficultyChange = () => {
  // Filter is reactive through computed property
}

const viewProcedure = (procedure: any) => {
  router.visit(`/procedures/${procedure.slug}`)
}

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

const getDifficultyBadge = (difficulty: string) => {
  const badges = {
    facile: 'inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-green-100 text-green-800',
    moyen: 'inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-yellow-100 text-yellow-800',
    difficile: 'inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-red-100 text-red-800'
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