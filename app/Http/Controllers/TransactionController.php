<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Financial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request): Response
    {
        $query = Transaction::query()->with(['product', 'user']);

        // Search by product name, sku, or notes
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by grade
        if ($request->filled('grade')) {
            $query->where('grade', $request->input('grade'));
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();
        
        // Fetch all products for selection in new transaction form
        $products = Product::all();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'products' => $products,
            'filters' => $request->only(['search', 'type', 'grade']),
        ]);
    }

    /**
     * Store a newly created transaction and handle stock & financials.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:masuk,keluar',
            'grade' => 'required|in:A,B,Reject',
            'quantity' => 'required|integer|min:1',
            'harga_aktual' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $productId = $validated['product_id'];
                $grade = $validated['grade'];
                $quantity = $validated['quantity'];
                $type = $validated['type'];
                $hargaAktual = $validated['harga_aktual'];

                // 1. Fetch & lock the stock record for update to prevent race conditions
                $stock = Stock::where('product_id', $productId)
                    ->where('grade', $grade)
                    ->lockForUpdate()
                    ->first();

                if ($type === 'keluar') {
                    // Check if stock exists and has enough quantity
                    if (!$stock || $stock->quantity < $quantity) {
                        $currentQty = $stock ? $stock->quantity : 0;
                        throw new \Exception("Stok tidak mencukupi! Stok saat ini untuk Grade {$grade} adalah {$currentQty} pcs.");
                    }
                    
                    // Reduce stock
                    $stock->quantity -= $quantity;
                    $stock->save();
                } else {
                    // It is 'masuk' (restock)
                    if (!$stock) {
                        // Create a new stock row if it didn't exist before
                        $stock = new Stock();
                        $stock->product_id = $productId;
                        $stock->grade = $grade;
                        $stock->quantity = 0;
                        $stock->rak_lokasi = 'Belum Dialokasikan';
                    }
                    
                    // Increase stock
                    $stock->quantity += $quantity;
                    $stock->save();
                }

                // 2. Create the transaction log
                $transaction = Transaction::create([
                    'product_id' => $productId,
                    'user_id' => auth()->id() ?? 1, // Fallback to 1 for seeders/unauthenticated CLI tests
                    'type' => $type,
                    'quantity' => $quantity,
                    'grade' => $grade,
                    'harga_aktual' => $hargaAktual,
                    'notes' => $validated['notes'],
                ]);

                // 3. Create the corresponding financial ledger entry
                // 'masuk' = Buying goods (Pengeluaran / Expense)
                // 'keluar' = Selling goods (Pemasukan / Income)
                $financialType = ($type === 'masuk') ? 'pengeluaran' : 'pemasukan';
                $financialAmount = $quantity * $hargaAktual;

                Financial::create([
                    'type' => $financialType,
                    'amount' => $financialAmount,
                    'date' => now()->toDateString(),
                    'transaction_id' => $transaction->id,
                ]);
            });

            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil disimpan dan stok diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Export transaction to PDF (Surat Jalan/Invois).
     */
    public function downloadPdf(Transaction $transaction)
    {
        $transaction->load(['product', 'user']);
        $pdf = Pdf::loadView('pdf.transaction', compact('transaction'));
        return $pdf->download('invois-mutasi-' . $transaction->id . '.pdf');
    }
}
