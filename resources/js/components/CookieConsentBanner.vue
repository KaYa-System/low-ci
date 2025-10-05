<template>
    <Transition 
        enter-active-class="transition-all duration-500 ease-out"
        enter-from-class="transform translate-y-full opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition-all duration-300 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-full opacity-0"
    >
        <div 
            v-if="showBanner" 
            class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t shadow-lg"
        >
            <div class="max-w-7xl mx-auto p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <div class="text-2xl">🍪</div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-1">
                                    Respect de votre vie privée
                                </h3>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    Nous utilisons des cookies et collectons des données anonymes pour améliorer votre expérience 
                                    et analyser l'utilisation de notre IA juridique. 
                                    <button 
                                        @click="showDetails = !showDetails"
                                        class="text-indigo-600 hover:text-indigo-700 underline"
                                    >
                                        {{ showDetails ? 'Masquer' : 'En savoir plus' }}
                                    </button>
                                </p>
                                
                                <!-- Détails étendus -->
                                <Transition
                                    enter-active-class="transition-all duration-300 ease-out"
                                    enter-from-class="opacity-0 max-h-0"
                                    enter-to-class="opacity-100 max-h-96"
                                    leave-active-class="transition-all duration-200 ease-in"
                                    leave-from-class="opacity-100 max-h-96"
                                    leave-to-class="opacity-0 max-h-0"
                                >
                                    <div v-if="showDetails" class="mt-3 p-3 bg-gray-50 rounded-lg text-xs text-gray-600 overflow-hidden">
                                        <div class="space-y-2">
                                            <div>
                                                <strong>🔍 Données collectées :</strong>
                                                <ul class="ml-4 mt-1 list-disc">
                                                    <li>Adresse IP (pour géolocalisation anonyme)</li>
                                                    <li>Type d'appareil et navigateur</li>
                                                    <li>Résolution d'écran et fuseau horaire</li>
                                                    <li>Historique des conversations (anonymisé)</li>
                                                </ul>
                                            </div>
                                            <div>
                                                <strong>🎯 Finalités :</strong>
                                                Améliorer l'IA, analyser l'usage, optimiser les performances, statistiques anonymes.
                                            </div>
                                            <div>
                                                <strong>📊 Conformité RGPD :</strong>
                                                Vous pouvez refuser et utiliser l'IA sans tracking. Vos droits : accès, rectification, effacement.
                                            </div>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button
                            @click="rejectCookies"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Continuer sans tracking
                        </button>
                        <button
                            @click="acceptCookies"
                            class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                        >
                            Accepter et améliorer l'IA
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showBanner = ref(false)
const showDetails = ref(false)

// Vérifie si l'utilisateur a déjà fait son choix
const checkConsentStatus = () => {
    const consent = getCookie('tracking_consent')
    if (!consent) {
        // Délai pour laisser la page se charger
        setTimeout(() => {
            showBanner.value = true
        }, 1000)
    }
}

// Fonctions utilitaires pour les cookies
const setCookie = (name, value, days = 365) => {
    const expires = new Date()
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000))
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`
}

const getCookie = (name) => {
    const value = `; ${document.cookie}`
    const parts = value.split(`; ${name}=`)
    if (parts.length === 2) return parts.pop().split(';').shift()
    return null
}

// Actions des boutons
const acceptCookies = () => {
    setCookie('tracking_consent', 'accepted')
    showBanner.value = false
    
    // Log pour audit
    console.log('✅ Consentement tracking accordé')
    
    // Optionnel: recharger la page pour appliquer le tracking
    if (window.location.pathname.includes('/chat')) {
        // Notifier les autres composants du changement
        window.dispatchEvent(new CustomEvent('trackingConsentChanged', { 
            detail: { granted: true } 
        }))
    }
}

const rejectCookies = () => {
    setCookie('tracking_consent', 'rejected')
    showBanner.value = false
    
    // Log pour audit
    console.log('❌ Consentement tracking refusé - mode anonyme')
    
    // Notifier les autres composants du changement
    window.dispatchEvent(new CustomEvent('trackingConsentChanged', { 
        detail: { granted: false } 
    }))
}

// Fonction exposée pour réouvrir le panneau depuis d'autres composants
const reopenBanner = () => {
    showBanner.value = true
}

// Exposition pour usage externe
defineExpose({ reopenBanner })

onMounted(() => {
    checkConsentStatus()
})
</script>

<style scoped>
/* Assurer que le banner est au-dessus de tout */
.z-50 {
    z-index: 50;
}

/* Animation smooth pour le contenu expandable */
.max-h-0 {
    max-height: 0;
}
.max-h-96 {
    max-height: 24rem;
}
</style>