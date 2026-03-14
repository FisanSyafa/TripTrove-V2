<?php
// app/Http/Controllers/Admin/UserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash; // <-- Tambahkan ini
use Illuminate\Validation\Rules; // <-- Tambahkan ini

class UserController extends Controller
{
    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:user,admin,driver,guide',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')
                         ->with('message', 'User berhasil ditambahkan.');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        // Opsi: Tambahkan logika untuk mencegah admin menghapus dirinya sendiri
        // if ($user->id === auth()->id()) {
        //     return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        // }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('message', 'User berhasil dihapus.');
    }

    // Daftar semua user
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->input('search'), fn($q, $s) => 
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    // Tampilkan form edit user
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            // Definisikan role yang bisa dipilih
            'roles' => [ 
                ['value' => 'user', 'label' => 'User'],
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'guide', 'label' => 'Guide'],
                ['value' => 'driver', 'label' => 'Driver'],
            ] 
        ]);
    }

    // Update data user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['user', 'admin', 'guide', 'driver'])],
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        // Jangan biarkan admin mengunci akunnya sendiri dari admin
        if ($user->id === auth()->id() && $user->role === 'admin' && $validated['role'] !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('message', 'User berhasil diperbarui.');
    }
}