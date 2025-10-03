<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Documents</h1>
            <p class="text-gray-600 mt-2">Gérez la constitution, les lois et autres documents juridiques</p>
          </div>
          <button
            @click="showCreateModal = true"
            class="bg-indigo-600 text-white px-6 py-3 rounded-xl hover:bg-indigo-700 transition-colors flex items-center"
          >
            <Plus class="w-5 h-5 mr-2" />
            Nouveau document
          </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-xl">
            <div class="flex items-center">
              <FileText class="w-8 h-8 text-blue-600 mr-3" />
              <div>
                <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
                <p class="text-sm text-blue-700">Total documents</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-xl">
            <div class="flex items-center">
              <CheckCircle class="w-8 h-8 text-green-600 mr-3" />
              <div>
                <p class="text-2xl font-bold text-green-900">{{ stats.published }}</p>
                <p class="text-sm text-green-700">Publiés</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-6 rounded-xl">
            <div class="flex items-center">
              <Clock class="w-8 h-8 text-yellow-600 mr-3" />
              <div>
                <p class="text-2xl font-bold text-yellow-900">{{ stats.drafts }}</p>
                <p class="text-sm text-yellow-700">Brouillons</p>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-6 rounded-xl">
            <div class="flex items-center">
              <Eye class="w-8 h-8 text-purple-600 mr-3" />
              <div>
                <p class="text-2xl font-bold text-purple-900">{{ stats.totalViews }}</p>
                <p class="text-sm text-purple-700">Vues totales</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-2xl p-6 mb-8 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher des documents..."
              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>

          <div class="flex gap-4">
            <select
              v-model="selectedType"
              class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="">Tous les types</option>
              <option value="constitution">Constitution</option>
              <option value="loi">Loi</option>
              <option value="decret">Décret</option>
              <option value="arrete">Arrêté</option>
              <option value="code">Code</option>
              <option value="ordonnance">Ordonnance</option>
            </select>

            <select
              v-model="selectedStatus"
              class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
              <option value="">Tous les statuts</option>
              <option value="published">Publié</option>
              <option value="draft">Brouillon</option>
              <option value="archived">Archivé</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Documents Table -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vues</th>
                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modifié</th>
                <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div :class="`w-8 h-8 rounded-lg flex items-center justify-center ${getTypeColor(doc.type)}`">
                        <FileText class="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ doc.title }}</div>
                      <div class="text-sm text-gray-500">{{ doc.reference_number }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getTypeBadgeColor(doc.type)">
                    {{ doc.type }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getStatusBadgeColor(doc.status)">
                    {{ doc.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ doc.views_count || 0 }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ formatDate(doc.updated_at) }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewDocument(doc)"
                      class="text-indigo-600 hover:text-indigo-900 p-1"
                      title="Voir"
                    >
                      <Eye class="w-4 h-4" />
                    </button>
                    <button
                      @click="editDocument(doc)"
                      class="text-blue-600 hover:text-blue-900 p-1"
                      title="Modifier"
                    >
                      <Edit class="w-4 h-4" />
                    </button>
                    <button
                      @click="duplicateDocument(doc)"
                      class="text-green-600 hover:text-green-900 p-1"
                      title="Dupliquer"
                    >
                      <Copy class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteDocument(doc)"
                      class="text-red-600 hover:text-red-900 p-1"
                      title="Supprimer"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ (currentPage - 1) * perPage + 1 }} à {{ Math.min(currentPage * perPage, totalDocuments) }} sur {{ totalDocuments }} résultats
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="prevPage"
                :disabled="currentPage <= 1"
                class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              <button
                @click="nextPage"
                :disabled="currentPage >= totalPages"
                class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div v-if="showCreateModal || editingDocument" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">
              {{ editingDocument ? 'Modifier le document' : 'Nouveau document' }}
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>

          <form @submit.prevent="saveDocument" class="space-y-6">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Numéro de référence</label>
                <input
                  v-model="form.reference_number"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select
                  v-model="form.type"
                  required
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                  <option value="constitution">Constitution</option>
                  <option value="loi">Loi</option>
                  <option value="decret">Décret</option>
                  <option value="arrete">Arrêté</option>
                  <option value="code">Code</option>
                  <option value="ordonnance">Ordonnance</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                <select
                  v-model="form.category_id"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                  <option value="">Sélectionner une catégorie</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select
                  v-model="form.status"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                  <option value="draft">Brouillon</option>
                  <option value="published">Publié</option>
                  <option value="archived">Archivé</option>
                </select>
              </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de publication</label>
                <input
                  v-model="form.publication_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date d'effet</label>
                <input
                  v-model="form.effective_date"
                  type="date"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                />
              </div>
            </div>

            <!-- Summary -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Résumé</label>
              <textarea
                v-model="form.summary"
                rows="3"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                placeholder="Résumé du document..."
              ></textarea>
            </div>

            <!-- PDF Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fichier PDF</label>
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition-colors">
                <FileText class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <div class="text-sm text-gray-600 mb-4">
                  Glissez-déposez votre fichier PDF ici, ou
                  <label class="text-indigo-600 hover:text-indigo-800 cursor-pointer">
                    parcourez
                    <input
                      ref="pdfInput"
                      type="file"
                      accept=".pdf"
                      @change="handlePdfUpload"
                      class="hidden"
                    />
                  </label>
                </div>
                <div v-if="form.pdf_file_name" class="text-sm text-green-600">
                  Fichier sélectionné : {{ form.pdf_file_name }}
                </div>
              </div>
            </div>

            <!-- Content -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Contenu (optionnel)</label>
              <textarea
                v-model="form.content"
                rows="10"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                placeholder="Contenu textuel du document..."
              ></textarea>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4 pt-6 border-t">
              <button
                type="button"
                @click="closeModal"
                class="px-6 py-3 text-gray-600 hover:text-gray-800 transition-colors"
              >
                Annuler
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ saving ? 'Enregistrement...' : (editingDocument ? 'Modifier' : 'Créer') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Plus, FileText, CheckCircle, Clock, Eye, Edit, Copy, Trash2, X
} from 'lucide-vue-next'

// Reactive data
const documents = ref<any[]>([])
const categories = ref<any[]>([])
const stats = ref({
  total: 0,
  published: 0,
  drafts: 0,
  totalViews: 0
})

const searchQuery = ref('')
const selectedType = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const perPage = ref(20)
const totalDocuments = ref(0)
const totalPages = ref(0)

const showCreateModal = ref(false)
const editingDocument = ref<any>(null)
const saving = ref(false)

const form = ref({
  title: '',
  reference_number: '',
  type: 'loi',
  category_id: '',
  status: 'draft',
  publication_date: '',
  effective_date: '',
  summary: '',
  content: '',
  pdf_file: null,
  pdf_file_name: ''
})

const pdfInput = ref<any>(null)

// Computed
const filteredDocuments = computed(() => {
  let filtered = documents.value

  if (searchQuery.value) {
    filtered = filtered.filter(doc =>
      doc.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      doc.reference_number?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  if (selectedType.value) {
    filtered = filtered.filter(doc => doc.type === selectedType.value)
  }

  if (selectedStatus.value) {
    filtered = filtered.filter(doc => doc.status === selectedStatus.value)
  }

  return filtered
})

// Methods
const loadDocuments = async () => {
  try {
    const response = await fetch('/api/admin/documents')
    const data = await response.json()
    documents.value = data.data || []
    totalDocuments.value = data.total || 0
    totalPages.value = Math.ceil(totalDocuments.value / perPage.value)

    // Calculate stats
    stats.value = {
      total: documents.value.length,
      published: documents.value.filter(d => d.status === 'published').length,
      drafts: documents.value.filter(d => d.status === 'draft').length,
      totalViews: documents.value.reduce((sum, d) => sum + (d.views_count || 0), 0)
    }
  } catch (error) {
    console.error('Error loading documents:', error)
  }
}

const loadCategories = async () => {
  try {
    const response = await fetch('/api/legal/categories')
    const data = await response.json()
    categories.value = data.data || []
  } catch (error) {
    console.error('Error loading categories:', error)
  }
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

const getTypeBadgeColor = (type: string) => {
  const colors = {
    'constitution': 'bg-red-100 text-red-800',
    'loi': 'bg-blue-100 text-blue-800',
    'decret': 'bg-green-100 text-green-800',
    'arrete': 'bg-yellow-100 text-yellow-800',
    'code': 'bg-purple-100 text-purple-800',
    'ordonnance': 'bg-indigo-100 text-indigo-800'
  }
  return colors[type as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const getStatusBadgeColor = (status: string) => {
  const colors = {
    'published': 'bg-green-100 text-green-800',
    'draft': 'bg-yellow-100 text-yellow-800',
    'archived': 'bg-gray-100 text-gray-800'
  }
  return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const viewDocument = (doc: any) => {
  router.visit(`/documents/${doc.slug}`)
}

const editDocument = (doc: any) => {
  editingDocument.value = doc
  form.value = {
    title: doc.title,
    reference_number: doc.reference_number || '',
    type: doc.type,
    category_id: doc.category_id || '',
    status: doc.status,
    publication_date: doc.publication_date ? doc.publication_date.split('T')[0] : '',
    effective_date: doc.effective_date ? doc.effective_date.split('T')[0] : '',
    summary: doc.summary || '',
    content: doc.content || '',
    pdf_file: null,
    pdf_file_name: doc.pdf_file_name || ''
  }
}

const duplicateDocument = async (doc: any) => {
  if (confirm('Êtes-vous sûr de vouloir dupliquer ce document ?')) {
    try {
      const response = await fetch(`/api/admin/documents/${doc.id}/duplicate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      if (response.ok) {
        await loadDocuments()
        alert('Document dupliqué avec succès !')
      } else {
        throw new Error('Erreur lors de la duplication')
      }
    } catch (error) {
      console.error('Error duplicating document:', error)
      alert('Erreur lors de la duplication du document')
    }
  }
}

const deleteDocument = async (doc: any) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer "${doc.title}" ? Cette action est irréversible.`)) {
    try {
      const response = await fetch(`/api/admin/documents/${doc.id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      })

      if (response.ok) {
        await loadDocuments()
        alert('Document supprimé avec succès !')
      } else {
        throw new Error('Erreur lors de la suppression')
      }
    } catch (error) {
      console.error('Error deleting document:', error)
      alert('Erreur lors de la suppression du document')
    }
  }
}

const handlePdfUpload = (event: any) => {
  const file = event.target.files[0]
  if (file && file.type === 'application/pdf') {
    form.value.pdf_file = file
    form.value.pdf_file_name = file.name
  } else {
    alert('Veuillez sélectionner un fichier PDF valide.')
    event.target.value = ''
  }
}

const saveDocument = async () => {
  saving.value = true

  try {
    const formData = new FormData()

    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key])
      }
    })

    const url = editingDocument.value
      ? `/api/admin/documents/${editingDocument.value.id}`
      : '/api/admin/documents'

    const method = editingDocument.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData
    })

    if (response.ok) {
      await loadDocuments()
      closeModal()
      alert(editingDocument.value ? 'Document modifié avec succès !' : 'Document créé avec succès !')
    } else {
      const error = await response.json()
      throw new Error(error.message || 'Erreur lors de la sauvegarde')
    }
  } catch (error) {
    console.error('Error saving document:', error)
    alert('Erreur lors de la sauvegarde du document')
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingDocument.value = null
  form.value = {
    title: '',
    reference_number: '',
    type: 'loi',
    category_id: '',
    status: 'draft',
    publication_date: '',
    effective_date: '',
    summary: '',
    content: '',
    pdf_file: null,
    pdf_file_name: ''
  }
  if (pdfInput.value) {
    pdfInput.value.value = ''
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    loadDocuments()
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    loadDocuments()
  }
}

// Load data on mount
onMounted(() => {
  loadDocuments()
  loadCategories()
})
</script>