<script setup lang="ts">
import type { Instituicao } from '@/types';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    instituicao?: Instituicao;
}>();

</script>

<template>
    <div class="flex aspect-square size-14 items-center justify-center rounded-md text-sidebar-primary-foreground">
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="m-1 grid flex-1 text-left text-sm">
        <span class="m-1 truncate leading-tight font-semibold whitespace-normal">{{ instituicao?.nomeinst }}</span>
    </div>
</template>
