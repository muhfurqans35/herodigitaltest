import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    allowedGlobalRoles?: string[];
    allowedTenantRoles?: string[];
    minSubscriptionTier?: SubscriptionPackageName;
}
export interface AuthProps {
    user: User;
    global_role: string;
    tenant_roles: Record<number, number>;
    subscription: Subscription;
}

export interface Subscription {
    package_name: string;
    is_paid: boolean;
}
  
export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}


export interface GlobalRole {
    id: number;
    name: string;
    label: string;
}
  
export interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    email_verified_at: string | null;
    global_role: GlobalRole;
    created_at: string;
    updated_at: string;
}

export interface Transaction {
    id: string;
    customer_name: string;
    transaction_date: string;
    total_price: number;
    items: Array<{
      id: number;
      name: string;
      quantity: number;
      price_per_unit: number;
      fields: Array<{
        name: string;
        price: number;
        quantity: number;
      }>;
    }>;
} 

export interface SubscriptionPackage {
    id: number;
    name: string;
    max_tenants: number;
    max_users_per_tenant: number;
    price_per_month: number;
}

export interface Subscription {
    id: string;
    user_id: number;
    subscription_package_id: number;
    starts_at: string;
    ends_at: string;
    package: SubscriptionPackage;
}



export type BreadcrumbItemType = BreadcrumbItem;
