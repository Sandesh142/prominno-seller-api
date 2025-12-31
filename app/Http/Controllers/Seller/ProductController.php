<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'brands' => 'required|array|min:1',
            'brands.*.name' => 'required|string|max:255',
            'brands.*.detail' => 'nullable|string',
            'brands.*.image' => 'nullable', // can be file or string
            'brands.*.price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'seller_id' => $request->user()->id,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            foreach ($request->brands as $brandData) {
                $imagePath = null;
                // Handle image upload if present and is a file
                // If it's a string (simulation), use it directly
                // For this implementation I assume valid file or string. 
                // In real scenario: if ($request->hasFile(...))
                // Here brandData['image'] might be a file instance if sent as multipart/form-data
                
                // Note: handling arrays of files in multipart is tricky in Laravel checks.
                // Assuming basic implementation or string path for now.
                // If the user sends files, we'd need: $brandData['image']->store('brands', 'public');
                // But $request->brands is an array. Laravel handles this if structured correctly.
                
                if (isset($brandData['image']) && $brandData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $imagePath = $brandData['image']->store('brands', 'public');
                } elseif (isset($brandData['image'])) {
                     $imagePath = $brandData['image'];
                }

                Brand::create([
                    'product_id' => $product->id,
                    'name' => $brandData['name'],
                    'detail' => $brandData['detail'] ?? null,
                    'image' => $imagePath,
                    'price' => $brandData['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product->load('brands')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to add product: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $products = Product::where('seller_id', $request->user()->id)
            ->with('brands')
            ->paginate(10);

        return response()->json($products);
    }

    public function generatePdf($id)
    {
        $product = Product::with('brands')->where('id', $id)->firstOrFail();

        // Check ownership
        if ($product->seller_id !== auth()->id()) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalPrice = $product->brands->sum('price');

        $pdf = Pdf::loadView('pdf.product', compact('product', 'totalPrice'));
        return $pdf->download('product-'.$product->id.'.pdf');
    }

    public function destroy($id)
    {
        $product = Product::where('id', $id)->firstOrFail();

        if ($product->seller_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
