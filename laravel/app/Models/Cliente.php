<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
        'fecha_registro',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
    ];

    public $timestamps = false;

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente');
    }
}
