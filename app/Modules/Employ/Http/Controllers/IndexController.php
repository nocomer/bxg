<?php

namespace App\Modules\Employ\Http\Controllers;

use App\Http\Controllers\IndexController as BasicIndexController;
use App\Http\Requests;
use App\Modules\Employ\Http\Requests\EmployCreateRequest;
use App\Modules\Employ\Http\Requests\EvaluateRequest;
use App\Modules\Employ\Models\EmployAttachmentModel;
use App\Modules\Employ\Models\EmployCommentsModel;
use App\Modules\Employ\Models\EmployGoodsModel;
use App\Modules\Employ\Models\EmployModel;
use App\Modules\Employ\Models\EmployUserModel;
use App\Modules\Employ\Models\EmployWorkModel;
use App\Modules\Employ\Models\UnionAttachmentModel;
use App\Modules\Employ\Models\UnionRightsModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\Shop\Models\GoodsModel;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserFocusModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Omnipay;
use Theme;
use QrCode;

class IndexController extends BasicIndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('employ');
    }

    public function index()
    {
        return $this->theme->scope('employ.index')->render();
    }

    /**
     * 创建雇佣页面
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employCreate($id,$service=0)
    {
        $this->theme->setTitle('雇佣页面');
        $service = intval($service);
        //查询被雇佣者的信息
        $employee_data = UserDetailModel::employeeData($id);
        //判断当前用户是不是在雇佣自己
        if ($id == Auth::user()['id']) {
            return redirect()->back()->with(['error' => '自己不能雇佣自己！']);
        }
        //如果是服务雇佣
        if($service!=0)
        {
            $service = GoodsModel::where('id',$service)->where('type',2)->where('uid',$id)->first()->toArray();
            if(!$service)
                return redirect()->back()->with(['error' => '服务不存在！']);
        }
        //查询当前用户店铺是否开启
        $user_shop = ShopModel::where('status',1)->where('uid',$id)->first();
        $domain = url();
        $view = [
            'employ_data' => $employee_data,
            'domain' => $domain,
            'contact' => Theme::get('is_IM_open'),
            'service' => $service,
            'user_shop'=>$user_shop
        ];
        return $this->theme->scope('employ.create', $view)->render();
    }

    /**
     * 提交雇佣任务
     * @param Request $request
     */
    public function employUpdate(EmployCreateRequest $request)
    {
        $data = $request->except('_token');
        $time = date('Y-m-d', time());
        $employ_bounty_min_limit = \CommonClass::getConfig('employ_bounty_min_limit');
        //验证赏金最小值
        $task_bounty_min_limit = $employ_bounty_min_limit;
        //验证赏金大小合法性
        if ($data['bounty'] < $task_bounty_min_limit) {
            return redirect()->back()->with(['error' => '赏金不能小于' . $task_bounty_min_limit]);
        }
        //创建一条雇佣记录
        $data['employee_uid'] = intval($data['employee_uid']);
        $data['employer_uid'] = Auth::user()['id'];
        $data['delivery_deadline'] = preg_replace('/([\x80-\xff]*)/i', '', $data['delivery_deadline']);
        $data['status'] = 0;
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        //判断
        if($data['service_id']!=0)
        {
            $data['employ_type'] = 1;
        }
        //创建一条雇佣记录
        $result = EmployModel::employCreate($data);

        if (!$result)
            return redirect()->back()->with(['error' => '创建雇佣任务失败']);

        return redirect('employ/bounty/' . $result['id']);
    }

    /**
     * 托管赏金页面
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employBounty($id)
    {
        $this->theme->setTitle('雇佣托管');
        //查询雇佣数据
        $employ_data = EmployModel::where('id', intval($id))->first();

        if (!$employ_data) {
            return redirect()->back()->with(['error' => '参数错误！']);
        }
        //查询当前的用户的信息
        $user_data = UserDetailModel::where('uid', Auth::user()['id'])->first();
        //判断第三方支付是否开启
        $payConfig = ConfigModel::getConfigByType('thirdpay');
        $view = [
            'employ_data' => $employ_data,
            'user_data' => $user_data,
            'payConfig' => $payConfig
        ];

        return $this->theme->scope('employ.bounty', $view)->render();
    }

    /**
     * 雇佣赏金托管
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employBountyUpdate(Request $request)
    {
        $data = $request->except('_token');
        $data['id'] = intval($data['id']);
        //查询用户发布的数据
        $employ = EmployModel::where('id', $data['id'])->first();

        //判断用户所要支付的是否是自己的任务和任务是否已经支付
        if ($employ['employer_uid'] != Auth::user()['id'] || $employ['bounty_status'] != 0)
        {
            return redirect()->back()->with('error', '该雇佣已经托管！');
        }

        //查询用户的余额
        $balance = UserDetailModel::where('uid', Auth::user()['id'])->first();
        $balance = $balance['balance'];

        //创建订单
        $is_ordered = ShopOrderModel::employOrder(Auth::user()['id'], $employ['bounty'], $data);

        if (!$is_ordered) return redirect()->back()->with(['error' => '创建订单失败！']);
        //判断用户如果选择的余额支付
        if ($balance >= $employ['bounty'] && $data['pay_canel'] == 0)
        {
            //验证用户的密码是否正确
            $password = UserModel::encryptPassword($data['password'], Auth::user()['salt']);
            if ($password != Auth::user()['alternate_password'])
            {
                return redirect()->back()->with(['error' => '您的支付密码不正确']);
            }
            //支付产生订单
            EmployModel::employBounty($employ['bounty'], $employ['id'], Auth::user()['id'], $is_ordered->code);

            return Redirect::route('success',['id' => $employ['id'],'uid'=>$employ['employee_uid']]);
        } else if (isset($data['pay_type']) && $data['pay_canel'] == 1) {
            if ($data['pay_type'] == 1) {//支付宝支付
                $config = ConfigModel::getPayConfig('alipay');
                $objOminipay = Omnipay::gateway('alipay');
                $objOminipay->setPartner($config['partner']);
                $objOminipay->setKey($config['key']);
                $objOminipay->setSellerEmail($config['sellerEmail']);
                $siteUrl = \CommonClass::getConfig('site_url');
                $objOminipay->setReturnUrl($siteUrl . '/order/pay/alipay/return');
                $objOminipay->setNotifyUrl($siteUrl . '/order/pay/alipay/notify');

                $response = Omnipay::purchase([
                    'out_trade_no' => $is_ordered->code, //your site trade no, unique
                    'subject' => \CommonClass::getConfig('site_name'), //order title
                    'total_fee' => $employ['bounty'], //order total fee $money $employ['bounty']
                ])->send();
                $response->redirect();
            } else if ($data['pay_type'] == 2) {//微信支付
                $config = ConfigModel::getPayConfig('wechatpay');
                $wechat = Omnipay::gateway('wechat');
                $wechat->setAppId($config['appId']);
                $wechat->setMchId($config['mchId']);
                $wechat->setAppKey($config['appKey']);
                $out_trade_no = $is_ordered->code;
                $params = array(
                    'out_trade_no' => $is_ordered->code, // billing id in your system
                    'notify_url' => env('WECHAT_NOTIFY_URL', url('order/pay/wechat/notify')), // URL for asynchronous notify
                    'body' => \CommonClass::getConfig('site_name') . '余额充值', // A simple description
                    'total_fee' => $employ['bounty'], // Amount with less than 2 decimals places
                    'fee_type' => 'CNY', // Currency name from ISO4217, Optional, default as CNY
                );
                $response = $wechat->purchase($params)->send();

                $img = QrCode::size('280')->generate($response->getRedirectUrl());

                $view = array(
                    'cash' => $employ['bounty'],
                    'img' => $img
                );
                return $this->theme->scope('task.wechatpay', $view)->render();
            }
        } else {
            return redirect()->back()->with(['error' => '请选择一种支付方式']);
        }
    }

    /**
     * 雇佣托管赏金等待页面
     * @param Request $request
     * @return mixed
     */
    public function success(Request $request)
    {
        $this->theme->setTitle('雇佣成功');
        $data = $request->all();
        $uid = intval($data['uid']);
        //查询当前用户店铺是否开启
        $user_shop = ShopModel::where('status',1)->where('uid',$uid)->first();
        $view = [
            'id' => $data['id'],
            'user_shop'=>$user_shop,
        ];
        return $this->theme->scope('employ.success', $view)->render();
    }

    /**
     * 雇佣详情页面
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function workin($id)
    {
        $this->theme->setTitle('雇佣详情');
        //判断当前用户的角色是雇主还是被雇佣人和游客
        $user_id = Auth::user()['id'];
        //查询当前
        $employ = EmployModel::where('id', $id)->first();
        //判断是否是一个雇佣任务
        if (empty($employ)) {
            return redirect()->back()->with(['error' => '参数错误！']);
        }

        if ($employ['employer_uid'] == $user_id) {
            $role = 1;//表示雇主
            $user_data = UserDetailModel::employeeData($employ['employee_uid']);
        } else if ($employ['employee_uid'] == $user_id) {
            $role = 2;//表示威客
            $user_data = UserDetailModel::employerData($employ['employer_uid']);
        } else {
            return redirect()->back()->with(['error' => '参数错误！']);
        }
        //关联查询task详细信息
//        $employ_detail = EmployModel::where('id',$id)->first();

        $employ_detail = EmployModel::employDetail($id);
//        dd($employ_detail);
        //查询任务的附件
        $attatchment_ids = UnionAttachmentModel::where('object_id', '=', $id)->where('object_type', 2)->lists('attachment_id')->toArray();
        $attatchment_ids = array_flatten($attatchment_ids);
        $attatchment = AttachmentModel::whereIn('id', $attatchment_ids)->get();
        //根据任务进度查询任务的稿件以及评价等信息
        $work = array();
        $work_attachment = array();
        if ($employ_detail['status'] >= 2 && $employ_detail['status'] < 6) {
            //查询稿件
            $work = EmployWorkModel::where('employ_id', $id)->first();
            //查询稿件附件
            $work_attachment = UnionAttachmentModel::where('object_id', $work['id'])->where('object_type', 3)->lists('attachment_id')->toArray();
            $work_attachment = AttachmentModel::whereIn('id', $work_attachment)->get();
        }
        $comment = array();
        $comment_status = false;
        if ($employ_detail['status'] == 4 || $employ_detail['status']==3)
        {
            //查询评论
            $comment = EmployCommentsModel::where('employ_id',$id)->get();
            //判断当前角色是否已经评价
            $comment_status = EmployCommentsModel::where('employ_id',$id)->where('from_uid',$user_id)->first();
        }
        //查询是否被关注
        $isFocus = UserFocusModel::where('uid',$user_id)->where('focus_uid',$user_data['uid'])->first();

        //查询当前用户店铺是否开启
        $user_shop = ShopModel::where('status',1)->where('uid',$user_data['uid'])->first();
        $domain = url();
        $this->theme->set('employ_status',$employ_detail['status']);
        $this->theme->set('employ_bounty_status',$employ_detail['bounty_status']);

        $view = [
            'role' => $role,
            'user_data' => $user_data,
            'employ_data' => $employ_detail,
            'attachment' => $attatchment,
            'work' => $work,
            'comment' => $comment,
            'domain' => $domain,
            'contact' => Theme::get('is_IM_open'),
            'work_attachment' => $work_attachment,
            'comment_status'=>$comment_status,
            'user_shop'=>$user_shop,
            'isFocus'=>$isFocus
        ];

        return $this->theme->scope('employ.workin', $view)->render();
    }

    public function result(Request $request)
    {
        $data = $request->all();
        $data = [
            'pay_account' => $data['buyer_email'],
            'code' => $data['out_trade_no'],
            'pay_code' => $data['trade_no'],
            'money' => $data['total_fee'],
        ];
        $gateway = Omnipay::gateway('alipay');

        $options = [
            'request_params' => $_REQUEST,
        ];
        $response = $gateway->completePurchase($options)->send();

        if ($response->isSuccessful() && $response->isTradeStatusOk()) {
            //给用户充值
            $result = UserDetailModel::recharge($this->user['id'], 2, $data);

            if (!$result) {
                echo '支付失败！';
                return redirect()->back()->withErrors(['errMsg' => '支付失败！']);
            }
            //修改订单状态，产生财务记录，修改任务状态
            $task_id = OrderModel::where('code', $data['code'])->first();

            TaskModel::employbounty($data['money'], $task_id['task_id'], $this->user['id'], $data['code'], 2);
            echo '支付成功';
            return redirect()->to('task/' . $task_id['task_id']);
        } else {
            //支付失败通知.
            echo '支付失败';
            return redirect()->to('task/bounty')->withErrors(['errMsg' => '支付失败！']);
        }
    }

    /**
     * 接受、拒绝或取消雇佣
     * @param $id
     * @param $type
     * @return \Illuminate\Http\RedirectResponse
     */
    public function except($id, $type)
    {
        $user_id = Auth::user()['id'];

        $result = EmployModel::employHandle($type, $id, $user_id);

        if (!$result)
            return redirect()->back()->with(['error' => '操作失败！']);

        return redirect()->back()->with(['message' => '操作成功！']);
    }

    /**
     * 交付稿件
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function workCreate(Request $request)
    {
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        //判断当前用户是否是被雇佣者
        $uid = Auth::user()['id'];
        $employ_id = intval($data['employ_id']);

        $employ = EmployModel::where('id', $employ_id)->where('employee_uid', $uid)->first();
        if (!$employ)
            return redirect()->back()->with(['error' => '你不是被雇佣者不需要交付当前任务稿件！']);
        //判断当前稿件是否处于投稿期间
        if ($employ['status'] != 1) {
            return redirect()->back()->with(['error' => '当前任务不是处于交稿状态！']);
        }

        //创建一条work记录，修改当前任务状态
        $result = EmployWorkModel::employDilivery($data, $uid);

        if (!$result)
            return redirect()->back()->with(['error' => '投稿失败！']);

        return redirect()->back()->with(['message' => '投稿成功！']);
    }

    /**
     * 验收成功
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptWork($id)
    {
        $uid = Auth::user()['id'];
        //验证当前任务验收合法性
        $employ = EmployModel::where('id', $id)->first();
        if ($employ['status'] != 2)
            return redirect()->back()->with(['error' => '当前任务不是处于验收状态！']);

        if ($employ['employer_uid'] != $uid)
            return redirect()->back()->with(['error' => '你不是当前雇佣任务的雇主，不能验收！']);

        //验收操作
        $result = EmployModel::acceptWork($id, $uid);

        if (!$result)
            return redirect()->back()->with('error', '验收失败！');

        return redirect()->back()->with(['message' => '验收成功！']);
    }

    /**
     * 维权提交
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employRights(Request $request)
    {
        $data = $request->all();
        //判断当前用户的角色是雇主还是被雇佣人和游客
        $user_id = Auth::user()['id'];
        //查询当前
        $employ = EmployModel::where('id', $data['id'])->first();
        //判断是否是一个雇佣任务
        if (empty($employ)) {
            return redirect()->back()->with(['error' => '参数错误！']);
        }

        if ($employ['employer_uid'] == $user_id) {
            $role = 1;//表示雇主
            $to_uid = $employ['employee_uid'];
        } else if ($employ['employee_uid'] == $user_id) {
            $role = 2;//表示威客
            $to_uid = $employ['employer_uid'];
        } else {
            return redirect()->back()->with(['error' => '参数错误！']);
        }
        $employ_rights = [
            'type'=>intval($data['type']),
            'object_id'=>intval($data['id']),
            'object_type'=>1,
            'desc'=>\CommonClass::removeXss($data['desc']),
            'status'=>0,
            'from_uid'=>$user_id,
            'to_uid'=>$to_uid,
            'created_at'=>date('Y-m-d H:i:s',time()),
        ];
        $result = UnionRightsModel::employRights($employ_rights,$role);

        if(!$result)
            return redirect()->bakc()->with(['error'=>'维权失败！']);

        return redirect()->back()->with(['message'=>'维权成功！']);

    }
    //评价提交
    public function employEvaluate(EvaluateRequest $request)
    {
        $data = $request->except('_token');
        $uid = Auth::user()['id'];
        //判断当前雇佣任务是否处于评价阶段
        $employ = EmployModel::where('id',$data['employ_id'])->first();
        if($employ['status']!=3)
        {
            return redirect()->back()->with(['error'=>'当前任务不在评价状态！']);
        }
        //判断当前角色
        if($employ['employer_uid']==$uid)
        {
            $comment_by = 1;//评价来自雇主
            $to_uid = $employ['employee_uid'];
        }else if($employ['employee_uid']==$uid)
        {
            $comment_by = 0;//评价来自威客
            $to_uid = $employ['employer_uid'];
        }else{
            return redirect()->back()->with(['error'=>'你不是雇主也不是被雇佣的威客，不能评价！']);
        }
        //创建评价
        $evaluate_data = [
            'employ_id'=>intval($data['employ_id']),
            'from_uid'=>$uid,
            'to_uid'=>$to_uid,
            'comment'=>$data['comment'],
            'comment_by'=>$comment_by,
            'speed_score'=>intval($data['speed_score']),
            'quality_score'=>intval($data['quality_score']),
            'attitude_score'=>isset($data['attitude_score'])?intval($data['attitude_score']):0,
            'type'=>intval($data['type']),
            'created_at'=>date('Y-m-d H:i:s',time()),
        ];

        $result = EmployCommentsModel::serviceCommentsCreate($evaluate_data,intval($data['employ_id']));

        if(!$result)
            return redirect()->back()->with('error','评论失败！');

        //增加服务的总评价数量和好评数
        if($employ['employer_uid']==$uid && $employ['employ_type']==1)
        {
            //查询当前雇佣是来源于哪一个服务
            $service_id = EmployGoodsModel::where('employ_id',$employ['id'])->first();
            //增加服务的总评价数量
            GoodsModel::where('id',$service_id['service_id'])->increment('comments_num',1);
            //增加用户雇佣数量
            UserDetailModel::where('uid',$uid)->increment('publish_task_num',1);
            //如果是好评就将数量加一
            if($data['type']==1)
            {
                GoodsModel::where('id',$service_id['service_id'])->increment('good_comment',1);
                UserDetailModel::where('uid',$uid)->increment('employer_praise_rate',1);
            }
        }else
        {
            //增加用户承接数量
            UserDetailModel::where('uid',$uid)->increment('receive_task_num',1);
            //如果是好评就将数量加一
            if($data['type']==1)
            {
                UserDetailModel::where('uid',$uid)->increment('employee_praise_rate',1);
            }
        }
        return redirect()->back()->with(['message'=>'评论成功！']);
    }
    /**
     * 验证赏金合法性
     * @param Request $request
     * @return string
     */
    public function validBounty(Request $request)
    {
        $data = $request->except('_token');
        //检测赏金额度是否在后台设置的范围之内
        $task_bounty_min_limit = \CommonClass::getConfig('employ_bounty_min_limit');

        //判断赏金必须大于最小限定
        if ($task_bounty_min_limit > $data['param']) {
            $data['info'] = '赏金应该大于' . $task_bounty_min_limit ;
            $data['status'] = 'n';
            return json_encode($data);
        }

        $data['status'] = 'y';
        return json_encode($data);
    }

    /**
     * 判断雇佣成立
     * @param $id
     * @return mixed
     */
    public function employCheck($id)
    {
        $employ = EmployModel::where('id',$id)->first();

        if(!$employ)
            return redirect()->back()->with(['error'=>'参数错误！']);

        if($employ['bounty_status']==0)
           return redirect()->to(URL('employ/workin',['id'=>$id]))->with(['error'=>'支付失败']);

       return redirect()->to(URL('employ/workin',['id'=>$id]))->with(['message'=>'支付成功']);
    }
}
