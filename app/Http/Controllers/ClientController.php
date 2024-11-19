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
        $clients = Client::with('address')->orderBy('id')->get();
        return response()->json($clients);
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
