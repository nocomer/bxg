<?php

namespace App\Modules\User\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\DB;

class UserModel extends Model implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'email_status', 'mobile', 'password', 'alternate_password', 'salt', 'status', 'overdue_date', 'validation_code', 'expire_date',
        'reset_password_code', 'remember_token','source'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * 密码加密
     *
     * @param $password
     * @param string $sign
     * @return string
     */
    static function encryptPassword($password, $sign = '')
    {
        return md5(md5($password . $sign));
    }

    /**
     * 检查账户密码
     *
     * @param $username
     * @param $password
     * @return bool
     */
    static function checkPassword($username, $password)
    {
        $user = UserModel::where('name', $username)
            ->orWhere('email', $username)->orWhere('mobile', $username)->first();

        if ($user) {
            $password = self::encryptPassword($password, $user->salt);
            if ($user->password === $password) {
                return true;
            }
        }
        return false;
    }
    /**
     * 检查用户支付密码
     *
     * @param $email
     * @param $password
     * @return bool
     */
    static function checkPayPassword($email, $password)
    {
        $user = UserModel::where('email', $email)->first();
        if ($user) {
            $password = self::encryptPassword($password, $user->salt);
            if ($user->alternate_password == $password) {
                return true;
            }
        }
        return false;
    }
    /**
     * 用户修改密码
     * @param $data
     * @param $userInfo
     * @return mixed
     * author: muker（qq:372980503）
     */
    static function psChange($data, $userInfo)
    {
        $user = new UserModel;
        $password = UserModel::encryptPassword($data['password'], $userInfo['salt']);
        $result = $user->where(['id'=>$userInfo['id']])->update(['password'=>$password]);

        return $result;
    }

    /**
     * 用户修改支付密码
     * @param $data
     * @param $userInfo
     * @return mixed
     * author: muker（qq:372980503）
     */
    static function payPsUpdate($data, $userInfo)
    {
        $user = new UserModel;
        $password = UserModel::encryptPassword($data['password'], $userInfo['salt']);
        $result = $user->where(['id'=>$userInfo['id']])->update(['alternate_password'=>$password]);

        return $result;
    }

    /**
     * 新建用户
     *
     * @param array $data
     * @return string
     */
    static function createUser(array $data)
    {
        //创建用户
        $salt = \CommonClass::random(4);
        $validationCode = \CommonClass::random(6);
        $date = date('Y-m-d H:i:s');
        $now = time();
        $userArr = array(
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => UserModel::encryptPassword($data['password'], $salt),
            'alternate_password' => UserModel::encryptPassword($data['password'], $salt),
            'salt' => $salt,
            'last_login_time' => $date,
            'overdue_date' => date('Y-m-d H:i:s', $now + 60*60*3),
            'validation_code' => $validationCode,
            'created_at' => $date,
            'updated_at' => $date
        );
        $objUser = new UserModel();
        //初始化用户信息和用户详情
        $status = $objUser->initUser($userArr);
        if ($status){
            $emailSendStatus = \MessagesClass::sendActiveEmail($data['email']);
            if (!$emailSendStatus){
                $status = false;
            }
            return $status;
        }
    }


    /**
     * 新增用户及用户信息事务
     *
     * @param array $data
     * @return bool
     */
    public function initUser(array $data)
    {
        $status = DB::transaction(function() use ($data){
            $data['uid'] = UserModel::insertGetId($data);
            UserDetailModel::create($data);
            return $data['uid'];
        });
        return $status;

    }

    /**
     * 获取用户名
     *
     * @param $id
     * @return mixed
     */
    static function getUserName($id)
    {
        $userInfo = UserModel::where('id',$id)->first();
        return $userInfo->name;
    }

    /**
     * @param $uid
     */
    public function isAuth($uid)
    {
        $auth = AuthRecordModel::where('uid',$uid)->where('status',4)->first();
        $bankAuth = BankAuthModel::where('uid',$uid)->where('status',4)->first();
        $aliAuth = AlipayAuthModel::where('uid',$uid)->where('status',4)->first();
        $data['auth'] = is_null($auth)?true:false;
        $data['bankAuth'] = is_null($bankAuth)?true:false;
        $data['aliAuth'] = is_null($aliAuth)?true:false;

        return $data;
    }

    /**
     * 后台编辑用户事务
     *
     * @param $data
     * @return bool
     */
    static function editUser($data)
    {
        $status = DB::transaction(function () use ($data){
            UserModel::where('id', $data['uid'])->update([
                'email' => $data['email'],
                'password' => $data['password'],
                'mobile' => $data['mobile']
            ]);
            UserDetailModel::where('uid', $data['uid'])->update([
                'realname' => $data['realname'],
                'qq' => $data['qq'],
                'mobile' => $data['mobile'],
                'province' => $data['province'],
                'city' => $data['city'],
                'area' => $data['area']
            ]);
        });
        return is_null($status) ? true : false;
    }

    /**
     * 后台新建用户事务
     *
     * @param $data
     * @return bool
     */
    static function addUser($data)
    {
        $status = DB::transaction(function () use ($data){
            $data['uid'] = UserModel::insertGetId([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'salt' => $data['salt']
            ]);
            UserDetailModel::create([
                'uid' => $data['uid'],
                'realname' => $data['realname'],
                'qq' => $data['qq'],
                'mobile' => $data['mobile'],
                'province' => $data['province'],
                'city' => $data['city'],
                'area' => $data['area']
            ]);
        });
        return is_null($status) ? true : false;
    }

    /**
     * 手机注册初始化用户注册信息
     *
     * @param $data
     * @return bool
     */
    static function mobileInitUser($data)
    {
        $status = DB::transaction(function() use ($data){
            $sign = str_random(4);
            $userInfo = [
                'name' => $data['username'],
                'mobile' => $data['mobile'],
                'password' => self::encryptPassword($data['password'], $sign),
                'alternate_password' => self::encryptPassword($data['password'], $sign),
                'salt' => $sign,
                'status' => 1,
                'source' => 1
            ];
            $user = UserModel::create($userInfo);
            UserDetailModel::create([
                'uid' => $user->id,
                'mobile' => $user->mobile,
            ]);
            return $user->id;
        });
        return $status;
    }
}
