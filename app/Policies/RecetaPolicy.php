<?php

namespace App\Policies;

use App\Models\Receta;
use App\Models\User;

class RecetaPolicy
{
    // ¿Puede el usuario actualizar esta receta?
    public function update(User $user, Receta $receta): bool
    {
        return $user->id === $receta->usuario_id || $user->role === 'admin';
    }

    // ¿Puede el usuario eliminar esta receta?
    public function delete(User $user, Receta $receta): bool
    {
        return $user->id === $receta->usuario_id || $user->role === 'admin';
    }
}
