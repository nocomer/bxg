<?php

namespace App\Modules\Task\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class ServiceModel extends Model
{

    protected $table = 'service';

    /**
     * 计算服务需要的金额
     * @param $product_ids
     * @return int
     * author: muker（qq:372980503）
     */
    static public function serviceMoney($product_ids)
    {
        $money = 0;
        foreach($product_ids as $k=>$v)
        {
            $data = Self::where('id','=',$v)->first()->toArray();
            $money += $data['price'];
        }
        return $money;
    }

}
