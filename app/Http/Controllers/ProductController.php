<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::all();
            return DataTables::of($data)
                ->addColumn('action', function($row){
                    $btn = '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm edit-btn">Edit</button>';
                    $btn .= ' <button data-id="'.$row->id.'" class="btn btn-danger btn-sm delete-btn">Delete</button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('products.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string',
            'product_price' => 'required|numeric',
            'product_description' => 'required|string',
            'product_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $product = new Product();
        $product->product_name = $request->input('product_name');
        $product->product_price = $request->input('product_price');
        $product->product_description = $request->input('product_description');
        
        // Handle product_images upload
        if ($request->hasFile('product_images')) {
            $images = [];
            foreach ($request->file('product_images') as $image) {
                $imageName = $image->store('product_images', 'public');
                $images[] = $imageName;
            }
            $product->product_images = $images;
        }
        
        $product->save();
        
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Check if product_images is already an array (common in Laravel)
        if (is_array($product->product_images)) {
            foreach ($product->product_images as $image) {
                Storage::disk('public')->delete($image);
            }
        } elseif (is_string($product->product_images)) {
            // If product_images is a JSON string, decode it
            $images = json_decode($product->product_images);

            if ($images && is_array($images)) {
                foreach ($images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        // Delete the product
        $product->delete();

        return response()->json(['success' => true]);
    }
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }
    public function update(Request $request, $id)
{
    // Validate incoming request data
    $validatedData = $request->validate([
        'product_name' => 'required|string|max:255',
        'product_price' => 'required|numeric',
        'product_description' => 'required|string',
        // Add validation rules for product_images if needed
    ]);

    // Update the product
    $product = Product::findOrFail($id);
    $product->product_name = $validatedData['product_name'];
    $product->product_price = $validatedData['product_price'];
    $product->product_description = $validatedData['product_description'];
    // Handle product_images update if applicable

    $product->save();

    return response()->json(['message' => 'Product updated successfully']);
}

}
