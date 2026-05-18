<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\UserController;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\Financial;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $totalProducts = Product::count();
    $totalStock = Stock::sum('quantity');
    $totalIncome = Financial::where('type', 'pemasukan')->sum('amount');
    $totalExpense = Financial::where('type', 'pengeluaran')->sum('amount');
    $netProfit = $totalIncome - $totalExpense;

    $recentTransactions = Transaction::with(['product', 'user'])
        ->latest()
        ->take(5)
        ->get();

    $lowStocks = Stock::with('product')
        ->where('quantity', '<=', 5)
        ->where('grade', 'A')
        ->get();

    // Chart.js data
    $gradeA = Stock::where('grade', 'A')->sum('quantity');
    $gradeB = Stock::where('grade', 'B')->sum('quantity');
    $reject = Stock::where('grade', 'Reject')->sum('quantity');

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
        ],
        'recentTransactions' => $recentTransactions,
        'lowStocks' => $lowStocks,
        'chartStockGrades' => [
            'labels' => ['Grade A', 'Grade B', 'Reject'],
            'data' => [$gradeA, $gradeB, $reject]
        ]
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Products CRUD
    Route::resource('products', ProductController::class);

    // Stocks
    Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
    Route::put('stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');

    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}/pdf', [TransactionController::class, 'downloadPdf'])->name('transactions.pdf');

    // Financials
    Route::get('financials', [FinancialController::class, 'index'])->name('financials.index');
    Route::post('financials', [FinancialController::class, 'store'])->name('financials.store');
    Route::put('financials/{financial}', [FinancialController::class, 'update'])->name('financials.update');
    Route::delete('financials/{financial}', [FinancialController::class, 'destroy'])->name('financials.destroy');
    Route::post('financials/clear-logs', [FinancialController::class, 'clearLogs'])->name('financials.clear-logs');
    Route::get('financials/export', [FinancialController::class, 'export'])->name('financials.export');

    // QR/Barcode Scan page
    Route::get('scan', function () {
        $products = Product::all();
        return Inertia::render('Scan', [
            'products' => $products
        ]);
    })->name('scan');

    // PRD Viewer
    Route::get('prd', function () {
        return Inertia::render('PRD');
    })->name('prd');

    // Users Management
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/auth.php';
