<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'receta_id',
        'precio_pagado',
        'metodo_pago',
        'tarjeta_ultimos4',
        'referencia_pago',
    ];

    protected $casts = [
        'precio_pagado' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}
