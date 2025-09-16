<template>
  <LegalLayout :breadcrumbs="[{label: 'Recherche'}]">
    <div class="container mx-auto px-4 py-8">
      
      <!-- Search Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Recherche dans la législation</h1>
        <p class="text-gray-600">Trouvez rapidement les textes juridiques qui vous intéressent</p>
      </div>

      <!-- Advanced Search Form -->
      <div class="bg-white rounded-2xl p-6 shadow-sm mb-8">
        <form @submit.prevent="performSearch" class="space-y-6">
          
          <!-- Main Search -->
          <div>
            <label for="query" class="block text-sm font-medium text-gray-700 mb-2">
              Termes de recherche
            </label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                id="query"
                v-model="searchForm.query"
                type="text"
                placeholder="Constitution, travail, article 15..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Filters Row -->
          <div class="grid md:grid-cols-3 gap-4">
            
            <!-- Document Type -->
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                Type de document
              </label>
              <select
                id="type"
                v-model="searchForm.type"
                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              >
                <option value="">Tous les types</option>
                <option value="constitution">Constitution</option>
                <option value="loi">Loi</option>
                <option value="code">Code</option>
                <option value="decret">Décret</option>
                <option value="arrete">Arrêté</option>
                <option value="ordonnance">Ordonnance</option>
              </select>
            </div>

            <!-- Category -->
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                Catégorie
              </label>
              <select
                id="category"
                v-model="searchForm.category_id"
                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              >
                <option value="">Toutes les catégories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Date Range -->
            <div>
              <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                Publié après
              </label>
              <input
                id="date_from"
                v-model="searchForm.date_from"
                type="date"
                class="w-full px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Search Button -->
          <div class="flex items-center justify-between">
            <button
              type="button"
              @click="resetFilters"
              class="text-gray-600 hover:text-gray-800 font-medium"
            >
              Réinitialiser les filtres
            </button>
            
            <button
              type="submit"
              :disabled="isSearching"
              class="bg-indigo-600 text-white px-8 py-3 rounded-xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center"
            >
              <Search class="w-4 h-4 mr-2" />
              {{ isSearching ? 'Recherche...' : 'Rechercher' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Search Results -->
      <div v-if="hasSearched">
        
        <!-- Results Stats -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-xl font-semibold text-gray-900">Résultats de recherche</h2>
            <p class="text-gray-600 mt-1" v-if="searchResults">
              {{ searchResults.total || 0 }} résultat(s) pour "{{ currentQuery }}"
            </p>
          </div>

          <!-- Sort Options -->
          <div class="flex items-center space-x-4">
            <label class="text-sm text-gray-600">Trier par :</label>
            <select 
              v-model="sortBy"
              @change="performSearch"
              class="px-3 py-1 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="relevance">Pertinence</option>
              <option value="date_desc">Date (récent)</option>
              <option value="date_asc">Date (ancien)</option>
              <option value="title">Titre</option>
            </select>
          </div>
        </div>

        <!-- Results List -->
        <div v-if="searchResults && searchResults.data" class="space-y-6">
          
          <!-- Document Results -->
          <div v-for="document in searchResults.data" :key="document.id" class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center mb-2">
                  <span :class="`inline-block w-2 h-2 rounded-full mr-2 ${getTypeColor(document.type)}`"></span>
                  <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ document.type }}</span>
                  <span class="mx-2 text-gray-300">•</span>
                  <span class="text-sm text-gray-600">{{ document.reference_number }}</span>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  <Link :href="`/documents/${document.slug}`" class="hover:text-indigo-600 transition-colors">
                    {{ document.title }}
                  </Link>
                </h3>
                
                <p v-if="document.summary" class="text-gray-600 mb-3 line-clamp-2">
                  {{ document.summary }}
                </p>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <div class="flex items-center">
                    <Calendar class="w-4 h-4 mr-1" />
                    {{ formatDate(document.publication_date) }}
                  </div>
                  
                  <div v-if="document.category" class="flex items-center">
                    <Tag class="w-4 h-4 mr-1" />
                    {{ document.category.name }}
                  </div>
                  
                  <div class="flex items-center">
                    <Eye class="w-4 h-4 mr-1" />
                    {{ document.views_count }} vues
                  </div>
                </div>
              </div>
              
              <div class="ml-4 flex flex-col gap-2">
                <Link 
                  :href="`/documents/${document.slug}`"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium text-center"
                >
                  Consulter
                </Link>
                
                <button 
                  @click="askAiAboutDocument(document)"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium"
                >
                  Poser une question
                </button>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="searchResults.last_page > 1" class="flex items-center justify-center space-x-2 pt-6">
            <button
              v-for="page in getPaginationPages()"
              :key="page"
              @click="goToPage(page)"
              class="px-3 py-2 rounded-lg transition-colors"
              :class="page === searchResults.current_page 
                ? 'bg-indigo-600 text-white' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            >
              {{ page }}
            </button>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-12">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <SearchX class="w-8 h-8 text-gray-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun résultat trouvé</h3>
          <p class="text-gray-600 mb-4">Essayez de modifier vos termes de recherche ou vos filtres.</p>
          <button 
            @click="resetFilters"
            class="text-indigo-600 hover:text-indigo-800 font-medium"
          >
            Réinitialiser la recherche
          </button>
        </div>
      </div>

      <!-- Search Suggestions (when no search has been performed) -->
      <div v-else class="bg-white rounded-2xl p-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recherches populaires</h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
          <button
            v-for="suggestion in searchSuggestions"
            :key="suggestion"
            @click="searchForm.query = suggestion; performSearch()"
            class="text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all"
          >
            <div class="flex items-center">
              <TrendingUp class="w-4 h-4 text-indigo-600 mr-2" />
              <span class="text-sm text-gray-700">{{ suggestion }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </LegalLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  Search, Calendar, Tag, Eye, SearchX, TrendingUp
} from 'lucide-vue-next'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Reactive data
const searchForm = ref({
  query: '',
  type: '',
  category_id: '',
  date_from: '',
  date_to: ''
})

const searchResults = ref<any>(null)
const categories = ref<any[]>([])
const isSearching = ref(false)
const hasSearched = ref(false)
const currentQuery = ref('')
const sortBy = ref('relevance')

const searchSuggestions = [
  "Constitution article 1",
  "Code du travail contrat",
  "Création d'entreprise",
  "Droits de l'homme",
  "Protection environnement",
  "Procédure pénale"
]

// Methods
const loadCategories = async () => {
  try {
    const response = await fetch('/api/legal/categories')
    const data = await response.json()
    categories.value = data.data || []
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const performSearch = async () => {
  if (!searchForm.value.query.trim()) return

  isSearching.value = true
  hasSearched.value = true
  currentQuery.value = searchForm.value.query

  try {
    const params = new URLSearchParams()
    
    Object.entries(searchForm.value).forEach(([key, value]) => {
      if (value && typeof value === 'string' && value.trim()) {
        params.append(key, value)
      }
    })

    if (sortBy.value !== 'relevance') {
      params.append('sort', sortBy.value)
    }

    const response = await fetch(`/api/legal/search/advanced?${params.toString()}`)
    const data = await response.json()
    searchResults.value = data
  } catch (error) {
    console.error('Error performing search:', error)
  } finally {
    isSearching.value = false
  }
}

const resetFilters = () => {
  searchForm.value = {
    query: '',
    type: '',
    category_id: '',
    date_from: '',
    date_to: ''
  }
  searchResults.value = null
  hasSearched.value = false
  currentQuery.value = ''
}

const getTypeColor = (type: string) => {
  const colors = {
    'constitution': 'bg-red-500',
    'loi': 'bg-blue-500',
    'decret': 'bg-green-500',
    'arrete': 'bg-yellow-500',
    'code': 'bg-purple-500',
    'ordonnance': 'bg-indigo-500'
  }
  return colors[type as keyof typeof colors] || 'bg-gray-500'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getPaginationPages = () => {
  if (!searchResults.value) return []
  
  const current = searchResults.value.current_page
  const last = searchResults.value.last_page
  const pages = []
  
  // Show pages around current page
  const start = Math.max(1, current - 2)
  const end = Math.min(last, current + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
}

const goToPage = (page: number) => {
  // Add pagination logic here
  console.log('Go to page:', page)
}

const askAiAboutDocument = (document: any) => {
  router.visit('/chat', {
    method: 'get',
    data: {
      document_context: {
        id: document.id,
        title: document.title,
        type: document.type
      }
    }
  })
}

// Initialize URL search params
onMounted(() => {
  loadCategories()
  
  // Check for URL parameters
  const urlParams = new URLSearchParams(window.location.search)
  const query = urlParams.get('q')
  
  if (query) {
    searchForm.value.query = query
    performSearch()
  }
})
</script>

<style>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>