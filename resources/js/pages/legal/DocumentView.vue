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
        
        <!-- Table of Contents Sidebar -->
        <aside class="lg:col-span-1">
          <div class="bg-white rounded-2xl p-6 sticky top-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Table des matières</h3>
            
            <!-- Document Info -->
            <div class="mb-6 p-4 bg-gray-50 rounded-xl">
              <div class="flex items-center mb-2">
                <span :class="`inline-block w-2 h-2 rounded-full mr-2 ${getTypeColor(document.type)}`"></span>
                <span class="text-sm font-medium text-gray-600 uppercase">{{ document.type }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ document.reference_number }}</p>
              <p class="text-sm text-gray-600">{{ formatDate(document.publication_date) }}</p>
            </div>

            <!-- Navigation -->
            <nav class="space-y-2">
              <a href="#content" class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                Contenu principal
              </a>
              
              <template v-if="document.sections && document.sections.length > 0">
                <div v-for="section in document.sections" :key="section.id">
                  <a 
                    :href="`#section-${section.id}`"
                    class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                  >
                    {{ section.number }} - {{ section.title }}
                  </a>
                  
                  <div v-if="section.children_sections" class="ml-4 mt-1 space-y-1">
                    <a 
                      v-for="subsection in section.children_sections"
                      :key="subsection.id"
                      :href="`#section-${subsection.id}`"
                      class="block px-3 py-2 text-xs text-gray-500 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                      {{ subsection.number }} - {{ subsection.title }}
                    </a>
                  </div>
                </div>
              </template>
              
              <template v-if="document.articles && document.articles.length > 0">
                <div class="border-t pt-2 mt-4">
                  <p class="px-3 py-1 text-xs font-medium text-gray-500 uppercase tracking-wide">Articles</p>
                  <div class="max-h-64 overflow-y-auto">
                    <a 
                      v-for="article in document.articles.slice(0, 20)"
                      :key="article.id"
                      :href="`#article-${article.id}`"
                      class="block px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                      Article {{ article.number }}
                    </a>
                  </div>
                  <p v-if="document.articles.length > 20" class="px-3 py-2 text-xs text-gray-500">
                    ... et {{ document.articles.length - 20 }} autres articles
                  </p>
                </div>
              </template>
            </nav>
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
              
               <div class="ml-6 flex flex-col gap-2">
                 <button
                   @click="viewPdf"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors"
                 >
                   <FileText class="w-4 h-4 mr-2" />
                   Voir le PDF
                 </button>

                 <button
                   @click="askAiAboutDocument"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors"
                 >
                   <MessageCircle class="w-4 h-4 mr-2" />
                   Poser une question
                 </button>

                 <button class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                   <Share class="w-4 h-4 mr-2" />
                   Partager
                 </button>
               </div>
            </div>
          </div>

          <!-- Document Content -->
          <div class="bg-white rounded-2xl p-8 shadow-sm">
            <div id="content" class="prose prose-lg max-w-none">
              
              <!-- Sections Structure -->
              <template v-if="document.sections && document.sections.length > 0">
                <div v-for="section in document.sections" :key="section.id" class="mb-12">
                  <div :id="`section-${section.id}`" class="scroll-mt-8">
                    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                      <div class="w-1 h-8 bg-indigo-600 rounded-full mr-4"></div>
                      <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ section.title }}</h2>
                        <p v-if="section.number" class="text-sm text-gray-600">{{ section.number }}</p>
                      </div>
                    </div>
                    
                    <div v-if="section.content" v-html="formatContent(section.content)" class="mb-6"></div>
                    
                    <!-- Section Articles -->
                    <div v-if="section.articles && section.articles.length > 0" class="space-y-6">
                      <article 
                        v-for="article in section.articles"
                        :key="article.id"
                        :id="`article-${article.id}`"
                        class="bg-gray-50 rounded-xl p-6 scroll-mt-8"
                      >
                        <div class="flex items-start mb-4">
                          <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-lg text-sm font-semibold mr-4 flex-shrink-0">
                            Art. {{ article.number }}
                          </div>
                          <div class="flex-1">
                            <h3 v-if="article.title" class="text-lg font-semibold text-gray-900 mb-2">{{ article.title }}</h3>
                            <div v-html="formatContent(article.content)" class="text-gray-800"></div>
                            <div v-if="article.commentary" class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                              <p class="text-sm text-blue-800 font-medium mb-1">Commentaire explicatif</p>
                              <div v-html="formatContent(article.commentary)" class="text-blue-700 text-sm"></div>
                            </div>
                          </div>
                        </div>
                      </article>
                    </div>

                    <!-- Subsections -->
                    <div v-if="section.children_sections && section.children_sections.length > 0" class="mt-8 ml-6">
                      <div v-for="subsection in section.children_sections" :key="subsection.id" class="mb-8">
                        <div :id="`section-${subsection.id}`" class="scroll-mt-8">
                          <h3 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                            <div class="w-0.5 h-6 bg-gray-400 rounded-full mr-3"></div>
                            {{ subsection.number }} - {{ subsection.title }}
                          </h3>
                          <div v-if="subsection.content" v-html="formatContent(subsection.content)" class="mb-4"></div>
                          
                          <!-- Subsection Articles -->
                          <div v-if="subsection.articles && subsection.articles.length > 0" class="space-y-4">
                            <article 
                              v-for="article in subsection.articles"
                              :key="article.id"
                              :id="`article-${article.id}`"
                              class="bg-gray-50 rounded-xl p-6 scroll-mt-8"
                            >
                              <div class="flex items-start">
                                <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-lg text-sm font-semibold mr-4 flex-shrink-0">
                                  Art. {{ article.number }}
                                </div>
                                <div class="flex-1">
                                  <h4 v-if="article.title" class="font-medium text-gray-900 mb-2">{{ article.title }}</h4>
                                  <div v-html="formatContent(article.content)" class="text-gray-800"></div>
                                </div>
                              </div>
                            </article>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Direct Articles (if no sections) -->
              <template v-else-if="document.articles && document.articles.length > 0">
                <div class="space-y-6">
                  <article 
                    v-for="article in document.articles"
                    :key="article.id"
                    :id="`article-${article.id}`"
                    class="bg-gray-50 rounded-xl p-6 scroll-mt-8"
                  >
                    <div class="flex items-start">
                      <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-lg text-sm font-semibold mr-4 flex-shrink-0">
                        Art. {{ article.number }}
                      </div>
                      <div class="flex-1">
                        <h3 v-if="article.title" class="text-lg font-semibold text-gray-900 mb-2">{{ article.title }}</h3>
                        <div v-html="formatContent(article.content)" class="text-gray-800"></div>
                        
                        <div v-if="article.commentary" class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                          <p class="text-sm text-blue-800 font-medium mb-1">Commentaire explicatif</p>
                          <div v-html="formatContent(article.commentary)" class="text-blue-700 text-sm"></div>
                        </div>
                      </div>
                    </div>
                  </article>
                </div>
              </template>

              <!-- Fallback: Raw Content -->
              <template v-else>
                <div v-html="formatContent(document.content)" class="text-gray-800"></div>
              </template>

            </div>
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
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ChevronRight, Calendar, Clock, Eye, MessageCircle, Share, X, FileText
} from 'lucide-vue-next'

// Props
const props = defineProps<{
  documentSlug: string
}>()

// Reactive data
const document = ref<any>({})
const showAiChat = ref(false)
const aiQuestion = ref('')

// Methods
const loadDocument = async () => {
  try {
    const response = await fetch(`/api/legal/documents/${props.documentSlug}`)
    const data = await response.json()
    document.value = data.data || {}
  } catch (error) {
    console.error('Error loading document:', error)
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

const formatContent = (content: string) => {
  if (!content) return ''
  return content.replace(/\n/g, '<br>').replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;')
}

const viewPdf = () => {
  router.visit(`/pdf/${props.documentSlug}`)
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

// Load data on mount
onMounted(() => {
  loadDocument()
})
</script>

<style>
.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
  font-weight: 600;
  color: rgb(17 24 39);
}

.prose p {
  color: rgb(31 41 55);
  line-height: 1.625;
}

.prose strong {
  font-weight: 600;
  color: rgb(17 24 39);
}

.prose em {
  font-style: italic;
  color: rgb(55 65 81);
}

.prose ul, .prose ol {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.prose li {
  color: rgb(31 41 55);
}

.scroll-mt-8 {
  scroll-margin-top: 2rem;
}
</style>