<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user(); 
        return view('dashboard', compact('usuario'));
    }

    public function profileEdit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.edit', [
            'usuario' => $user,
            'email' => $user->email
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(), // Evita duplicados excepto el propio
            'current_password' => 'required',
            'password' => 'nullable|min:8|confirmed',
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'password.confirmed' => 'La confirmación no coincide.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Verificación de seguridad de la contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        // 2. Actualización de datos
        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 3. Guardado con validación de éxito
        if ($user->save()) {
            // Refrescamos la sesión para que los cambios se vean en toda la web de inmediato
            Auth::setUser($user); 
            return back()->with('success', '¡Perfil actualizado con éxito en la base de datos!');
        }

        return back()->withErrors(['error' => 'Hubo un problema al guardar los cambios.']);
    }

public function usersIndex()
{
    if (!Auth::check()) return redirect()->route('login');

    /** @var \App\Models\User $user */
    $user = Auth::user();
    $usuario = $user->name;

    // Obtenemos todos los usuarios para el listado
    $usuariosListado = User::all();

    return view('users.index', compact('usuario', 'usuariosListado'));
}
}