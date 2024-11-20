<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     description="Product model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Product ID"),
 *         @OA\Property(property="name", type="string", description="Product name"),
 *         @OA\Property(property="description", type="string", description="Product description"),
 *         @OA\Property(property="sku", type="string", description="Product SKU"),
 *         @OA\Property(property="price", type="number", format="float", description="Product price"),
 *         @OA\Property(property="quantity", type="integer", description="Product quantity"),
 *         @OA\Property(property="is_active", type="boolean", description="Product active status"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp")
 *     }
 * )
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List active products",
     *     description="Retrieve a list of active products ordered by name.",
     *     tags={"Products"},
     *     security={{"apiAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of active products",
     *     @OA\JsonContent(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Product")
     *     )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get product by ID",
     *     description="Retrieve a specific product by its ID.",
     *     tags={"Products"},
     *     security={{"apiAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Product retrieved successfully.',
                'data' => $product,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     description="Create a new product with validation and optional deactivation if quantity is zero.",
     *     tags={"Products"},
     *     security={{"apiAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "sku", "price", "quantity"},
     *             @OA\Property(property="name", type="string", example="MacBook Pro"),
     *             @OA\Property(property="description", type="string", example="Apple MacBook Pro with M1 Pro chip, 16GB RAM, 512GB SSD"),
     *             @OA\Property(property="sku", type="string", example="MBPM1P16512"),
     *             @OA\Property(property="price", type="number", format="float", example=1299.00),
     *             @OA\Property(property="quantity", type="integer", example=2),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'sku' => 'required|string|max:255|unique:products,sku',
                'price' => 'required|numeric',
                'quantity' => 'required|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if (isset($validatedData['quantity']) && $validatedData['quantity'] === 0) {
                $validatedData['is_active'] = false;
            }

            $product = Product::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     description="Update the specified product by its ID.",
     *     tags={"Products"},
     *     security={{"apiAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "sku", "price", "quantity"},
     *             @OA\Property(property="name", type="string", example="MacBook Pro Updated"),
     *             @OA\Property(property="description", type="string", example="Apple MacBook Pro Updated with M1 Pro chip, 16GB RAM, 512GB SSD"),
     *             @OA\Property(property="sku", type="string", example="MBPM1P16512"),
     *             @OA\Property(property="price", type="number", format="float", example=1399.00),
     *             @OA\Property(property="quantity", type="integer", example=5),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'sku' => 'sometimes|required|string|max:255|unique:products,sku,' . $product->id,
                'price' => 'sometimes|required|numeric',
                'quantity' => 'sometimes|required|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if (isset($validatedData['quantity']) && $validatedData['quantity'] === 0) {
                $validatedData['is_active'] = false;
            }

            $product->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully.',
                'data' => $product,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Soft delete a product",
     *     description="Mark a product as inactive (soft delete).",
     *     tags={"Products"},
     *     security={{"apiAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product soft deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product soft deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function delete($id)
    {
        try {
            $product = Product::findOrFail($id);

            $product->update(['is_active' => false]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product soft deleted successfully.',
            ], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ], 404);
        }
    }
}
