<?php

namespace App\Http\Controllers;

use App\Modules\Manage\Model\ArticleCategoryModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\PromoteTypeModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserCenterController extends BasicController
{

    public function __construct()
    {
        parent::__construct();

        //网站关闭
        $siteConfig = ConfigModel::getConfigByType('site');
        if ($siteConfig['site_close'] == 2){
            abort('404');
        }

        //前端头部
        if (Auth::check()){
            $user = Auth::User();

            $userDetail = UserDetailModel::select('alternate_tips','avatar')->where('uid', $user->id)->first();
            $this->theme->set('username', $user->name);
            $this->theme->set('tips', empty($userDetail)?'':$userDetail->alternate_tips);
            $this->theme->set('avatar',empty($userDetail)?'':$userDetail->avatar);

            //查询未读消息
            $systemMessage =  MessageReceiveModel::where('js_id', $user->id)->where('message_type',1)->where('status',0)->count();
            $tradeMessage =  MessageReceiveModel::where('js_id',$user->id)->where('message_type',2)->where('status',0)->count();
            $receiveMessage =  MessageReceiveModel::where('js_id',$user->id)->where('message_type',3)->where('status',0)->count();
            $this->theme->set('system_message_count',$systemMessage);
            $this->theme->set('trade_message_count',$tradeMessage);
            $this->theme->set('receive_message_count',$receiveMessage);
        }

        //前端底部公共页脚配置
        $parentCate = ArticleCategoryModel::select('id')->where('cate_name','页脚配置')->first();
        if(!empty($parentCate)){
            $articleCate = ArticleCategoryModel::where('pid',$parentCate->id)->limit(4)->get()->toArray();
            $this->theme->set('article_cate', $articleCate);
        }

        //判断是否开启IM (1=>开启)
        $basisConfig = ConfigModel::getConfigByType('basis');
        if(!empty($basisConfig)){
            $this->theme->set('basis_config',$basisConfig);
        }
        if(!empty($basisConfig) && $basisConfig['open_IM'] == 1){
            $ImPath = app_path('Modules' . DIRECTORY_SEPARATOR . 'Im');
            //判断是否有Im目录
            if(is_dir($ImPath)){
                $contact = 1;
                if (Auth::check()){
                    $arrFriendUid = \App\Modules\Im\Model\ImAttentionModel::where('uid', $user->id)->lists('friend_uid')->toArray();
                    $arrAttention = UserModel::select('users.id', 'users.name', 'user_detail.avatar', 'user_detail.autograph')->whereIn('users.id', $arrFriendUid)
                        ->leftJoin('user_detail', 'users.id', '=', 'user_detail.uid')->get()->toArray();
                    $this->theme->set('attention', $arrAttention);
                }
            }else{
                $contact = 2;
            }
        }else{
            $contact = 2;
        }
        $this->theme->set('is_IM_open',$contact);

        //判断问答是否开启
        $question_switch = \CommonClass::getConfig('question_switch');
        $this->theme->set('question_switch',$question_switch);

        //判断注册推广是否开启
        $promoteType = PromoteTypeModel::where('is_open',1)->where('code_name','ZHUCETUIGUANG')->first();
        if(!empty($promoteType)){
            if($promoteType->is_open == 1){
                $this->theme->set('promote_switch',1);
            }
        }

    }


}
