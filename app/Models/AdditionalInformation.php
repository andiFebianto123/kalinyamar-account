<?php

namespace App\Models;

use App\Models\CastAccount;
use Illuminate\Database\Eloquent\Model;

class AdditionalInformation extends Model
{
    protected $table = 'additional_informations';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    public function castAccounts(){
        return $this->belongsToMany(CastAccount::class, 'account_information', 'account_information_id', 'cast_account_id');
    }

}
