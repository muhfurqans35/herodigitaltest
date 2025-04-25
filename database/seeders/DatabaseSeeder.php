<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SubscriptionPackage;
use App\Models\GlobalRole;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Insert Global Roles
        $roles = [
            ['name' => 'superadmin', 'label' => 'Super Admin'],
            ['name' => 'admin', 'label' => 'Admin'],
            ['name' => 'customer', 'label' => 'Customer'],
        ];

        foreach ($roles as $role) {
            GlobalRole::firstOrCreate(['name' => $role['name']], ['label' => $role['label']]);
        }

        // Insert Subscription Packages
        $packages = [
            [
                'name' => 'Free',
                'max_tenants' => 1,
                'max_users_per_tenant' => 1,
                'price_per_month' => 0,
                'features_json' => json_encode([
                    'max_transactions' => 5,
                    'max_services' => 2,
                    'transaction_history_months' => 1,
                    'advanced_services' => false,
                    'export_reports' => false,
                    'digital_receipt' => true,
                    'basic_dashboard' => false,
                    'priority_support' => false,
                ]),
            ],
            [
                'name' => 'Basic',
                'max_tenants' => 1,
                'max_users_per_tenant' => 3,
                'price_per_month' => 50000,
                'features_json' => json_encode([
                    'max_transactions' => -1, // unlimited
                    'max_services' => -1, // unlimited
                    'transaction_history_months' => 3,
                    'advanced_services' => true,
                    'export_reports' => true,
                    'digital_receipt' => true,
                    'basic_dashboard' => true,
                    'priority_support' => false,
                ]),
            ],
            [
                'name' => 'Pro',
                'max_tenants' => 2,
                'max_users_per_tenant' => 5,
                'price_per_month' => 100000,
                'features_json' => json_encode([
                    'max_transactions' => -1, // unlimited
                    'max_services' => -1, // unlimited
                    'transaction_history_months' => -1, // unlimited
                    'advanced_services' => true,
                    'export_reports' => true,
                    'dynamic_export_filters' => true,
                    'digital_receipt' => true,
                    'advanced_dashboard' => true,
                    'priority_support' => true,
                ]),
            ],
        ];

        foreach ($packages as $package) {
            SubscriptionPackage::firstOrCreate(
                ['name' => $package['name']],
                [
                    'max_tenants' => $package['max_tenants'],
                    'max_users_per_tenant' => $package['max_users_per_tenant'],
                    'price_per_month' => $package['price_per_month'],
                    'features_json' => $package['features_json'],
                ]
            );
        }

        // Rest of your code...
        // Ambil ID global role
        $customerRole = GlobalRole::where('name', 'customer')->first();
        $adminRole = GlobalRole::where('name', 'admin')->first();
        $superadminRole = GlobalRole::where('name', 'superadmin')->first();

        // Test Users
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test1234'),
                'global_role_id' => $customerRole->id,
            ]
        );

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'global_role_id' => $adminRole->id,
            ]
        );

        // Superadmin
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Test Superadmin',
                'password' => Hash::make('password'),
                'global_role_id' => $superadminRole->id,
            ]
        );

        // Insert Tenant Roles
        $roles = [
            ['name' => 'owner', 'label' => 'Pemilik'],
            ['name' => 'employee', 'label' => 'Karyawan'],
        ];

        foreach ($roles as $role) {
            DB::table('tenant_roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'id' => Str::uuid(),
                    'label' => $role['label'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}