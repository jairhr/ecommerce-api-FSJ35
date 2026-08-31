<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $order = DB::transaction(function () use ($request, $user) {

                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'total' => 0,
                ]);

                $total = 0;

                foreach ($request->validated('items') as $item) {

                    $product = Product::where('id', $item['product_id'])
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (!$product) {
                        throw new \RuntimeException(
                            "Product {$item['product_id']} is not available."
                        );
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \RuntimeException(
                            "Insufficient stock for product: {$product->name}."
                        );
                    }

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $item['quantity'];

                    $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    $product->decrement('stock', $item['quantity']);

                    $total += $subtotal;
                }

                $order->update([
                    'total' => $total,
                ]);

                return $order;
            });

            $order->load([
                'items.product',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'data' => $order,
            ], 201);

        } catch (\RuntimeException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the order.',
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with([
                'items.product',
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => $orders,
        ]);
    }

    public function show(Order $order, Request $request): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this order.',
            ], 403);
        }

        $order->load([
            'items.product',
            'payments',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data' => $order,
        ]);
    }
}