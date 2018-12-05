<?php

namespace App\Modules\User\Model;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Order\Model\OrderModel;
use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class UserDetailModel extends Model
{
    //
    protected $table = 'user_detail';

    protected $fillable = [
        'uid', 'realname', 'avatar', 'mobile', 'qq', 'wechat', 'card_number', 'province', 'city', 'area', 'address', 'sign', 'balance', 'balance_status', 'last_login_time', 'alternate_tips', 'nickname',
        'publish_task_num', 'receive_task_num', 'employer_praise_rate', 'employee_praise_rate'
    ];

    public function tags()
    {
        return $this->hasMany('App\Modules\User\Model\UserTagsModel', 'uid', 'uid');
    }

    /**
     * 通过uid查询用户的信息
     * @param $uid
     * @return mixed
     */
    static public function findByUid($uid)
    {
        $result = UserDetailModel::where(['uid' => $uid])->first();

        return $result;
    }

    /**
     * 更新用户信息，第一次的时候创建
     * @param $data
     * @param $uid
     * @return mixed
     */
    static function updateData($data, $uid)
    {
        //如果用户是第一次设置资料就创建一条用户的资料
        UserDetailModel::firstOrCreate(['uid' => $uid]);
        $result = UserDetailModel::where('uid', '=', $uid)->update($data);
        return $result;
    }

    /**
     * 给用户充值
     * @param $uid
     * @param $type
     * @param array $data
     * @return bool
     */
    static function recharge($uid, $type, array $data)
    {
        $status = DB::transaction(function () use ($uid, $type, $data) {
            //为用户充值
            $result1 = UserDetailModel::where('uid', '=', $uid)->increment('balance', $data['money']);

            if(!empty($data['code']))
                OrderModel::where('code', $data['code'])->update(['status' => 1]);

            //产生财务记录 用户充值行为
            $financial = [
                'action' => 3,
                'pay_type' => $type,
                'pay_account' => $data['pay_account'],
                'pay_code' => $data['pay_code'],
                'cash' => $data['money'],
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time()),
            ];
            $result2 = FinancialModel::create($financial);

        });
        return is_null($status) ? true : false;
    }

    /**
     * 关闭支付密码提示
     *
     * @return mixed
     */
    static function closeTips()
    {
        $user = Auth::User();
        return self::where('uid', $user->id)->update(['alternate_tips' => 1]);
    }

    /**
     * 根据用户id获取用户地区信息
     * @param $uid 用户id
     * @return string
     */
    static function getAreaByUserId($uid)
    {
        $pre = UserDetailModel::join('district', 'user_detail.province', '=', 'district.id')
            ->select('district.name')->where('user_detail.uid', $uid)->first();
        $city = UserDetailModel::join('district', 'user_detail.city', '=', 'district.id')
            ->select('district.name')->where('user_detail.uid', $uid)->first();
        $province = $pre ? $pre->name : '';
        $city = $city ? $city->name : '';
        $addr = $province . $city;
        return $addr;
    }

    /**
     * 查询雇佣服务商信息
     * @param $uid
     * @return mixed
     */
    static function employeeData($uid)
    {
        $employee = self::select('user_detail.*', 'ur.name as user_name', 'ur.email_status', 'dp.name as province_name', 'dc.name as city_name')
            ->with('tags')
            ->where('user_detail.uid', $uid)
            ->join('users as ur', 'ur.id', '=', 'user_detail.uid')
            ->leftjoin('district as dp', 'dp.id', '=', 'user_detail.province')
            ->leftjoin('district as dc', 'dc.id', '=', 'user_detail.city')
            ->first()->toArray();
        $tags_id = \CommonClass::getList($employee['tags'], 'tag_id');

        //查询组装服务商的tags
        if (!empty($tags_id)) {
            $tags = TagsModel::findById($tags_id);
            $employee['tags'] = $tags;
        }

        //查询用户的认证情况
        $auth_data = AuthRecordModel::where('uid', $uid)->where('status', 1)->lists('auth_code')->toArray();

        $employee['auth'] = $auth_data;

        //计算好评率
        if ($employee['receive_task_num'] != 0) {
            $employee['good_rate'] = floor($employee['employee_praise_rate'] * 100 / $employee['receive_task_num']);
        } else {
            $employee['good_rate'] = 100;
        }

        return $employee;
    }

    static function employerData($uid)
    {
        $employee = self::select('user_detail.*', 'ur.name as user_name', 'ur.email_status', 'dp.name as province_name', 'dc.name as city_name')
            ->with('tags')
            ->where('user_detail.uid', $uid)
            ->join('users as ur', 'ur.id', '=', 'user_detail.uid')
            ->leftjoin('district as dp', 'dp.id', '=', 'user_detail.province')
            ->leftjoin('district as dc', 'dc.id', '=', 'user_detail.city')
            ->first()->toArray();

        //查询用户的认证情况
        $auth_data = AuthRecordModel::where('uid', $uid)->where('status', 1)->lists('auth_code')->toArray();

        $employee['auth'] = $auth_data;

        //计算好评率
        if ($employee['receive_task_num'] != 0) {
            $employee['good_rate'] = floor($employee['employer_praise_rate'] * 100 / $employee['receive_task_num']);
        } else {
            $employee['good_rate'] = 100;
        }

        return $employee;
    }
}
