<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $books = Book::with('category')->get();
        return response()->json($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $book = Book::create($request->all());
        return response()->json($book, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::with('category')->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        return response()->json($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $book->update($request->all());
        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $book->delete();
        return response()->json(['message' => 'Book deleted']);
    }
    
    /**
     * Search books by title, author, or category.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query');

        $books = Book::where('title', 'like', "%{$query}%")
                     ->orWhere('author', 'like', "%{$query}%")
                     ->orWhereHas('category', function ($q) use ($query) {
                         $q->where('name', 'like', "%{$query}%");
                     })
                     ->with('category')
                     ->get();

        return response()->json($books);
    }
}