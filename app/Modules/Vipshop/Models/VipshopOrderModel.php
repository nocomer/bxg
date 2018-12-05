<?php

namespace App\Modules\Vipshop\Models;

use Illuminate\Database\Eloquent\Model;

class VipshopOrderModel extends Model
{
    protected $table = 'vipshop_order';

    protected $fillable = [
        'code', 'title', 'uid', 'package_id', 'shop_id', 'cash', 'time_period', 'status'
    ];
}
