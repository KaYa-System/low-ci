<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { BarChart3, Users, FileText, TrendingUp, Activity, Clock, ChevronRight } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const stats = [
    {
        title: 'Utilisateurs Actifs',
        value: '2,847',
        change: '+12%',
        trend: 'up',
        icon: Users,
        color: 'bg-blue-500'
    },
    {
        title: 'Procédures Traitées',
        value: '1,329',
        change: '+8%',
        trend: 'up',
        icon: FileText,
        color: 'bg-green-500'
    },
    {
        title: 'Taux de Satisfaction',
        value: '94.2%',
        change: '+2.1%',
        trend: 'up',
        icon: TrendingUp,
        color: 'bg-purple-500'
    }
];

const recentActivities = [
    { title: 'Nouvelle procédure ajoutée', time: 'Il y a 5 min', type: 'success' },
    { title: 'Rapport mensuel généré', time: 'Il y a 15 min', type: 'info' },
    { title: 'Utilisateur connecté', time: 'Il y a 23 min', type: 'default' },
    { title: 'Sauvegarde complétée', time: 'Il y a 1h', type: 'success' }
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- En-tête avec salutation -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tableau de Bord</h1>
                    <p class="text-muted-foreground mt-1">Aperçu de vos activités et performances</p>
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

            <!-- Statistiques principales -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card
                    v-for="stat in stats"
                    :key="stat.title"
                    class="group relative overflow-hidden hover-lift glass-card border-border/50"
                >
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            {{ stat.title }}
                        </CardTitle>
                        <div :class="`p-2 rounded-lg ${stat.color} bg-opacity-10`">
                            <component :is="stat.icon" :class="`h-5 w-5 ${stat.color.replace('bg-', 'text-')}`" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground mt-2 flex items-center gap-1">
                            <TrendingUp class="h-3 w-3 text-green-500" />
                            <span class="text-green-500 font-medium">{{ stat.change }}</span>
                            depuis le mois dernier
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Contenu principal -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Graphique principal -->
                <Card class="lg:col-span-2 glass-card border-border/50">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-primary" />
                            Évolution des Performances
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="h-[300px] flex items-center justify-center bg-gradient-to-br from-primary/5 to-secondary/5 rounded-lg border border-border/30">
                            <div class="text-center">
                                <BarChart3 class="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                                <p class="text-muted-foreground font-medium">Graphique des performances</p>
                                <p class="text-sm text-muted-foreground mt-1">Intégration en cours...</p>
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
                            class="flex items-start gap-3 p-3 rounded-lg hover:bg-accent/50 transition-colors cursor-pointer group"
                        >
                            <div class="mt-1">
                                <Badge
                                    :variant="activity.type as any"
                                    class="h-2 w-2 p-0 rounded-full"
                                />
                            </div>
                            <div class="flex-1 space-y-1">
                                <p class="text-sm font-medium group-hover:text-primary transition-colors">
                                    {{ activity.title }}
                                </p>
                                <p class="text-xs text-muted-foreground flex items-center gap-1">
                                    <Clock class="h-3 w-3" />
                                    {{ activity.time }}
                                </p>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground group-hover:text-primary transition-colors" />
                        </div>
                        <Button variant="ghost" class="w-full mt-4">
                            Voir toutes les activités
                        </Button>
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
                        <Button variant="outline" class="h-20 flex-col gap-2 hover-lift">
                            <FileText class="h-6 w-6" />
                            <span class="text-sm">Nouvelle Procédure</span>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2 hover-lift">
                            <Users class="h-6 w-6" />
                            <span class="text-sm">Gérer Utilisateurs</span>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2 hover-lift">
                            <BarChart3 class="h-6 w-6" />
                            <span class="text-sm">Générer Rapport</span>
                        </Button>
                        <Button variant="outline" class="h-20 flex-col gap-2 hover-lift">
                            <Activity class="h-6 w-6" />
                            <span class="text-sm">Analyser Données</span>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
