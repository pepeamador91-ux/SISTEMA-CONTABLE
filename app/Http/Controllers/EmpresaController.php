<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Bitacora;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    /**
     * Muestra la lista de empresas con paginación.
     */
    public function index()
    {
        $empresas = Empresa::orderBy('id', 'desc')->paginate(25);
        $usuario = auth()->user();
        
        return view('empresas.index', compact('empresas', 'usuario'));
    }

    /**
     * Almacena una nueva empresa.
     */
    public function store(Request $request)
    {
        // 1. Validación estricta
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc'          => 'required|string|min:12|max:13|unique:empresas,rfc',
            'fiel_cer'     => 'nullable|file|mimes:cer,bin', // Validar extensiones comunes de certificados
            'fiel_key'     => 'nullable|file|mimes:key,bin',
        ], [
            'rfc.unique' => 'Este RFC ya se encuentra registrado.',
            'rfc.min'    => 'El RFC debe tener al menos 12 caracteres.',
        ]);

        $data = $request->all();
        $rfcUpper = strtoupper($request->rfc);
        $data['rfc'] = $rfcUpper;

        // 2. Gestión de archivos FIEL
        if ($request->hasFile('fiel_cer')) {
            $path = $request->file('fiel_cer')->store("certificados/{$rfcUpper}", 'public');
            $data['fiel_cer_path'] = basename($path);
        }

        if ($request->hasFile('fiel_key')) {
            $path = $request->file('fiel_key')->store("certificados/{$rfcUpper}", 'public');
            $data['fiel_key_path'] = basename($path);
        }

        // 3. Creación (El modelo cifra ciec/fiel_password automáticamente)
        $empresa = Empresa::create($data);

        // 4. Registro en Bitácora
        Bitacora::create([
            'user_id'    => Auth::id(),
            'accion'     => 'ALTA',
            'modelo'     => 'Empresa',
            'modelo_id'  => $empresa->id,
            'datos_nuevos' => [
                'razon_social' => $empresa->razon_social,
                'rfc'          => $empresa->rfc
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Empresa dada de alta correctamente.');
    }

    /**
     * Actualiza los datos de una empresa existente.
     */
    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc'          => 'required|string|min:12|max:13|unique:empresas,rfc,' . $empresa->id,
            'fiel_cer'     => 'nullable|file',
            'fiel_key'     => 'nullable|file',
        ]);

        $datosAnteriores = $empresa->toArray();
        $rfcUpper = strtoupper($request->rfc);
        
        $data = [
            'razon_social' => $request->razon_social,
            'rfc'          => $rfcUpper,
        ];

        // Lógica de archivos: Eliminar anteriores si se suben nuevos
        if ($request->hasFile('fiel_cer')) {
            // Borrar archivo físico anterior si existe
            if ($empresa->fiel_cer_path) {
                Storage::disk('public')->delete("certificados/{$empresa->rfc}/{$empresa->fiel_cer_path}");
            }
            $pathCer = $request->file('fiel_cer')->store("certificados/{$rfcUpper}", 'public');
            $data['fiel_cer_path'] = basename($pathCer);
        }

        if ($request->hasFile('fiel_key')) {
            // Borrar archivo físico anterior si existe
            if ($empresa->fiel_key_path) {
                Storage::disk('public')->delete("certificados/{$empresa->rfc}/{$empresa->fiel_key_path}");
            }
            $pathKey = $request->file('fiel_key')->store("certificados/{$rfcUpper}", 'public');
            $data['fiel_key_path'] = basename($pathKey);
        }

        // Solo actualizar campos sensibles si se enviaron
        if ($request->filled('ciec')) {
            $data['ciec'] = $request->ciec;
        }
        if ($request->filled('fiel_password')) {
            $data['fiel_password'] = $request->fiel_password;
        }

        $empresa->update($data);

        // Registro en Bitácora si hubo cambios
        if ($empresa->wasChanged()) {
            Bitacora::create([
                'user_id'          => Auth::id(),
                'accion'           => 'EDICION',
                'modelo'           => 'Empresa',
                'modelo_id'        => $empresa->id,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos'     => $empresa->fresh()->toArray(),
                'ip_address'       => $request->ip(),
            ]);
        }

        return back()->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * Elimina (Soft Delete) una empresa.
     */
    public function destroy(Request $request, $id)
    {
        $empresa = Empresa::findOrFail($id);
        $datosAnteriores = $empresa->toArray();

        $empresa->delete();

        Bitacora::create([
            'user_id'          => Auth::id(),
            'accion'           => 'ELIMINACION',
            'modelo'           => 'Empresa',
            'modelo_id'        => $id,
            'datos_anteriores' => $datosAnteriores,
            'ip_address'       => $request->ip(),
        ]);

        return back()->with('success', 'Empresa eliminada correctamente.');
    }
}