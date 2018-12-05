<?php
/**
 * Created by PhpStorm.
 * User: xuanke
 * Date: 2016/7/4
 * Time: 15:14
 */
namespace App\Modules\Api\Http\Controllers;

use App\Http\Controllers\ApiBaseController;
use App\Modules\Finance\Model\CashoutModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\User\Model\AlipayAuthModel;
use App\Modules\User\Model\BankAuthModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Omnipay;
use DB;
use Validator;

class PayController extends ApiBaseController
{
    protected $uid;

    public function __construct(Request $request)
    {
        $tokenInfo = Crypt::decrypt($request->get('token'));
        $this->uid = $tokenInfo['uid'];
    }

    /**
     * 赏金托管之余额支付
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function taskDepositByBalance(Request $request)
    {

        $data = $request->all();
        $validator = Validator::make($data, [
            'task_id' => 'required',
            'pay_type' => 'required',
        ], [
            'task_id.required' => '请选择要托管的任务',
            'pay_type.required' => '请选择支付方式',
        ]);
        $error = $validator->errors()->all();
        if (count($error)) {
            return $this->formateResponse(2010, '信息有误', $error);
        }
        $task_id = $data['task_id'];
        //判断用户是否有支付此任务的权限
        $task = TaskModel::where('id', $task_id)->first();
        if ($task->uid != $this->uid || $task->status >= 2) {
            return $this->formateResponse(2011, '非法操作');
        }

        //计算用户的任务需要的金额
        $taskModel = new TaskModel();
        $money = $taskModel->taskMoney($task_id);
        //查询用户余额
        $user = UserModel::where('id', $this->uid)->first();
        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $balance = (float)$userDetail->balance;

        if ($balance >= $money && $data['pay_type'] == 0) {//余额支付
            //创建订单
            $order = OrderModel::bountyOrder($this->uid, $money, $task_id);
            if (!$order) {
                return $this->formateResponse(2012, '任务托管失败');
            }
            $alternate_password = UserModel::encryptPassword($data['password'], $user->salt);
            if ($alternate_password != $user->alternate_password) {
                return $this->formateResponse(2013, '支付密码不正确');
            }
            $result = TaskModel::bounty($money, $task_id, $this->uid, $order->code);
            if ($result) {
                return $this->formateResponse(1000, 'success');
            } else {
                return $this->formateResponse(2014, '赏金托管失败');
            }
        } else {
            return $this->formateResponse(2015, '余额支付失败');
        }
    }

    /**
     * 创建订单信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function createOrderInfo(Request $request)
    {
        $data = $request->all();
        $task_id = $data['task_id'];
        //判断用户是否有支付此任务的权限
        $task = TaskModel::where('id', $task_id)->first();
        if ($task->uid == $this->uid || $task->status >= 2) {
            return $this->formateResponse(2011, '非法操作');
        }
        //计算用户的任务需要的金额
        $taskModel = new TaskModel();
        $money = $taskModel->taskMoney($task_id);
        $orderInfo = OrderModel::where('task_id', $task_id)->first();
        if ($orderInfo) {
            $order = $orderInfo;
        } else {
            $order = OrderModel::bountyOrder($this->uid, $money, $task_id);
        }
        if ($order) {
            return $this->formateResponse(1000, 'success', $order);
        } else {
            return $this->formateResponse(2022, '订单创建失败');
        }
    }

    /**
     * 检测第三方支付配置信息
     * @param Request $request
     * @return array|bool|mixed|null|string
     */
    public function checkThirdConfig(Request $request)
    {

        $pay_type = $request->get('pay_type');
        $configInfo = $pay_type_name = '';
        $status = 1;
        switch ($pay_type) {
            case 1:
                $configInfo = ConfigModel::getPayConfig('alipay');//支付宝
                $pay_type_name = '支付宝';
                break;
            case 2:
                $configInfo = ConfigModel::getPayConfig('wechatpay');//微信支付
                $pay_type_name = '微信支付';
                break;
            case 3:
//                $configInfo = ConfigModel::getPayConfig('unionpay');//银联
                $configInfo = null;
                $pay_type_name = '银联支付';
                break;
        }
        //判断配置信息是否有空值
        if (is_array($configInfo)) {
            foreach ($configInfo as $con) {
                if (empty($con)) {
                    $status = 0;
                }
            }
        }
        if (!$configInfo) {
            $status = 0;
        }

        if ($status) {
            return $this->formateResponse(1000, 'success', $configInfo);
        } else {
            return $this->formateResponse(2021, $pay_type_name . '配置信息不全');
        }
    }

    /**
     * 账户余额
     * @return \Illuminate\Http\Response
     */
    public function balance()
    {
        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $data = array(
            'balance' => $userDetail->balance
        );
        return $this->formateResponse(1000, 'success', $data);
    }

    /**
     * 财务流水
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function financeList(Request $request)
    {
        $data = $request->all();
        $data['timeStatus'] = isset($data['timeStatus']) ? $data['timeStatus'] : 0;
        $finance = FinancialModel::where('uid', $this->uid);
        if (isset($data['timeStatus'])) {
            $sql = 'date_format(created_at,"%Y-%m")=date_format(date_sub(now(),interval ' . $data['timeStatus'] . ' month),"%Y-%m")';
            if ($data['timeStatus']) {
                $finance = $finance->whereRaw($sql);
            } else {
                $finance = $finance->whereRaw('date_format(created_at,"%Y-%m")=date_format(now(),"%Y-%m")');
            }
        }
        $finance = $finance->orderBy('created_at','desc')->get()->toArray();
        $userInfo = UserDetailModel::where('uid',$this->uid)->select('balance')->first();
        $financeInfo = [
            'balance' => $userInfo->balance,
            'finance' => $finance
        ];
        return $this->formateResponse(1000,'success',$financeInfo);
    }

    /**
     * 提现
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function cashOut(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'cash' => 'required|numeric',
            'cashout_type' => 'required',
            'cashout_account' => 'required',
            'alternate_password' => 'required',
        ], [
            'cash.required' => '请输入提现金额',
            'cash.numeric' => '请输入正确的金额格式',
            'cashout_type.required' => '请选择提现方式',
            'cashout_account.required' => '请输入提现账户',
            'alternate_password.required' => '请输入支付密码',
        ]);
        $error = $validator->errors()->all();
        if (count($error)) {
            return $this->formateResponse(1070,'输入信息有误',$error);
        }

        $userDetail = UserDetailModel::where('uid', $this->uid)->first();
        $user = UserModel::where('id', $this->uid)->first();
        $balance = $userDetail->balance;
        //提现配置
        $cashConfig = ConfigModel::getConfigByAlias('cash');
        $rule = json_decode($cashConfig->rule, true);

        //时间计算
        $now = strtotime(date('Y-m-d'));
        $start = date('Y-m-d H:i:s', $now);
        $end = date('Y-m-d H:i:s', $now + 24 * 3600);

        //用户当日提现总金额
        $cashOutSum = CashoutModel::where('uid', $this->uid)->whereBetween('created_at', [$start, $end])->sum('cash');
        $message = '';
        if ($data['cash'] > $balance) {
            return $this->formateResponse(1071,'提现金额不得大于账户余额');
        }
        if ($rule['withdraw_min'] && $data['cash'] < $rule['withdraw_min']) {
            return $this->formateResponse(1072,'单笔提现金额不得小于' . $rule['withdraw_min'] . '元');
        }
        if ($rule['withdraw_max'] && $cashOutSum > $rule['withdraw_max']) {
            return $this->formateResponse(1073,'当日提现金额不得大于' . $rule['withdraw_max'] . '元');
        }

        $alternate_password = UserModel::encryptPassword($data['alternate_password'], $user->salt);
        if ($alternate_password === $user->alternate_password) {
            $fees = FinancialModel::getFees($data['cash']);
            $info = array(
                'uid' => $this->uid,
                'cash' => $data['cash'],
                'fees' => $fees,
                'real_cash' => $data['cash'] - $fees,
                'cashout_type' => $data['cashout_type'],
                'cashout_account' => $data['cashout_account'],
            );

            $status = $this->addCashOut($info);
            if ($status) {
                return $this->formateResponse(1000, 'success');
            } else {
                return $this->formateResponse(1075, '提现失败');
            }
        } else {
            return $this->formateResponse(1074, '支付密码不正确');
        }
    }

    /**
     * 用户已认证的银行卡
     * @return \Illuminate\Http\Response
     */
    public function bankAccount()
    {
        $bankCard = BankAuthModel::where('uid', $this->uid)->where('status', 2)->get();
        if (count($bankCard)) {

            return $this->formateResponse(1000, 'success', $bankCard);
        } else {
            return $this->formateResponse(2017, '暂无已认证的银行卡信息');
        }
    }

    /**
     * 用户已认证的支付宝
     * @return \Illuminate\Http\Response
     */
    public function alipayAccount()
    {
        $alipay = AlipayAuthModel::where('uid', $this->uid)->where('status', 2)->get();
        if (count($alipay)) {
            return $this->formateResponse(1000, 'success', $alipay);
        } else {
            return $this->formateResponse(2018, '暂无已认证的支付宝信息');
        }
    }

    /**
     * 提现事务处理
     * @param $data
     * @return bool
     */
    static function addCashOut($data)
    {
        $status = DB::transaction(function () use ($data) {
            CashoutModel::create($data);
            $finance = array(
                'action' => 4,
                'pay_account' => $data['cashout_account'],
                'cash' => $data['cash'],
                'uid' => $data['uid'],
                'created_at' => date('Y-m-d H:i:d', time()),
            );
            if ($data['cashout_type'] == 1) {
                $finance['pay_type'] = 2;
            } elseif ($data['cashout_type'] == 2) {
                $finance['pay_type'] = 4;
            }
            FinancialModel::create($finance);
            UserDetailModel::where('uid', $data['uid'])->decrement('balance', $data['cash']);
        });
        return is_null($status) ? true : false;
    }

    /**
     * 支付宝充值异步回调
     *
     * @return string
     */
    public function alipayNotify()
    {
        if (app('alipay.mobile')->verify()) {
            $data = [
                'pay_account' => Input::get('buy_email'),
                'code' => Input::get('out_trade_no'),
                'pay_code' => Input::get('trade_no'),
                'money' => Input::get('total_fee')
            ];

            // 判断通知类型。
            switch (Input::get('trade_status')) {
                case 'TRADE_SUCCESS':
                case 'TRADE_FINISHED':
                    $orderInfo = OrderModel::where('code', $data['code'])->first();
                    if (!empty($orderInfo)) {
                        if ($orderInfo->task_id) {
                            $uid = $orderInfo->uid;
                            $money = $data['money'];
                            $task_id = $orderInfo->task_id;
                            $code = $data['code'];
                            $result = DB::transaction(function () use ($money, $task_id, $uid, $code) {
                                //修改任务的赏金托管状态
                                $data = self::where('id', $this->task_id)->update(['bounty_status' => 1,'status' => 2]);
                                //生成财务记录，action 1表示发布任务
                                $financial = [
                                    'action' => 1,
                                    'pay_type' => 2,
                                    'cash' => $money,
                                    'uid' => $uid,
                                    'created_at' => date('Y-m-d H:i:s', time())
                                ];
                                FinancialModel::create($financial);
                                //修改订单状态
                                OrderModel::where('code', $code)->update(['status' => 1]);

                                //修改用户的托管状态
                                //判断用户的赏金是否大于系统的任务审核金额
                                $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
                                if ($bounty_limit < $money) {
                                    self::where('id', '=', $task_id)->update(['status' => 3]);
                                } else {
                                    self::where('id', '=', $task_id)->update(['status' => 2]);
                                }
                                //增加用户的发布任务数量
                                UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
                                return true;
                            });
                        } else {
                            $result = UserDetailModel::recharge($orderInfo->uid, 2, $data);
                        }


                        if (!$result) {
                            return $this->formateResponse(2022, '支付失败');
                        }

                        return $this->formateResponse(1000, 'success');
                    }
                    return $this->formateResponse(2023, '订单信息错误');
                    break;
            }

            return $this->formateResponse(2023, '支付失败');
        }
    }

    /**
     * 微信异步回调
     * @return mixed
     */
    public function wechatpayNotify()
    {
        Log::info('微信支付回调');
        $gateway = Omnipay::gateway('WechatPay');

        $response = $gateway->completePurchase([
            'request_params' => file_get_contents('php://input')
        ])->send();

        if ($response->isPaid()) {
            //pay success
            $result = $response->getData();
            $data = [
                'pay_account' => $result['openid'],
                'code' => $result['out_trade_no'],
                'pay_code' => $result['transaction_id'],
                'money' => $result['total_fee']
            ];
            $orderInfo = OrderModel::where('code', $data['code'])->first();
            if (!empty($orderInfo)) {
                if ($orderInfo->task_id) {
                    $uid = $orderInfo->uid;
                    $money = $data['money'];
                    $task_id = $orderInfo->task_id;
                    $code = $data['code'];
                    $result = DB::transaction(function () use ($money, $task_id, $uid, $code) {
                        //修改任务的赏金托管状态
                        $data = self::where('id', $this->task_id)->update(['bounty_status' => 1,'status' => 2]);
                        //生成财务记录，action 1表示发布任务
                        $financial = [
                            'action' => 1,
                            'pay_type' => 3,
                            'cash' => $money,
                            'uid' => $uid,
                            'created_at' => date('Y-m-d H:i:s', time())
                        ];
                        FinancialModel::create($financial);
                        //修改订单状态
                        OrderModel::where('code', $code)->update(['status' => 1]);

                        //修改用户的托管状态
                        //判断用户的赏金是否大于系统的任务审核金额
                        $bounty_limit = \CommonClass::getConfig('task_bounty_limit');
                        if ($bounty_limit < $money) {
                            self::where('id', '=', $task_id)->update(['status' => 3]);
                        } else {
                            self::where('id', '=', $task_id)->update(['status' => 2]);
                        }
                        //增加用户的发布任务数量
                        UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
                        return true;
                    });
                } else {
                    $result = UserDetailModel::recharge($orderInfo->uid, 2, $data);
                }


                if (!$result) {
                    return $this->formateResponse(2022, '支付失败');
                }

                return $this->formateResponse(1000, 'success');
            }

        } else {
            //pay fail
        }
    }


    /**
     *用户充值信息处理
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postCash(Request $request)
    {
        if ($request->get('task_id')) {
            $task_id = $request->get('task_id');
            //判断用户是否有支付此任务的权限
            $task = TaskModel::where('id', $task_id)->first();
            if ($task->uid != $this->uid || $task->status >= 2) {
                return $this->formateResponse(1071, '非法操作');
            }

            //计算用户的任务需要的金额
            $taskModel = new TaskModel();
            $money = $taskModel->taskMoney($task_id);

            //创建订单
            $order = OrderModel::bountyOrder($this->uid, $money, $task_id);
            /*$result = TaskModel::bounty($money,$task_id,$this->uid,$order->code);
            if($result){
                return $this->formateResponse(1000,'success');
            }else{
                return $this->formateResponse(2014,'赏金托管失败');
            }*/
        } else {
            $data = array(
                'code' => OrderModel::randomCode($this->uid),
                'title' => $request->get('title'),
                'cash' => $request->get('cash'),
                'uid' => $this->uid,
                'created_at' => date('Y-m-d H:i:s', time()),
                'note' => $request->get('note'),
                'task_id' => $request->get('task_id')
            );
            $order = OrderModel::create($data);
        }

        if ($order) {
            $payType = $request->get('pay_type');
            switch ($payType) {
                case 'alipay':
                    $alipay = app('alipay.mobile');
                    $config = ConfigModel::getConfigByAlias('app_alipay');
                    $info = [];
                    if($config && !empty($config['rule'])){
                        $info = json_decode($config['rule'],true);
                    }
                    if(!empty($info)){
                        $alipay->setPartner($info['partner_id']);
                    }
                    if(!empty($info)){
                        $alipay->setSellerId($info['seller_id']);
                    }
                    $alipay->setNotifyUrl(url('api/alipay/notify'));
                    $alipay->setOutTradeNo($order->code);
                    $alipay->setTotalFee($order->cash);
                    $alipay->setSubject($order->title);
                    $alipay->setBody($order->note);
                    return $this->formateResponse(1000, '确认充值', ['payParam' => $alipay->getPayPara()]);
                    break;
                case 'wechat':
                    $gateway = Omnipay::gateway('WechatPay');
                    $configInfo = ConfigModel::getConfigByAlias('app_wechat');
                    $config = [];
                    if($configInfo && !empty($configInfo['rule'])){
                        $config = json_decode($configInfo['rule'],true);
                    }
                    if(isset($config['appId'])){
                        $gateway->setAppId($config['appId']);
                    }
                    if(isset($config['mchId'])){
                        $gateway->setMchId($config['mchId']);
                    }
                    if(isset($config['apiKey'])){
                        $gateway->setApiKey($config['apiKey']);
                    }
                    $gateway->setNotifyUrl(url('api/wechatpay/notify'));
                    $data = [
                        'body' => $order->title,
                        'out_trade_no' => $order->code,
                        'total_fee' => $order->cash*100, //=0.01
                        'spbill_create_ip' => Input::getClientIp(),
                        'fee_type' => 'CNY'
                    ];
                    $request = $gateway->purchase($data);
                    $response = $request->send();
                    if ($response->isSuccessful()) {
                        return $this->formateResponse(1000, '确认充值', ['params' => $response->getAppOrderData()]);
                    }
                    break;
            }
        } else {
            return $this->formateResponse(1072, '订单生成失败');
        }
    }


}