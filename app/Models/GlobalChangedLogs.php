<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GlobalChangedLogs extends Model
{
    protected $table = 'global_changed_logs';
    protected $fillable = [
        'log_payment_id',
        'reference_type',
        'reference_id',
        'action',
        'old_values',
        'new_values',
        'changed_by',
    ];

    const UPDATE = 'UPDATE';
    const INSERT = 'INSERT';
    const DELETE = 'DELETE';

    public $timestamps = false;

    public function log_payment()
    {
        return $this->belongsTo(LogPayment::class);
    }

    public static function addCapture(array $payload, Model &$old_data, Model &$new_data, int $id_log_payment): void
    {
        $new_data_entry = (array)[];
        $old_data_entry = (array)[];
        foreach ($payload as $key_capture) {
            $new_data_entry[$key_capture] = $new_data->{$key_capture};
            $old_data_entry[$key_capture] = $old_data->{$key_capture};
        }

        self::create([
            'log_payment_id' => $id_log_payment,
            'reference_type' => get_class($old_data),
            'reference_id' => $old_data->id,
            'action' => self::UPDATE,
            'old_values' => json_encode($old_data_entry),
            'new_values' => json_encode($new_data_entry),
            'changed_by' => backpack_user()->id,
            'created_at' => Carbon::now(),
        ]);
    }
}
