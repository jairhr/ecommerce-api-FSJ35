<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Post(
        path: '/api/orders',
        summary: 'Create an order',
        description: 'Creates a new order for the authenticated user, validates product availability and stock, decreases stock, and calculates the order total.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Orders'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        minItems: 1,
                        items: new OA\Items(
                            type: 'object',
                            required: ['product_id', 'quantity'],
                            properties: [
                                new OA\Property(
                                    property: 'product_id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'quantity',
                                    type: 'integer',
                                    minimum: 1,
                                    example: 2
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Order created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                description: 'Product unavailable, insufficient stock, or validation error'
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/api/orders',
        summary: 'Get authenticated user orders',
        description: 'Returns all orders belonging to the authenticated user, including their products.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Orders'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Orders retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/api/orders/{order}',
        summary: 'Get an order',
        description: 'Returns a specific order belonging to the authenticated user, including its products and payments.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Orders'],
        parameters: [
            new OA\Parameter(
                name: 'order',
                description: 'Order ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Order retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'You are not authorized to access this order'
            ),
            new OA\Response(
                response: 404,
                description: 'Order not found'
            ),
        ]
    )]
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
