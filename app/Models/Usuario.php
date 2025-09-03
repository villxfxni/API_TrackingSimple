<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model {
    use UsesUuid, SoftDeletes;

    protected $table = 'usuarios';
    protected $fillable = ['nombre','email','password','ci'];

    public function donaciones() { return $this->hasMany(Donacion::class); }
    public function solicitudesCreadas() { return $this->hasMany(Solicitud::class, 'creado_por_usuario_id'); }
}
