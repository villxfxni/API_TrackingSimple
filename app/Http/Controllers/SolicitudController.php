<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Solicitante;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SolicitudController extends Controller
{
    public function index()
    {
        $solicitudes = Solicitud::with(['solicitante','creador'])->latest()->paginate(12);
        if (request()->expectsJson()) {                     
            return response()->json($solicitudes);       
        }                                        
        return view('solicitudes.index', compact('solicitudes'));
    }
    public function create()
    {
        $solicitantes = Solicitante::orderBy('nombre')->get(['id','nombre']);
        $usuarios     = Usuario::orderBy('nombre')->get(['id','nombre']); // opcional: creador
        return view('solicitudes.create', compact('solicitantes','usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'solicitante_id'        => ['required','uuid', Rule::exists('solicitantes','id')],
            'creado_por_usuario_id' => ['nullable','uuid', Rule::exists('usuarios','id')],
            'tipo'                  => ['required','string','max:100'],
            'estado'                => ['required','string','max:50'],
            'descripcion'           => ['nullable','string'],
            'detalle'               => ['nullable','array'],
        ]);

        $solicitud = Solicitud::create($data);
        if (request()->expectsJson()) {                      
            return response()->json($solicitud, 201);        
        }                                                   
        return redirect()->route('solicitudes.index')->with('ok','Solicitud creada');
    }
    public function show(Solicitud $solicitud)
    {
        $solicitud->load(['solicitante','creador','donaciones']);
          if (request()->expectsJson()) {               
            return response()->json($solicitud);             
        } 
            return view('solicitudes.show', compact('solicitud'));
    }

    public function edit(Solicitud $solicitud)
    {
        $solicitantes = Solicitante::orderBy('nombre')->get(['id','nombre']);
        $usuarios     = Usuario::orderBy('nombre')->get(['id','nombre']);
        if (request()->expectsJson()) {                      
                return response()->json(compact('solicitud','solicitantes','usuarios')); 
            }  
        return view('solicitudes.edit', compact('solicitud','solicitantes','usuarios'));
    }

    public function update(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'solicitante_id'        => ['required','uuid', Rule::exists('solicitantes','id')],
            'creado_por_usuario_id' => ['nullable','uuid', Rule::exists('usuarios','id')],
            'tipo'                  => ['required','string','max:100'],
            'estado'                => ['required','string','max:50'],
            'descripcion'           => ['nullable','string'],
            'detalle'               => ['nullable','array'],
        ]);

        $solicitud->update($data);
          if (request()->expectsJson()) {                      
                return response()->json($solicitud->fresh());
            } 
        return redirect()->route('solicitudes.index')->with('ok','Solicitud actualizada');
    }

    public function destroy(Solicitud $solicitud)
    {
        $solicitud->delete();
         if (request()->expectsJson()) {
            return response()->noContent();                
        } 
        return back()->with('ok','Solicitud eliminada');
    }
}
