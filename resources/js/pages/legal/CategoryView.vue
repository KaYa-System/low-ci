<template>
  <LegalLayout :breadcrumbs="breadcrumbItems">
    <div class="container mx-auto px-4 py-8">
      
      <!-- Category Header -->
      <div v-if="category" class="mb-8">
        <div class="flex items-start justify-between mb-6">
          <div class="flex items-center">
            <div 
              class="w-16 h-16 rounded-2xl flex items-center justify-center mr-4"
              :style="`background-color: ${category.color}20`"
            >
              <component 
                :is="getIcon(category.icon)" 
                class="w-8 h-8" 
                :style="`color: ${category.color}`" 
              />
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ category.name }}</h1>
              <p v-if="category.description" class="text-gray-600 text-lg">{{ category.description }}</p>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid md:grid-cols-4 gap-4 mb-8">
          <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="flex items-center">
              <FileText class="w-5 h-5 text-blue-600 mr-2" />
              <div>
                <p class="text-sm text-gray-600">Documents</p>
                <p class="text-xl font-semibold text-gray-900">{{ totalDocuments }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="flex items-center">
              <Calendar class="w-5 h-5 text-green-600 mr-2" />
              <div>
                <p class="text-sm text-gray-600">Mis à jour</p>
                <p class="text-sm font-semibold text-gray-900">{{ getLastUpdate() }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="flex items-center">
              <Eye class="w-5 h-5 text-purple-600 mr-2" />
              <div>
                <p class="text-sm text-gray-600">Vues totales</p>
                <p class="text-xl font-semibold text-gray-900">{{ getTotalViews() }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl p-4 border border-gray-200">
            <div class="flex items-center">
              <TrendingUp class="w-5 h-5 text-indigo-600 mr-2" />
              <div>
                <p class="text-sm text-gray-600">Sous-catégories</p>
                <p class="text-xl font-semibold text-gray-900">{{ category.children?.length || 0 }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sub-categories -->
      <div v-if="category?.children && category.children.length > 0" class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Sous-catégories</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link 
            v-for="subcategory in category.children"
            :key="subcategory.id"
            :href="`/categories/${subcategory.slug}`"
            class="bg-white rounded-xl p-4 border border-gray-200 hover:shadow-md transition-all group"
          >
            <div class="flex items-center mb-2">
              <div 
                class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
                :style="`background-color: ${subcategory.color || category.color}20`"
              >
                <component 
                  :is="getIcon(subcategory.icon || 'folder')" 
                  class="w-5 h-5" 
                  :style="`color: ${subcategory.color || category.color}`" 
                />
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ subcategory.name }}</h3>
                <p class="text-sm text-gray-600">{{ subcategory.documents?.length || 0 }} documents</p>
              </div>
            </div>
            <p v-if="subcategory.description" class="text-sm text-gray-600">{{ subcategory.description }}</p>
          </Link>
        </div>
      </div>

      <!-- Documents -->
      <div v-if="documents.length > 0">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-semibold text-gray-900">Documents</h2>
          
          <!-- Filters -->
          <div class="flex items-center space-x-4">
            <select 
              v-model="filters.type"
              @change="filterDocuments"
              class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="">Tous les types</option>
              <option value="constitution">Constitution</option>
              <option value="loi">Loi</option>
              <option value="code">Code</option>
              <option value="decret">Décret</option>
              <option value="arrete">Arrêté</option>
              <option value="ordonnance">Ordonnance</option>
            </select>
            
            <select 
              v-model="filters.sort"
              @change="filterDocuments"
              class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="date_desc">Plus récent</option>
              <option value="date_asc">Plus ancien</option>
              <option value="title">Titre</option>
              <option value="views">Plus consulté</option>
            </select>
          </div>
        </div>

        <!-- Documents Grid -->
        <div class="grid gap-6">
          <div 
            v-for="document in filteredDocuments"
            :key="document.id"
            class="bg-white rounded-xl p-6 border border-gray-200 hover:shadow-md transition-all"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center mb-3">
                  <span :class="`inline-block w-2 h-2 rounded-full mr-2 ${getTypeColor(document.type)}`"></span>
                  <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ document.type }}</span>
                  <span class="mx-2 text-gray-300">•</span>
                  <span class="text-sm text-gray-600">{{ document.reference_number }}</span>
                  
                  <span v-if="document.is_featured" class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <Star class="w-3 h-3 mr-1" />
                    Vedette
                  </span>
                </div>
                
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                  <Link :href="`/documents/${document.slug}`" class="hover:text-indigo-600 transition-colors">
                    {{ document.title }}
                  </Link>
                </h3>
                
                <p v-if="document.summary" class="text-gray-600 mb-4 line-clamp-2">
                  {{ document.summary }}
                </p>
                
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <div class="flex items-center">
                    <Calendar class="w-4 h-4 mr-1" />
                    {{ formatDate(document.publication_date) }}
                  </div>
                  
                  <div class="flex items-center">
                    <Eye class="w-4 h-4 mr-1" />
                    {{ document.views_count }} vues
                  </div>
                  
                  <div v-if="document.articles && document.articles.length > 0" class="flex items-center">
                    <FileText class="w-4 h-4 mr-1" />
                    {{ document.articles.length }} articles
                  </div>
                </div>
              </div>
              
              <div class="ml-6 flex flex-col gap-2">
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
                  Questions IA
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More -->
        <div v-if="hasMoreDocuments" class="text-center mt-8">
          <button 
            @click="loadMoreDocuments"
            :disabled="isLoadingMore"
            class="px-6 py-3 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium"
          >
            {{ isLoadingMore ? 'Chargement...' : 'Charger plus de documents' }}
          </button>
        </div>
      </div>

      <!-- No Documents -->
      <div v-else-if="!isLoading" class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <FileX class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun document disponible</h3>
        <p class="text-gray-600">Cette catégorie ne contient pas encore de documents.</p>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="w-8 h-8 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-gray-600">Chargement des documents...</p>
      </div>
    </div>
  </LegalLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  FileText, Calendar, Eye, TrendingUp, Star, FileX,
  Scale, Briefcase, Shield, Users, Calculator, Folder
} from 'lucide-vue-next'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Props
const props = defineProps<{
  categorySlug: string
}>()

// Reactive data
const category = ref<any>(null)
const documents = ref<any[]>([])
const isLoading = ref(true)
const isLoadingMore = ref(false)
const hasMoreDocuments = ref(false)

const filters = ref({
  type: '',
  sort: 'date_desc'
})

// Computed
const breadcrumbItems = computed(() => {
  const items = []
  if (category.value) {
    if (category.value.parent) {
      items.push({
        label: category.value.parent.name,
        href: `/categories/${category.value.parent.slug}`
      })
    }
    items.push({
      label: category.value.name
    })
  }
  return items
})

const totalDocuments = computed(() => {
  if (!category.value) return 0
  let total = documents.value.length
  if (category.value.children) {
    total += category.value.children.reduce((sum: number, child: any) => 
      sum + (child.documents?.length || 0), 0)
  }
  return total
})

const filteredDocuments = computed(() => {
  let filtered = [...documents.value]
  
  if (filters.value.type) {
    filtered = filtered.filter(doc => doc.type === filters.value.type)
  }
  
  // Sort
  switch (filters.value.sort) {
    case 'date_desc':
      filtered.sort((a, b) => new Date(b.publication_date).getTime() - new Date(a.publication_date).getTime())
      break
    case 'date_asc':
      filtered.sort((a, b) => new Date(a.publication_date).getTime() - new Date(b.publication_date).getTime())
      break
    case 'title':
      filtered.sort((a, b) => a.title.localeCompare(b.title))
      break
    case 'views':
      filtered.sort((a, b) => b.views_count - a.views_count)
      break
  }
  
  return filtered
})

// Methods
const loadCategory = async () => {
  isLoading.value = true
  
  try {
    const response = await fetch(`/api/legal/categories/${props.categorySlug}`)
    const data = await response.json()
    
    category.value = data.data
    documents.value = data.data.documents || []
  } catch (error) {
    console.error('Error loading category:', error)
  } finally {
    isLoading.value = false
  }
}

const loadMoreDocuments = async () => {
  // Implement pagination logic here
  isLoadingMore.value = true
  // ... 
  isLoadingMore.value = false
}

const filterDocuments = () => {
  // Already handled by computed property
}

const getIcon = (iconName: string) => {
  const icons = {
    scale: Scale,
    briefcase: Briefcase,
    shield: Shield,
    users: Users,
    calculator: Calculator,
    folder: Folder
  }
  return icons[iconName as keyof typeof icons] || Folder
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

const getLastUpdate = () => {
  if (!documents.value.length) return 'N/A'
  const latest = documents.value.reduce((latest, doc) => 
    new Date(doc.publication_date) > new Date(latest.publication_date) ? doc : latest
  )
  return formatDate(latest.publication_date)
}

const getTotalViews = () => {
  return documents.value.reduce((total, doc) => total + doc.views_count, 0).toLocaleString()
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

// Initialize
onMounted(() => {
  loadCategory()
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