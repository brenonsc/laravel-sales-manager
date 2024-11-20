<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'signup']]);
    }

    /**
     * @OA\Post (
     *     path="/api/signup",
     *     tags={"Auth"},
     *     summary="Register a User",
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *     required={"name","email","password", "password_confirmation"},
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="johndoe@email.com"),
     *     @OA\Property(property="password", type="string", example="password123"),
     *     @OA\Property(property="password_confirmation", type="string", example="password123"),
     *     )
     *    ),
     *     @OA\Response(
     *     response=200,
     *     description="User registered successfully",
     *     ),
     *     @OA\Response(
     *     response=422,
     *     description="Validation Error",
     *     ),
     *     @OA\Response(
     *     response=500,
     *     description="Server Error",
     *     ))
     */
    public function signup(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return $this->login($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'An unexpected error occurred.'
            ], 500);
        }
    }

    /**
     * @OA\Post (
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Authenticate User and return a token",
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *     required={"email","password"},
     *     @OA\Property(property="email", type="string", example="johndoe@email.com"),
     *     @OA\Property(property="password", type="string", example="password123"),
     *     )
     *    ),
     *     @OA\Response(
     *     response=200,
     *     description="Authentication successful",
     *     @OA\JsonContent(
     *         @OA\Property(property="access_token", type="string", example="token"),
     *         @OA\Property(property="token_type", type="string", example="bearer"),
     *         @OA\Property(property="expires_in", type="integer", example=3600),
     *     )
     *     ),
     *     @OA\Response(
     *     response=500,
     *     description="Server Error",
     *     ))
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid email or password.'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Get (
     *     path="/api/me",
     *     tags={"Auth"},
     *     summary="Get the authenticated User",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *     response=200,
     *     description="User data retrieved successfully",
     *     @OA\JsonContent(
     *     @OA\Property(property="success", type="boolean", example=true),
     *     @OA\Property(property="user", type="object",
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", example="johndoe@email.com"),
     *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-20T10:00:00.000000Z"),
     *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-20T10:00:00.000000Z")
     *     )
     *     )
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="Unauthorized",
     *     ),
     *     @OA\Response(
     *     response=500,
     *     description="Server Error",
     *     ))
     */
    public function me()
    {
        try {
            return response()->json([
                'success' => true,
                'user' => auth()->user()->makeHidden('email_verified_at')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'Unable to fetch user data.'
            ], 500);
        }
    }

    /**
     * @OA\Post (
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Log the user out (Invalidate the token)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *     response=200,
     *     description="Successfully logged out",
     *     @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Successfully logged out")
     *     )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *     response=500,
     *     description="Server Error",
     *     ))
     */
    public function logout()
    {
        try {
            auth()->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => 'Failed to log out.'
            ], 500);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
