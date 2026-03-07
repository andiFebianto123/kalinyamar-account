<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetupCompanyClassification extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'setup_company_classifications';
    protected $guarded = ['id'];
}
