<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Instituicao, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { formatDate } from '../lib/utils';
import Info from '@/components/Info.vue';


defineProps<{
    instituicao: Instituicao;
    dados_servidor: {
        default: () => ({})
        type: Object,
        required: true,
        rh01_regist: BigInteger,
        z01_cgccpf: string,
        z01_ident: string,
        z01_nome: string, 
        rh01_nasc: Date,
        z01_sexo: string,
        rh16_pis: string, 
        rh21_descr: string,
        rh08_descr: string,
        tipo_logradouro: string,
        z01_compl: string,
        z01_ender: string,
        z01_numero: string,
        z01_munic: string,
        z01_bairro: string,
        z01_cep: string,
        z01_uf: string,
        z01_telef: string,
        z01_telcel: string,
        z01_email: string,
        rh01_admiss: Date,
        horas_semanais: string,
        horas_mensais: string,
        r70_descr: string,
        rh37_descr: string,
        regime: string,
        rh37_cbo: string,
        padrao_inicial: string,
        padraoatual: string,
        rh44_codban: string,
        rh44_agencia: string,
        rh44_dvagencia: string,
        rh44_conta: string,
        rh44_dvconta: string,
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dados Cadastrais',
        href: '/dashboard',
    },
];

</script>


<template>

    <Head title="Dados Cadastrais" />

    <AppLayout :breadcrumbs="breadcrumbs" :instituicao="instituicao">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-2 overflow-x-auto">
            <div
                class=" relative min-h-[70vh] flex-1 rounded-xl border border-sidebar-border/30 md:min-h-min dark:border-sidebar-border text-sm">
                    <!-- Cabeçalho -->
                    <h2 class="bg-ring/70 font-bold p-2 text-white rounded-t-lg">Dados Cadastrais</h2>

                    <!-- Dados Pessoais -->
                    <div class="border rounded-b-lg border-ring/10">
                        <h3 class="bg-ring/20 font-semibold p-1 ">Dados Pessoais</h3>
                        <div class="grid grid-cols-3 gap-x-3 gap-y-1 p-1">
                            <Info label="Matrícula" :value="dados_servidor?.rh01_regist" />
                            <Info label="CPF" :value="dados_servidor?.z01_cgccpf" />
                            <Info label="RG" :value="dados_servidor?.z01_ident" />
                            <Info label="Nome" :value="dados_servidor?.z01_nome" class="col-span-3" />
                            <Info label="Data Nascimento" :value="formatDate(dados_servidor?.rh01_nasc)" />
                            <Info label="Sexo" :value="dados_servidor?.z01_sexo" />
                            <Info label="PIS/PASEP" :value="dados_servidor?.rh16_pis" />
                            <Info label="Grau de Instrução" :value="dados_servidor?.rh21_descr" class="col-span-2" />
                            <Info label="Estado Civil" :value="dados_servidor?.rh08_descr" />
                        </div>

                        <!-- Endereço -->
                        <h3 class="bg-ring/20 font-semibold p-1 ">Endereço</h3>
                        <div class="grid grid-cols-4 gap-x-3 gap-y-1 p-1">
                            <Info label="Tipo de Logradouro" :value="dados_servidor?.tipo_logradouro" />
                            <Info label="Endereço" :value="dados_servidor?.z01_ender" class="col-span-2" />
                        </div>
                        <div class="grid grid-cols-4 gap-x-3 gap-y-1 p-1">
                            <Info label="Complemento" :value="dados_servidor?.z01_compl" />
                            
                            <Info label="Nº" :value="dados_servidor?.z01_numero" />
                            <Info label="Cidade" :value="dados_servidor?.z01_munic" />
                            <Info label="Bairro" :value="dados_servidor?.z01_bairro" />
                            <Info label="CEP" :value="dados_servidor?.z01_cep" />
                            <Info label="UF" :value="dados_servidor?.z01_uf" />
                            <Info label="Telefone" :value="dados_servidor?.z01_telef" />
                            <Info label="Tel. Celular" :value="dados_servidor?.z01_telcel" />
                            <Info label="Email" :value="dados_servidor?.z01_email" class="col-span-2" />
                        </div>
                    </div>
                    <!-- dados_servidor? Admissionais -->
                    <div class="mt-2 border border-ring/10 rounded-t-lg">
                        <h3 class="bg-ring/70 font-semibold p-1 text-white rounded-t-lg">Dados Admissionais</h3>
                        <div class="grid grid-cols-3 gap-x-3 gap-y-1 p-1 ">
                           <Info label="Data Admissão" :value="formatDate(dados_servidor?.rh01_admiss)" />
                           <Info label="Horas Semanais" :value="dados_servidor?.horas_semanais" />
                           <Info label="Horas Mensais" :value="dados_servidor?.horas_mensais" />
                           <Info label="Lotação" :value="dados_servidor?.r70_descr" class="col-span-3" />
                           <Info label="Cargo" :value="dados_servidor?.rh37_descr" />
                           <Info label="Regime" :value="dados_servidor?.regime" />
                           <Info label="CBO" :value="dados_servidor?.rh37_cbo" />
                           <Info label="Padrão Inicial" :value="dados_servidor?.padrao_inicial" />
                           <Info label="Padrão Atual" :value="dados_servidor?.padraoatual" />
                           <Info label="Banco" :value="dados_servidor?.rh44_codban" />
                           <Info label="Agência" :value="dados_servidor?.rh44_agencia + '-' + dados_servidor?.rh44_dvagencia" />
                           <Info label="Conta" :value="dados_servidor?.rh44_conta + '-' + dados_servidor?.rh44_dvconta" />
                        </div>
                    </div>
                </div>
        </div>
    </AppLayout>
</template>
