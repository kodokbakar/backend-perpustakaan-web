<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name'         => 'Andi Santoso',
                'member_id'    => 'MBR001',
                'email'        => 'andi@example.com',
                'phone_number' => '081234567890',
                'address'      => 'Jakarta',
            ],
            [
                'name'         => 'Budi Hartono',
                'member_id'    => 'MBR002',
                'email'        => 'budi@example.com',
                'phone_number' => '081298765432',
                'address'      => 'Bandung',
            ],
            [
                'name'         => 'Citra Lestari',
                'member_id'    => 'MBR003',
                'email'        => 'citra@example.com',
                'phone_number' => '082112223333',
                'address'      => 'Surabaya',
            ],
        ];

        foreach ($members as $m) {
            Member::firstOrCreate(
                ['member_id' => $m['member_id']],
                $m
            );
        }
    }
}
