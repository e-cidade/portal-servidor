<script setup lang="ts">
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Instituicao, type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/formatters';
import { computed, watch, reactive } from 'vue';


interface Rubrica {
    rubric: string;
    descr: string;
    tipo: number;
    quant: number | string;
    provento: number | string;
    desconto: number | string;
}

interface Resumo {
    base_prev: number;
    base_irrf: number;
    tot_desc: number;
    tot_prov: number;
    saldo_devedor: number;
    base_irrf_ferias: number;
}

interface Rubricas {
    rubricas: Rubrica[]
    resumo: Resumo;
}

interface Periodo {
    regist: number;
    anousu: number;
    mesusu: number;
}

interface TipoFolha {
    codigo: number,
    descr: string,
}

interface Filter {
    registro: number;
    ano: number;
    mes: number;
    tipo_folha: number;
}

const props = defineProps<{
    instituicao: Instituicao;
    matriculas?: Periodo[];
    matricula?: string;
    cpf?: string;
    rg?: string;
    nome?: string;
    tipos_folha?: TipoFolha[],
    vinculo?: string;
    contra_cheque?: Rubricas;
    filters: Filter
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contra Cheque',
        href: '/contracheque',
    },
];


const form = useForm<{ tipo_folha: number , registro: number, ano: number, mes: number }>({
    tipo_folha: props.filters?.tipo_folha ?? 0,
    registro: props.filters?.registro ?? 0,
    ano: props.filters?.ano ?? 0,
    mes: props.filters?.mes ?? 0,
})

const submit = (e: Event) => {
    e.preventDefault();
    form.post(route('contracheque'), {
        preserveScroll: true,
        preserveState: true,
        preserveUrl: true
    });
};


const registros = computed(() => {
    return [...new Set(props.matriculas?.map(r => r.regist))];
});

const anos = computed(() => {
    if (!form.registro) return [];
    return [
        ...new Set(
            (props.matriculas ?? [])
                .filter(r => r.regist === form.registro)
                .map(r => r.anousu)
        )
    ];
});

const anosUnicos = computed(() => {
    const valores = anos.value.map(m => Number(m));
    const unicos = [...new Set(valores)];
    return unicos.sort((a, b) => b - a);
});

const mesUnicos = computed(() => {
    if (!form.ano) return [];
    return props.matriculas?.filter(r => r.anousu === form.ano && r.regist === form.registro)
        .map(r => r.mesusu);
});

// Adicione watchers para resetar os campos dependentes
watch(() => form.registro, (novoValor, valorAnterior) => {
    // Quando registro muda, limpa ano e mês
    if (novoValor !== valorAnterior) {
        form.ano = 0; // ou '' dependendo do seu caso
        form.mes = 0;
    }
});

watch(() => form.ano, (novoValor, valorAnterior) => {
    // Quando ano muda, limpa apenas o mês
    if (novoValor !== valorAnterior) {
        form.mes = 0;
    }
});

// botão só habilita se o form estiver válido
const isDisabled = computed(() => {
    return form.ano === 0 || form.mes === 0 || form.tipo_folha === 0
})

// Sempre que todos os campos tiverem valor, faz o POST
watch(
    () => [form.registro, form.ano, form.mes],
    ([matricula, ano, mes]) => {
        if (matricula && ano && mes) {
            router.visit(route('folha.tipos'), {
                method: 'post',
                data: form.data(), // dados enviados
                preserveScroll: true,
                preserveState: true,
                preserveUrl: true,
                only: ['tipos_folha' , 'contra_cheque'], // atualiza só essa prop
                replace: true,        // não cria histórico
            })
        }
    }
)

const abrirRelatorio = () => {
  const params = new URLSearchParams({
      ano: form.ano.toString(),
      mes: form.mes.toString(),
      tipo_folha: form.tipo_folha.toString(),
      registro: form.registro.toString(),
  }).toString();

  window.open(`${route('relatorio.contracheque')}?${params}`, '_blank');
};

</script>

<template>

    <Head title="Contra cheque" />

    <AppLayout :breadcrumbs="breadcrumbs" :instituicao="instituicao">
        <div class="p-4  min-h-screen text-xs">
            <form @submit.prevent="submit"
                class="flex flex-col gap-4 sm:gap-6 space-y-4 sm:space-y-4 border-1 rounded border-ring/20">
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-2 space-y-4 sm:space-y-0 p-5">
                    <div class="flex flex-col">
                        <Label class="p-2 font-semibold text-ring/100">Matrícula:</Label>
                        <select id="registro" v-model.number="form.registro" required
                            class="border border-ring/50 rounded-sm px-6 py-2 bg-white text-sm text-ring/100 focus:outline-none focus:ring-2 focus:ring-customprimarybutton">
                            <option :value="0">Selecione a matrícula</option>
                            <option v-for="(registro, index) in registros" :key="index" :value="registro">
                                {{ registro }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <Label class="p-2 font-semibold text-ring/100">Ano:</Label>
                        <select id="ano" v-model.number="form.ano" required @change="form.mes = 0; form.tipo_folha = 0"
                            class="border border-ring/50 rounded-sm px-6 py-2 bg-white text-sm text-ring/100 focus:outline-none focus:ring-2 focus:ring-customprimarybutton">
                            <option :value="0">Selecione o ano</option>
                            <option v-for="(ano, index) in anosUnicos" :key="index" :value="ano">
                                {{ ano }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <Label class="p-2 font-semibold text-ring/100">Mês:</Label>
                        <select id="mes" v-model.number="form.mes" required @change="form.tipo_folha = 0"
                            class="border border-ring/50 rounded-sm px-6 py-2 bg-white text-sm text-ring/100 focus:outline-none focus:ring-2 focus:ring-customprimarybutton">
                            <option :value="0">Selecione o mês</option>
                            <option v-for="(mes, index) in mesUnicos" :key="index" :value="mes" class="text-ring/100">
                                {{ mes }}
                            </option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <Label class="p-2 font-semibold text-ring/100">Tipo de Folha:</Label>
                        <select v-model.number="form.tipo_folha"
                            class="border border-ring/50 rounded-sm px-6 py-2 bg-white text-sm text-ring/100 focus:outline-none focus:ring-2 focus:ring-customprimarybutton">
                            <option v-for="tipo in props.tipos_folha" :key="tipo.codigo" :value="tipo.codigo">{{
                                tipo.descr }}</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:mt-6 space-y-2 sm:space-y-0 sm:space-x-4">
                        <button :disabled="isDisabled"
                            class="cursor-pointer group relative flex justify-center h-10 py-2 px-4 mx-2 border border-transparent text-sm font-medium rounded-md text-white transition-all duration-200"
                            :class="[
                                isDisabled
                                    ? 'bg-gray-400 cursor-not-allowed opacity-70'
                                    : 'from-customprimarybutton to-customsecondarybutton bg-ring/100 hover:opacity-90 hover:bg-ring/100 focus:ring-offset-2 focus:ring-customprimarybutton'
                            ]" :tabindex="4">
                            Pesquisar
                        </button>
                        <input :disabled="isDisabled" @click="abrirRelatorio" type="button" value="Imprimir"
                            class="cursor-pointer group relative flex justify-center h-10 py-2 px-4 mx-2 border border-transparent text-sm font-medium rounded-md text-white transition-all duration-200"
                            :class="[
                                isDisabled
                                    ? 'bg-gray-400 cursor-not-allowed opacity-70'
                                    : 'from-customprimarybutton to-customsecondarybutton bg-ring/100 hover:opacity-90 hover:bg-ring/100 focus:ring-offset-2 focus:ring-customprimarybutton'
                            ]" :tabindex="4" />

                    </div>
                </div>
            </form>
            <div v-if="props.contra_cheque?.rubricas?.length" class="space-y-2 text-xs justify-items-center">
                <!-- Dados Pessoais -->
                <!-- <div class="bg-white shadow rounded-b-md mt-0 border border-ring/600">
                    <div class="bg-ring/100 font-bold px-4 py-2 text-ring/800">DADOS PESSOAIS</div>
                    <div class="grid grid-cols-2 gap-4 p-4 text-sm">
                        <p><span class="font-semibold">Instituição:</span> {{ instituicao.nomeinst }}</p>
                        <p><span class="font-semibold">Matrícula:</span> {{ matricula }}</p>
                        <p><span class="font-semibold">CPF:</span> {{ cpf }}</p>
                        <p><span class="font-semibold">RG:</span> {{ rg }}</p>
                        <p><span class="font-semibold">Nome:</span> {{ nome }}</p>
                        <p><span class="font-semibold">Tipo de Folha:</span> {{ tipos_folha }}</p>
                        <p><span class="font-semibold">Vínculo:</span> {{ vinculo }}</p>
                    </div>
                </div> -->

                <!-- Rubricas -->
                <div class="bg-white rounded mt-6 border border-ring/10 overflow-x-auto">
                    <div class="bg-ring/30 font-bold px-4 py-2 text-ring/100">RUBRICAS</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-ring/10 text-ring/100 text-left text-xs">
                                    <th class="px-2 py-1">Rubrica</th>
                                    <th class="px-2 py-1">Descrição</th>
                                    <th class="px-2 py-1 text-center">Quantidade</th>
                                    <th class="px-2 py-1 text-right">Proventos</th>
                                    <th class="px-2 py-1 text-right">Descontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(rubrica, i) in props.contra_cheque?.rubricas" :key="i"
                                    class="border-b hover:bg-green-50 text-xs">
                                    <td class="px-2 py-1">{{ rubrica?.rubric }}</td>
                                    <td class="px-2 py-1">{{ rubrica?.descr }}</td>
                                    <td class="px-2 py-1 text-center">{{ rubrica?.quant }}</td>
                                    <td class="px-2 py-1 text-right">{{ formatCurrency(rubrica?.provento) }}</td>
                                    <td class="px-2 py-1 text-right">{{ formatCurrency(rubrica?.desconto) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumo -->
                <div class="bg-white mt-6 border border-ring/10 rounded overflow-x-auto">
                    <div class="bg-ring/30 font-bold px-4 py-2 ">RESUMO</div>
                    <table class="w-full text-x-sm border-collapse">
                        <thead>
                            <tr class="bg-ring/10  text-left">
                                <th class="px-3 py-2">Base p/ IRRF</th>
                                <th class="px-3 py-2">Total Vantagens</th>
                                <th class="px-3 py-2">Total Provendos</th>
                                <th class="px-3 py-2">Total Descontos</th>
                                <th class="px-3 py-2">Total Líquido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-3 py-2">{{ formatCurrency(props.contra_cheque?.resumo?.base_irrf) }}</td>
                                <td class="px-3 py-2">{{ formatCurrency(props.contra_cheque?.resumo?.tot_prov) }}</td>
                                <td class="px-3 py-2">{{ formatCurrency(props.contra_cheque?.resumo?.tot_prov) }}</td>
                                <td class="px-3 py-2">{{ formatCurrency(props.contra_cheque?.resumo?.tot_desc) }}</td>
                                <td class="px-3 py-2">{{ formatCurrency(props.contra_cheque?.resumo?.tot_prov - props.contra_cheque?.resumo?.tot_desc) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
