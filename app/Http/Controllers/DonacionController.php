<?php

namespace App\Http\Controllers;

use App\Models\Donacion;
use App\Models\Solicitud;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DonacionController extends Controller
{
    public function index()
    {
        $donaciones = Donacion::with(['solicitud.solicitante','usuario'])
            ->latest()->paginate(12);
         if (request()->expectsJson()) {                         
            return response()->json($donaciones);              
        }   
        return view('donaciones.index', compact('donaciones'));
    }

    public function create()
    {
        // Donación proviene de una Solicitud
        $solicitudes = Solicitud::orderByDesc('created_at')->get(['id','tipo','estado']);
        $usuarios    = Usuario::orderBy('nombre')->get(['id','nombre']); // donante opcional
        return view('donaciones.create', compact('solicitudes','usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'solicitud_id' => ['required','uuid', Rule::exists('solicitudes','id')],
            'usuario_id'   => ['nullable','uuid', Rule::exists('usuarios','id')],
            'titulo'       => ['required','string','max:255'],
            'cantidad'     => ['nullable','integer','min:0'],
            'estado'       => ['required','string','max:50'], // ofrecida, confirmada, entregada, cancelada
            'notas'        => ['nullable','string'],
        ]);

        $d = Donacion::create($data);
         if (request()->expectsJson()) {                       
            return response()->json($d, 201);         
        } 
        return redirect()->route('donaciones.index')->with('ok','Donación creada');
    }

    public function show(Donacion $donacion)
    {
        $donacion->load(['solicitud.solicitante','usuario']);
         if (request()->expectsJson()) {                         
            return response()->json($donacion);              
        } 
        return view('donaciones.show', compact('donacion'));
    }

    public function edit(Donacion $donacion)
    {
        $solicitudes = Solicitud::orderByDesc('created_at')->get(['id','tipo','estado']);
        $usuarios    = Usuario::orderBy('nombre')->get(['id','nombre']);
         if (request()->expectsJson()) {                         
            return response()->json($donacion);              
        } 
        return view('donaciones.edit', compact('donacion','solicitudes','usuarios'));
    }

    public function update(Request $request, Donacion $donacion)
    {
        $data = $request->validate([
            'solicitud_id' => ['required','uuid', Rule::exists('solicitudes','id')],
            'usuario_id'   => ['nullable','uuid', Rule::exists('usuarios','id')],
            'titulo'       => ['required','string','max:255'],
            'cantidad'     => ['nullable','integer','min:0'],
            'estado'       => ['required','string','max:50'],
            'notas'        => ['nullable','string'],
        ]);

        $donacion->update($data);
          if (request()->expectsJson()) {                          
            return response()->json($donacion->fresh());
        } 
        return redirect()->route('donaciones.index')->with('ok','Donación actualizada');
    }

    public function destroy(Donacion $donacion)
    {
        $donacion->delete();
         if (request()->expectsJson()) { 
            return response()->noContent(); 
        }   
        return back()->with('ok','Donación eliminada');
    }
}
