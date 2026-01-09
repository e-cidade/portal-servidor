<script setup lang="ts">
// import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Instituicao, type NavItem } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LayoutGrid , PersonStanding, TreePalm, DollarSign, LockKeyhole, LogOut} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

defineProps<{
    instituicao: Instituicao;
}>();

const handleLogout = () => {
    router.flushAll();
};

const mainNavItems: NavItem[] = [
    {
        title: 'Dados Cadastrais',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Dependentes',
        href: '/dependentes',
        icon: PersonStanding,
    },
    // {
    //     title: 'Férias',
    //     href: '/ferias',
    //     icon: TreePalm,
    // },
    {
        title: 'Contra Cheque',
        href: '/contracheque',
        icon: DollarSign,
    },
    
    {
        title: 'Alterar Senha',
        href: '/settings/password',
        icon: LockKeyhole,
    },

    // AINDA NÃO SERA DIPONIVEL
    // {
    //     title: 'Rendimentos IRRF',
    //     href: '/rendimentos',
    //     icon: Logs,
    // },
];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo :instituicao="instituicao"/>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />                     
       
            <SidebarMenuItem class="px-2 py-0">
                <SidebarMenuButton as-child >
                    <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
                        <LogOut />
                        Sair
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
    
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
