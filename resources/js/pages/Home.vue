<template>
  <LegalLayout>
    <div class="min-h-screen">
      <!-- Hero Section - Modern & Clean -->
      <section class="relative overflow-hidden" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/twcomponents/header.webp'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <!-- Overlay for better text readability -->
        <div class="absolute inset-0 bg-black/20 backdrop-blur-[0.5px]"></div>
        <div class="container mx-auto px-4 py-20 lg:py-32 relative z-10">
          <div class="text-center space-y-8">
            <!-- Logo Animation -->
            <div class="relative">
              <div class="inline-flex items-center justify-center w-20 h-20 bg-primary rounded-3xl mb-6 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <Scale class="w-10 h-10 text-primary-foreground" />
              </div>
              <div class="absolute -inset-4 bg-primary/10 rounded-full blur-2xl -z-10 animate-pulse" />
            </div>

            <!-- Modern Typography -->
            <div class="space-y-4">
              <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black tracking-tight">
                <span class="text-foreground font-black text-white leading-tight">Législation</span>
                <br />
                <span class="bg-gradient-to-r from-primary via-purple-600 to-red-500 bg-clip-text text-transparent">
                  Ivoirienne
                </span>
              </h1>

              <p class="text-lg sm:text-xl text-white max-w-3xl mx-auto leading-relaxed">
                Découvrez, explorez et comprenez la législation ivoirienne avec notre plateforme moderne et intuitive
              </p>
            </div>

            <!-- Enhanced Search Bar -->
            <div class="max-w-2xl mx-auto">
              <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-purple-600 rounded-3xl blur opacity-25 group-hover:opacity-50 transition duration-300" />
                <div class="relative bg-background border-2 border-border rounded-3xl shadow-lg hover:shadow-xl transition-all duration-300">
                  <Search class="absolute left-6 top-1/2 transform -translate-y-1/2 text-muted-foreground w-5 h-5" />
                  <input
                    v-model="searchQuery"
                    @keyup.enter="performSearch"
                    @focus="isSearchFocused = true"
                    @blur="isSearchFocused = false"
                    type="text"
                    placeholder="Rechercher une loi, un décret, un article..."
                    class="w-full pl-16 pr-32 py-6 text-lg bg-transparent border-0 rounded-3xl focus:outline-none focus:ring-0 placeholder:text-muted-foreground/60"
                  />
                  <Button
                    @click="performSearch"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 rounded-2xl px-8 py-3 shadow-md hover:shadow-lg transition-all duration-200"
                    size="lg"
                  >
                    <Search class="w-4 h-4 mr-2" />
                    Rechercher
                  </Button>
                </div>
              </div>
            </div>

            <!-- Quick Access Pills -->
            <div class="flex flex-wrap justify-center gap-3">
              <Button
                v-for="category in featuredCategories"
                :key="category.slug"
                @click="browseCategory(category)"
                variant="outline"
                size="lg"
                class="rounded-full hover:scale-105 transition-all duration-200 hover:shadow-md"
              >
                <component :is="category.icon" class="w-4 h-4 mr-2" />
                {{ category.name }}
              </Button>
            </div>
          </div>
        </div>
      </section>
        <!-- Procedures Section - Premium Design -->
        <section class="py-20 bg-gradient-to-b from-muted/50 to-background">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-primary/10 rounded-2xl mb-4">
                        <Calculator class="w-6 h-6 text-primary" />
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Procédures & Guides</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Accès rapide aux procédures essentielles pour optimiser votre travail au quotidien
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 mb-12">
                    <!-- Technical Procedures -->
                    <Card class="group cursor-pointer hover:shadow-2xl transition-all duration-500 hover:scale-105 glass-card border-border/50 relative overflow-hidden hover-lift"
                          @click="navigateToProcedure('techniques')">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-primary/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <CardHeader class="relative z-10">
                            <div class="flex items-start space-x-4 mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-3xl flex items-center justify-center shadow-lg group-hover:shadow-primary/30 group-hover:shadow-xl transition-all duration-300">
                                    <Calculator class="w-8 h-8 text-primary-foreground" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold mb-2 text-foreground group-hover:text-primary transition-colors">Procédures Techniques</h3>
                                    <Badge variant="secondary" class="bg-primary/10 text-primary border-primary/20">Guides pratiques</Badge>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="relative z-10">
                            <p class="text-muted-foreground leading-relaxed mb-6">
                                Maîtrisez les processus techniques avec nos guides détaillés et nos bonnes pratiques éprouvées.
                            </p>
                            <div class="flex items-center text-primary font-medium group-hover:text-primary/80 transition-colors">
                                <span>Découvrir</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quality Procedures -->
                    <Card class="group cursor-pointer hover:shadow-2xl transition-all duration-500 hover:scale-105 glass-card border-border/50 relative overflow-hidden hover-lift"
                          @click="navigateToProcedure('qualite')">
                        <div class="absolute inset-0 bg-gradient-to-br from-chart-2/5 to-chart-2/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <CardHeader class="relative z-10">
                            <div class="flex items-start space-x-4 mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-chart-2 to-chart-2/80 rounded-3xl flex items-center justify-center shadow-lg group-hover:shadow-chart-2/30 group-hover:shadow-xl transition-all duration-300">
                                    <Shield class="w-8 h-8 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold mb-2 text-foreground group-hover:text-chart-2 transition-colors">Procédures Qualité</h3>
                                    <Badge variant="secondary" class="bg-chart-2/10 text-chart-2 border-chart-2/20">Contrôle & Validation</Badge>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="relative z-10">
                            <p class="text-muted-foreground leading-relaxed mb-6">
                                Garantissez l'excellence avec nos procédures de contrôle qualité et nos standards de validation.
                            </p>
                            <div class="flex items-center text-chart-2 font-medium group-hover:text-chart-2/80 transition-colors">
                                <span>Explorer</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Security Procedures -->
                    <Card class="group cursor-pointer hover:shadow-2xl transition-all duration-500 hover:scale-105 glass-card border-border/50 relative overflow-hidden hover-lift"
                          @click="navigateToProcedure('securite')">
                        <div class="absolute inset-0 bg-gradient-to-br from-destructive/5 to-destructive/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <CardHeader class="relative z-10">
                            <div class="flex items-start space-x-4 mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-destructive to-destructive/80 rounded-3xl flex items-center justify-center shadow-lg group-hover:shadow-destructive/30 group-hover:shadow-xl transition-all duration-300">
                                    <Scale class="w-8 h-8 text-destructive-foreground" />
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-xl font-bold mb-2 text-foreground group-hover:text-destructive transition-colors">Procédures Sécurité</h3>
                                    <Badge variant="destructive" class="bg-destructive/10 text-destructive border-destructive/20">Protocoles essentiels</Badge>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="relative z-10">
                            <p class="text-muted-foreground leading-relaxed mb-6">
                                Protégez vos opérations avec nos protocoles de sécurité avancés et nos bonnes pratiques.
                            </p>
                            <div class="flex items-center text-destructive font-medium group-hover:text-destructive/80 transition-colors">
                                <span>Accéder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Enhanced Quick Access -->
                <div class="text-center">
                    <div class="inline-flex flex-col sm:flex-row items-center gap-4">
                        <Button
                            @click="navigateToProcedure('emergency')"
                            variant="destructive"
                            size="lg"
                            class="rounded-full shadow-lg hover:shadow-xl transition-all duration-300 group px-8 py-4"
                        >
                            <Shield class="w-5 h-5 mr-3" />
                            <span class="font-semibold">Procédures d'urgence</span>
                            <Badge class="ml-3 bg-red-800/20 text-red-100">Critique</Badge>
                        </Button>

                        <Button
                            @click="navigateToProcedure('all')"
                            variant="outline"
                            size="lg"
                            class="rounded-full shadow-md hover:shadow-lg transition-all duration-300 group px-8 py-4"
                        >
                            <Briefcase class="w-5 h-5 mr-3" />
                            <span class="font-semibold">Toutes les procédures</span>
                            <svg class="w-4 h-4 ml-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Button>
                    </div>
                </div>
            </div>
        </section>
      <!-- Featured Documents - Modern Cards -->
      <section class="py-20 bg-accent/30">
        <div class="container mx-auto px-4">
          <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-primary/10 rounded-2xl mb-4">
              <Eye class="w-6 h-6 text-primary" />
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Documents vedettes</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
              Les textes les plus importants et consultés de la législation ivoirienne
            </p>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <Card
              v-for="document in featuredDocuments"
              :key="document.id"
              class="group cursor-pointer hover:shadow-xl transition-all duration-300 hover:scale-[1.02] bg-card/80 backdrop-blur-sm border-0 shadow-md"
              @click="viewDocument(document)"
            >
              <CardHeader class="pb-3">
                <div class="flex items-center justify-between mb-3">
                  <Badge :variant="getBadgeVariant(document.type)" class="rounded-full">
                    <div :class="`w-1.5 h-1.5 rounded-full mr-1.5 ${getTypeColor(document.type)}`" />
                    {{ document.type }}
                  </Badge>
                  <div class="flex items-center text-sm text-muted-foreground/70">
                    <Eye class="w-3.5 h-3.5 mr-1" />
                    {{ document.views_count }}
                  </div>
                </div>
                <h3 class="text-lg font-semibold leading-tight group-hover:text-primary transition-colors">
                  {{ document.title }}
                </h3>
              </CardHeader>
              <CardContent>
                <p class="text-muted-foreground text-sm line-clamp-3 mb-4">{{ document.summary }}</p>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-muted-foreground/70">{{ formatDate(document.publication_date) }}</span>
                  <Button variant="ghost" size="sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                    Voir plus
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      <!-- Categories - Enhanced Grid -->
      <section class="py-20 bg-background">
        <div class="container mx-auto px-4">
          <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-primary/10 rounded-2xl mb-4">
              <Briefcase class="w-6 h-6 text-primary" />
            </div>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Explorer par catégorie</h2>
            <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
              Naviguez facilement dans la législation organisée par domaine juridique
            </p>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <Card
              v-for="category in categories"
              :key="category.id"
              class="group cursor-pointer hover:shadow-2xl transition-all duration-300 hover:scale-105 bg-gradient-to-br from-card to-card/50 border-0 shadow-md overflow-hidden relative"
              @click="browseCategory(category)"
            >
              <!-- Subtle background pattern -->
              <div class="absolute inset-0 bg-gradient-to-br opacity-5" :style="`background: linear-gradient(135deg, ${category.color || '#3b82f6'} 0%, transparent 100%)`" />

              <CardHeader class="pb-2">
                <div class="flex items-center space-x-4 mb-3">
                  <div
                    class="w-14 h-14 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-all duration-300 shadow-lg"
                    :style="`background: linear-gradient(135deg, ${category.color || '#3b82f6'}20, ${category.color || '#3b82f6'}10)`"
                  >
                    <component
                      :is="category.icon || Briefcase"
                      class="w-7 h-7"
                      :style="`color: ${category.color || '#3b82f6'}`"
                    />
                  </div>
                  <div>
                    <h3 class="text-lg font-bold mb-1 group-hover:text-primary transition-colors">{{ category.name }}</h3>
                    <Badge variant="secondary" class="text-xs">
                      {{ category.documents_count || 0 }} docs
                    </Badge>
                  </div>
                </div>
              </CardHeader>
              <CardContent>
                <p class="text-muted-foreground text-sm leading-relaxed">{{ category.description }}</p>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>



      <!-- AI Assistant CTA - Revolutionary Design -->
      <section class="py-20 bg-gradient-to-br from-primary via-purple-600 to-pink-500 relative overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
          <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse" />
          <div class="absolute bottom-10 right-10 w-24 h-24 bg-white/10 rounded-full blur-lg animate-pulse" style="animation-delay: 1s" />
          <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/10 rounded-full blur-md animate-pulse" style="animation-delay: 2s" />
        </div>

        <div class="container mx-auto px-4 text-center relative z-10">
          <div class="max-w-4xl mx-auto">
            <!-- Icon with animation -->
            <div class="mb-8">
              <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 backdrop-blur-sm text-white rounded-3xl mb-4 shadow-2xl hover:scale-110 transition-all duration-300">
                <MessageCircle class="w-12 h-12" />
              </div>
            </div>

            <!-- Modern typography -->
            <div class="space-y-6 mb-12">
              <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight">
                Assistant IA
                <br class="sm:hidden" />
                <span class="bg-gradient-to-r from-yellow-200 to-orange-200 bg-clip-text text-transparent">
                  Juridique
                </span>
              </h2>

              <p class="text-xl sm:text-2xl text-white/90 max-w-3xl mx-auto leading-relaxed">
                Intelligence artificielle avancée pour comprendre et naviguer dans la législation ivoirienne
              </p>

              <div class="flex flex-wrap justify-center gap-4 text-white/80">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse" />
                  <span class="text-sm">Réponses instantanées</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse" style="animation-delay: 0.5s" />
                  <span class="text-sm">Recherche intelligente</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-purple-400 rounded-full mr-2 animate-pulse" style="animation-delay: 1s" />
                  <span class="text-sm">Analyses juridiques</span>
                </div>
              </div>
            </div>

            <!-- Enhanced CTA -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
              <Button
                @click="openAiChat"
                size="lg"
                class="bg-white text-primary hover:bg-white/95 rounded-2xl shadow-2xl hover:shadow-white/25 transition-all duration-300 group px-10 py-6 text-lg font-bold"
              >
                <MessageCircle class="w-6 h-6 mr-3 group-hover:rotate-12 transition-transform" />
                <span>Démarrer maintenant</span>
                <svg class="w-5 h-5 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </Button>

              <Button
                variant="outline"
                size="lg"
                class="border-2 border-white/30 hover:bg-white/10 hover:border-white/50 rounded-2xl backdrop-blur-sm px-8 py-4 font-semibold transition-all duration-300"
              >
                <Eye class="w-5 h-5 mr-2" />
                Voir une démo
              </Button>
            </div>

            <!-- Trust indicators -->
            <div class="mt-16 pt-8 border-t border-white/20">
              <p class="text-white/60 text-sm mb-4">Déjà utilisé par des professionnels du droit</p>
              <div class="flex flex-wrap justify-center items-center gap-8 opacity-60">
                <div class="flex items-center text-white/80">
                  <Users class="w-4 h-4 mr-2" />
                  <span class="text-sm font-medium">1000+ utilisateurs</span>
                </div>
                <div class="flex items-center text-white/80">
                  <MessageCircle class="w-4 h-4 mr-2" />
                  <span class="text-sm font-medium">10k+ questions résolues</span>
                </div>
                <div class="flex items-center text-white/80">
                  <Shield class="w-4 h-4 mr-2" />
                  <span class="text-sm font-medium">Sécurisé & fiable</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </LegalLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Scale, Search, Eye, MessageCircle, Briefcase, Shield, Users, Calculator } from 'lucide-vue-next'
import LegalLayout from '@/layouts/LegalLayout.vue'

// Modern UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

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

const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.visit('/search', {
      method: 'get',
      data: { q: searchQuery.value }
    })
  }
}

const viewDocument = (document: any) => {
  router.visit(`/documents/${document.slug}`)
}

const browseCategory = (category: any) => {
  router.visit(`/categories/${category.slug}`)
}

const openAiChat = () => {
  router.visit('/chat')
}

const navigateToProcedure = (type: string) => {
  if (type === 'all') {
    router.visit('/procedures')
  } else {
    router.visit(`/procedures/${type}`)
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

const getBadgeVariant = (type: string) => {
  const variants = {
    'constitution': 'destructive',
    'loi': 'default',
    'decret': 'secondary',
    'arrete': 'outline',
    'code': 'secondary',
    'ordonnance': 'default'
  }
  return variants[type as keyof typeof variants] || 'secondary'
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Load data on mount
onMounted(() => {
  loadData()
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
