<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SellerManagementController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:sellers,email',
            'mobile_no' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'skills' => 'required|array',
            'password' => 'required|string|min:6',
        ]);

        $seller = Seller::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_no' => $request->mobile_no,
            'country' => $request->country,
            'state' => $request->state,
            'skills' => $request->skills,
            'password' => Hash::make($request->password),
            'role' => 'seller',
        ]);

        return response()->json([
            'message' => 'Seller created successfully',
            'seller' => $seller
        ], 201);
    }

    public function index()
    {
        $sellers = Seller::paginate(10);
        return response()->json($sellers);
    }
}
