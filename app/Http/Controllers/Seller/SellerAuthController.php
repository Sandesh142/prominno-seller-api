<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $seller = Seller::where('email', $request->email)->first();

        if (! $seller || ! Hash::check($request->password, $seller->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $seller->createToken('seller-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $seller->role,
        ]);
    }
}
