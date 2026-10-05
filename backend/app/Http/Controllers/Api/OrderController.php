<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:qris,cashier'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.id' => ['nullable'],
            'items.*.name' => ['nullable', 'string', 'max:150'],
            'items.*.product_name' => ['nullable', 'string', 'max:150'],
            'items.*.price' => ['nullable', 'integer', 'min:0'],
            'items.*.unit_price' => ['nullable', 'integer', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.options' => ['nullable', 'array'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $total = 0;
            $prepared = [];

            foreach ($data['items'] as $item) {
                $product = null;

                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                }

                $name = $item['product_name']
                    ?? $item['name']
                    ?? ($product?->name ?? 'Menu');

                $unitPrice = $item['unit_price']
                    ?? $item['price']
                    ?? ($product?->price ?? 0);

                $quantity = (int) $item['quantity'];
                $subtotal = $unitPrice * $quantity;

                $total += $subtotal;

                $prepared[] = [
                    'product_id' => $product?->id,
                    'product_name' => $name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'options' => $item['options'] ?? null,
                ];
            }

            $order = Order::create([
                'order_code' => 'RK-' . now()->format('ymd') . '-' . strtoupper(Str::random(4)),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cashier'
                    ? 'pending'
                    : 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($prepared as $item) {
                $order->items()->create($item);
            }

            return $order->load('items');
        });

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order,
        ], 201);
    }

    public function show(Order $order)
    {
        return response()->json([
            'success' => true,
            'data' => $order->load('items'),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => ['sometimes', 'in:pending,paid,failed,expired'],
            'order_status' => ['sometimes', 'in:pending,confirmed,preparing,ready,completed,cancelled'],
            'customer_name' => ['sometimes', 'string', 'max:150'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Order updated',
            'data' => $order->fresh()->load('items'),
        ]);
    }
}
