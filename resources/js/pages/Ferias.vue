<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Instituicao, type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '../lib/utils';


interface FeriasGozadas {
    r30_perai: Date;
    r30_peraf: Date;
    r30_abono: string;
    r30_per1i: Date;
    r30_per1f: Date;
    r30_dias1: number;
    r30_proc1: string;

    r30_per2i: Date;
    r30_per2f: Date;
    r30_dias2: number;
    r30_proc2: string;
}

interface Pagination<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
    next_page_url: string | null;
    prev_page_url: string | null;
}

interface FeriasAGozar {
    periodo_aquisitivo_inicial: Date;
    periodo_aquisitivo_final: Date;
}

const props = defineProps<{
    feriasGozadas: Pagination<FeriasGozadas>;
    feriasAGozar: FeriasAGozar[];
    instituicao: Instituicao;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Ferias',
        href: '/ferias',
    },
];

// Estado para controlar aba ativa
const activeTab = ref<'gozadas' | 'agozar'>('gozadas');

function changePage(page: number) {
    router.get(route('atendimentos.index'), { page });
}
</script>


<template>

    <Head title="Férias" />
    <AppLayout :breadcrumbs="breadcrumbs" :instituicao="instituicao">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-10 overflow-x-auto">
            <div class="flex border-b ">
                <button @click="activeTab = 'gozadas'" :class="[
                    'px-4 py-2 font-semibold cursor-pointer',
                    activeTab === 'gozadas' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500'
                ]">
                    Férias Gozadas
                </button>
                <button @click="activeTab = 'agozar'" :class="[
                    'px-4 py-2 font-semibold cursor-pointer',
                    activeTab === 'agozar' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500'
                ]">
                    Férias a Gozar
                </button>
            </div>
            <!-- Conteúdo Abas -->
            <div v-if="activeTab === 'gozadas'">
                <!-- Paginação -->
                <div class="flex p-4 space-x-1">
                    <template v-for="(link, index) in props.feriasGozadas?.links" :key="index">
                        <button v-if="link.url" @click="router.visit(link.url)" :class="[
                            'px-3 py-1 rounded cursor-pointer text-sm',
                            link.active
                                ? 'bg-green-500 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]" v-html="link.label" />
                        <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                    </template>
                </div>
                <div v-if="props?.feriasGozadas?.data?.length > 0" class="space-y-2">
                    <div v-for="(ferias, index) in props.feriasGozadas.data" :key="index"
                        class="border rounded-sm shadow-sm overflow-hidden">
                        <div class="bg-ring/70 px-4 py-2 flex justify-between">
                            <span class="font-semibold ">
                                Período Aquisitivo:
                                {{ formatDate(ferias?.r30_perai) }} à
                                {{ formatDate(ferias?.r30_peraf) }}
                            </span>
                            <span>Abono: {{ ferias.r30_abono }}</span>
                        </div>

                        <table class="w-full text-sm text-center border-t ">
                            <thead class="bg-ring/10">
                                <tr>
                                    <th class="px-3 py-2">Períodos de Gozo</th>
                                    <th class="px-3 py-2">Dias de Gozo</th>
                                    <th class="px-3 py-2">Mês Pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="px-3 py-2">
                                        {{ formatDate(ferias.r30_per1i) }} à
                                        {{ formatDate(ferias.r30_per1f) }}
                                    </td>
                                    <td class="px-3 py-2">{{ ferias.r30_dias1 }}</td>
                                    <td class="px-3 py-2">{{ ferias.r30_proc1 }}</td>
                                </tr>
                                <tr v-if="ferias.r30_proc2">
                                    <td class="px-3 py-2">
                                        {{ formatDate(ferias?.r30_per2i) }} à
                                        {{ formatDate(ferias?.r30_per2f) }}
                                    </td>
                                    <td class="px-3 py-2">{{ ferias.r30_dias2 }}</td>
                                    <td class="px-3 py-2">{{ ferias.r30_proc2 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <p v-else class="text-center text-gray-500">Nenhum Registro Encontrado</p>
            </div>

            <div v-else>
                <div v-if="props?.feriasAGozar?.length > 0" class="space-y-4">
                    <div v-for="(ferias, index) in props?.feriasAGozar" :key="index" class="border rounded-lg p-4">
                        <p>
                            <span class="font-semibold">Período Aquisitivo:</span>
                            {{ formatDate(ferias.periodo_aquisitivo_inicial) }} à
                            {{ formatDate(ferias.periodo_aquisitivo_final) }}
                        </p>
                    </div>

                    <p class="text-xs font-bold text-gray-600 p-2">
                        *Períodos aquisitivos de férias ainda não gozadas estão sujeitos a
                        avaliação pela Instituição
                    </p>
                </div>
                <p v-else class="text-center text-gray-500">Nenhum Registro Encontrado</p>
            </div>
        </div>
    </AppLayout>
</template>
