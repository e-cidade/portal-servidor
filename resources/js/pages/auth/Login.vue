<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import type { Instituicao } from '@/types';


defineProps<{
    status?: string;
    canResetPassword: boolean;
    instituicao: Instituicao;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase :title="instituicao.nomeinst" description="Entre com suas credenciais">
        <Head title="Acesso" />
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-50">
            <form @submit.prevent="submit" class="flex flex-col gap-6 space-y-6">
                <div class="grid gap-6 space-y-4">
                    <div class="grid gap-2">
                        <Label for="email">CPF:</Label>
                        <Input
                            id="login"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            v-model="form.login"
                            placeholder="Usuário"
                        />
                        <InputError :message="form.errors.login" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Senha:</Label>
                            <!-- <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                                Esqueci minha senha
                            </TextLink> -->
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button type="submit" 
                            class="bg-green-700 cursor-pointer group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white  from-customprimarybutton to-customsecondarybutton hover:opacity-90 focus:outline-none hover:bg-green-600 focus:ring-offset-2 focus:ring-customprimarybutton transition-all duration-200"
                            :class="{ 'opacity-70 cursor-not-allowed ': form.processing }" 
                            :tabindex="4" 
                            :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Entrando...' : 'Entrar' }}
                    </Button>
                </div>

            </form>
        </div>
    </AuthBase>
</template>
