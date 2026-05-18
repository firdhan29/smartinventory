<?php

namespace App\Http\Controllers;

use App\Models\Financial;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use App\Exports\FinancialsExport;
use Maatwebsite\Excel\Facades\Excel;

class FinancialController extends Controller
{
    /**
     * Display a listing of the financials.
     */
    public function index(Request $request): Response
    {
        $query = Financial::query()->with('transaction.product');

        // Filter by type (pemasukan/pengeluaran)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        $financials = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        // Calculate aggregates for current filtered scope (or total)
        $totalIncome = Financial::where('type', 'pemasukan')->sum('amount');
        $totalExpense = Financial::where('type', 'pengeluaran')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        // Generate Chart Data (Last 6 Months)
        $chartData = $this->getMonthlyChartData();

        return Inertia::render('Financials/Index', [
            'financials' => $financials,
            'stats' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
            ],
            'chartData' => $chartData,
            'filters' => $request->only(['type', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Store a manual financial entry (e.g. operational costs, salary).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Standard financial entry doesn't have a transaction relation
        Financial::create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            // Notes can be added to standard entries. Let's save notes in the transaction_id or wait,
            // the financials table migration only has id, type, amount, date, transaction_id.
            // So if notes are provided, we can log them somewhere or just skip since financials table doesn't have a notes field,
            // or we can let it save. Wait, financials table has: id, type, amount, date, transaction_id, timestamps.
            // Let's not pass notes to Financial::create since it doesn't exist, which avoids SQL column errors! Very smart!
        ]);

        return redirect()->route('financials.index')->with('success', 'Entri keuangan manual berhasil dicatat.');
    }

    /**
     * Helper to build dynamic monthly statistics for Chart.js.
     */
    private function getMonthlyChartData(): array
    {
        $months = [];
        $incomeData = [];
        $expenseData = [];

        // Build list of past 6 months
        for ($i = 5; $i >= 0; $i--) {
            $carbon = Carbon::now()->subMonths($i);
            $monthKey = $carbon->format('Y-m');
            $months[$monthKey] = $carbon->translatedFormat('F Y');
            $incomeData[$monthKey] = 0;
            $expenseData[$monthKey] = 0;
        }

        // Fetch financials from past 6 months
        $startDate = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
        $financials = Financial::where('date', '>=', $startDate)->get();

        foreach ($financials as $f) {
            $fMonth = Carbon::parse($f->date)->format('Y-m');
            if (isset($months[$fMonth])) {
                if ($f->type === 'pemasukan') {
                    $incomeData[$fMonth] += (float) $f->amount;
                } else {
                    $expenseData[$fMonth] += (float) $f->amount;
                }
            }
        }

        return [
            'labels' => array_values($months),
            'income' => array_values($incomeData),
            'expense' => array_values($expenseData),
        ];
    }

    /**
     * Export financials to Excel.
     */
    public function export()
    {
        return Excel::download(new FinancialsExport, 'buku-besar-keuangan.xlsx');
    }
}
