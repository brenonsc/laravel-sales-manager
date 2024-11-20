<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Schema(
 *     schema="Client",
 *     type="object",
 *     title="Client",
 *     description="Client model",
 *     properties={
 *         @OA\Property(property="id", type="integer", description="Client ID"),
 *         @OA\Property(property="name", type="string", description="Client name"),
 *         @OA\Property(property="cpf", type="string", description="Client CPF"),
 *         @OA\Property(property="email", type="string", description="Client email"),
 *         @OA\Property(property="phone", type="string", description="Client phone"),
 *         @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp"),
 *         @OA\Property(property="address", type="object",
 *             @OA\Property(property="street", type="string", description="Street name"),
 *             @OA\Property(property="number", type="string", description="Street number"),
 *             @OA\Property(property="complement", type="string", description="Address complement"),
 *             @OA\Property(property="neighbourhood", type="string", description="Neighbourhood"),
 *             @OA\Property(property="city", type="string", description="City"),
 *             @OA\Property(property="state", type="string", description="State"),
 *             @OA\Property(property="postal_code", type="string", description="Postal code"),
 *             @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp")
 *         )
 *     }
 * )
 */
class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get (
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary="List all clients",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of clients retrieved successfully",
     *     @OA\JsonContent(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Client")
     *     )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     )
     * )
     */
    public function index()
    {
        $clients = Client::orderBy('id')->get();
        return response()->json($clients);
    }

    /**
     * @OA\Get (
     *     path="/api/clients/{id}/sales",
     *     tags={"Clients"},
     *     summary="Get all client's sales by date (descending)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client sales retrieved successfully",
     *     @OA\JsonContent(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Sale")
     *     )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *     )
     * )
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
     * @OA\Get (
     *     path="/api/clients/{id}/sales/{year}/{month}",
     *     tags={"Clients"},
     *     summary="Get a client's sales by month and year",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         description="Year",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         description="Month",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client sales retrieved successfully",
     *     @OA\JsonContent(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Sale")
     *     )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid month or year provided.",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *     )
     * )
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
     * @OA\Post (
     *     path="/api/clients",
     *     tags={"Clients"},
     *     summary="Create a new client",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","cpf","email","phone","address"},
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="cpf", type="string", example="12345678901"),
     *             @OA\Property(property="email", type="string", example="janedoe@email.com"),
     *             @OA\Property(property="phone", type="string", example="12991234567"),
     *             @OA\Property(property="address", type="object",
     *                 @OA\Property(property="street", type="string", example="Rua das Flores"),
     *                 @OA\Property(property="number", type="string", example="123"),
     *                 @OA\Property(property="complement", type="string", example="Apto 101"),
     *                 @OA\Property(property="neighbourhood", type="string", example="Centro"),
     *                 @OA\Property(property="city", type="string", example="São Paulo"),
     *                 @OA\Property(property="state", type="string", example="SP"),
     *                 @OA\Property(property="postal_code", type="string", example="01000000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client")
     *        )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *     )
     * )
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
     * @OA\Put (
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary="Update a client",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe Updated"),
     *             @OA\Property(property="cpf", type="string", example="12345678901"),
     *             @OA\Property(property="email", type="string", example="janedoe@email.com"),
     *             @OA\Property(property="phone", type="string", example="12991283898"),
     *             @OA\Property(property="address", type="object",
     *                 @OA\Property(property="street", type="string", example="Avenida Paulista"),
     *                 @OA\Property(property="number", type="string", example="100"),
     *                 @OA\Property(property="complement", type="string", example="Sala 10"),
     *                 @OA\Property(property="neighbourhood", type="string", example="Bela Vista"),
     *                 @OA\Property(property="city", type="string", example="São Paulo"),
     *                 @OA\Property(property="state", type="string", example="SP"),
     *                 @OA\Property(property="postal_code", type="string", example="01310000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *     )
     * )
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
     * @OA\Delete (
     *     path="/api/clients/{id}",
     *     tags={"Clients"},
     *     summary="Delete a client, including its address and sales",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Client deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *     )
     * )
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
