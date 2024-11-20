<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::orderBy('created_at', 'desc')->get();
        return response()->json($sales);
    }

    /**
     * Store a newly created resource in storage.
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

            return response()->json($sale, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        }
    }
}
