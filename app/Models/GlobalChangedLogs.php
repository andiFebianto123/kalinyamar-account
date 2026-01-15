<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalChangedLogs extends Model
{
    protected $table = 'global_changed_logs';
    protected $guarded = ['id'];

    const UPDATE = 'UPDATE';
    const INSERT = 'INSERT';
    const DELETE = 'DELETE';

    public function log_payment()
    {
        return $this->belongsTo(LogPayment::class);
    }
}
