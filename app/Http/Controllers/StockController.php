<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockController extends Controller
{
    /**
     * Display a listing of the stocks.
     */
    public function index(Request $request): Response
    {
        $query = Stock::query()->with('product');

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }

        // Filter by search (product name, sku, or rak_lokasi)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })->orWhere('rak_lokasi', 'like', "%{$search}%");
            });
        }

        // Pagination
        $stocks = $query->latest()->paginate(15)->withQueryString();

        // Safety stock alerts: products where total quantity across grades is low,
        // or individual stock rows are below safety threshold (e.g. 5 units)
        $lowStocks = Stock::with('product')
            ->where('quantity', '<=', 5)
            ->where('grade', 'A') // focus safety warnings on prime quality
            ->get();

        return Inertia::render('Stocks/Index', [
            'stocks' => $stocks,
            'lowStocks' => $lowStocks,
            'filters' => $request->only(['grade', 'search']),
        ]);
    }

    /**
     * Update the specified stock location or quantity manually (Adjustment).
     */
    public function update(Request $request, Stock $stock): RedirectResponse
    {
        $validated = $request->validate([
            'rak_lokasi' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
        ]);

        $stock->update($validated);

        return redirect()->route('stocks.index')->with('success', 'Lokasi/Kuantitas stok berhasil diperbarui.');
    }
}
