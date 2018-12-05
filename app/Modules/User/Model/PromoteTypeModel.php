<?php
/**
 * Created by PhpStorm.
 * User: KEKE-1003
 * Date: 2016/7/12
 * Time: 16:08
 */
namespace App\Modules\User\Model;
use Illuminate\Database\Eloquent\Model;

class PromoteTypeModel extends Model
{
    protected $table = 'promote_type';

    public $timestamps = false;

    protected $fillable = [
        'id','name','code_name','price','finish_conditions','type','is_open','created_at','updated_at'
    ];


}