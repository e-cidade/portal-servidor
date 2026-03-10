<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

defineProps<{
    anos: number[]
}>();

const selectedAno = ref<number | string>('');

const imprimir = () => {
    if (selectedAno.value) {
        window.open(route('relatorio.rendimentos', { ano: selectedAno.value }), '_blank');
    }
};
</script>

<template>
    <Head title="Comprovante de Rendimentos" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Comprovante de Rendimentos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-4">Selecione o ano para gerar o comprovante de rendimentos:</p>

                        <div class="flex items-center gap-4">
                            <select v-model="selectedAno" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled>Selecione um ano</option>
                                <option v-for="ano in anos" :key="ano" :value="ano">{{ ano }}</option>
                            </select>

                            <button
                                @click="imprimir"
                                :disabled="!selectedAno"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                            >
                                Imprimir PDF
                            </button>
                        </div>

                        <div v-if="anos.length === 0" class="mt-4 text-red-600">
                            Nenhum registro de rendimento encontrado para o seu CPF.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
