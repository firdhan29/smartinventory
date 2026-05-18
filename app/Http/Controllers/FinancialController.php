<?php

namespace App\Http\Controllers;

use App\Models\Financial;
use App\Models\FinancialAuditLog;
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

        // Retrieve financial audit trail logs
        $auditLogs = FinancialAuditLog::with('user')
            ->latest()
            ->take(50)
            ->get();

        $user = auth()->user();

        return Inertia::render('Financials/Index', [
            'financials' => $financials,
            'stats' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
            ],
            'chartData' => $chartData,
            'filters' => $request->only(['type', 'start_date', 'end_date']),
            'auditLogs' => $auditLogs,
            'isAdmin' => $user ? $user->hasRole('admin') : false,
            'isFinance' => $user ? $user->hasRole('finance') : false,
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

        $financial = Financial::create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Record create action in Audit Logs
        FinancialAuditLog::create([
            'user_id' => auth()->id(),
            'financial_id' => $financial->id,
            'action' => 'STORE',
            'new_values' => [
                'type' => $financial->type,
                'amount' => (float) $financial->amount,
                'date' => $financial->date instanceof Carbon ? $financial->date->toDateString() : (is_string($financial->date) ? $financial->date : Carbon::parse($financial->date)->toDateString()),
                'notes' => $financial->notes,
            ]
        ]);

        return redirect()->route('financials.index')->with('success', 'Entri keuangan manual berhasil dicatat.');
    }

    /**
     * Update an existing financial entry.
     */
    public function update(Request $request, Financial $financial): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['admin', 'finance'])) {
            abort(403, 'Hanya Administrator dan Staf Finance yang diizinkan untuk mengedit keuangan.');
        }

        $validated = $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Snapshot old values before update
        $oldValues = [
            'type' => $financial->type,
            'amount' => (float) $financial->amount,
            'date' => $financial->date instanceof Carbon ? $financial->date->toDateString() : (is_string($financial->date) ? $financial->date : Carbon::parse($financial->date)->toDateString()),
            'notes' => $financial->notes,
        ];

        $financial->update([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Record edit action in Audit Logs
        FinancialAuditLog::create([
            'user_id' => auth()->id(),
            'financial_id' => $financial->id,
            'action' => 'EDIT',
            'old_values' => $oldValues,
            'new_values' => [
                'type' => $financial->type,
                'amount' => (float) $financial->amount,
                'date' => $financial->date instanceof Carbon ? $financial->date->toDateString() : (is_string($financial->date) ? $financial->date : Carbon::parse($financial->date)->toDateString()),
                'notes' => $financial->notes,
            ]
        ]);

        return redirect()->route('financials.index')->with('success', 'Entri keuangan berhasil diperbarui.');
    }

    /**
     * Delete a financial entry.
     */
    public function destroy(Financial $financial): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['admin', 'finance'])) {
            abort(403, 'Hanya Administrator dan Staf Finance yang diizinkan untuk menghapus data keuangan.');
        }

        // Snapshot old values before deletion
        $oldValues = [
            'type' => $financial->type,
            'amount' => (float) $financial->amount,
            'date' => $financial->date instanceof Carbon ? $financial->date->toDateString() : (is_string($financial->date) ? $financial->date : Carbon::parse($financial->date)->toDateString()),
            'notes' => $financial->notes,
        ];

        $financialId = $financial->id;
        $financial->delete();

        // Record delete action in Audit Logs
        FinancialAuditLog::create([
            'user_id' => auth()->id(),
            'financial_id' => $financialId,
            'action' => 'DELETE',
            'old_values' => $oldValues,
        ]);

        return redirect()->route('financials.index')->with('success', 'Entri keuangan berhasil dihapus.');
    }

    /**
     * Clear all financial audit trail logs (Admin only).
     */
    public function clearLogs(): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Hanya Administrator utama yang diizinkan untuk membersihkan riwayat audit.');
        }

        FinancialAuditLog::truncate();

        return redirect()->route('financials.index')->with('success', 'Riwayat audit keuangan berhasil dibersihkan.');
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

