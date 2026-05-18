<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): Response
    {
        $query = Product::query()->with('stocks');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('kategori', $request->input('category'));
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        
        // Get unique categories for filter dropdown
        $categories = Product::distinct()->pluck('kategori');

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku|max:50',
            'name' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            try {
                // Ensure storage directory exists
                Storage::disk('public')->makeDirectory('products');
                
                // Try compressing image with Intervention Image (v3)
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                
                // Resize if too large
                if ($image->width() > 800) {
                    $image->scale(width: 800);
                }
                
                $path = storage_path('app/public/products/' . $filename);
                $image->save($path, 80); // compress to 80% quality
                $validated['foto'] = 'products/' . $filename;
            } catch (\Exception $e) {
                // Fallback to standard Laravel upload if Intervention fails
                $path = $file->storeAs('products', $filename, 'public');
                $validated['foto'] = $path;
            }
        }

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }

            $file = $request->file('foto');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            try {
                Storage::disk('public')->makeDirectory('products');
                
                // Try Intervention V3 compression
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file);
                
                if ($image->width() > 800) {
                    $image->scale(width: 800);
                }
                
                $path = storage_path('app/public/products/' . $filename);
                $image->save($path, 80);
                $validated['foto'] = 'products/' . $filename;
            } catch (\Exception $e) {
                $path = $file->storeAs('products', $filename, 'public');
                $validated['foto'] = $path;
            }
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->foto) {
            Storage::disk('public')->delete($product->foto);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
