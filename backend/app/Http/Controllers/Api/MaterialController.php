<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query();

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'unit' => ['required', 'string', 'max:30'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'price' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['stock'] = $data['stock'] ?? 0;
        $data['minimum_stock'] = $data['minimum_stock'] ?? 0;
        $data['price'] = $data['price'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return response()->json([
            'success' => true,
            'message' => 'Material created',
            'data' => Material::create($data),
        ], 201);
    }

    public function show(Material $material)
    {
        return response()->json([
            'success' => true,
            'data' => $material,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:150'],
            'unit' => ['sometimes', 'string', 'max:30'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'minimum_stock' => ['sometimes', 'integer', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'price' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $material->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Material updated',
            'data' => $material->fresh(),
        ]);
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material deleted',
        ]);
    }
}
