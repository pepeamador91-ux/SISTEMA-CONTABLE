<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // Necesario para el perfil

class UserController extends Controller
{
    /**
     * Muestra el listado de usuarios con paginación profesional.
     */
    public function index(Request $request)
    {
        // 1. Capturamos registros por página (por defecto 25)
        $perPage = $request->get('perPage', 25);

        // 2. Paginación: Esto arregla el error de "Method total does not exist"
        $usuariosListado = User::orderBy('id', 'desc')
                                ->paginate($perPage)
                                ->withQueryString();

        // 3. Obtenemos el usuario autenticado para el NavBar
        $usuario = Auth::user();

        return view('users.index', compact('usuariosListado', 'usuario'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos o restaura uno eliminado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role'     => 'required|string'
        ]);

        $usuarioExistente = User::withTrashed()->where('email', $request->email)->first();

        if ($usuarioExistente) {
            if ($usuarioExistente->trashed()) {
                $usuarioExistente->restore();
                $usuarioExistente->update([
                    'name'     => $request->name,
                    'role'     => $request->role,
                    'password' => Hash::make($request->password),
                ]);

                return redirect()->route('usuarios.index')
                                 ->with('success', '¡El usuario ya existía en el historial y ha sido reactivado!');
            }
            return back()->withErrors(['email' => 'Este correo ya pertenece a un usuario activo.']);
        }

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), 
            'role'     => $request->role,
        ]);

        return redirect()->route('usuarios.index')->with('success', '¡Usuario creado correctamente!');
    }

    /**
     * Actualiza los datos de un usuario existente.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role'     => 'required|string'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('usuarios.index')->with('success', '¡Usuario actualizado correctamente!');
    }

    /**
     * Elimina (desactiva) un usuario usando Soft Deletes.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('usuarios.index')->with('success', '¡Usuario eliminado correctamente del sistema!');
    }
}