<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Activity, BarChart3, ChevronRight, CheckCircle, Clock, Copy, Edit, Eye, FileText, Plus, Trash2, TrendingUp, Users, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Props pour recevoir les données du backend
const props = defineProps<{
    isAdmin?: boolean;
    adminStats?: any;
    documents?: any[];
}>();

// État local
const activeTab = ref('overview');

// Admin state
const adminDocuments = ref<any[]>([]);
const adminStatsLocal = ref({
    total: 0,
    published: 0,
    drafts: 0,
    totalViews: 0,
});
const searchQuery = ref('');
const selectedType = ref('');
const selectedStatus = ref('');
const showCreateModal = ref(false);
const editingDocument = ref<any>(null);
const saving = ref(false);

const form = ref({
    title: '',
    reference_number: '',
    type: 'loi',
    category_id: '',
    status: 'draft',
    publication_date: '',
    effective_date: '',
    summary: '',
    content: '',
    pdf_file: null,
    pdf_file_name: '',
});

const categories = ref<any[]>([]);
const pdfInput = ref<any>(null);

// Données mockées pour la démo

// Statistiques utilisateur normal
const userStats = [
    {
        title: 'Documents Consultés',
        value: '24',
        change: '+12%',
        trend: 'up',
        icon: FileText,
        color: 'bg-blue-500',
    },
    {
        title: 'Questions Posées',
        value: '8',
        change: '+25%',
        trend: 'up',
        icon: Activity,
        color: 'bg-green-500',
    },
    {
        title: 'Temps Moyen',
        value: '3.2 min',
        change: '-8%',
        trend: 'down',
        icon: Clock,
        color: 'bg-purple-500',
    },
];

const recentActivities = [
    { title: 'Nouvelle procédure ajoutée', time: 'Il y a 5 min', type: 'success' },
    { title: 'Rapport mensuel généré', time: 'Il y a 15 min', type: 'info' },
    { title: 'Utilisateur connecté', time: 'Il y a 23 min', type: 'default' },
    { title: 'Sauvegarde complétée', time: 'Il y a 1h', type: 'success' },
];

// Computed
const isAdmin = computed(() => props.isAdmin || false);

// Admin computed
const filteredDocuments = computed(() => {
    let filtered = adminDocuments.value;

    if (searchQuery.value) {
        filtered = filtered.filter(
            (doc) =>
                doc.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                doc.reference_number?.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }

    if (selectedType.value) {
        filtered = filtered.filter((doc) => doc.type === selectedType.value);
    }

    if (selectedStatus.value) {
        filtered = filtered.filter((doc) => doc.status === selectedStatus.value);
    }

    return filtered;
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Admin methods
const loadAdminData = async () => {
    if (!isAdmin.value) return;

    try {
        const response = await fetch('/api/admin/documents');
        const data = await response.json();
        adminDocuments.value = data.data || [];
        adminStatsLocal.value = {
            total: adminDocuments.value.length,
            published: adminDocuments.value.filter((d) => d.status === 'published').length,
            drafts: adminDocuments.value.filter((d) => d.status === 'draft').length,
            totalViews: adminDocuments.value.reduce((sum, d) => sum + (d.views_count || 0), 0),
        };
    } catch (error) {
        console.error('Error loading admin data:', error);
    }
};

const loadCategories = async () => {
    try {
        const response = await fetch('/api/legal/categories');
        const data = await response.json();
        categories.value = data.data || [];
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

const getTypeColor = (type: string) => {
    const colors = {
        constitution: 'bg-red-500',
        loi: 'bg-blue-500',
        decret: 'bg-green-500',
        arrete: 'bg-yellow-500',
        code: 'bg-purple-500',
        ordonnance: 'bg-indigo-500',
    };
    return colors[type as keyof typeof colors] || 'bg-gray-500';
};

const getTypeBadgeColor = (type: string) => {
    const colors = {
        constitution: 'bg-red-100 text-red-800',
        loi: 'bg-blue-100 text-blue-800',
        decret: 'bg-green-100 text-green-800',
        arrete: 'bg-yellow-100 text-yellow-800',
        code: 'bg-purple-100 text-purple-800',
        ordonnance: 'bg-indigo-100 text-indigo-800',
    };
    return colors[type as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeColor = (status: string) => {
    const colors = {
        published: 'bg-green-100 text-green-800',
        draft: 'bg-yellow-100 text-yellow-800',
        archived: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const viewDocument = (doc: any) => {
    router.visit(`/documents/${doc.slug}`);
};

const editDocument = (doc: any) => {
    editingDocument.value = doc;
    form.value = {
        title: doc.title,
        reference_number: doc.reference_number || '',
        type: doc.type,
        category_id: doc.category_id || '',
        status: doc.status,
        publication_date: doc.publication_date ? doc.publication_date.split('T')[0] : '',
        effective_date: doc.effective_date ? doc.effective_date.split('T')[0] : '',
        summary: doc.summary || '',
        content: doc.content || '',
        pdf_file: null,
        pdf_file_name: doc.pdf_file_name || '',
    };
};

const duplicateDocument = async (doc: any) => {
    if (confirm('Êtes-vous sûr de vouloir dupliquer ce document ?')) {
        try {
            const response = await fetch(`/api/admin/documents/${doc.id}/duplicate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (response.ok) {
                await loadAdminData();
                alert('Document dupliqué avec succès !');
            } else {
                throw new Error('Erreur lors de la duplication');
            }
        } catch (error) {
            console.error('Error duplicating document:', error);
            alert('Erreur lors de la duplication du document');
        }
    }
};

const deleteDocument = async (doc: any) => {
    if (confirm(`Êtes-vous sûr de vouloir supprimer "${doc.title}" ? Cette action est irréversible.`)) {
        try {
            const response = await fetch(`/api/admin/documents/${doc.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (response.ok) {
                await loadAdminData();
                alert('Document supprimé avec succès !');
            } else {
                throw new Error('Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Error deleting document:', error);
            alert('Erreur lors de la suppression du document');
        }
    }
};

const saveDocument = async () => {
    saving.value = true;

    try {
        const formData = new FormData();

        Object.keys(form.value).forEach((key) => {
            if (form.value[key] !== null && form.value[key] !== '') {
                formData.append(key, form.value[key]);
            }
        });

        const url = editingDocument.value ? `/api/admin/documents/${editingDocument.value.id}` : '/api/admin/documents';

        const method = editingDocument.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });

        if (response.ok) {
            await loadAdminData();
            closeModal();
            alert(editingDocument.value ? 'Document modifié avec succès !' : 'Document créé avec succès !');
        } else {
            const error = await response.json();
            throw new Error(error.message || 'Erreur lors de la sauvegarde');
        }
    } catch (error) {
        console.error('Error saving document:', error);
        alert('Erreur lors de la sauvegarde du document');
    } finally {
        saving.value = false;
    }
};

const handlePdfUpload = (event: any) => {
    const file = event.target.files[0];
    if (file && file.type === 'application/pdf') {
        form.value.pdf_file = file;
        form.value.pdf_file_name = file.name;
    } else {
        alert('Veuillez sélectionner un fichier PDF valide.');
        event.target.value = '';
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    editingDocument.value = null;
    form.value = {
        title: '',
        reference_number: '',
        type: 'loi',
        category_id: '',
        status: 'draft',
        publication_date: '',
        effective_date: '',
        summary: '',
        content: '',
        pdf_file: null,
        pdf_file_name: '',
    };
    if (pdfInput.value) {
        pdfInput.value.value = '';
    }
};

// Load admin data on mount if admin
if (isAdmin.value) {
    loadAdminData();
    loadCategories();
}

const stats = [
    {
        title: 'Utilisateurs Actifs',
        value: '2,847',
        change: '+12%',
        trend: 'up',
        icon: Users,
        color: 'bg-blue-500',
    },
    {
        title: 'Procédures Traitées',
        value: '1,329',
        change: '+8%',
        trend: 'up',
        icon: FileText,
        color: 'bg-green-500',
    },
    {
        title: 'Taux de Satisfaction',
        value: '94.2%',
        change: '+2.1%',
        trend: 'up',
        icon: TrendingUp,
        color: 'bg-purple-500',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6 lg:p-8">
            <!-- En-tête avec salutation -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tableau de Bord</h1>
                    <p class="mt-2 text-muted-foreground">Aperçu de vos activités et performances</p>
                </div>
                <div class="flex gap-3">
                    <Button variant="outline" class="gap-2">
                        <BarChart3 class="h-4 w-4" />
                        Rapports
                    </Button>
                    <Button class="gap-2">
                        <Activity class="h-4 w-4" />
                        Nouvelle Analyse
                    </Button>
                </div>
            </div>

            <!-- Admin Tabs -->
            <div v-if="isAdmin" class="border-b border-border">
                <nav class="-mb-px flex space-x-8">
                    <button
                        @click="activeTab = 'overview'"
                        :class="[
                            'border-b-2 px-1 py-3 text-sm font-medium whitespace-nowrap transition-colors',
                            activeTab === 'overview'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
                        ]"
                    >
                        Aperçu Général
                    </button>
                    <button
                        @click="activeTab = 'admin'"
                        :class="[
                            'border-b-2 px-1 py-3 text-sm font-medium whitespace-nowrap transition-colors',
                            activeTab === 'admin'
                                ? 'border-primary text-primary'
                                : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
                        ]"
                    >
                        Gestion des Documents
                    </button>
                </nav>
            </div>

            <!-- Overview Tab Content -->
            <div v-if="!isAdmin || activeTab === 'overview'" class="space-y-8">
                <!-- Statistiques principales -->
                <div class="grid gap-6 md:grid-cols-3">
                    <Card v-for="stat in stats" :key="stat.title" class="group hover-lift glass-card relative overflow-hidden border-border/50">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ stat.title }}
                            </CardTitle>
                            <div :class="`rounded-lg p-2 ${stat.color} bg-opacity-10`">
                                <component :is="stat.icon" :class="`h-5 w-5 ${stat.color.replace('bg-', 'text-')}`" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ stat.value }}</div>
                            <p class="mt-2 flex items-center gap-1 text-xs text-muted-foreground">
                                <TrendingUp class="h-3 w-3 text-green-500" />
                                <span class="font-medium text-green-500">{{ stat.change }}</span>
                                depuis le mois dernier
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Contenu principal -->
                <div class="grid gap-8 lg:grid-cols-3">
                <!-- Graphique principal -->
                <Card class="glass-card border-border/50 lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-primary" />
                            Évolution des Performances
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="flex h-[300px] items-center justify-center rounded-lg border border-border/30 bg-gradient-to-br from-primary/5 to-secondary/5"
                        >
                            <div class="text-center">
                                <BarChart3 class="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                                <p class="font-medium text-muted-foreground">Graphique des performances</p>
                                <p class="mt-1 text-sm text-muted-foreground">Intégration en cours...</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Activités récentes -->
                <Card class="glass-card border-border/50">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Activity class="h-5 w-5 text-primary" />
                            Activités Récentes
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.title"
                            class="group flex cursor-pointer items-start gap-3 rounded-lg p-3 transition-colors hover:bg-accent/50"
                        >
                            <div class="mt-1">
                                <Badge :variant="activity.type as any" class="h-2 w-2 rounded-full p-0" />
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="text-sm font-medium transition-colors group-hover:text-primary">
                                    {{ activity.title }}
                                </p>
                                <p class="flex items-center gap-1 text-xs text-muted-foreground">
                                    <Clock class="h-3 w-3" />
                                    {{ activity.time }}
                                </p>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground transition-colors group-hover:text-primary" />
                        </div>
                        <Button variant="ghost" class="mt-4 w-full"> Voir toutes les activités </Button>
                    </CardContent>
                </Card>
                </div>

                <!-- Actions rapides -->
                <Card class="glass-card border-border/50">
                    <CardHeader>
                        <CardTitle>Actions Rapides</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-4">
                            <Button variant="outline" class="hover-lift h-20 flex-col gap-2">
                                <FileText class="h-6 w-6" />
                                <span class="text-sm">Nouvelle Procédure</span>
                            </Button>
                            <Button variant="outline" class="hover-lift h-20 flex-col gap-2">
                                <Users class="h-6 w-6" />
                                <span class="text-sm">Gérer Utilisateurs</span>
                            </Button>
                            <Button variant="outline" class="hover-lift h-20 flex-col gap-2">
                                <BarChart3 class="h-6 w-6" />
                                <span class="text-sm">Générer Rapport</span>
                            </Button>
                            <Button variant="outline" class="hover-lift h-20 flex-col gap-2">
                                <Activity class="h-6 w-6" />
                                <span class="text-sm">Analyser Données</span>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Admin Tab Content -->
            <div v-if="isAdmin && activeTab === 'admin'" class="space-y-8">
                <!-- Admin Header -->
                <Card class="glass-card border-border/50">
                    <CardContent class="p-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-foreground">Gestion des Documents</h2>
                                <p class="mt-2 text-muted-foreground">Gérez la constitution, les lois et autres documents juridiques</p>
                            </div>
                            <Button @click="showCreateModal = true" class="gap-2">
                                <Plus class="h-4 w-4" />
                                Nouveau document
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Admin Stats -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <Card class="glass-card border-border/50">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-lg p-2 bg-blue-500 bg-opacity-10">
                                    <FileText class="h-6 w-6 text-blue-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-2xl font-bold text-foreground">{{ adminStatsLocal.total }}</p>
                                    <p class="text-sm text-muted-foreground">Total documents</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="glass-card border-border/50">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-lg p-2 bg-green-500 bg-opacity-10">
                                    <CheckCircle class="h-6 w-6 text-green-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-2xl font-bold text-foreground">{{ adminStatsLocal.published }}</p>
                                    <p class="text-sm text-muted-foreground">Publiés</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="glass-card border-border/50">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-lg p-2 bg-yellow-500 bg-opacity-10">
                                    <Clock class="h-6 w-6 text-yellow-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-2xl font-bold text-foreground">{{ adminStatsLocal.drafts }}</p>
                                    <p class="text-sm text-muted-foreground">Brouillons</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="glass-card border-border/50">
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="rounded-lg p-2 bg-purple-500 bg-opacity-10">
                                    <Eye class="h-6 w-6 text-purple-500" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-2xl font-bold text-foreground">{{ adminStatsLocal.totalViews }}</p>
                                    <p class="text-sm text-muted-foreground">Vues totales</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Filters and Search -->
                <Card class="glass-card border-border/50">
                    <CardContent class="p-6">
                        <div class="flex flex-col gap-4 lg:flex-row">
                            <div class="flex-1">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Rechercher des documents..."
                                    class="w-full rounded-lg border border-border px-4 py-3 bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                />
                            </div>

                            <div class="flex gap-3">
                                <select
                                    v-model="selectedType"
                                    class="rounded-lg border border-border px-4 py-3 bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                >
                                    <option value="">Tous les types</option>
                                    <option value="constitution">Constitution</option>
                                    <option value="loi">Loi</option>
                                    <option value="decret">Décret</option>
                                    <option value="arrete">Arrêté</option>
                                    <option value="code">Code</option>
                                    <option value="ordonnance">Ordonnance</option>
                                </select>

                                <select
                                    v-model="selectedStatus"
                                    class="rounded-lg border border-border px-4 py-3 bg-background focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors"
                                >
                                    <option value="">Tous les statuts</option>
                                    <option value="published">Publié</option>
                                    <option value="draft">Brouillon</option>
                                    <option value="archived">Archivé</option>
                                </select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Documents Table -->
                <Card class="glass-card border-border/50">
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-muted/30">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Document</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Statut</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Vues</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Modifié</th>
                                        <th class="px-6 py-4 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border bg-background">
                                    <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-muted/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div :class="`flex h-8 w-8 items-center justify-center rounded-lg ${getTypeColor(doc.type)}`">
                                                        <FileText class="h-4 w-4 text-white" />
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-foreground">{{ doc.title }}</div>
                                                    <div class="text-sm text-muted-foreground">{{ doc.reference_number }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getTypeBadgeColor(doc.type)">
                                                {{ doc.type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="getStatusBadgeColor(doc.status)">
                                                {{ doc.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-foreground">
                                            {{ doc.views_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-muted-foreground">
                                            {{ formatDate(doc.updated_at) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Button @click="viewDocument(doc)" variant="ghost" size="sm" class="h-8 w-8 p-0" title="Voir">
                                                    <Eye class="h-4 w-4" />
                                                </Button>
                                                <Button @click="editDocument(doc)" variant="ghost" size="sm" class="h-8 w-8 p-0" title="Modifier">
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                                <Button @click="duplicateDocument(doc)" variant="ghost" size="sm" class="h-8 w-8 p-0" title="Dupliquer">
                                                    <Copy class="h-4 w-4" />
                                                </Button>
                                                <Button @click="deleteDocument(doc)" variant="ghost" size="sm" class="h-8 w-8 p-0 text-destructive hover:text-destructive" title="Supprimer">
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="isAdmin && activeTab === 'admin'">
            <div v-if="showCreateModal || editingDocument" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">
                            {{ editingDocument ? 'Modifier le document' : 'Nouveau document' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <X class="h-6 w-6" />
                        </button>
                    </div>

                    <form @submit.prevent="saveDocument" class="space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Titre</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Numéro de référence</label>
                                <input
                                    v-model="form.reference_number"
                                    type="text"
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Type</label>
                                <select
                                    v-model="form.type"
                                    required
                                    class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="constitution">Constitution</option>
                                    <option value="loi">Loi</option>
                                    <option value="decret">Décret</option>
                                    <option value="arrete">Arrêté</option>
                                    <option value="code">Code</option>
                                    <option value="ordonnance">Ordonnance</option>
                                </select>
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

                        <!-- Dates -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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
                        </div>

                        <!-- Summary -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Résumé</label>
                            <textarea
                                v-model="form.summary"
                                rows="3"
                                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                placeholder="Résumé du document..."
                            ></textarea>
                        </div>

                        <!-- PDF Upload -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Fichier PDF</label>
                            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center transition-colors hover:border-indigo-400">
                                <FileText class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                                <div class="mb-4 text-sm text-gray-600">
                                    Glissez-déposez votre fichier PDF ici, ou
                                    <label class="cursor-pointer text-indigo-600 hover:text-indigo-800">
                                        parcourez
                                        <input ref="pdfInput" type="file" accept=".pdf" @change="handlePdfUpload" class="hidden" />
                                    </label>
                                </div>
                                <div v-if="form.pdf_file_name" class="text-sm text-green-600">Fichier sélectionné : {{ form.pdf_file_name }}</div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Contenu (optionnel)</label>
                            <textarea
                                v-model="form.content"
                                rows="10"
                                class="w-full resize-none rounded-xl border border-gray-200 px-4 py-3 focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                                placeholder="Contenu textuel du document..."
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4 border-t pt-6">
                            <button type="button" @click="closeModal" class="px-6 py-3 text-gray-600 transition-colors hover:text-gray-800">
                                Annuler
                            </button>
                            <button
                                type="submit"
                                :disabled="saving"
                                class="rounded-xl bg-indigo-600 px-6 py-3 text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {{ saving ? 'Enregistrement...' : editingDocument ? 'Modifier' : 'Créer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
