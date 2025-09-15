<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'mesa' => ['nullable', 'string', 'max:50'],
            'pedido' => ['required', 'array', 'min:1'],
            'pedido.*.nombre' => ['required', 'string', 'max:255'],
            'pedido.*.precio' => ['required', 'numeric', 'min:0'],
            'pedido.*.cantidad' => ['required', 'integer', 'min:1'],
            'pedido.*.descripcion' => ['nullable', 'string'],
            'pedido.*.categoria' => ['nullable', 'string', 'max:100'],
        ]);

        $total = collect($validated['pedido'])
            ->sum(fn ($item) => $item['precio'] * $item['cantidad']);

        $pedido = DB::transaction(function () use ($validated) {
            $cliente = Cliente::updateOrCreate(
                ['telefono' => $validated['telefono']],
                [
                    'nombre' => $validated['nombre'],
                    'direccion' => $validated['direccion'] ?? null,
                ]
            );

            $pedido = Pedido::create([
                'id_cliente' => $cliente->id,
                'fecha' => now(),
                'estado' => 'pendiente',
                'mesa' => $validated['mesa'] ?? null,
            ]);

            foreach ($validated['pedido'] as $detalle) {
                $pedido->detalles()->create([
                    'nombre_producto' => $detalle['nombre'],
                    'precio' => $detalle['precio'],
                    'cantidad' => $detalle['cantidad'],
                    'categoria' => $detalle['categoria'] ?? null,
                    'descripcion' => $detalle['descripcion'] ?? null,
                ]);
            }

            return $pedido;
        });

        return response()->json([
            'status' => 'success',
            'id_pedido' => $pedido->id,
            'total' => $total,
        ], 201);
    }
}
