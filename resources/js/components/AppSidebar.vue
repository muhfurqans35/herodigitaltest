<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, HomeIcon, UserCog, Gamepad2, BookCheck, UserPen, MessageSquareDiff, Sheet } from 'lucide-vue-next';


const mainNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/',
        icon: HomeIcon,
    },    
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '/dashboard/usermanagement',
        icon: UserCog,
    },
    {
        title: 'Role Management',
        href: '/dashboard/roles',
        icon: UserPen,
    },
    {
        title: 'Booking Management',
        href: '/dashboard/bookings',
        icon: BookCheck,
    },
    {
        title: 'Service Management',
        href: '/dashboard/services',
        icon: Gamepad2,
    },
    {
        title: 'Review Management',
        href: '/dashboard/reviews',
        icon: MessageSquareDiff,
    },
    {
        title: 'Excel Import/Export',
        href: '/dashboard/excel',
        icon: Sheet,
    },
];

const footerNavItems: NavItem[] = [

];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <h1 class="text-sm font-bold text-red-600">RENTAL PS</h1>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
