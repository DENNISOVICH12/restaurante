<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';

    protected $fillable = [
        'id_pedido',
        'nombre_producto',
        'precio',
        'cantidad',
        'categoria',
        'descripcion',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'cantidad' => 'integer',
    ];

    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido');
    }
}
