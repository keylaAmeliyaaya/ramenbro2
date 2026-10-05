<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();
        if ($request->filled('phone')) {
            $query->where('phone', $request->string('phone')->toString());
        }
        return ['data' => $query->paginate(50)];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string|max:500',
            'payment_method' => 'required|string|max:30',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.product_name' => 'required|string|max:150',
            'items.*.price' => 'required|integer|min:0',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.variant' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($data) {
            $subtotal = 0;
            $rows = [];

            foreach ($data['items'] as $item) {
                $product = !empty($item['product_id']) ? Product::find($item['product_id']) : null;
                $price = (int) $item['price'];

                // Produk dari UI legacy boleh belum memiliki ID yang sama dengan
                // katalog Laravel. Kalau ada ID valid, stok tetap dicek/dikurangi.
                if ($product) {
                    if ($product->stock < $item['qty']) {
                        abort(422, 'Stok ' . $product->name . ' tidak cukup.');
                    }
                    $price = (int) $item['price'];
                }

                $line = $price * $item['qty'];
                $subtotal += $line;
                $rows[] = [$product, $item, $price, $line];
            }

            $fee = $subtotal > 0 ? 5000 : 0;
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'subtotal' => $subtotal,
                'fee' => $fee,
                'total' => $subtotal + $fee,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => 'baru',
            ]);

            foreach ($rows as [$product, $item, $price, $line]) {
                $order->items()->create([
                    'product_id' => $product?->id,
                    'product_name' => $item['product_name'],
                    'variant' => $item['variant'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $price,
                    'subtotal' => $line,
                ]);

                if ($product) {
                    $product->decrement('stock', $item['qty']);
                }
            }

            return response()->json(['data' => $order->load('items')], 201);
        });
    }

    public function show(Order $order)
    {
        return ['data' => $order->load('items')];
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => 'sometimes|string|in:pending,paid,failed,refunded',
            'status' => 'sometimes|string|in:baru,diproses,siap,selesai,dibatalkan',
        ]);

        $order->update($data);
        return ['data' => $order->load('items')];
    }
}
