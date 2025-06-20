<?php
namespace App\Models;
use App\Models\CastAccount;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountTransaction extends Model
{
    use CrudTrait;
    use HasFactory;

    const BANK_LOAN = 'PINJAMAN BANK';

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'account_transactions';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function cast_account()
    {
        return $this->belongsTo(CastAccount::class, 'cast_account_id');
    }

    public function cast_account_destination(){
        return $this->belongsTo(CastAccount::class, 'cast_account_destination_id');
    }

    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function journal_entry()
    {
        return $this->morphOne(JournalEntry::class, 'reference');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
