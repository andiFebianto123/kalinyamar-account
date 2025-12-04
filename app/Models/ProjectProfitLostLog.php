<?php
namespace App\Models;

use App\Models\ProjectProfitLost;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class ProjectProfitLostLog extends Model{
    use CrudTrait;
    protected $table = 'project_profit_lost_log';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    public function project_profit_lost(){
        return $this->belongsTo(ProjectProfitLost::class, 'project_profit_lost_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
