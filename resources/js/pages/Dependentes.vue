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
        rh21_descr: string,
        rh31_depend: string,
        rh31_gparen: string,
        rh31_irf: string,
}

// Definir o tipo
type ParentescoCode = 'C' | 'F' | 'P' | 'M' | 'A';

// Criar o objeto tipado
const parentesco: Record<ParentescoCode, string> = {
  'C': 'Cônjuge',
  'F': 'Filho',
  'P': 'Pai',
  'M': 'Mãe',
  'A': 'Avó'
};

// Função helper tipada
const getParentesco = (codigo?: string): string => {
  return parentesco[codigo as ParentescoCode] || 'Outros';
};

type DependenteCode = 'C' | 'S';
const dependente: Record<DependenteCode, string> = {
    'C': 'Cálculo',
    'S': 'Sempre dependente'
}
const getDependente = (codigo?: string): string => {
    return dependente[codigo as DependenteCode] || 'Não dependente'
}

type DependenteIRFCode = '0' | '1' | '2' | '3' | '4' | '5' | '6' | '7';

const dependenteIRF: Record<DependenteIRFCode, string> = {
  '0': 'Não dependente',
  '1': 'Cônjuge, Companheiro(a)',
  '2': 'Filho(a)/Enteado(a), até 21 anos de idade',
  '3': 'Filho(a) ou enteado(a), 24 anos de idade cursando ensino superior',
  '4': 'Irmão(ã), neto(a) ou bisneto(a), até 21 anos',
  '5': 'Irmão(ã), neto(a) ou bisneto(a), de 21 a 24 anos c/ensino superior',
  '6': 'Pais, avós e bisavós',
  '7': 'Menor pobre até 21 anos, com a guarda judicial'
};

const getDependenteIRF = (codigo?: string): string => {
  return dependenteIRF[codigo as DependenteIRFCode] || 'Pessoa absolutamente incapaz';
};

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
                            <Info label="Parentesco:" :value="getParentesco(dependente?.rh31_gparen)" />
                            <Info label="Salário Família:" :value="getDependente(dependente?.rh31_depend)" />   
                            <Info label="Dependente IRRF:" :value="getDependenteIRF(dependente?.rh31_irf)" />                                             
                        </div>        
                    </div>
                
                </div>
        </div>
    </AppLayout>
</template>
