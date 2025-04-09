<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/usermanagement/Index', [
            'users' => User::with('roles')
                ->orderBy('created_at', 'desc')
                ->get(),
            'roles' => Role::all(),
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        return redirect()->back()->with('success', 'User berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        \Log::info('Attempting to delete user', ['user_id' => $user->id]);

        // Soft delete user
        $user->delete();

        \Log::info('User successfully deleted', ['user_id' => $user->id]);

        return back()->with('success', 'User berhasil dihapus.');
    }

}