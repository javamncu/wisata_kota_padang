<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount(['reviews', 'favorites'])
            ->when($request->filled('q'), fn ($q) => $q
                ->where('name', 'like', '%'.$request->input('q').'%')
                ->orWhere('email', 'like', '%'.$request->input('q').'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::options(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Tidak bisa mengubah role akun sendiri.');
        }

        $request->validate(['role' => ['required', Rule::in(Role::values())]]);
        $user->update(['role' => $request->input('role')]);

        return back()->with('status', "Role {$user->name} diperbarui.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Tidak bisa memblokir akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active
            ? "{$user->name} diaktifkan."
            : "{$user->name} diblokir.");
    }
}
