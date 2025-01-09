<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard data.
     */
    public function index(Request $request)
    {
        // Buku terpopuler
        $popularBooks = Book::withCount('loans')
            ->orderBy('loans_count', 'desc')
            ->take(5)
            ->get();

        // Jumlah peminjaman per bulan (tahun ini)
        $loansPerMonth = Loan::select(DB::raw('MONTH(borrow_date) as month'), DB::raw('count(*) as total'))
            ->whereYear('borrow_date', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Daftar anggota aktif (meminjam buku dalam 30 hari terakhir)
        $activeMembers = Member::whereHas('loans', function ($query) {
            $query->where('borrow_date', '>=', now()->subDays(30));
        })->withCount(['loans' => function($query){
            $query->where('borrow_date', '>=', now()->subDays(30));
        }])->get();

        return response()->json([
            'popular_books' => $popularBooks,
            'loans_per_month' => $loansPerMonth,
            'active_members' => $activeMembers,
        ]);
    }
}