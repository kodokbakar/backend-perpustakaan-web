<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'member_id',
        'email',
        'phone_number',
        'address',
    ];

    /**
     * Get the loans for the member.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}