<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Concerns\HasUuids;
class Solicitud extends Model {
    use UsesUuid, SoftDeletes;

    protected $table = 'solicitudes';
    protected $fillable = [
        'solicitante_id', 'creado_por_usuario_id',
        'tipo', 'estado', 'descripcion', 'detalle'
    ];

    protected $casts = [
        'detalle' => 'array',  
    ];

    public function solicitante(){ return $this->belongsTo(Solicitante::class); }
    public function creador(){ return $this->belongsTo(Usuario::class, 'creado_por_usuario_id'); }
    public function donaciones(){ return $this->hasMany(Donacion::class); }
}
