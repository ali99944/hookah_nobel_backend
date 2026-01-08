<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function login()
    {
        $validator = validator(request()->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $manager = Manager::where('email', request('email'))->first();

        if (!$manager || !Hash::check(request('password'), $manager->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }


        $token = $manager->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'manager' => $manager,
            'access_token' => $token,
        ]);
    }

    public function logout()
    {
        $manager = auth('manager')->user();
        $manager->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request)
    {
        $manager = $request->user();

        return response()->json([
            'status' => 'success',
            'manager' => $manager,
        ]);
    }
}
