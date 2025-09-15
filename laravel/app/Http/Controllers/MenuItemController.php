<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $categoria = $request->query('categoria');

        $items = MenuItem::query()
            ->disponibles()
            ->when($categoria, fn ($query) => $query->where('categoria', $categoria))
            ->orderBy('nombre')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($items);
        }

        return view('menu.index', [
            'items' => $items,
            'categoria' => $categoria,
        ]);
    }
}
