<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Schema(
 *     schema="Sale",
 *     type="object",
 *     title="Sale",
 *     description="Sale model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Sale ID"),
 *         @OA\Property(property="client_id", type="integer", description="Client ID"),
 *         @OA\Property(property="product_id", type="integer", description="Product ID"),
 *         @OA\Property(property="quantity", type="integer", description="Quantity of products sold"),
 *         @OA\Property(property="unit_price", type="number", format="float", description="Unit price of the product"),
 *         @OA\Property(property="total_price", type="number", format="float", description="Total price of the sale"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp")
 *     }
 * )
 */
class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/sales",
     *     summary="Get a list of all sales",
     *     tags={"Sales"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of sales retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Sale")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $sales = Sale::orderBy('created_at', 'desc')->get();
        return response()->json($sales);
    }

    /**
     * @OA\Post(
     *     path="/api/sales",
     *     summary="Store a new sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"client_id", "product_id", "quantity"},
     *             @OA\Property(property="client_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sale executed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Sale executed successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Sale")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Product is not available or not enough stock"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'client_id' => 'required|exists:clients,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::find($request->product_id);

            if (!$product->is_active) {
                return response()->json(['error' => 'Product is not available'], 400);
            }

            if ($product->quantity < $request->quantity) {
                return response()->json(['error' => 'Not enough stock available'], 400);
            }

            $unit_price = $product->price;

            $total_price = $unit_price * $request->quantity;

            $sale = Sale::create([
                'client_id' => $request->client_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'unit_price' => $unit_price,
                'total_price' => $total_price,
            ]);

            $product->quantity -= $request->quantity;
            if ($product->quantity == 0) {
                $product->is_active = false;
            }
            $product->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Sale executed successfully',
                'data' => $sale], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        }
    }
}
