<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import AppLogo from './AppLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';

import { BookOpen, BookUser, BookCheck, LayoutGrid, ScrollText, Package } from 'lucide-vue-next';
import type { NavItem, AuthProps } from '@/types';


const page = usePage();

const auth = page.props.auth as AuthProps;
const globalRole = auth.global_role;
const tenantRoles = auth.tenant_roles;

const subscriptionTierMap = {
    free: 0,
    basic: 1,
    advance: 2,
    intermediate: 3,
    business: 4,
} as const;

type SubscriptionPackageName = keyof typeof subscriptionTierMap;

const rawPackageName = auth.subscription?.package_name?.toLowerCase() || 'free';
const packageName = rawPackageName as SubscriptionPackageName;
const userSubscriptionTier = subscriptionTierMap[packageName];


// Optional: mapping role_id to name
const roleNameMap: Record<number, string> = {
    1: 'owner',
    2: 'employee',
};



const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Manajemen Transaksi',
        href: '/transactions',
        icon: ScrollText,
        allowedGlobalRoles: ['superadmin', 'customer'],
        allowedTenantRoles: ['owner', 'employee'],

    },
    {
        title: 'Manajemen Layanan',
        href: '/services',
        icon: Package,
        allowedGlobalRoles: ['superadmin', 'customer'],
        allowedTenantRoles: ['owner', 'employee'],
    },
    {
        title: 'Manajemen Pengguna',
        href: '/users',
        icon: BookUser,
        allowedGlobalRoles: ['superadmin'],
    },
    {
        title: 'Manajemen Tenant',
        href: '/tenants',
        icon: BookOpen,
        allowedGlobalRoles: ['superadmin'],
        allowedTenantRoles: ['owner'],
        minSubscriptionTier: 'basic',
    },
    {
        title: 'Manajemen Subscription',
        href: '/subscriptions',
        icon: BookCheck,
        allowedGlobalRoles: ['superadmin'],
    },
];

const filteredNavItems = mainNavItems.filter((item) => {
    // Cek role global
    const matchGlobal = item.allowedGlobalRoles?.includes(globalRole) ?? false;

    // Cek role tenant
    const matchTenant = item.allowedTenantRoles
        ? Object.values(tenantRoles).some((roleId) => {
            const roleName = roleNameMap[roleId] ?? '';
            return item.allowedTenantRoles!.includes(roleName);
        })
        : false;

    // Cek minimum tier
    const matchTier = item.minSubscriptionTier
        ? (subscriptionTierMap[item.minSubscriptionTier as SubscriptionPackageName] <= userSubscriptionTier)
        : false;
    // Salah satu dari tiga kondisi cukup
    return matchGlobal || matchTenant || matchTier;
});



</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                        <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
