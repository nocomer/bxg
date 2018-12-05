<?php
/**
 * Created by PhpStorm.
 * User: KEKE-1003
 * Date: 2016/5/31
 * Time: 14:36
 */
namespace App\Modules\Advertisement\Model;

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

class RecommendModel extends Model
{
    protected $table = 'recommend';
    protected $fillable =
        [   'id',
            'position_id',
            'type',
            'recommend_id',
            'recommend_type',
            'recommend_name',
            'recommend_pic',
            'url',
            'start_time',
            'end_time',
            'sort',
            'is_open',
            'created_at'
        ];
    public  $timestamps = false;  //关闭自动更新时间戳


    /**
     * 根据推荐位id和类型获取推荐位信息
     * @param $recommendPositionId 推荐位位置id
     * @param $recommendType 推荐位类型
     * @return mixed
     */
    static function getRecommendInfo($recommendPositionId,$recommendType='')
    {
        if($recommendType){
            $recommend = RecommendModel::where('recommend.position_id',$recommendPositionId)
                ->where('recommend.type',$recommendType)->where('recommend.is_open',1)
                ->where(function($recommend){
                    $recommend->where('recommend.end_time','0000-00-00 00:00:00')
                        ->orWhere('recommend.end_time','>',date('Y-m-d h:i:s',time()));
                });
        }else{
            $recommend = RecommendModel::where('recommend.position_id',$recommendPositionId)
                ->where('recommend.is_open',1)
                ->where(function($recommend){
                    $recommend->where('recommend.end_time','0000-00-00 00:00:00')
                        ->orWhere('recommend.end_time','>',date('Y-m-d h:i:s',time()));
                });
        }
        return $recommend;
    }


    /**
     * 根据推荐类型查询已推荐的id
     * @param $recommendType 推荐位类型
     * @return mixed
     */
    static function getRecommended($recommendType)
    {
        $recommend = RecommendModel::select('recommend.recommend_id')
            ->where('recommend.type',$recommendType)
            ->where('recommend.is_open',1)
            ->where(function($recommend){
                $recommend->where('recommend.end_time','0000-00-00 00:00:00')
                    ->orWhere('recommend.end_time','>',date('Y-m-d h:i:s',time()));
            })
            ->get()->toArray();
        if(empty($recommend)){
            return false;
        }
        $recommend_ids = array_unique(array_flatten($recommend));
        return $recommend_ids;
    }

}
