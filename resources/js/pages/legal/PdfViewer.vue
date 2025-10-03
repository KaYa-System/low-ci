<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Breadcrumb -->
    <div class="bg-white border-b">
      <div class="container mx-auto px-4 py-4">
        <nav class="flex items-center space-x-2 text-sm">
          <a href="/" class="text-indigo-600 hover:text-indigo-800">Accueil</a>
          <ChevronRight class="w-4 h-4 text-gray-400" />
          <a v-if="document.category" :href="`/categories/${document.category.slug}`" class="text-indigo-600 hover:text-indigo-800">
            {{ document.category.name }}
          </a>
          <ChevronRight v-if="document.category" class="w-4 h-4 text-gray-400" />
          <span class="text-gray-600">{{ document.title }}</span>
        </nav>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid lg:grid-cols-4 gap-8">

        <!-- Sidebar with Document Info -->
        <aside class="lg:col-span-1">
          <div class="bg-white rounded-2xl p-6 sticky top-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations</h3>

            <!-- Document Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
              <div class="flex items-center mb-2">
                <span :class="`inline-block w-2 h-2 rounded-full mr-2 ${getTypeColor(document.type)}`"></span>
                <span class="text-sm font-medium text-gray-600 uppercase">{{ document.type }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ document.reference_number }}</p>
              <p class="text-sm text-gray-600">{{ formatDate(document.publication_date) }}</p>
            </div>

            <!-- View Toggle -->
            <div class="mb-6">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Mode d'affichage</h4>
              <div class="space-y-2">
                <button
                  @click="viewMode = 'pdf'"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                    viewMode === 'pdf'
                      ? 'bg-indigo-100 text-indigo-700 font-medium'
                      : 'text-gray-600 hover:bg-gray-50'
                  ]"
                >
                  <FileText class="w-4 h-4 inline mr-2" />
                  Lecteur PDF
                </button>
                <button
                  @click="viewMode = 'text'"
                  :class="[
                    'w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                    viewMode === 'text'
                      ? 'bg-indigo-100 text-indigo-700 font-medium'
                      : 'text-gray-600 hover:bg-gray-50'
                  ]"
                >
                  <AlignLeft class="w-4 h-4 inline mr-2" />
                  Vue texte
                </button>
              </div>
            </div>

            <!-- PDF Controls -->
            <div v-if="viewMode === 'pdf'" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Zoom</label>
                <select
                  v-model="zoomLevel"
                  @change="updateZoom"
                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
                  <option value="0.5">50%</option>
                  <option value="0.75">75%</option>
                  <option value="1">100%</option>
                  <option value="1.25">125%</option>
                  <option value="1.5">150%</option>
                  <option value="2">200%</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Page</label>
                <div class="flex items-center space-x-2">
                  <button
                    @click="prevPage"
                    :disabled="currentPage <= 1"
                    class="p-2 text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <ChevronLeft class="w-4 h-4" />
                  </button>
                  <span class="text-sm text-gray-600 flex-1 text-center">
                    {{ currentPage }} / {{ totalPages }}
                  </span>
                  <button
                    @click="nextPage"
                    :disabled="currentPage >= totalPages"
                    class="p-2 text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <ChevronRight class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="space-y-2 pt-4 border-t">
              <button
                @click="askAiAboutDocument"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors text-sm"
              >
                <MessageCircle class="w-4 h-4 mr-2" />
                Poser une question
              </button>

              <button class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors text-sm">
                <Download class="w-4 h-4 mr-2" />
                Télécharger PDF
              </button>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="lg:col-span-3">
          <!-- Document Header -->
          <div class="bg-white rounded-2xl p-8 mb-8 shadow-sm">
            <div class="flex items-start justify-between mb-6">
              <div class="flex-1">
                <div class="flex items-center mb-4">
                  <span :class="`inline-block w-3 h-3 rounded-full mr-3 ${getTypeColor(document.type)}`"></span>
                  <span class="text-sm font-medium text-gray-600 uppercase tracking-wide">{{ document.type }}</span>
                  <span class="mx-2 text-gray-300">•</span>
                  <span class="text-sm text-gray-600">{{ document.reference_number }}</span>
                </div>

                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ document.title }}</h1>

                <div v-if="document.summary" class="prose prose-lg text-gray-700 mb-6">
                  <p>{{ document.summary }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                  <div class="flex items-center">
                    <Calendar class="w-4 h-4 mr-2" />
                    Publié le {{ formatDate(document.publication_date) }}
                  </div>

                  <div v-if="document.effective_date" class="flex items-center">
                    <Clock class="w-4 h-4 mr-2" />
                    Effectif le {{ formatDate(document.effective_date) }}
                  </div>

                  <div class="flex items-center">
                    <Eye class="w-4 h-4 mr-2" />
                    {{ document.views_count }} vues
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- PDF Viewer -->
          <div v-if="viewMode === 'pdf'" class="bg-white rounded-2xl p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Lecteur PDF</h2>
              <div class="flex items-center space-x-2 text-sm text-gray-600">
                <FileText class="w-4 h-4" />
                <span>{{ document.pdf_file_name || 'Document PDF' }}</span>
              </div>
            </div>

            <div class="relative">
              <div
                ref="pdfContainer"
                class="border border-gray-200 rounded-xl overflow-hidden bg-gray-100"
                :style="{ height: pdfHeight + 'px' }"
              >
                <div v-if="loading" class="flex items-center justify-center h-full">
                  <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Chargement du PDF...</p>
                  </div>
                </div>

                <canvas
                  v-for="page in pdfPages"
                  :key="page.pageNumber"
                  :ref="el => setCanvasRef(el, page.pageNumber)"
                  class="shadow-sm mb-4 mx-auto bg-white"
                  :style="{ width: page.width + 'px', height: page.height + 'px' }"
                ></canvas>
              </div>
            </div>
          </div>

          <!-- Text View Redirect -->
          <div v-else class="bg-white rounded-2xl p-8 shadow-sm text-center">
            <FileText class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Vue texte disponible</h3>
            <p class="text-gray-600 mb-6">Consultez la version texte structurée de ce document avec table des matières interactive.</p>
            <button
              @click="goToTextView"
              class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors"
            >
              <AlignLeft class="w-5 h-5 mr-2" />
              Voir la version texte
            </button>
          </div>
        </main>
      </div>
    </div>

    <!-- AI Chat Modal -->
    <div v-if="showAiChat" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Poser une question sur ce document</h3>
          <button @click="showAiChat = false" class="text-gray-400 hover:text-gray-600">
            <X class="w-6 h-6" />
          </button>
        </div>

        <div class="mb-4">
          <textarea
            v-model="aiQuestion"
            placeholder="Posez votre question sur ce document..."
            class="w-full h-32 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
          ></textarea>
        </div>

        <div class="flex justify-end gap-3">
          <button @click="showAiChat = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
            Annuler
          </button>
          <button
            @click="submitAiQuestion"
            class="px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
          >
            Envoyer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ChevronRight, ChevronLeft, Calendar, Clock, Eye, MessageCircle, FileText, AlignLeft, Download, X
} from 'lucide-vue-next'

// Props
const props = defineProps<{
  documentSlug: string
}>()

// Reactive data
const document = ref<any>({})
const viewMode = ref('pdf')
const loading = ref(true)
const pdfDoc = ref<any>(null)
const pdfPages = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(0)
const zoomLevel = ref(1)
const pdfHeight = ref(800)
const showAiChat = ref(false)
const aiQuestion = ref('')
const canvasRefs = ref<any[]>([])

// Methods
const loadDocument = async () => {
  try {
    const response = await fetch(`/api/legal/documents/${props.documentSlug}`)
    const data = await response.json()
    document.value = data.data || {}

    if (document.value.pdf_url && viewMode.value === 'pdf') {
      await loadPDF()
    }
  } catch (error) {
    console.error('Error loading document:', error)
  }
}

const loadPDF = async () => {
  if (!document.value.pdf_url) return

  loading.value = true

  try {
    // Import PDF.js dynamically
    const pdfjsLib = await import('pdfjs-dist')

    // Set worker path
    pdfjsLib.GlobalWorkerOptions.workerSrc = '/node_modules/pdfjs-dist/build/pdf.worker.mjs'

    // Load PDF
    const loadingTask = pdfjsLib.getDocument(document.value.pdf_url)
    pdfDoc.value = await loadingTask.promise

    totalPages.value = pdfDoc.value.numPages
    await renderAllPages()
  } catch (error) {
    console.error('Error loading PDF:', error)
  } finally {
    loading.value = false
  }
}

const renderAllPages = async () => {
  pdfPages.value = []

  for (let pageNum = 1; pageNum <= totalPages.value; pageNum++) {
    const page = await pdfDoc.value.getPage(pageNum)
    const viewport = page.getViewport({ scale: zoomLevel.value })

    pdfPages.value.push({
      pageNumber: pageNum,
      width: viewport.width,
      height: viewport.height,
      page: page
    })
  }

  await nextTick()
  await renderVisiblePages()
}

const renderVisiblePages = async () => {
  for (let i = 0; i < pdfPages.value.length; i++) {
    const pageData = pdfPages.value[i]
    const canvas = canvasRefs.value[i]

    if (canvas && pageData.page) {
      const context = canvas.getContext('2d')
      const viewport = pageData.page.getViewport({ scale: zoomLevel.value })

      canvas.height = viewport.height
      canvas.width = viewport.width

      const renderContext = {
        canvasContext: context,
        viewport: viewport
      }

      await pageData.page.render(renderContext).promise
    }
  }
}

const setCanvasRef = (el: any, pageNumber: number) => {
  if (el) {
    canvasRefs.value[pageNumber - 1] = el
  }
}

const updateZoom = async () => {
  await renderAllPages()
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    scrollToPage(currentPage.value)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    scrollToPage(currentPage.value)
  }
}

const scrollToPage = (pageNum: number) => {
  const canvas = canvasRefs.value[pageNum - 1]
  if (canvas) {
    canvas.scrollIntoView({ behavior: 'smooth', block: 'start' })
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const askAiAboutDocument = () => {
  showAiChat.value = true
  aiQuestion.value = `J'ai une question concernant "${document.value.title}": `
}

const submitAiQuestion = () => {
  if (aiQuestion.value.trim()) {
    router.visit('/chat', {
      method: 'post',
      data: {
        question: aiQuestion.value,
        document_context: {
          id: document.value.id,
          title: document.value.title,
          type: document.value.type
        }
      }
    })
  }
}

const goToTextView = () => {
  router.visit(`/documents/${props.documentSlug}`)
}

// Watch for view mode changes
watch(viewMode, async (newMode) => {
  if (newMode === 'pdf' && document.value.pdf_url && !pdfDoc.value) {
    await loadPDF()
  }
})

// Load data on mount
onMounted(() => {
  loadDocument()
})
</script>

<style scoped>
canvas {
  max-width: 100%;
  height: auto;
}
</style>