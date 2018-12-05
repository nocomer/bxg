<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //银联，支付宝相关

        'order/pay/alipay/notify',
        'order/pay/wechat/notify',

        //app接口相关请求
        'api/*'
    ];
}
