<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\UserController; 

// 1. Ruta para VER la pantalla de Login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('login');

// 2. Ruta para RECIBIR los datos del Login
Route::post('/', function (Request $request) {
    $credenciales = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credenciales)) {
        $request->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'El correo o la contraseña no coinciden con nuestros registros.',
    ])->onlyInput('email'); 
});

// 3. Ruta del Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// 4. MÓDULO DE PERFIL
Route::get('/perfil', [DashboardController::class, 'profileEdit'])->name('profile.edit');
Route::put('/perfil', [DashboardController::class, 'profileUpdate'])->name('profile.update');

// 5. Ruta para Cerrar Sesión
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// 6. Ruta para VER el listado de Usuarios
Route::get('/usuarios', [DashboardController::class, 'usersIndex'])->name('usuarios.index');

// 7. Ruta para CREAR un nuevo Usuario 
// El nombre 'usuarios.store' es el que invoca tu formulario
Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');

// 8. Ruta para ACTUALIZAR un Usuario existente
Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');

// 9. Ruta para ELIMINAR un Usuario (SOFT DELETE)
Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');