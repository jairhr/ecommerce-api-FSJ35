<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    /**
     * Display a listing of active products.
     */
    #[OA\Get(
        path: '/api/products',
        summary: 'Get active products',
        description: 'Returns a list of all active products ordered by name.',
        tags: ['Products'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Products retrieved successfully'
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    /**
     * Display a specific product.
     */
    #[OA\Get(
        path: '/api/products/{product}',
        summary: 'Get a product',
        description: 'Returns a specific active product.',
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product retrieved successfully'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
        ]
    )]
    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product,
        ]);
    }

    /**
     * Store a newly created product.
     */
    #[OA\Post(
        path: '/api/products',
        summary: 'Create a product',
        description: 'Creates a new product. Authentication is required.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Products'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Wireless Headphones'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Bluetooth wireless headphones'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 99.99
                    ),
                    new OA\Property(
                        property: 'stock',
                        type: 'integer',
                        example: 25
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product created successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Update the specified product.
     */
    #[OA\Put(
        path: '/api/products/{product}',
        summary: 'Update a product',
        description: 'Updates an existing product. Authentication is required.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Updated Wireless Headphones'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Updated product description'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 109.99
                    ),
                    new OA\Property(
                        property: 'stock',
                        type: 'integer',
                        example: 30
                    ),
                    new OA\Property(
                        property: 'is_active',
                        type: 'boolean',
                        example: true
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->fresh(),
        ]);
    }

    /**
     * Remove the specified product.
     */
    #[OA\Delete(
        path: '/api/products/{product}',
        summary: 'Delete a product',
        description: 'Deletes a product. Authentication is required.',
        security: [
            ['sanctum' => []]
        ],
        tags: ['Products'],
        parameters: [
            new OA\Parameter(
                name: 'product',
                description: 'Product ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
                example: 1
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found'
            ),
        ]
    )]
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }
}
