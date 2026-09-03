<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ], $this->messages());

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ], $this->messages());

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            ...(isset($validated['password'])
                ? ['password' => Hash::make($validated['password'])]
                : []),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with(
                'error',
                'لا يمكنك حذف حسابك الخاص.'
            );
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح.');
    }

    /**
     * Messages de validation en arabe.
     */
    protected function messages()
    {
        return [
            'name.required' => 'اسم المستخدم مطلوب.',
            'name.string'   => 'اسم المستخدم يجب أن يكون نصاً.',
            'name.max'      => 'اسم المستخدم يجب ألا يتجاوز 255 حرفاً.',

            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email'    => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.unique'   => 'هذا البريد الإلكتروني مستخدم بالفعل.',

            'password.required'  => 'كلمة المرور مطلوبة.',
            'password.string'    => 'كلمة المرور يجب أن تكون نصاً.',
            'password.min'       => 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',

            'role.required' => 'الدور مطلوب.',
            'role.exists'   => 'الدور المحدد غير موجود.',
        ];
    }
}
