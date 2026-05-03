<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Guarda un nuevo usuario en la base de datos o restaura uno eliminado.
     */
    public function store(Request $request)
    {
        // 1. Validación inicial
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
            'role'     => 'required|string'
        ]);

        // 2. Lógica de recuperación/creación
        // Buscamos si el correo ya existe, incluyendo los registros con Soft Delete
        $usuarioExistente = User::withTrashed()->where('email', $request->email)->first();

        if ($usuarioExistente) {
            // Si el usuario existe pero está "eliminado", lo restauramos
            if ($usuarioExistente->trashed()) {
                $usuarioExistente->restore();
                $usuarioExistente->update([
                    'name'     => $request->name,
                    'role'     => $request->role,
                    'password' => Hash::make($request->password),
                ]);

                return redirect()->route('usuarios.index')
                                 ->with('success', '¡El usuario ya existía en el historial y ha sido reactivado con los nuevos datos!');
            }

            // Si el usuario existe y NO está eliminado, lanzamos error de duplicado manual
            return back()->withErrors(['email' => 'Este correo ya pertenece a un usuario activo.']);
        }

        // 3. Creación normal si no existe en ningún estado
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), 
            'role'     => $request->role,
        ]);

        return redirect()->route('usuarios.index')
                         ->with('success', '¡Usuario creado correctamente!');
    }

    /**
     * Actualiza los datos de un usuario existente.
     */
    public function update(Request $request, $id)
    {
        // 1. Buscamos al usuario o lanzamos 404 si no existe
        $user = User::findOrFail($id);

        // 2. Validación
        // 'unique:users,email,'.$id permite que el usuario mantenga su mismo correo sin error
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8', // Opcional al editar
            'role'     => 'required|string'
        ]);

        // 3. Actualización de datos básicos
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        // 4. Solo actualizamos la contraseña si el usuario escribió una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // 5. Redirección con mensaje de éxito
        return redirect()->route('usuarios.index')
                         ->with('success', '¡Usuario actualizado correctamente!');
    }

    /**
     * Elimina (desactiva) un usuario usando Soft Deletes.
     */
    public function destroy($id)
    {
        // Buscamos el usuario por su ID
        $user = User::findOrFail($id);

        // Ejecutamos el borrado. Al tener SoftDeletes en el modelo, 
        // solo se llenará la columna 'deleted_at' en la base de datos.
        $user->delete();

        // Redireccionamos al listado con un mensaje de éxito
        return redirect()->route('usuarios.index')
                         ->with('success', '¡Usuario eliminado correctamente del sistema!');
    }
}