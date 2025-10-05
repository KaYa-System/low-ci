<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-8">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">
                    {{ editingDocument ? 'Modifier le document' : 'Nouveau document' }}
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Mode d'upload simple -->
            <div class="mb-6 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-xl">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-2">✨ Upload Intelligent</h4>
                    <p class="text-gray-600">Déposez votre PDF et laissez l'IA faire le travail !<br>
                    <span class="text-sm text-indigo-600 font-medium">Titre, type, résumé... tout sera détecté automatiquement</span></p>
                </div>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                
                <!-- PDF Upload (Obligatoire) -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Fichier PDF <span class="text-red-500">*</span>
                    </label>
                    <div :class="[
                        'rounded-xl border-2 border-dashed p-8 text-center transition-colors',
                        uploadState === 'analyzing' ? 'border-indigo-300 bg-indigo-50' : 
                        form.pdf_file ? 'border-green-300 bg-green-50' :
                        getFieldError('pdf_file') ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-indigo-400'
                    ]">
                        
                        <!-- Étapes d'upload et analyse -->
                        <div v-if="uploadState === 'analyzing'" class="text-indigo-600">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                            <div class="text-sm font-medium mb-2">🧠 Analyse IA en cours...</div>
                            <div class="text-xs text-indigo-500">Extraction du titre, type, résumé et autres métadonnées</div>
                        </div>

                        <div v-else-if="analysisResults" class="text-green-600">
                            <CheckCircle class="mx-auto mb-4 h-12 w-12" />
                            <div class="text-sm font-medium mb-2">✨ Analyse terminée !</div>
                            <div class="text-xs">
                                <strong>{{ analysisResults.title }}</strong><br>
                                Type: {{ analysisResults.type }} | Confiance: {{ Math.round(analysisResults.confidence_score * 100) }}%
                            </div>
                        </div>

                        <div v-else-if="form.pdf_file" class="text-green-600">
                            <CheckCircle class="mx-auto mb-4 h-12 w-12" />
                            <div class="text-sm font-medium">
                                Fichier sélectionné : {{ form.pdf_file_name }}
                            </div>
                        </div>

                        <div v-else class="text-gray-600">
                            <FileText class="mx-auto mb-4 h-12 w-12" :class="getFieldError('pdf_file') ? 'text-red-400' : 'text-gray-400'" />
                            <div class="mb-4 text-sm">
                                Glissez-déposez votre fichier PDF ici, ou
                                <label class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-800">
                                    parcourez
                                    <input ref="pdfInput" type="file" accept=".pdf" @change="handlePdfUpload" class="hidden" />
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PDF uniquement, taille maximum : 50MB</p>
                        </div>
                    </div>
                    <p v-if="getFieldError('pdf_file')" class="mt-1 text-sm text-red-600">
                        {{ getFieldError('pdf_file') }}
                    </p>
                </div>

                <!-- Aperçu simple des résultats (si disponible) -->
                <div v-if="analysisResults" class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                    <div class="flex items-center justify-center mb-3">
                        <CheckCircle class="h-8 w-8 text-green-600 mr-2" />
                        <h4 class="text-lg font-semibold text-green-900">🎉 Document analysé avec succès !</h4>
                    </div>
                    
                    <div class="bg-white rounded-lg p-4 mb-4 text-left">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase">Titre détecté</span>
                                <p class="text-sm font-medium text-gray-900 mt-1">{{ analysisResults.title }}</p>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase">Type détecté</span>
                                <p class="text-sm font-medium text-gray-900 mt-1 capitalize">{{ analysisResults.type }}</p>
                            </div>
                        </div>
                        <div class="mt-3" v-if="analysisResults.summary">
                            <span class="text-xs font-medium text-gray-500 uppercase">Résumé généré</span>
                            <p class="text-sm text-gray-700 mt-1 line-clamp-2">{{ analysisResults.summary }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center">
                        <div class="flex items-center bg-white rounded-full px-4 py-2 border">
                            <div class="w-4 h-4 rounded-full mr-2" :class="analysisResults.confidence_score > 0.7 ? 'bg-green-500' : analysisResults.confidence_score > 0.4 ? 'bg-yellow-500' : 'bg-red-500'"></div>
                            <span class="text-sm font-medium">Confiance IA : {{ Math.round(analysisResults.confidence_score * 100) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4 border-t pt-6">
                    <button type="button" @click="$emit('close')" class="px-6 py-3 text-gray-600 transition-colors hover:text-gray-800">
                        Annuler
                    </button>
                    <button
                        type="submit"
                        :disabled="saving || uploadState === 'analyzing' || !form.pdf_file"
                        class="rounded-xl bg-indigo-600 px-6 py-3 text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ saving ? 'Enregistrement...' : uploadState === 'analyzing' ? 'Analyse en cours...' : editingDocument ? 'Modifier' : 'Créer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { X, FileText, CheckCircle } from 'lucide-vue-next'

const props = defineProps({
    editingDocument: Object,
    categories: Array,
    errors: Object,
    saving: Boolean
})

const emit = defineEmits(['close', 'submit'])

// Form data
const form = ref({
    title: '',
    reference_number: '',
    type: '',
    category_id: '',
    status: 'draft',
    publication_date: '',
    effective_date: '',
    summary: '',
    pdf_file: null,
    pdf_file_name: ''
})

// Upload and analysis state
const uploadState = ref('idle') // idle, uploading, analyzing, complete
const analysisResults = ref(null)
const pdfInput = ref(null)

// Handle PDF upload and trigger AI analysis
const handlePdfUpload = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    form.value.pdf_file = file
    form.value.pdf_file_name = file.name

    // Trigger AI analysis
    await analyzeDocument(file)
}

// Analyze document with AI
const analyzeDocument = async (pdfFile) => {
    uploadState.value = 'analyzing'
    
    try {
        const formData = new FormData()
        formData.append('pdf_file', pdfFile)
        
        const response = await fetch('/api/documents/preview', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        
        const result = await response.json()
        
        if (result.success) {
            analysisResults.value = result.analysis
            uploadState.value = 'complete'
        } else {
            console.error('Analysis failed:', result.error)
            uploadState.value = 'idle'
        }
    } catch (error) {
        console.error('Analysis error:', error)
        uploadState.value = 'idle'
    }
}

// Submit form
const submitForm = () => {
    emit('submit', form.value)
}

// Get field errors
const getFieldError = (field) => {
    return props.errors && props.errors[field] ? props.errors[field][0] : null
}

// Initialize form for editing
if (props.editingDocument) {
    Object.assign(form.value, props.editingDocument)
}
</script>