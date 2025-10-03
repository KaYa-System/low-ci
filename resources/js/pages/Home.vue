<template>
    <LegalLayout>
        <div class="min-h-screen">
            <!-- Hero Section - Modern & Clean -->
            <section class="relative overflow-hidden min-h-[90vh] flex items-center" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/twcomponents/header.webp'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                <!-- Overlay for better text readability -->
                <div class="absolute inset-0 bg-black/20 backdrop-blur-[0.5px]"></div>
                <div class="container mx-auto px-4 py-12 sm:py-16 lg:py-24 relative z-10 w-full">
                    <div class="text-center space-y-6 lg:space-y-8">
                        <!-- Logo Animation -->
                        <div class="relative">
                            <div class="inline-flex items-center justify-center w-16 h-16 lg:w-20 lg:h-20 bg-primary rounded-2xl lg:rounded-3xl mb-4 lg:mb-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                                <Scale class="w-8 h-8 lg:w-10 lg:h-10 text-primary-foreground" />
                            </div>
                            <div class="absolute -inset-4 bg-primary/10 rounded-full blur-2xl -z-10 animate-pulse" />
                        </div>

                        <!-- Modern Typography -->
                        <div class="space-y-3 lg:space-y-4">
                            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-black tracking-tight">
                                <span class="text-foreground font-black text-white leading-tight">Législation</span>
                                <br />
                                <span class="bg-gradient-to-r from-primary via-purple-600 to-red-500 bg-clip-text text-transparent">
                                    Ivoirienne
                                </span>
                            </h1>

                            <p class="text-base sm:text-lg lg:text-xl text-white max-w-2xl lg:max-w-3xl mx-auto leading-relaxed px-4">
                                Découvrez, explorez et comprenez la législation ivoirienne avec notre plateforme moderne et intuitive
                            </p>
                        </div>

                        <!-- Enhanced Search Bar -->
                        <div class="max-w-2xl mx-auto px-4">
                            <div class="relative group">
                                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-purple-600 rounded-2xl lg:rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-300" />
                                <div class="relative bg-background border-2 border-border rounded-2xl lg:rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300">
                                    <Bot class="absolute left-4 lg:left-6 top-4 lg:top-6 text-muted-foreground w-4 h-4 lg:w-5 lg:h-5" />
                                    <textarea
                                        v-model="searchQuery"
                                        @focus="isSearchFocused = true"
                                        @blur="isSearchFocused = false"
                                        placeholder="Posez une question sur la législation ivoirienne..."
                                        rows="3"
                                        class="w-full pl-12 pr-4 lg:pl-16 lg:pr-32 py-4 lg:py-6 text-base lg:text-lg bg-transparent border-0 rounded-2xl lg:rounded-3xl focus:outline-none focus:ring-0 placeholder:text-muted-foreground/60 resize-none touch-target mobile-scroll"
                                    ></textarea>
                                    <Button
                                        @click="performSearch"
                                        class="mt-4 lg:mt-0 lg:absolute lg:right-2 lg:bottom-2 w-full lg:w-auto rounded-xl lg:rounded-2xl px-6 lg:px-8 py-3 shadow-md hover:shadow-lg transition-all duration-200 touch-target"
                                        size="lg"
                                    >
                                        <MessageCircle class="w-4 h-4 lg:mr-2" />
                                        <span class="ml-2 lg:ml-0">Demander à l'IA</span>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- AI Response Section -->
                        <div v-if="isLoadingResponse || showAiResponse">
                            <!-- Background Overlay -->
                            <div class="fixed inset-0 bg-black/20 backdrop-blur-sm z-30"></div>

                            <!-- Response Container - Mobile optimized -->
                            <div class="fixed inset-x-2 bottom-2 md:inset-x-4 md:bottom-4 lg:inset-x-8 lg:bottom-8 max-w-4xl mx-auto z-40">
                                <!-- Loading Animation -->
                                <div
                                    v-if="isLoadingResponse"
                                    class="bg-gradient-to-br from-background via-background/95 to-primary/5 backdrop-blur-sm border border-border rounded-2xl lg:rounded-3xl p-6 lg:p-8 shadow-xl animate-fade-in overflow-hidden relative"
                                >
                                    <!-- Background Legal Pattern -->
                                    <div class="absolute inset-0 opacity-5">
                                        <div class="absolute top-4 left-4 text-6xl transform rotate-12">⚖️</div>
                                        <div class="absolute top-8 right-8 text-4xl transform -rotate-12">📜</div>
                                        <div class="absolute bottom-6 left-8 text-5xl transform rotate-6">🏛️</div>
                                        <div class="absolute bottom-4 right-4 text-3xl transform -rotate-6">📚</div>
                                    </div>

                                    <!-- Main Loading Content -->
                                    <div class="relative z-10">
                                        <!-- Header with animated avatar -->
                                        <div class="flex items-center justify-center mb-6">
                                            <div class="relative">
                                                <!-- Animated thinking circles -->
                                                <div class="absolute -top-8 -left-2 w-3 h-3 bg-blue-400 rounded-full animate-ping" style="animation-delay: 0s; animation-duration: 2s;"></div>
                                                <div class="absolute -top-6 left-4 w-2 h-2 bg-purple-400 rounded-full animate-ping" style="animation-delay: 0.5s; animation-duration: 2s;"></div>
                                                <div class="absolute -top-4 -left-4 w-1 h-1 bg-pink-400 rounded-full animate-ping" style="animation-delay: 1s; animation-duration: 2s;"></div>

                                                <!-- Main avatar -->
                                                <div class="w-16 h-16 bg-gradient-to-br from-primary via-purple-600 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-300">
                                                    <Bot class="w-8 h-8 text-white animate-pulse" style="animation-duration: 1.5s;" />
                                                </div>

                                                <!-- Pulsing ring -->
                                                <div class="absolute inset-0 bg-gradient-to-r from-primary to-purple-600 rounded-2xl animate-ping opacity-20" style="animation-duration: 3s;"></div>
                                            </div>
                                        </div>

                                        <!-- Animated Legal Thinking Process -->
                                        <div class="text-center space-y-4">
                                            <h3 class="text-lg lg:text-xl font-bold text-foreground mb-2">🧠 Analyse juridique en cours...</h3>

                                            <!-- Thinking steps animation -->
                                            <div class="space-y-3 max-w-md mx-auto">
                                                <div class="flex items-center space-x-3 text-sm text-muted-foreground animate-fade-in" style="animation-delay: 0s;">
                                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                                    <span>📖 Consultation des textes législatifs...</span>
                                                </div>
                                                <div class="flex items-center space-x-3 text-sm text-muted-foreground animate-fade-in" style="animation-delay: 1s;">
                                                    <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                                                    <span>⚖️ Analyse de la jurisprudence...</span>
                                                </div>
                                                <div class="flex items-center space-x-3 text-sm text-muted-foreground animate-fade-in" style="animation-delay: 2s;">
                                                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-pulse"></div>
                                                    <span>🎯 Recherche de précédents...</span>
                                                </div>
                                                <div class="flex items-center space-x-3 text-sm text-muted-foreground animate-fade-in" style="animation-delay: 3s;">
                                                    <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></div>
                                                    <span>✨ Synthèse de la réponse...</span>
                                                </div>
                                            </div>

                                            <!-- Fun legal quotes rotation -->
                                            <div class="mt-6 p-4 bg-muted/20 rounded-xl border border-border/30">
                                                <div class="text-xs italic text-muted-foreground text-center legal-quote-animation">
                                                    <div class="quote-item" style="animation-delay: 0s;">"La justice est la vérité en action" - Victor Hugo</div>
                                                    <div class="quote-item" style="animation-delay: 4s;">"Le droit est l'art du bon et de l'équitable" - Ulpien</div>
                                                    <div class="quote-item" style="animation-delay: 8s;">"Nul n'est censé ignorer la loi" - Principe juridique</div>
                                                    <div class="quote-item" style="animation-delay: 12s;">"La loi est dure, mais c'est la loi" - Locution latine</div>
                                                </div>
                                            </div>

                                            <!-- Progress animation -->
                                            <div class="mt-4 space-y-2">
                                                <div class="flex justify-center space-x-1">
                                                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0s;"></div>
                                                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                                                    <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                                                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.6s;"></div>
                                                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0.8s;"></div>
                                                </div>

                                                <!-- Fun waiting messages -->
                                                <div class="text-center space-y-1">
                                                    <p class="text-xs text-muted-foreground waiting-message" style="animation-delay: 0s;">Patience... les meilleures réponses juridiques prennent du temps ! 🕰️</p>
                                                    <div class="text-xs text-muted-foreground/80 italic waiting-tips">
                                                        <div class="tip-item" style="animation-delay: 5s;">💡 Astuce: Les réponses de l'IA sont à titre informatif uniquement</div>
                                                        <div class="tip-item" style="animation-delay: 10s;">⚖️ Bon à savoir: Consultez toujours un professionnel pour vos cas spécifiques</div>
                                                        <div class="tip-item" style="animation-delay: 15s;">📚 Le saviez-vous? La base contient plus de 1000 documents juridiques</div>
                                                        <div class="tip-item" style="animation-delay: 20s;">✨ Info: L'IA analyse plusieurs sources avant de répondre</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- AI Response -->
                                <div
                                    v-else-if="showAiResponse && aiResponse"
                                    class="bg-gradient-to-b from-background via-background to-muted/20 backdrop-blur-md border border-border/60 rounded-xl md:rounded-2xl lg:rounded-3xl shadow-2xl animate-slide-up overflow-hidden max-h-[85vh] md:max-h-[80vh] flex flex-col ai-response-fixed"
                                    dir="ltr"
                                    @click.stop
                                >
                                    <!-- Header with gradient background -->
                                    <div class="bg-gradient-to-r from-primary/5 via-purple-500/5 to-pink-500/5 px-3 md:px-6 lg:px-8 py-3 md:py-4 lg:py-6 border-b border-border/30">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3 lg:space-x-4 flex-1 min-w-0">
                                                <div class="relative flex-shrink-0">
                                                    <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-primary via-purple-600 to-pink-500 rounded-xl lg:rounded-2xl flex items-center justify-center shadow-lg">
                                                        <Bot class="w-5 h-5 lg:w-6 lg:h-6 text-white" />
                                                    </div>
                                                    <div class="absolute -top-1 -right-1 w-3 h-3 lg:w-4 lg:h-4 bg-green-400 rounded-full border-2 border-background animate-pulse"></div>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <h3 class="text-lg lg:text-xl font-bold text-foreground truncate">Assistant IA Juridique</h3>
                                                    <p class="text-xs lg:text-sm text-muted-foreground font-medium">Réponse intelligente et précise</p>
                                                    <div v-if="aiResponse.message.metadata && aiResponse.message.metadata.stream" class="mt-1">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <div class="w-1.5 h-1.5 bg-blue-400 rounded-full mr-1.5 animate-pulse"></div>
                                                            Streaming activé
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <Button
                                                @click="closeAiResponse"
                                                variant="ghost"
                                                size="sm"
                                                class="rounded-full hover:bg-muted/80 transition-all duration-200 hover:scale-105 touch-target flex-shrink-0"
                                                :aria-label="'Fermer la réponse'"
                                            >
                                                <X class="w-4 h-4 lg:w-5 lg:h-5" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- Scrollable Content -->
                                    <div class="flex-1 overflow-y-auto ai-response-content px-3 md:px-6 lg:px-8 py-3 md:py-4 lg:py-6">
                                        <div class="space-y-4 lg:space-y-6">
                                            <!-- User Question -->
                                            <div class="bg-gradient-to-r from-muted/40 to-muted/20 rounded-xl lg:rounded-2xl p-4 lg:p-5 border border-border/40">
                                                <div class="flex items-start space-x-3">
                                                    <div class="w-6 h-6 lg:w-8 lg:h-8 bg-primary/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                                        <div class="w-2 h-2 lg:w-3 lg:h-3 bg-primary rounded-full"></div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-2">Votre question</p>
                                                        <p class="text-sm lg:text-base text-foreground font-medium leading-relaxed mobile-select">{{ aiResponse.query }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- AI Answer -->
                                            <div class="bg-card/50 rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-border/30 shadow-sm">
                                                <div class="flex items-start space-x-3 lg:space-x-4">
                                                    <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-br from-primary to-purple-600 rounded-lg lg:rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                                        <Bot class="w-4 h-4 lg:w-5 lg:h-5 text-white" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="prose prose-sm max-w-none text-foreground leading-relaxed mobile-select">
                                                            <div v-html="formatAiMessage(aiResponse.message.content)" class="text-sm lg:text-base text-justify"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Referenced Documents -->
                                            <div
                                                v-if="aiResponse.message.metadata && aiResponse.message.metadata.cited_documents && aiResponse.message.metadata.cited_documents.length > 0"
                                                class="bg-gradient-to-r from-blue-50/50 to-indigo-50/50 dark:from-blue-950/20 dark:to-indigo-950/20 rounded-xl md:rounded-2xl p-3 md:p-5 border border-blue-200/30 dark:border-blue-800/30"
                                            >
                                                <div class="flex items-center space-x-2 mb-3 md:mb-4">
                                                    <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Documents référencés</p>
                                                </div>
                                                <!-- Mobile: 1 column, Medium+: 2 columns -->
                                                <div class="grid gap-2 md:gap-3 md:grid-cols-2">
                                                    <a
                                                        v-for="doc in aiResponse.message.metadata.cited_documents"
                                                        :key="doc.id"
                                                        :href="`/documents/${doc.slug}`"
                                                        class="group block p-3 md:p-4 bg-white/60 dark:bg-card/60 rounded-lg md:rounded-xl border border-border/40 hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-md transition-all duration-200 active:scale-[0.98] md:hover:scale-[1.02]"
                                                    >
                                                        <div class="flex items-start space-x-2 md:space-x-3">
                                                            <span :class="`inline-flex w-3 h-3 rounded-full mt-1 flex-shrink-0 ${getTypeColor(doc.type)}`"></span>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-xs md:text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 leading-tight md:leading-normal">{{ doc.title }}</p>
                                                                <p class="text-xs text-muted-foreground mt-1 font-mono truncate">{{ doc.reference_number }}</p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Discrete Actions Footer -->
                                    <div class="flex-shrink-0 px-3 md:px-6 lg:px-8 py-2 md:py-3 border-t border-border/20">
                                        <div class="flex flex-col gap-2 md:gap-3">
                                            <!-- Mobile: Stack buttons vertically, Desktop: Horizontal -->
                                            <div class="flex flex-col md:flex-row gap-2 md:gap-3 md:items-center md:justify-end">
                                                <Button
                                                    @click="continueToChat"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="md:w-auto text-xs text-muted-foreground hover:text-foreground transition-colors duration-200 px-3 py-2"
                                                >
                                                    <MessageCircle class="w-3 h-3 mr-1.5" />
                                                    <span>Continuer</span>
                                                </Button>
                                                <Button
                                                    @click="copyResponse"
                                                    variant="ghost"
                                                    size="sm"
                                                    class="md:w-auto text-xs text-muted-foreground hover:text-foreground transition-colors duration-200 px-3 py-2"
                                                >
                                                    <Copy class="w-3 h-3 mr-1.5" />
                                                    <span>Copier</span>
                                                </Button>
                                            </div>
                                            <!-- Disclaimer avec meilleur responsive -->
                                            <div class="text-xs text-muted-foreground bg-muted/50 px-2 md:px-3 py-2 rounded-lg md:rounded-full">
                                                <div class="flex items-center justify-center space-x-1 text-center">
                                                    <div class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse flex-shrink-0"></div>
                                                    <span class="leading-tight text-center">Réponse générée par IA - Vérifiez auprès d'un professionnel</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Access Pills -->
                        <div class="flex flex-wrap justify-center gap-2 lg:gap-3 px-4">
                            <Button
                                v-for="category in featuredCategories"
                                :key="category.slug"
                                @click="browseCategory(category)"
                                variant="outline"
                                size="sm"
                                class="rounded-full hover:scale-105 transition-all duration-200 hover:shadow-md touch-target text-sm lg:text-base px-3 lg:px-4 py-2 lg:py-2.5 no-select"
                            >
                                <component :is="category.icon" class="w-3 h-3 lg:w-4 lg:h-4 mr-1 lg:mr-2" />
                                <span class="truncate">{{ category.name }}</span>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </LegalLayout>
</template>


<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { Scale, Search, Eye, MessageCircle, Briefcase, Shield, Users, Calculator, Bot, X, Copy } from 'lucide-vue-next'
import { marked } from 'marked'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Modern UI Components
import { Button } from '@/components/ui/button'


// Reactive data with enhanced UX
const searchQuery = ref('')
const isSearchFocused = ref(false)
const featuredDocuments = ref([])
const categories = ref([])
const featuredCategories = ref([
    { name: 'Constitution', slug: 'constitution', icon: Scale },
    { name: 'Code du travail', slug: 'code-du-travail', icon: Users },
    { name: 'Code pénal', slug: 'code-penal', icon: Shield },
    { name: 'Droit commercial', slug: 'droit-commercial', icon: Briefcase }
])

// AI Response state
const aiResponse = ref(null)
const isLoadingResponse = ref(false)
const showAiResponse = ref(false)

// Methods
const loadData = async () => {
    try {
        // Load featured documents
        const featuredResponse = await fetch('/api/legal/documents/featured')
        const featuredData = await featuredResponse.json()
        featuredDocuments.value = featuredData.data || []

        // Load categories
        const categoriesResponse = await fetch('/api/legal/categories')
        const categoriesData = await categoriesResponse.json()
        categories.value = categoriesData.data || []
    } catch (error) {
        console.error('Error loading data:', error)
    }
}

const performSearch = async () => {
    if (!searchQuery.value.trim()) return

    // Reset previous response
    aiResponse.value = null
    showAiResponse.value = false
    isLoadingResponse.value = true

    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        console.log('CSRF Token:', csrfToken)

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json'
        }

        console.log('Creating session...')
        // Create new AI chat session
        const sessionResponse = await fetch('/api/ai/chat/sessions', {
            method: 'POST',
            headers: headers
        })

        console.log('Session response status:', sessionResponse.status)
        console.log('Session response ok:', sessionResponse.ok)

        if (!sessionResponse.ok) {
            const errorText = await sessionResponse.text()
            console.error('Session response error:', errorText)
            throw new Error(`Session creation failed: ${sessionResponse.status} - ${errorText}`)
        }

        const sessionData = await sessionResponse.json()
        console.log('Session data:', sessionData)
        const sessionId = sessionData.data?.session_id

        if (!sessionId) {
            throw new Error('No session ID in response')
        }

        console.log('Sending message to session:', sessionId)
        // Send the search query as first message
        const messageResponse = await fetch(`/api/ai/chat/sessions/${sessionId}/messages`, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({ content: searchQuery.value })
        })

        console.log('Message response status:', messageResponse.status)

        if (!messageResponse.ok) {
            const errorText = await messageResponse.text()
            console.error('Message response error:', errorText)
            throw new Error(`Message send failed: ${messageResponse.status} - ${errorText}`)
        }

        // Normal JSON response
        const messageData = await messageResponse.json()
        console.log('Message data:', messageData)
        console.log('Message data.data:', messageData.data)

        // Store the response
        aiResponse.value = {
            sessionId: sessionId,
            message: messageData.data,
            query: searchQuery.value
        }

        console.log('aiResponse set to:', aiResponse.value)
        console.log('Setting showAiResponse to true')

        // Show response with animation
        isLoadingResponse.value = false
        showAiResponse.value = true

        console.log('isLoadingResponse:', isLoadingResponse.value)
        console.log('showAiResponse:', showAiResponse.value)

        // Force re-render
        await nextTick()
        console.log('After nextTick - showAiResponse:', showAiResponse.value)

    } catch (error) {
        console.error('Error creating AI session:', error)
        console.error('Error details:', {
            message: error.message,
            stack: error.stack
        })
        isLoadingResponse.value = false

        // For debugging, don't fallback immediately - show alert instead
        alert(`Erreur API: ${error.message}\n\nVérifiez la console pour plus de détails.`)

        // Fallback to regular search
        // router.visit('/search', {
        //   method: 'get',
        //   data: { q: searchQuery.value }
        // })
    }
}
const browseCategory = (category: any) => {
    router.visit(`/categories/${category.slug}`)
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

// AI Response methods
const closeAiResponse = () => {
    showAiResponse.value = false
    aiResponse.value = null
    searchQuery.value = ''
    // Re-enable body scroll
    document.body.style.overflow = ''
}

const continueToChat = () => {
    if (aiResponse.value) {
        router.visit(`/chat/${aiResponse.value.sessionId}`)
    }
}

const copyResponse = async () => {
    if (aiResponse.value) {
        try {
            await navigator.clipboard.writeText(aiResponse.value.message.content)
            // Could add a toast notification here
        } catch (error) {
            console.error('Error copying response:', error)
        }
    }
}

const formatAiMessage = (content: string) => {
    // Configure marked for inline rendering
    marked.setOptions({
        breaks: true,
        gfm: true
    })
    return marked.parse(content)
}

// Watch for popup state changes to control body scroll
watch([showAiResponse, isLoadingResponse], ([showResponse, isLoading]) => {
    if (showResponse || isLoading) {
        // Disable body scroll when popup is visible
        document.body.style.overflow = 'hidden'
    } else {
        // Re-enable body scroll when popup is closed
        document.body.style.overflow = ''
    }
})

// Load data on mount
onMounted(() => {
    loadData()
})

// Cleanup on unmount
onUnmounted(() => {
    // Make sure to re-enable scroll when component is unmounted
    document.body.style.overflow = ''
})
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* AI Response Animations */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.4s ease-out;
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

/* AI Response Enhancements */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.text-justify {
    text-align: justify;
    text-justify: inter-word;
}

/* RTL Support */
[dir="rtl"] .space-x-2 > * + * {
    margin-left: 0;
    margin-right: 0.5rem;
}

[dir="rtl"] .space-x-3 > * + * {
    margin-left: 0;
    margin-right: 0.75rem;
}

[dir="rtl"] .space-x-4 > * + * {
    margin-left: 0;
    margin-right: 1rem;
}

/* Enhanced animations */
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-slide-up {
    animation: slide-up 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Glass morphism effect */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Elegant gray levels instead of shadows */
.ai-response-card {
    background: linear-gradient(135deg,
    hsl(var(--background)) 0%,
    hsl(var(--muted) / 0.3) 50%,
    hsl(var(--background)) 100%
    );
}

.question-card {
    background: linear-gradient(135deg,
    hsl(var(--muted) / 0.4) 0%,
    hsl(var(--muted) / 0.2) 100%
    );
}

.answer-card {
    background: linear-gradient(135deg,
    hsl(var(--card) / 0.6) 0%,
    hsl(var(--card) / 0.4) 100%
    );
}

.document-card {
    background: linear-gradient(135deg,
    hsl(var(--background)) 0%,
    hsl(var(--muted) / 0.3) 100%
    );
}
</style>
