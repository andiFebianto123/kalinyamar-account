<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanTransactionFlag extends Model
{

    protected $table = 'loan_transaction_flags';

    protected $fillable = [
        'kode',
        'total_price',
        'status',
    ];
}
