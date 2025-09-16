<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-gray-100 p-1">
        <div class="flex items-center rounded-md px-3.5 py-1.5 bg-white shadow-sm">
            <Sun class="-ml-1 h-4 w-4 text-gray-700" />
            <span class="ml-1.5 text-sm text-gray-700">Mode clair</span>
        </div>
    </div>
</template>
