<?php

namespace App\Http\Controllers;

use App\Models\Solicitante;
use Illuminate\Http\Request;

class SolicitanteController extends Controller
{
    public function index()
    {
        $solicitantes = Solicitante::orderBy('nombre')->paginate(12);
         if (request()->expectsJson()) {                         
            return response()->json($solicitantes);              
        }     
        return view('solicitantes.index', compact('solicitantes'));
    }

    public function create()
    {
        return view('solicitantes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
        ]);

        $s = Solicitante::create($data);
         if (request()->expectsJson()) {                       
            return response()->json($s, 201);         
        } 
        return redirect()->route('solicitantes.index')->with('ok', 'Solicitante creado');
    }

    public function show(Solicitante $solicitante)
    {
         if (request()->expectsJson()) {                         
            return response()->json($solicitante);              
        }   
        return view('solicitantes.show', compact('solicitante'));
    }

    public function edit(Solicitante $solicitante)
    {
         if (request()->expectsJson()) {                         
            return response()->json($solicitante);              
        }   
        return view('solicitantes.edit', compact('solicitante'));
    }

    public function update(Request $request, Solicitante $solicitante)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'telefono'  => 'nullable|string|max:50',
            'direccion' => 'nullable|string|max:255',
        ]);

        $solicitante->update($data);
          if (request()->expectsJson()) {                          
            return response()->json($solicitante->fresh());
        } 
        return redirect()->route('solicitantes.index')->with('ok', 'Solicitante actualizado');
    }

    public function destroy(Solicitante $solicitante)
    {
        $solicitante->delete();
          if (request()->expectsJson()) { 
            return response()->noContent(); 
        }      
        return back()->with('ok', 'Solicitante eliminado');
    }
}
