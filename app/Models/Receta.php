<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'categoria_id',
        'titulo',
        'descripcion',
        'ingredientes',
        'pasos',
        'tiempo_preparacion',
        'imagen',
        'es_premium',
        'precio',
    ];

    protected $casts = [
        'es_premium' => 'boolean',
        'precio' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'receta_id');
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'receta_id');
    }

    public function compras()
    {
        return $this->hasMany(Compra::class, 'receta_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'receta_id');
    }
}
