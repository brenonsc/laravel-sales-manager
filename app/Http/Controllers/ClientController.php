<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientController extends Controller
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
        $clients = Client::orderBy('id')->get();
        return response()->json($clients);
    }

    /**
     * Show client sales by date (descending)
     */
    public function show($id)
    {
        try {
            $client = Client::findOrFail($id);

            $sales = $client->sales()
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales retrieved successfully.',
                'data' => $sales,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
    }

    /**
     * Show client sales filtered by month and year
     */
    public function getSalesByMonthAndYear($id, $year, $month)
    {
        if (!is_numeric($year) || $year < 1900 || !is_numeric($month) || $month < 1 || $month > 12) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid month or year provided.',
            ], 400);
        }

        try {
            $client = Client::findOrFail($id);

            $sales = $client->sales()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Sales retrieved successfully.',
                'data' => $sales,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedClientData = $request->validate([
                'name' => 'required|string|max:255',
                'cpf' => 'required|string|size:11|unique:clients',
                'email' => 'required|string|email|max:255|unique:clients',
                'phone' => 'required|string|max:20',
            ]);

            $validatedAddressData = $request->validate([
                'address.street' => 'required|string|max:255',
                'address.number' => 'required|string|max:10',
                'address.complement' => 'nullable|string|max:255',
                'address.neighbourhood' => 'required|string|max:255',
                'address.city' => 'required|string|max:255',
                'address.state' => 'required|string|max:2',
                'address.postal_code' => 'required|string|max:10',
            ]);

            $client = Client::create($validatedClientData);

            $addressData = $validatedAddressData['address'];
            $addressData['client_id'] = $client->id;
            Address::create($addressData);

            return response()->json([
                'status' => 'success',
                'message' => 'Client created successfully.',
                'data' => $client->load('address'),
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedClientData = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'cpf' => 'sometimes|required|string|size:11|unique:clients,cpf,' . $id,
                'email' => 'sometimes|required|string|email|max:255|unique:clients,email,' . $id,
                'phone' => 'sometimes|required|string|max:20',
            ]);

            $validatedAddressData = $request->validate([
                'address.street' => 'sometimes|required|string|max:255',
                'address.number' => 'sometimes|required|string|max:10',
                'address.complement' => 'nullable|string|max:255',
                'address.neighbourhood' => 'sometimes|required|string|max:255',
                'address.city' => 'sometimes|required|string|max:255',
                'address.state' => 'sometimes|required|string|max:2',
                'address.postal_code' => 'sometimes|required|string|max:10',
            ]);

            $client = Client::findOrFail($id);
            $client->update($validatedClientData);

            if (!empty($validatedAddressData['address'])) {
                $addressData = $validatedAddressData['address'];
                $address = $client->address()->first();

                if ($address) {
                    $address->update($addressData);
                } else {
                    $addressData['client_id'] = $client->id;
                    Address::create($addressData);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Client updated successfully.',
                'data' => $client->load('address'),
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
                'message' => 'Client not found.',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Client deleted successfully.',
            ], 204);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found.',
            ], 404);
        }
    }
}
