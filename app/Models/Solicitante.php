<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solicitante extends Model {
    use UsesUuid, SoftDeletes;

    protected $table = 'solicitantes';
    protected $fillable = ['nombre','telefono','direccion'];

    public function solicitudes() { return $this->hasMany(Solicitud::class); }
}
