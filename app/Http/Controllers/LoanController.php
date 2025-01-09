<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Book;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $loans = Loan::with(['member', 'book'])->get();
        return response()->json($loans);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Validasi stok buku
        $book = Book::find($request->book_id);
        if ($book->stock <= 0) {
            return response()->json(['message' => 'Book is out of stock'], 400);
        }

        // Kurangi stok buku
        $book->stock -= 1;
        $book->save();

        $loan = Loan::create($request->all());
        return response()->json($loan, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $loan = Loan::with(['member', 'book'])->find($id);

        if (!$loan) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        return response()->json($loan);
    }

     /**
     * Display the loan history for a specific member.
     */
    public function history(string $memberId): JsonResponse
    {
        $member = Member::find($memberId);
        if(!$member){
            return response()->json(['message' => 'Member not found'], 404);
        }
        $loans = Loan::with(['book'])->where('member_id', $memberId)->get();
        return response()->json($loans);
    }

    /**
     * Show the form for returning a book.
     */

     public function returnBook(string $id): JsonResponse
     {
         $loan = Loan::with(['member', 'book'])->find($id);
 
         if (!$loan) {
             return response()->json(['message' => 'Loan not found'], 404);
         }
         
         if ($loan->return_date !== null) {
            return response()->json(['message' => 'Book already returned'], 400);
         }

         return response()->json($loan);
     }
 
     /**
      * Process the return of a book.
      */
     public function processReturn(Request $request, string $id): JsonResponse
     {
         $loan = Loan::find($id);
 
         if (!$loan) {
             return response()->json(['message' => 'Loan not found'], 404);
         }
 
         if ($loan->return_date !== null) {
            return response()->json(['message' => 'Book already returned'], 400);
         }

         $validator = Validator::make($request->all(), [
             'return_date' => 'required|date',
         ]);
 
         if ($validator->fails()) {
             return response()->json($validator->errors(), 400);
         }
 
         // Hitung denda
         $returnDate = new \DateTime($request->return_date);
         $dueDate = new \DateTime($loan->due_date);
         $fineAmount = 0;
 
         if ($returnDate > $dueDate) {
             $interval = $returnDate->diff($dueDate);
             $daysLate = $interval->days;
             // Misalnya denda Rp. 1000 per hari
             $fineAmount = $daysLate * 1000;
         }
 
         // Update data peminjaman
         $loan->return_date = $request->return_date;
         $loan->fine_amount = $fineAmount;
         $loan->save();
 
         // Tambah stok buku
         $book = Book::find($loan->book_id);
         $book->stock += 1;
         $book->save();
 
         return response()->json(['message' => 'Book returned', 'fine' => $fineAmount]);
     }
}