<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { type NavItem } from '@/types';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 border-t border-border/50 pt-4 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu class="space-y-1">
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        class="group text-muted-foreground hover:text-foreground hover:bg-accent/50 transition-all duration-200"
                        as-child
                    >
                        <a
                            :href="toUrl(item.href)"
                            :target="item.href.startsWith('http') ? '_blank' : '_self'"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg"
                        >
                            <component :is="item.icon" class="h-4 w-4 transition-transform duration-200 group-hover:scale-110" />
                            <span class="text-sm">{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
