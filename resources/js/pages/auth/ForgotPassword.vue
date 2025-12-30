<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Recuperar Senha" description="Informe o email do seu cadastro para receber o link de recuperar senha!">
        <Head title="Recuperar Senha" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="space-y-6">
            <form @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start ">
                    <Button                             
                    class="bg-green-700 cursor-pointer group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white  from-customprimarybutton to-customsecondarybutton hover:bg-green-600 focus:outline-none focus:bg-green-300 focus:ring-offset-2 focus:ring-customprimarybutton transition-all duration-200"
                            :class="{ 'opacity-70 cursor-not-allowed ': form.processing }"  
                            :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Enviar recuperação de senha no meu email
                    </Button>
                </div>
            </form>

            <div class="space-x-1 text-center text-sm text-muted-foreground">
                <span>Retornar para o acesso</span>
                <TextLink :href="route('login')" class="">Entrar</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
