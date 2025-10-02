<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        // Definisikan pinjaman berdasar "member_code" (kolom member.member_id) dan "book_title"
        $loans = [
            [
                'member_code' => 'MBR001',
                'book_title'  => 'Clean Code',
                'borrow_date' => Carbon::now()->subDays(7)->toDateString(),
                'due_date'    => Carbon::now()->addDays(7)->toDateString(),
                'return_date' => null,
                'fine_amount' => 0,
            ],
            [
                'member_code' => 'MBR002',
                'book_title'  => 'Sapiens',
                'borrow_date' => Carbon::now()->subDays(20)->toDateString(),
                'due_date'    => Carbon::now()->subDays(6)->toDateString(),
                'return_date' => Carbon::now()->subDays(5)->toDateString(),
                'fine_amount' => 0, // ganti sesuai aturan denda kamu
            ],
            [
                'member_code' => 'MBR003',
                'book_title'  => 'Atomic Habits',
                'borrow_date' => Carbon::now()->subDays(3)->toDateString(),
                'due_date'    => Carbon::now()->addDays(11)->toDateString(),
                'return_date' => null,
                'fine_amount' => 0,
            ],
        ];

        foreach ($loans as $l) {
            $member = Member::where('member_id', $l['member_code'])->first();
            $book   = Book::where('title', $l['book_title'])->first();

            if (! $member || ! $book) {
                // Lewati jika data relasi belum ada
                continue;
            }

            // Hindari duplikasi: unik berdasar member_id + book_id + borrow_date
            Loan::firstOrCreate(
                [
                    'member_id'   => $member->id, // FK ke kolom "id" di tabel members
                    'book_id'     => $book->id,   // FK ke kolom "id" di tabel books
                    'borrow_date' => $l['borrow_date'],
                ],
                [
                    'due_date'    => $l['due_date'],
                    'return_date' => $l['return_date'],
                    'fine_amount' => $l['fine_amount'],
                ]
            );
        }
    }
}
