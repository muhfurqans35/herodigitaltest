<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/rolemanagement/Index', [
            'roles' => Role::withCount('users')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        Role::create($data);

        return redirect()->back()->with('success', 'Role berhasil dibuat.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
        ]);

        $role->update($data);

        return redirect()->back()->with('success', 'Role berhasil diupdate.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }

    public function show(Role $role)
    {
        return Inertia::render('dashboard/rolemanagement/ShowUsers', [
            'role' => $role->load('users:id,name,email')
        ]);
    }
}
