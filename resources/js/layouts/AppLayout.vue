<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType, Instituicao } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    instituicao: Instituicao;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :instituicao="instituicao">
        <slot />
    </AppLayout>
</template>
