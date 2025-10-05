<template>
    <Head title="Analytics - Statistiques d'utilisation IA" />
    
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📊 Analytics - Statistiques d'utilisation IA
                </h2>
                
                <div class="flex items-center space-x-4">
                    <!-- Filtre par dates -->
                    <div class="flex items-center space-x-2">
                        <input 
                            type="date" 
                            v-model="dateFilters.startDate"
                            class="rounded-md border-gray-300 text-sm"
                            @change="loadAnalytics"
                        >
                        <span class="text-sm text-gray-500">à</span>
                        <input 
                            type="date" 
                            v-model="dateFilters.endDate"
                            class="rounded-md border-gray-300 text-sm"
                            @change="loadAnalytics"
                        >
                    </div>
                    
                    <!-- Bouton d'export -->
                    <button
                        @click="exportData"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                        :disabled="loading"
                    >
                        📥 Exporter
                    </button>
                    
                    <!-- Bouton de refresh -->
                    <button
                        @click="loadAnalytics"
                        class="p-2 text-gray-600 hover:text-gray-800 transition-colors"
                        :disabled="loading"
                    >
                        <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Loading State -->
                <div v-if="loading && !analytics" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Chargement des statistiques...</p>
                </div>

                <!-- Statistiques générales -->
                <div v-if="analytics" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">💬</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Total Sessions
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ analytics.general?.total_sessions?.toLocaleString() || 0 }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">👥</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Utilisateurs Uniques
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ analytics.general?.unique_users?.toLocaleString() || 0 }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">🕶️</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Sessions Anonymes
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ analytics.general?.anonymous_sessions?.toLocaleString() || 0 }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">📱</div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            Top Appareil
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ getTopDevice() }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div v-if="analytics" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    
                    <!-- Graphique par pays -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">🌍 Sessions par Pays</h3>
                        <div v-if="analytics.countries && analytics.countries.length > 0">
                            <div class="space-y-4">
                                <div 
                                    v-for="country in analytics.countries.slice(0, 8)" 
                                    :key="country.country"
                                    class="flex items-center"
                                >
                                    <div class="w-12 text-sm font-medium text-gray-600">
                                        {{ country.country || 'N/A' }}
                                    </div>
                                    <div class="flex-1 mx-4">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-blue-600 h-2 rounded-full"
                                                :style="`width: ${getPercentage(country.sessions_count, getTotalCountrySessions())}%`"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="w-20 text-right text-sm text-gray-600">
                                        {{ country.sessions_count }} ({{ Math.round(getPercentage(country.sessions_count, getTotalCountrySessions())) }}%)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-center py-8">
                            Aucune donnée de géolocalisation disponible
                        </div>
                    </div>

                    <!-- Graphique par appareils -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📱 Sessions par Type d'Appareil</h3>
                        <div v-if="analytics.devices && analytics.devices.length > 0">
                            <div class="space-y-4">
                                <div 
                                    v-for="device in analytics.devices" 
                                    :key="device.device_type"
                                    class="flex items-center"
                                >
                                    <div class="w-16 text-sm font-medium text-gray-600 capitalize">
                                        {{ getDeviceIcon(device.device_type) }} {{ device.device_type }}
                                    </div>
                                    <div class="flex-1 mx-4">
                                        <div class="bg-gray-200 rounded-full h-3">
                                            <div 
                                                class="h-3 rounded-full"
                                                :class="getDeviceColor(device.device_type)"
                                                :style="`width: ${getPercentage(device.sessions_count, getTotalDeviceSessions())}%`"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="w-24 text-right text-sm text-gray-600">
                                        {{ device.sessions_count }} ({{ Math.round(getPercentage(device.sessions_count, getTotalDeviceSessions())) }}%)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-center py-8">
                            Aucune donnée d'appareil disponible
                        </div>
                    </div>

                    <!-- Graphique par navigateurs -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">🌐 Sessions par Navigateur</h3>
                        <div v-if="analytics.browsers && analytics.browsers.length > 0">
                            <div class="space-y-3">
                                <div 
                                    v-for="browser in analytics.browsers.slice(0, 6)" 
                                    :key="browser.browser"
                                    class="flex items-center"
                                >
                                    <div class="w-20 text-sm font-medium text-gray-600">
                                        {{ getBrowserIcon(browser.browser) }} {{ browser.browser || 'Inconnu' }}
                                    </div>
                                    <div class="flex-1 mx-4">
                                        <div class="bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-green-600 h-2 rounded-full"
                                                :style="`width: ${getPercentage(browser.sessions_count, getTotalBrowserSessions())}%`"
                                            ></div>
                                        </div>
                                    </div>
                                    <div class="w-16 text-right text-sm text-gray-600">
                                        {{ browser.sessions_count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-center py-8">
                            Aucune donnée de navigateur disponible
                        </div>
                    </div>

                    <!-- Graphique temporel -->
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">📈 Sessions par Jour (30 derniers jours)</h3>
                        <div v-if="analytics.general?.daily_stats && analytics.general.daily_stats.length > 0">
                            <div class="flex items-end space-x-1 h-40">
                                <div 
                                    v-for="day in analytics.general.daily_stats" 
                                    :key="day.date"
                                    class="flex-1 bg-blue-500 rounded-t min-h-[4px]"
                                    :style="`height: ${getTimeChartHeight(day.sessions)}%`"
                                    :title="`${day.date}: ${day.sessions} sessions`"
                                ></div>
                            </div>
                            <div class="mt-2 flex justify-between text-xs text-gray-500">
                                <span>{{ analytics.general.daily_stats[0]?.date }}</span>
                                <span>{{ analytics.general.daily_stats[analytics.general.daily_stats.length - 1]?.date }}</span>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-center py-8">
                            Aucune donnée temporelle disponible
                        </div>
                    </div>
                </div>

                <!-- Tableaux détaillés -->
                <div v-if="analytics" class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">📋 Détails par Pays</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Code Pays
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nom du Pays
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sessions
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pourcentage
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="country in analytics.countries" :key="country.country">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ country.country || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ country.country_name || 'Inconnu' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ country.sessions_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ Math.round(getPercentage(country.sessions_count, getTotalCountrySessions())) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const analytics = ref(null)
const loading = ref(false)

const dateFilters = ref({
    startDate: '',
    endDate: ''
})

// Charge les analytics
const loadAnalytics = async () => {
    loading.value = true
    try {
        const params = new URLSearchParams()
        if (dateFilters.value.startDate) params.append('start_date', dateFilters.value.startDate)
        if (dateFilters.value.endDate) params.append('end_date', dateFilters.value.endDate)
        
        const response = await fetch(`/api/analytics/dashboard?${params}`)
        const data = await response.json()
        
        if (response.ok) {
            analytics.value = data.data
        } else {
            console.error('Erreur lors du chargement:', data.message)
        }
    } catch (error) {
        console.error('Erreur:', error)
    } finally {
        loading.value = false
    }
}

// Export des données
const exportData = async () => {
    try {
        const params = new URLSearchParams({
            type: 'general',
            format: 'csv'
        })
        if (dateFilters.value.startDate) params.append('start_date', dateFilters.value.startDate)
        if (dateFilters.value.endDate) params.append('end_date', dateFilters.value.endDate)
        
        const response = await fetch(`/api/analytics/export?${params}`)
        const blob = await response.blob()
        
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = `analytics_${new Date().toISOString().split('T')[0]}.csv`
        a.click()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Erreur lors de l\'export:', error)
    }
}

// Fonctions utilitaires
const getPercentage = (value, total) => {
    return total > 0 ? (value / total) * 100 : 0
}

const getTotalCountrySessions = () => {
    return analytics.value.countries?.reduce((sum, country) => sum + country.sessions_count, 0) || 0
}

const getTotalDeviceSessions = () => {
    return analytics.value.devices?.reduce((sum, device) => sum + device.sessions_count, 0) || 0
}

const getTotalBrowserSessions = () => {
    return analytics.value.browsers?.reduce((sum, browser) => sum + browser.sessions_count, 0) || 0
}

const getTopDevice = () => {
    if (!analytics.value.devices || analytics.value.devices.length === 0) return 'N/A'
    const topDevice = analytics.value.devices[0]
    return `${topDevice.device_type} (${topDevice.sessions_count})`
}

const getDeviceIcon = (deviceType) => {
    switch (deviceType) {
        case 'mobile': return '📱'
        case 'tablet': return '📱'
        case 'desktop': return '💻'
        default: return '📟'
    }
}

const getDeviceColor = (deviceType) => {
    switch (deviceType) {
        case 'mobile': return 'bg-green-500'
        case 'tablet': return 'bg-blue-500'
        case 'desktop': return 'bg-purple-500'
        default: return 'bg-gray-500'
    }
}

const getBrowserIcon = (browser) => {
    switch (browser?.toLowerCase()) {
        case 'chrome': return '🌐'
        case 'firefox': return '🦊'
        case 'safari': return '🧭'
        case 'edge': return '🔷'
        default: return '🌐'
    }
}

const getTimeChartHeight = (sessions) => {
    if (!analytics.value.general?.daily_stats) return 0
    const maxSessions = Math.max(...analytics.value.general.daily_stats.map(d => d.sessions))
    return maxSessions > 0 ? (sessions / maxSessions) * 100 : 0
}

onMounted(() => {
    loadAnalytics()
})
</script>