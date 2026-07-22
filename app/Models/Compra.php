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
        'estado',
        'referencia_efectivo',
        'referencia_reembolso',
        'motivo_cancelacion',
        'pagado_at',
        'enviado_at',
        'entregado_at',
        'cancelado_at',
        'eliminado_at',
    ];

    protected $casts = [
        'precio_pagado' => 'decimal:2',
        'pagado_at' => 'datetime',
        'enviado_at' => 'datetime',
        'entregado_at' => 'datetime',
        'cancelado_at' => 'datetime',
        'eliminado_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function resena()
    {
        return $this->hasOne(Resena::class, 'compra_id');
    }
}
