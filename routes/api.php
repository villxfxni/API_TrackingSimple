<?php
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;
use App\Models\Solicitud;
use App\Models\Solicitante;
use App\Models\Donacion;
Route::get('/usuarios', fn() => Usuario::orderByDesc('created_at')->paginate(50));

Route::post('/usuarios', function (Request $r) {
    $d = $r->validate([
        'nombre'   => 'required|string|max:255',
        'email'    => 'required|email|unique:usuarios,email',
        'ci'       => 'nullable|string|max:50',
        'password' => 'nullable|string|min:6'
    ]);
    if (!empty($d['password'])) $d['password'] = bcrypt($d['password']);
    return response()->json(Usuario::create($d), 201);
});

Route::get('/usuarios/{usuario}', fn(Usuario $usuario) => $usuario);

Route::put('/usuarios/{usuario}', function (Request $r, Usuario $usuario) {
    $d = $r->validate([
        'nombre'   => 'required|string|max:255',
        'email'    => 'required|email|unique:usuarios,email,' . $usuario->id . ',id',
        'ci'       => 'nullable|string|max:50',
        'password' => 'nullable|string|min:6'
    ]);
    if (!empty($d['password'])) $d['password'] = bcrypt($d['password']); else unset($d['password']);
    $usuario->update($d);
    return $usuario->fresh();
});

Route::delete('/usuarios/{usuario}', function (Usuario $usuario) {
    $usuario->delete();
    return response()->noContent();
});

//SOLICITUDES
// List (paginated)
Route::get('/solicitudes', fn() => Solicitud::with(['solicitante','creador'])
    ->latest()->paginate(50));

// Create
Route::post('/solicitudes', function (Request $r) {
    $d = $r->validate([
        'solicitante_id'        => ['required','uuid','exists:solicitantes,id'],
        'creado_por_usuario_id' => ['nullable','uuid','exists:usuarios,id'],
        'tipo'                  => ['required','string','max:100'],
        'estado'                => ['required','string','max:50'],
        'descripcion'           => ['nullable','string'],
        'detalle'               => ['nullable','array'],
    ]);
    $s = \App\Models\Solicitud::create($d);
    return response()->json($s->load(['solicitante','creador']), 201);
});

// Read
Route::get('/solicitudes/{solicitud}', fn(\App\Models\Solicitud $solicitud) =>
    $solicitud->load(['solicitante','creador','donaciones'])
);

// Update
Route::put('/solicitudes/{solicitud}', function (Request $r, \App\Models\Solicitud $solicitud) {
    $d = $r->validate([
        'solicitante_id'        => ['required','uuid','exists:solicitantes,id'],
        'creado_por_usuario_id' => ['nullable','uuid','exists:usuarios,id'],
        'tipo'                  => ['required','string','max:100'],
        'estado'                => ['required','string','max:50'],
        'descripcion'           => ['nullable','string'],
        'detalle'               => ['nullable','array'],
    ]);
    $solicitud->update($d);
    return $solicitud->fresh()->load(['solicitante','creador']);
});

// Delete
Route::delete('/solicitudes/{solicitud}', function (\App\Models\Solicitud $solicitud) {
    $solicitud->delete();
    return response()->noContent();
});

//SOLICITANTES
Route::get('/solicitantes', fn() => Solicitante::orderBy('nombre')->paginate(50));

// Create
Route::post('/solicitantes', function (Request $r) {
    $d = $r->validate([
        'nombre'    => 'required|string|max:255',
        'telefono'  => 'nullable|string|max:50',
        'direccion' => 'nullable|string|max:255',
    ]);
    return response()->json(Solicitante::create($d), 201);
});

// Read
Route::get('/solicitantes/{solicitante}', fn(Solicitante $solicitante) => $solicitante);

// Update
Route::put('/solicitantes/{solicitante}', function (Request $r, Solicitante $solicitante) {
    $d = $r->validate([
        'nombre'    => 'required|string|max:255',
        'telefono'  => 'nullable|string|max:50',
        'direccion' => 'nullable|string|max:255',
    ]);
    $solicitante->update($d);
    return $solicitante->fresh();
});

// Delete
Route::delete('/solicitantes/{solicitante}', function (Solicitante $solicitante) {
    $solicitante->delete();
    return response()->noContent();
});

//DONACIONES
// Listar
Route::get('/donaciones', fn() =>
    Donacion::with(['solicitud.solicitante','usuario'])
        ->latest()
        ->paginate(50)
);

// Create
Route::post('/donaciones', function (Request $r) {
    $d = $r->validate([
        'solicitud_id' => ['required','uuid','exists:solicitudes,id'],
        'usuario_id'   => ['nullable','uuid','exists:usuarios,id'],
        'titulo'       => ['required','string','max:255'],
        'cantidad'     => ['nullable','integer','min:0'],
        'estado'       => ['required','string','max:50'],
        'notas'        => ['nullable','string'],
    ]);
    return response()->json(\App\Models\Donacion::create($d), 201);
});

// Read
Route::get('/donaciones/{donacion}', fn(\App\Models\Donacion $donacion) =>
    $donacion->load(['solicitud.solicitante','usuario'])
);

// Update
Route::put('/donaciones/{donacion}', function (Request $r, \App\Models\Donacion $donacion) {
    $d = $r->validate([
        'solicitud_id' => ['required','uuid','exists:solicitudes,id'],
        'usuario_id'   => ['nullable','uuid','exists:usuarios,id'],
        'titulo'       => ['required','string','max:255'],
        'cantidad'     => ['nullable','integer','min:0'],
        'estado'       => ['required','string','max:50'],
        'notas'        => ['nullable','string'],
    ]);
    $donacion->update($d);
    return $donacion->fresh()->load(['solicitud.solicitante','usuario']);
});

// Delete
Route::delete('/donaciones/{donacion}', function (\App\Models\Donacion $donacion) {
    $donacion->delete();
    return response()->noContent();
});