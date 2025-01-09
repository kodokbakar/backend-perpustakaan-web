<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class ReportController extends Controller
{
    /**
     * Generate book report.
     */
    public function bookReport(): JsonResponse
    {
        $books = Book::with('category')->get();

        return response()->json($books);
    }

    /**
     * Generate loan report.
     */
    public function loanReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period' => 'in:daily,weekly,monthly',
            'start_date' => 'date_format:Y-m-d',
            'end_date' => 'date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $period = $request->input('period', 'daily'); // Default to daily
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Loan::with(['book', 'member']);

        switch ($period) {
            case 'daily':
                if (!$startDate) $startDate = now()->toDateString();
                $query->whereDate('borrow_date', $startDate);
                break;
            case 'weekly':
                if (!$startDate) $startDate = now()->startOfWeek()->toDateString();
                if (!$endDate) $endDate = now()->endOfWeek()->toDateString();
                $query->whereBetween('borrow_date', [$startDate, $endDate]);
                break;
            case 'monthly':
                if (!$startDate) $startDate = now()->startOfMonth()->toDateString();
                if (!$endDate) $endDate = now()->endOfMonth()->toDateString();
                $query->whereBetween('borrow_date', [$startDate, $endDate]);
                break;
        }

        $loans = $query->get();

        return response()->json($loans);
    }
}