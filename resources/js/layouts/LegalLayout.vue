<template>
  <div class="min-h-screen bg-background">
    <!-- Navigation Header -->
    <header class="bg-background/95 backdrop-blur-sm shadow-sm border-b border-border sticky top-0 z-50">
      <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo and Brand -->
          <div class="flex items-center">
            <Link href="/" class="flex items-center space-x-3 group">
              <div class="w-10 h-10 bg-gradient-to-r from-primary to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                <Scale class="w-6 h-6 text-primary-foreground" />
              </div>
              <div class="hidden sm:block">
                <h1 class="text-xl font-bold text-foreground group-hover:text-primary transition-colors">LégisCI</h1>
                <p class="text-sm text-muted-foreground">Législation Ivoirienne</p>
              </div>
            </Link>
          </div>

          <!-- Search Bar (Desktop) -->
          <div class="hidden md:flex flex-1 max-w-lg mx-8">
            <div class="relative w-full group">
              <div class="absolute -inset-1 bg-gradient-to-r from-primary/20 to-purple-600/20 rounded-2xl blur opacity-0 group-hover:opacity-100 transition duration-300" />
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
                <input
                  v-model="searchQuery"
                  @keyup.enter="performSearch"
                  type="text"
                  placeholder="Rechercher dans la législation..."
                  class="w-full pl-10 pr-4 py-2.5 bg-card border border-border rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200 text-foreground placeholder:text-muted-foreground/60"
                />
              </div>
            </div>
          </div>

          <!-- Main Navigation -->
          <div class="flex items-center space-x-6">
            <!-- Chat IA Button -->
            <Link
              href="/chat"
              class="flex items-center px-4 py-2.5 bg-primary text-primary-foreground rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium shadow-md hover:shadow-lg group"
            >
              <MessageCircle class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform" />
              <span class="hidden sm:inline">Assistant IA</span>
            </Link>

            <!-- Mobile Menu Button -->
            <button
              class="md:hidden p-2 text-muted-foreground hover:text-foreground rounded-lg hover:bg-accent transition-colors"
              @click="showMobileMenu = !showMobileMenu"
            >
              <Menu class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Mobile Search -->
        <div v-if="showMobileMenu" class="mt-4 md:hidden">
          <div class="relative mb-4">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
            <input
              v-model="searchQuery"
              @keyup.enter="performSearch"
              type="text"
              placeholder="Rechercher dans la législation..."
              class="w-full pl-10 pr-4 py-2.5 bg-card border border-border rounded-xl focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200 text-foreground placeholder:text-muted-foreground/60"
            />
          </div>
        </div>
      </nav>
    </header>

    <!-- Breadcrumb (if provided) -->
    <div v-if="breadcrumbs && breadcrumbs.length > 0" class="bg-card/50 border-b border-border">
      <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center space-x-2 text-sm">
          <Link href="/" class="text-primary hover:text-primary/80 transition-colors font-medium">
            Accueil
          </Link>
          <template v-for="(breadcrumb, index) in breadcrumbs" :key="index">
            <ChevronRight class="w-4 h-4 text-muted-foreground" />
            <Link
              v-if="breadcrumb.href && index < breadcrumbs.length - 1"
              :href="breadcrumb.href"
              class="text-primary hover:text-primary/80 transition-colors font-medium"
            >
              {{ breadcrumb.label }}
            </Link>
            <span v-else class="text-foreground font-medium">{{ breadcrumb.label }}</span>
          </template>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-card/50 backdrop-blur-sm border-t border-border mt-16">
      <div class="container mx-auto px-4 py-12">
        <div class="grid md:grid-cols-4 gap-8">
          <!-- Brand -->
          <div class="md:col-span-1">
            <div class="flex items-center space-x-3 mb-4 group">
              <div class="w-10 h-10 bg-gradient-to-r from-primary to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                <Scale class="w-6 h-6 text-primary-foreground" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-foreground group-hover:text-primary transition-colors">LégisCI</h3>
                <p class="text-sm text-muted-foreground">Législation Ivoirienne</p>
              </div>
            </div>
            <p class="text-muted-foreground text-sm leading-relaxed">
              Accès libre et moderne à toute la législation de la République de Côte d'Ivoire.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h4 class="font-semibold text-foreground mb-4">Liens rapides</h4>
            <div class="space-y-3">
              <Link href="/" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Accueil
              </Link>
              <Link href="/search" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Rechercher
              </Link>
              <Link href="/ai-chat" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Assistant IA
              </Link>
              <Link href="/procedures" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Procédures
              </Link>
            </div>
          </div>

          <!-- Legal Categories -->
          <div>
            <h4 class="font-semibold text-foreground mb-4">Catégories</h4>
            <div class="space-y-3">
              <a href="/categories/constitution" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Constitution
              </a>
              <a href="/categories/codes" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Codes
              </a>
              <a href="/categories/lois" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Lois
              </a>
              <a href="/categories/decrets" class="block text-muted-foreground hover:text-primary text-sm transition-colors hover:translate-x-1 duration-200">
                Décrets
              </a>
            </div>
          </div>

          <!-- About -->
          <div>
            <h4 class="font-semibold text-foreground mb-4">À propos</h4>
            <div class="space-y-3 text-sm text-muted-foreground">
              <p class="leading-relaxed">Une initiative pour démocratiser l'accès au droit ivoirien.</p>
              <div class="flex items-center space-x-4 mt-4">
                <button class="text-muted-foreground hover:text-primary transition-all duration-200 hover:scale-110">
                  <Github class="w-5 h-5" />
                </button>
                <button class="text-muted-foreground hover:text-primary transition-all duration-200 hover:scale-110">
                  <Twitter class="w-5 h-5" />
                </button>
                <button class="text-muted-foreground hover:text-primary transition-all duration-200 hover:scale-110">
                  <Mail class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-border pt-8 mt-8">
          <div class="flex flex-col md:flex-row justify-between items-center">
            <p class="text-muted-foreground text-sm">
              © {{ new Date().getFullYear() }} LégisCI. Plateforme libre d'accès à la législation ivoirienne.
            </p>
            <div class="flex items-center space-x-6 mt-4 md:mt-0">
              <a href="#" class="text-muted-foreground hover:text-primary text-sm transition-colors">
                Politique de confidentialité
              </a>
              <a href="#" class="text-muted-foreground hover:text-primary text-sm transition-colors">
                Conditions d'utilisation
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import {
  Scale, ChevronRight, Search, MessageCircle, Menu,
  Github, Twitter, Mail
} from 'lucide-vue-next'

interface BreadcrumbItem {
  label: string
  href?: string
}

interface Props {
  breadcrumbs?: BreadcrumbItem[]
}

defineProps<Props>()

// Reactive state
const showMobileMenu = ref(false)
const searchQuery = ref('')

// Methods
const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.visit('/search', {
      method: 'get',
      data: { q: searchQuery.value }
    })
  }
}

// Close mobile menu when route changes
router.on('navigate', () => {
  showMobileMenu.value = false
})
</script>
