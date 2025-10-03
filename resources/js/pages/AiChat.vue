<template>
  <div class="h-screen flex flex-col">
    <!-- Header -->
    <header class="px-4 py-3 lg:py-4 border-b bg-white shadow-sm sticky top-0 z-50">
      <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center flex-1 min-w-0">
          <Link href="/" class="text-indigo-600 hover:text-indigo-800 mr-3 lg:mr-4 p-1 touch-target flex-shrink-0">
            <ArrowLeft class="w-4 h-4 lg:w-5 lg:h-5" />
          </Link>
          <div class="flex items-center min-w-0 flex-1">
            <div class="w-7 h-7 lg:w-8 lg:h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mr-2 lg:mr-3 flex-shrink-0">
              <Bot class="w-3 h-3 lg:w-4 lg:h-4 text-white" />
            </div>
            <div class="min-w-0 flex-1">
              <h1 class="text-base lg:text-lg font-semibold truncate">Assistant IA Juridique</h1>
              <p class="text-xs lg:text-sm text-muted-foreground truncate hidden sm:block">Spécialiste de la législation ivoirienne</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-1 lg:gap-3 flex-shrink-0">
          <button
            @click="clearChat"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors touch-target"
            title="Nouvelle conversation"
            aria-label="Nouvelle conversation"
          >
            <Plus class="w-4 h-4 lg:w-5 lg:h-5" />
          </button>

          <button
            @click="showHistory = !showHistory"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors touch-target lg:hidden"
            title="Historique des conversations"
            aria-label="Historique des conversations"
          >
            <History class="w-4 h-4 lg:w-5 lg:h-5" />
          </button>
          
          <button
            @click="showHistory = !showHistory"
            class="hidden lg:flex p-2 rounded-lg hover:bg-gray-100 transition-colors touch-target"
            title="Historique des conversations"
            aria-label="Historique des conversations"
          >
            <History class="w-4 h-4 lg:w-5 lg:h-5" />
          </button>
        </div>
      </div>
    </header>

    <div class="flex flex-1 overflow-hidden relative">
      <!-- Mobile Sidebar Overlay -->
      <div 
        v-if="showHistory" 
        class="lg:hidden fixed inset-0 bg-black/50 z-40" 
        @click="showHistory = false"
      ></div>
      
      <!-- Sidebar - Chat History -->
      <aside
        v-if="showHistory"
        class="w-80 lg:w-80 border-r flex flex-col bg-white absolute lg:relative inset-y-0 left-0 z-50 lg:z-auto transform transition-transform duration-200 ease-in-out mobile-scroll"
        :class="{
          'translate-x-0': showHistory,
          '-translate-x-full lg:translate-x-0': !showHistory
        }"
      >
        <div class="p-4 border-b flex items-center justify-between">
          <h2 class="text-base lg:text-lg font-semibold">Conversations récentes</h2>
          <button 
            @click="showHistory = false"
            class="lg:hidden p-1 hover:bg-gray-100 rounded-lg touch-target"
            aria-label="Fermer l'historique"
          >
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-2 mobile-scroll">
          <div
            v-for="session in chatHistory"
            :key="session.id"
            @click="loadChatSession(session); showHistory = false"
            class="p-3 rounded-lg border cursor-pointer transition-colors touch-target mobile-select"
            :class="{ 'bg-indigo-50 border-indigo-200': session.id === currentSessionId }"
          >
            <h3 class="font-medium text-sm mb-1 truncate">{{ session.title || 'Sans titre' }}</h3>
            <p class="text-xs text-muted-foreground">{{ formatDate(session.last_activity) }}</p>
          </div>
        </div>
      </aside>

      <!-- Main Chat Area -->
      <main class="flex-1 flex flex-col min-w-0">
        <!-- Messages Area -->
        <div
          ref="messagesContainer"
          class="flex-1 overflow-y-auto px-3 lg:px-4 py-4 lg:py-6 mobile-scroll"
        >
          <div class="max-w-4xl mx-auto space-y-4 lg:space-y-6">

            <!-- Welcome Message -->
            <div v-if="messages.length === 0" class="text-center py-8 lg:py-12 px-4">
              <div class="w-12 h-12 lg:w-16 lg:h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <Scale class="w-6 h-6 lg:w-8 lg:h-8 text-white" />
              </div>
              <h2 class="text-xl lg:text-2xl font-bold mb-2">Bonjour ! Comment puis-je vous aider ?</h2>
              <p class="text-sm lg:text-base mb-6 text-muted-foreground max-w-md mx-auto">Je suis votre assistant IA spécialisé dans la législation ivoirienne.</p>

              <!-- Suggested Questions -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 max-w-2xl mx-auto">
                <button
                  v-for="suggestion in suggestions"
                  :key="suggestion"
                  @click="sendSuggestion(suggestion)"
                  class="p-3 lg:p-4 text-left border rounded-xl transition-all hover:bg-gray-50 hover:border-indigo-200 touch-target mobile-scroll"
                >
                  <div class="flex items-start">
                    <MessageSquare class="w-4 h-4 text-indigo-600 mt-1 mr-3 flex-shrink-0" />
                    <span class="text-sm mobile-select leading-relaxed">{{ suggestion }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Chat Messages -->
            <div
              v-for="message in messages"
              :key="message.id"
              class="flex"
              :class="{ 'justify-end': message.role === 'user' }"
            >
              <div
                class="max-w-full lg:max-w-3xl w-full"
                :class="{ 'order-2': message.role === 'user' }"
              >
                <!-- User Message -->
                <div
                  v-if="message.role === 'user'"
                  class="bg-indigo-600 text-white rounded-2xl px-4 lg:px-6 py-3 lg:py-4 ml-8 lg:ml-12"
                >
                  <div class="whitespace-pre-wrap text-sm lg:text-base mobile-select">{{ message.content }}</div>
                </div>

                <!-- Assistant Message -->
                <div v-else class="flex">
                  <div class="w-7 h-7 lg:w-8 lg:h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mr-2 lg:mr-3 mt-1 flex-shrink-0">
                    <Bot class="w-3 h-3 lg:w-4 lg:h-4 text-white" />
                  </div>

                  <div class="flex-1 min-w-0">
                    <div class="border rounded-xl lg:rounded-2xl px-4 lg:px-6 py-3 lg:py-4 shadow-sm">
                      <div class="prose prose-sm max-w-none text-sm lg:text-base mobile-select">
                        <div v-html="formatMessage(message.content)"></div>
                      </div>

                      <!-- Referenced Documents -->
                      <div
                        v-if="message.metadata && message.metadata.cited_documents && message.metadata.cited_documents.length > 0"
                        class="mt-3 lg:mt-4 pt-3 lg:pt-4 border-t"
                      >
                        <p class="text-xs lg:text-sm font-medium mb-2">Documents référencés :</p>
                        <div class="space-y-2">
                          <a
                            v-for="doc in message.metadata.cited_documents"
                            :key="doc.id"
                            :href="`/documents/${doc.slug}`"
                            class="block p-2 lg:p-3 rounded-lg transition-colors hover:bg-gray-50 touch-target mobile-scroll"
                          >
                            <div class="flex items-center">
                              <span :class="`inline-block w-2 h-2 rounded-full mr-2 flex-shrink-0 ${getTypeColor(doc.type)}`"></span>
                              <span class="text-xs lg:text-sm font-medium mobile-select flex-1 min-w-0 truncate">{{ doc.title }}</span>
                            </div>
                            <p class="text-xs mt-1 text-muted-foreground mobile-select">{{ doc.reference_number }}</p>
                          </a>
                        </div>
                      </div>
                    </div>

                    <!-- Message Actions -->
                    <div class="flex items-center mt-2 space-x-2 lg:space-x-3">
                      <button
                        @click="copyMessage(message.content)"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors touch-target"
                        title="Copier"
                        aria-label="Copier le message"
                      >
                        <Copy class="w-3 h-3 lg:w-4 lg:h-4" />
                      </button>

                      <button
                        @click="likeMessage(message.id)"
                        class="text-green-600 p-2 rounded-lg hover:bg-green-50 transition-colors touch-target"
                        title="Utile"
                        aria-label="Marquer comme utile"
                      >
                        <ThumbsUp class="w-3 h-3 lg:w-4 lg:h-4" />
                      </button>

                      <button
                        @click="dislikeMessage(message.id)"
                        class="text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors touch-target"
                        title="Pas utile"
                        aria-label="Marquer comme pas utile"
                      >
                        <ThumbsDown class="w-3 h-3 lg:w-4 lg:h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="isTyping" class="flex">
              <div class="w-7 h-7 lg:w-8 lg:h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mr-2 lg:mr-3 mt-1">
                <Bot class="w-3 h-3 lg:w-4 lg:h-4 text-white" />
              </div>

              <div class="border rounded-xl lg:rounded-2xl px-4 lg:px-6 py-3 lg:py-4 shadow-sm">
                <div class="flex items-center space-x-2">
                  <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                  </div>
                  <span class="text-sm lg:text-base">L'assistant réfléchit...</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="border-t p-3 lg:p-4 bg-white">
          <div class="max-w-4xl mx-auto">
            <form @submit.prevent="sendMessage" class="flex gap-2 lg:gap-3">
              <div class="flex-1">
                <textarea
                  ref="messageInput"
                  v-model="currentMessage"
                  @keydown.enter.exact.prevent="sendMessage"
                  @keydown.enter.shift="onShiftEnter"
                  placeholder="Posez votre question sur la législation ivoirienne..."
                  class="w-full px-4 py-3 border rounded-xl lg:rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none text-base mobile-scroll touch-target"
                  rows="1"
                  :disabled="isTyping"
                  style="max-height: 120px;"
                ></textarea>
              </div>

              <button
                type="submit"
                :disabled="!currentMessage.trim() || isTyping"
                class="px-4 lg:px-6 py-3 bg-indigo-600 text-white rounded-xl lg:rounded-2xl hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center touch-target flex-shrink-0"
              >
                <Send class="w-4 h-4" />
                <span class="ml-2 text-sm hidden sm:inline">Envoyer</span>
              </button>
            </form>

            <p class="text-xs mt-2 text-center text-muted-foreground hidden lg:block">
              Appuyez sur Entrée pour envoyer, Shift+Entrée pour une nouvelle ligne
            </p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ArrowLeft, Bot, Scale, MessageSquare, MessageCircle, Send, Copy,
  ThumbsUp, ThumbsDown, Plus, History, X
} from 'lucide-vue-next'

interface Props {
  initialSessionId?: string
}

const props = defineProps<Props>()

// Reactive data
const messages = ref<any[]>([])
const currentMessage = ref('')
const isTyping = ref(false)
const currentSessionId = ref<string | null>(null)
const chatHistory = ref<any[]>([])
const showHistory = ref(false)
const messagesContainer = ref<HTMLElement>()
const messageInput = ref<HTMLTextAreaElement>()

const suggestions = [
  "Qu'est-ce que dit la Constitution ivoirienne sur les droits de l'homme ?",
  "Comment fonctionne le Code du travail en Côte d'Ivoire ?",
  "Quelles sont les sanctions prévues par le Code pénal ?",
  "Comment créer une entreprise selon la loi ivoirienne ?",
  "Quels sont les droits des femmes dans la législation ?"
]

// Methods
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const loadChatHistory = async () => {
  try {
    const response = await fetch('/api/ai/chat/sessions')
    const data = await response.json()
    chatHistory.value = data.data || []
  } catch (error) {
    console.error('Error loading chat history:', error)
  }
}

const createNewSession = async () => {
  try {
    const response = await fetch('/api/ai/chat/sessions', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' }
    })
    const data = await response.json()
    currentSessionId.value = data.data.session_id
    return data.data
  } catch (error) {
    console.error('Error creating session:', error)
    return null
  }
}

const loadChatSession = async (session: any) => {
  currentSessionId.value = session.session_id

  try {
    const response = await fetch(`/api/ai/chat/sessions/${session.session_id}/messages`)
    const data = await response.json()
    messages.value = data.data || []
    scrollToBottom()
  } catch (error) {
    console.error('Error loading session messages:', error)
  }
}

const sendMessage = async () => {
  if (!currentMessage.value.trim() || isTyping.value) return

  // Create session if needed
  if (!currentSessionId.value) {
    await createNewSession()
  }

  const userMessage = {
    id: Date.now(),
    role: 'user',
    content: currentMessage.value,
    sent_at: new Date()
  }

  messages.value.push(userMessage)
  const messageToSend = currentMessage.value
  currentMessage.value = ''
  isTyping.value = true

  scrollToBottom()

  try {
    const response = await fetch(`/api/ai/chat/sessions/${currentSessionId.value}/messages`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content: messageToSend })
    })

    const data = await response.json()

    if (data.data) {
      messages.value.push(data.data)
      scrollToBottom()
    }
  } catch (error) {
    console.error('Error sending message:', error)
    messages.value.push({
      id: Date.now() + 1,
      role: 'assistant',
      content: 'Désolé, je rencontre une erreur technique. Veuillez réessayer.',
      sent_at: new Date()
    })
  } finally {
    isTyping.value = false
    scrollToBottom()
  }
}

const sendSuggestion = (suggestion: string) => {
  currentMessage.value = suggestion
  sendMessage()
}

const clearChat = async () => {
  messages.value = []
  currentSessionId.value = null
  await loadChatHistory()
}

const formatMessage = (content: string) => {
  return content
    .replace(/\n/g, '<br>')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code class="px-1 rounded">$1</code>')
}

const getTypeColor = (type: string) => {
  const colors = {
    'constitution': 'bg-red-500',
    'loi': 'bg-indigo-500',
    'decret': 'bg-green-500',
    'arrete': 'bg-yellow-500',
    'code': 'bg-purple-500',
    'ordonnance': 'bg-blue-500'
  }
  return colors[type as keyof typeof colors] || 'bg-gray-500'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const copyMessage = async (content: string) => {
  try {
    await navigator.clipboard.writeText(content)
    // Show toast or feedback
  } catch (error) {
    console.error('Error copying message:', error)
  }
}

const likeMessage = (messageId: string) => {
  // Send feedback to backend
  console.log('Liked message:', messageId)
}

const dislikeMessage = (messageId: string) => {
  // Send feedback to backend
  console.log('Disliked message:', messageId)
}

const onShiftEnter = (event: KeyboardEvent) => {
  // Allow shift+enter for new line
  return true
}

// Auto-resize textarea
watch(currentMessage, () => {
  nextTick(() => {
    if (messageInput.value) {
      messageInput.value.style.height = 'auto'
      messageInput.value.style.height = messageInput.value.scrollHeight + 'px'
    }
  })
})

// Load data on mount
onMounted(async () => {
  await loadChatHistory()

  // Load initial session if provided
  if (props.initialSessionId) {
    const session = chatHistory.value.find(s => s.session_id === props.initialSessionId)
    if (session) {
      await loadChatSession(session)
    }
  }

  // Focus input
  nextTick(() => {
    if (messageInput.value) {
      messageInput.value.focus()
    }
  })
})
</script>

<style>
.prose code {
  background-color: #f3f4f6;
  padding: 2px 4px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.prose strong {
  font-weight: 600;
}

.prose em {
  font-style: italic;
}

.animate-bounce {
  animation: bounce 1.5s infinite;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-0.25rem);
  }
  60% {
    transform: translateY(-0.125rem);
  }
}
</style>