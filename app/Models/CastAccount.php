<?php

namespace App\Models;

use App\Models\AdditionalInformation;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CastAccount extends Model
{
    use CrudTrait;
    use HasFactory;

    const ENTER = 'enter';
    const OUT = 'out';

    const CASH = 'cash';
    const LOAN = 'loan';

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'cast_accounts';
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

    public function informations(){
        return $this->belongsToMany(AdditionalInformation::class, 'account_information', 'cast_account_id', 'account_information_id');
    }

    public function account_transactions(){
        return $this->hasMany(AccountTransaction::class, 'cast_account_id');
    }

    public function account_transactions_destination(){
        return $this->hasMany(AccountTransaction::class, 'cast_account_destination_id');
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
