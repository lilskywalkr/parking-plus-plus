<script setup lang="ts">
// import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type Auth, type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { SquareParking, LandPlot, ChartNoAxesCombined, CalendarRange } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth as Auth);

const mainNavItemsUser: NavItem[] = [
    {
        title: 'Parking',
        href: '/parking',
        icon: SquareParking,
    },
    {
        title: 'Your Stalls',
        href: '/stall',
        icon: LandPlot,
    },
];

const mainNavItemsAdmin: NavItem[] = [
    {
        title: 'Parking',
        href: '/parking',
        icon: SquareParking,
    },
    {
        title: 'Statistics',
        href: '/statistics',
        icon: ChartNoAxesCombined,
    },
    {
        title: 'Record',
        href: '/record',
        icon: CalendarRange,
    },
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
                        <Link :href="route('parking')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItemsAdmin" v-if="auth.user.is_admin" />
            <NavMain :items="mainNavItemsUser" v-else />
        </SidebarContent>

        <SidebarFooter>
            <!--            <NavFooter :items="footerNavItems" />-->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
