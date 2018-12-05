<?php

namespace App\Modules\User\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class RealnameAuthModel extends Model
{
    protected $table = 'realname_auth';
    //
    protected $fillable = [
        'uid', 'username', 'card_front_side', 'card_back_dside', 'validation_img', 'status', 'auth_time','card_type','type','realname','card_number'
    ];

    /**
     * 获取用户实名认证状态
     *
     * @param $uid
     * @return null
     */
    static function getRealnameAuthStatus($uid)
    {
        $realnameInfo = RealnameAuthModel::where('uid', $uid)->first();
        if ($realnameInfo) {
            return $realnameInfo->status;
        }
        return null;
    }

    public $transactionData;

    /**
     * 新增身份认证
     *
     * @param $realnameInfo
     * @param $authRecordInfo
     * @return bool
     */
    public function createRealnameAuth($realnameInfo, $authRecordInfo)
    {
        $status = DB::transaction(function () use ($realnameInfo, $authRecordInfo) {
            $authRecordInfo['auth_id'] = DB::table('realname_auth')->insertGetId($realnameInfo);
            DB::table('auth_record')->insert($authRecordInfo);
        });
        return is_null($status) ? true : $status;
    }

    /**
     * 前台用户取消实名认证
     *
     * @param $id
     * @return bool
     */
    public function removeRealnameAuth()
    {
        $status = DB::transaction(function () {
            $user = Auth::User();
            RealnameAuthModel::where('uid', $user->id)->delete();
            AuthRecordModel::where('auth_code', 'realname')->where('uid', $user->id)->delete();
        });
        return is_null($status) ? true : $status;
    }

    /**
     * 后台审核通过实名认证
     *
     * @param $id
     * @return bool
     */
    static function realnameAuthPass($id)
    {
        $status = DB::transaction(function () use ($id) {
            RealnameAuthModel::where('id', $id)->update(array('status' => 1, 'auth_time' => date('Y-m-d H:i:s')));
            AuthRecordModel::where('auth_id', $id)
                ->where('auth_code', 'realname')
                ->update(array('status' => 1, 'auth_time' => date('Y-m-d H:i:s')));
        });

        return is_null($status) ? true : $status;
    }

    /**
     * 后台审核失败实名认证
     *
     * @param $id
     * @return bool
     */
    static function realnameAuthDeny($id)
    {
        $status = DB::transaction(function () use ($id) {
            RealnameAuthModel::where('id', $id)->update(array('status' => 2));
            AuthRecordModel::where('auth_id', $id)
                ->where('auth_code', 'realname')
                ->update(array('status' => 2));
        });

        return is_null($status) ? true : $status;
    }


}
