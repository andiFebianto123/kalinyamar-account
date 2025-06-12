<?php
namespace App\Models;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model{
    protected $table = 'journal_entries';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
