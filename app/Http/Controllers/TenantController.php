<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10)->through(function ($tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'description' => $tenant->description,
                'logo' => $tenant->logo ? Storage::url($tenant->logo) : null,
            ];
        });

        return Inertia::render('dashboard/tenant/TenantManagement', [
            'tenants' => $tenants
        ]);
    }

    public function users(Tenant $tenant)
    {
        // Ambil user beserta relasi role-nya
        $users = $tenant->users()
            ->with(['roles']) // eager load relasi roles (untuk akses nama role)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()->name, // ambil nama role pertama (karena 1 user bisa punya 1 role di tenant)
                    'role_id' => $user->pivot->role_id, // akses role_id dari pivot
                ];
            });

        return response()->json([
            'users' => $users
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,jfif,png']
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('tenant_logos', 'public');
        }

        DB::beginTransaction();
        try {
            // Buat tenant baru
            $tenant = Tenant::create($validated);

            // Ambil UUID untuk role "owner"
            $ownerRoleId = TenantRole::where('name', 'owner')->value('id');

            if (!$ownerRoleId) {
                throw new \Exception('Role "owner" tidak ditemukan.');
            }

            // Tambahkan user login sebagai owner ke tenant
            $tenant->users()->attach(Auth::id(), [
                'role_id' => $ownerRoleId,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Tenant berhasil ditambahkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan tenant: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,jfif,png']
        ]);

        // Tangani jika 'logo' ada tapi bukan file (dikirim null dari frontend)
        if ($request->has('logo') && !$request->hasFile('logo')) {
            unset($validated['logo']);
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
                Storage::disk('public')->delete($tenant->logo);
            }
            $validated['logo'] = $request->file('logo')->store('tenant_logos', 'public');
        }

        $tenant->update($validated);

        return redirect()->back()->with('success', 'Tenant berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->logo && Storage::disk('public')->exists($tenant->logo)) {
            Storage::disk('public')->delete($tenant->logo);
        }

        $tenant->delete();

        return redirect()->back()->with('success', 'Tenant berhasil dihapus.');
    }

    public function invite(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:employee,owner',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        // Mengambil tenant yang sesuai
        $tenant = Tenant::findOrFail($request->tenant_id);

        // Cek apakah user sudah ada berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Jika user tidak ditemukan, tampilkan pesan dan kembali
        if (!$user) {
            return redirect()->back()->with('error', 'User dengan email tersebut tidak ditemukan.');
        }

        // Menambahkan user ke tenant dengan role yang sesuai
        $roleId = TenantRole::where('name', $request->role)->value('id');
        $tenant->users()->syncWithoutDetaching([
            $user->id => ['role_id' => $roleId],
        ]);

        // Mengembalikan response sukses
        return redirect()->back()->with('success', 'User berhasil ditambahkan♣.');
    }
}
