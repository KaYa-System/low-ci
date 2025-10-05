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

            <!-- Mode d'analyse automatique -->
            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
                <div class="flex items-center mb-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a9 9 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-indigo-900">🤖 Analyse IA Automatique</h4>
                        <p class="text-xs text-indigo-700">L'IA analysera automatiquement votre PDF et remplira les champs (titre, type, résumé, etc.)</p>
                    </div>
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

                <!-- Aperçu des résultats d'analyse (si disponible) -->
                <div v-if="analysisResults" class="space-y-6">
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">📋 Résultats de l'analyse IA</h4>
                        <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Titre détecté</span>
                                    <p class="text-sm text-gray-900">{{ analysisResults.title }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Type détecté</span>
                                    <p class="text-sm text-gray-900 capitalize">{{ analysisResults.type }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Référence</span>
                                    <p class="text-sm text-gray-900">{{ analysisResults.reference_number || 'Non détectée' }}</p>
                                </div>
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Confiance IA</span>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div 
                                                class="h-2 rounded-full"
                                                :class="analysisResults.confidence_score > 0.7 ? 'bg-green-500' : analysisResults.confidence_score > 0.4 ? 'bg-yellow-500' : 'bg-red-500'"
                                                :style="`width: ${analysisResults.confidence_score * 100}%`"
                                            ></div>
                                        </div>
                                        <span class="text-xs">{{ Math.round(analysisResults.confidence_score * 100) }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="text-xs font-medium text-gray-500 uppercase">Résumé généré</span>
                                <p class="text-sm text-gray-700 mt-1">{{ analysisResults.summary || 'Aucun résumé détecté' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Corrections manuelles optionnelles -->
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">✏️ Corrections manuelles (optionnel)</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Corriger le titre</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                    :placeholder="analysisResults.title"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Corriger le type</label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">-- Garder la détection IA --</option>
                                    <option value="constitution">Constitution</option>
                                    <option value="loi">Loi</option>
                                    <option value="decret">Décret</option>
                                    <option value="arrete">Arrêté</option>
                                    <option value="code">Code</option>
                                    <option value="ordonnance">Ordonnance</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Corriger la référence</label>
                                <input
                                    v-model="form.reference_number"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                    :placeholder="analysisResults.reference_number || 'Ex: LOI-2024-001'"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Catégorie</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">Sélectionner une catégorie</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Corriger le résumé</label>
                            <textarea
                                v-model="form.summary"
                                rows="3"
                                maxlength="1000"
                                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                :placeholder="analysisResults.summary || 'Résumé du document...'"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mt-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Date de publication</label>
                                <input
                                    v-model="form.publication_date"
                                    type="date"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Date d'effet</label>
                                <input
                                    v-model="form.effective_date"
                                    type="date"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Statut</label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="draft">Brouillon</option>
                                    <option value="published">Publié</option>
                                    <option value="archived">Archivé</option>
                                </select>
                            </div>
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