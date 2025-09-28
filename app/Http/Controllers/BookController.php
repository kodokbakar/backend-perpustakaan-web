<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

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
            'book_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $bookData = $request->except(['book_cover']);

        // Handle book cover upload
        if ($request->hasFile('book_cover')) {
            $bookData['book_cover'] = $this->handleImageUpload($request->file('book_cover'));
        }

        $book = Book::create($bookData);
        $book->load('category');
        
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
            'book_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $bookData = $request->except(['book_cover']);

        // Handle book cover upload
        if ($request->hasFile('book_cover')) {
            // Delete old cover if exists
            if ($book->book_cover) {
                Storage::disk('public')->delete('book_cover/' . basename($book->book_cover));
            }
            
            $bookData['book_cover'] = $this->handleImageUpload($request->file('book_cover'));
        }

        $book->update($bookData);
        $book->load('category');
        
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

        // Delete book cover if exists
        if ($book->book_cover) {
            Storage::disk('public')->delete('book_cover/' . basename($book->book_cover));
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

    /**
     * Handle image upload, conversion to WebP, and storage.
     */
    private function handleImageUpload($file): string
    {
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.webp';
        
        // Create directory if it doesn't exist
        Storage::disk('public')->makeDirectory('book_cover');
        
        // Load and process the image
        $image = Image::read($file);
        
        // Resize to 3000x3600 (1.2:1 aspect ratio) with proper scaling
        $image->cover(3000, 3600);
        
        // Convert to WebP format with 80% quality
        $webpData = $image->toWebp(80);
        
        // Save to storage
        $path = 'book_cover/' . $filename;
        Storage::disk('public')->put($path, $webpData);
        
        // Return the full URL
        return Storage::disk('public')->url($path);
    }

    /**
     * Update only the book cover image.
     */
    public function updateCover(Request $request, string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'book_cover' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Delete old cover if exists
        if ($book->book_cover) {
            Storage::disk('public')->delete('book_cover/' . basename($book->book_cover));
        }

        // Upload new cover
        $book->book_cover = $this->handleImageUpload($request->file('book_cover'));
        $book->save();

        $book->load('category');
        return response()->json($book);
    }

    /**
     * Delete book cover image.
     */
    public function deleteCover(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        if ($book->book_cover) {
            Storage::disk('public')->delete('book_cover/' . basename($book->book_cover));
            $book->book_cover = null;
            $book->save();
        }

        $book->load('category');
        return response()->json($book);
    }
}