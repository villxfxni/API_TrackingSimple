<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index() {
        $usuarios = Usuario::orderByDesc('created_at')->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    public function create() {
        return view('usuarios.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email'  => 'required|email|unique:usuarios,email',
            'ci'     => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6'
        ]);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        Usuario::create($data);
        return redirect()->route('usuarios.index')->with('ok','Usuario creado');
    }

    public function show(Usuario $usuario) {
        return view('usuarios.show', compact('usuario'));
    }

    public function edit(Usuario $usuario) {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario) {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'email'  => 'required|email|unique:usuarios,email,'.$usuario->id.',id',
            'ci'     => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6'
        ]);
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }
        $usuario->update($data);
        return redirect()->route('usuarios.index')->with('ok','Usuario actualizado');
    }

    public function destroy(Usuario $usuario) {
        $usuario->delete();
        return back()->with('ok','Usuario eliminado');
    }
}
