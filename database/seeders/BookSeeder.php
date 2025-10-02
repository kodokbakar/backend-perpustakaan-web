<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // Data buku dengan kategori-nya (berdasar nama)
        $books = [
            [
                'title'       => 'Clean Code',
                'author'      => 'Robert C. Martin',
                'publisher'   => 'Prentice Hall',
                'category'    => 'Teknologi',
                'stock'       => 5,
                'book_cover'  => 'storage/book_cover/clean-code.webp',
            ],
            [
                'title'       => 'Refactoring',
                'author'      => 'Martin Fowler',
                'publisher'   => 'Addison-Wesley',
                'category'    => 'Teknologi',
                'stock'       => 4,
                'book_cover'  => 'storage/book_cover/refactoring.webp',
            ],
            [
                'title'       => 'Sapiens',
                'author'      => 'Yuval Noah Harari',
                'publisher'   => 'Harper',
                'category'    => 'Sejarah',
                'stock'       => 6,
                'book_cover'  => 'storage/book_cover/sapiens.webp',
            ],
            [
                'title'       => 'Atomic Habits',
                'author'      => 'James Clear',
                'publisher'   => 'Avery',
                'category'    => 'Bisnis',
                'stock'       => 7,
                'book_cover'  => 'storage/book_cover/atomic-habits.webp',
            ],
            [
                'title'       => 'The Martian',
                'author'      => 'Andy Weir',
                'publisher'   => 'Crown',
                'category'    => 'Fiksi',
                'stock'       => 3,
                'book_cover'  => 'storage/book_cover/the-martian.webp',
            ],
            [
                'title'       => 'A Brief History of Time',
                'author'      => 'Stephen Hawking',
                'publisher'   => 'Bantam',
                'category'    => 'Sains',
                'stock'       => 4,
                'book_cover'  => 'storage/book_cover/brief-history-of-time.webp',
            ],
        ];

        foreach ($books as $b) {
            $category = Category::where('name', $b['category'])->first();
            if (! $category) {
                // fallback jika kategori belum ada
                $category = Category::create(['name' => $b['category']]);
            }

            Book::firstOrCreate(
                ['title' => $b['title']],
                [
                    'author'      => $b['author'],
                    'publisher'   => $b['publisher'],
                    'category_id' => $category->id,
                    'stock'       => $b['stock'],
                    'book_cover'  => $b['book_cover'],
                ]
            );
        }
    }
}
