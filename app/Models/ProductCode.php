<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCode extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    // Ini mengizinkan 'updateOrCreate' dari seeder Anda
    // untuk mengisi semua kolom.
    protected $guarded = [];
}