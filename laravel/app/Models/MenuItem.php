<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $table = 'menu_items';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'imagen',
        'categoria',
        'disponible',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public const CREATED_AT = 'fecha_creacion';

    public const UPDATED_AT = 'fecha_actualizacion';

    public function scopeDisponibles($query)
    {
        return $query->where('disponible', true);
    }
}
