<?php

namespace App\Models;

use App\Models\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donacion extends Model {
    use UsesUuid, SoftDeletes;

    protected $table = 'donaciones';
    protected $fillable = ['solicitud_id','usuario_id','titulo','cantidad','estado','notas'];

    public function solicitud() { return $this->belongsTo(Solicitud::class); }
    public function usuario() { return $this->belongsTo(Usuario::class); }
}
