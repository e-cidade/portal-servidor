<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Instituicao, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { formatDate } from '../lib/utils';
import Info from '@/components/Info.vue';

interface Dependente {
    default: () => ({})
        type: Object,
        required: true,
        rh31_codigo: number,
        rh31_nome: string,
        rh31_dtnasc: Date,
        rh31_sexo: string,
        rh16_pis: string,
        rh21_descr: string
}

defineProps<{
    instituicao: Instituicao;
    dependentes: Dependente[]; 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dependentes',
        href: '/dependentes',
    },
];
</script>


<template>

    <Head title="Dependentes" />
    <AppLayout :breadcrumbs="breadcrumbs" :instituicao="instituicao">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-2 overflow-x-auto">
            <div
                class=" relative min-h-[70vh] flex-1 rounded-xl border border-sidebar-border/30 md:min-h-min dark:border-sidebar-border text-sm">
                    <!-- Cabeçalho -->
                    <h2 class="bg-ring/70 font-bold p-2 text-white rounded-t-lg">Dependentes</h2>

                    <!-- Dados Pessoais -->
                    <div class="border rounded-b-lg border-ring/10" v-for="dependente in dependentes" :key="dependente?.rh31_codigo">
                        <div class="grid grid-cols-3 gap-x-3 gap-y-1 p-1">
                            <Info label="Nome:" :value="dependente?.rh31_nome" />
                            <Info label="Data Nascimento" :value="formatDate(dependente?.rh31_dtnasc)" />
                            <Info label="Parentesco:" :value="dependente?.rh31_sexo" />
                            <Info label="Salário Família:" :value="dependente?.rh16_pis" />
                            <Info label="Dep. IRRF: " :value="dependente?.rh21_descr" class="col-span-2" />
                        </div>        
                    </div>
                
                </div>
        </div>
    </AppLayout>
</template>
