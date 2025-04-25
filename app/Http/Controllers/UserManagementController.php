<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GlobalRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{

    public function index()
    {
        $users = User::with('globalRole')->paginate(10);
        $roles = GlobalRole::all();

        return Inertia::render('dashboard/user/UserManagement', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'global_role_id' => 'required|exists:global_roles,id',
        ]);

        $user->update([
            'global_role_id' => $request->global_role_id,
        ]);

        return back()->with('success', 'Role updated.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'global_role_id' => 'required|exists:global_roles,id',
        ]);

        $user->update($request->only('name', 'email', 'phone', 'global_role_id'));

        // Returning updated user info in case needed on the frontend
        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted.');
    }


}

