<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return response()->json($products);
    }

    /**
     * Show a specific product by ID.
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
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'sku' => 'required|string|max:255|unique:products,sku',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'is_active' => 'nullable|boolean',
            ]);

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
     * Update the specified product.
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
                'quantity' => 'sometimes|required|integer',
                'is_active' => 'nullable|boolean',
            ]);

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
     * Soft delete the specified product.
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
