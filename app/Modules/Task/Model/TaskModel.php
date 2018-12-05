<?php

namespace App\Modules\Task\Model;

use App\Modules\Employ\Models\EmployUserModel;
use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Order\Model\OrderModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use App\Modules\Task\Model\TaskCateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

//use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
//use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
//use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class TaskModel extends Model
{
    protected $table = 'task';
    protected $fillable = [
        'title', 'desc', 'type_id', 'cate_id', 'phone', 'region_limit', 'status', 'bounty', 'bounty_status', 'created_at', 'updated_at',
        'verified_at', 'begin_at', 'end_at', 'delivery_deadline', 'show_cash', 'real_cash', 'deposit_cash', 'province', 'city', 'area',
        'view_count', 'delivery_count', 'uid', 'username', 'worker_num', 'selected_work_at', 'publicity_at', 'checked_at', 'comment_at',
        'top_status', 'task_success_draw_ratio', 'task_fail_draw_ratio', 'engine_status', 'work_status','kee_status'
    ];
    public function province()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','province');
    }
    public function city()
    {
        return $this->hasOne('App\Modules\User\Model\DistrictModel','id','city');
    }
    static public function myTasks($data)
    {
        $query = self::select('task.*', 'tt.name as type_name','tt.alias','us.name as nickname', 'ud.avatar', 'tc.name as cate_name', 'province.name as province_name', 'city.name as city_name')
            ->where('task.status', '>', 0)
            ->where('task.status', '<=', 11)->where('task.uid', $data['uid'])->where(function($query){
				$query->where(function($querys){
					 $querys->where('task.bounty_status',1)->where('tt.alias','xuanshang');
				 })->orwhere(function($querys){
					 $querys->whereIn('task.bounty_status',[0,1])->where('tt.alias','zhaobiao');
				 });
			});
        //状态筛选
        if (isset($data['status']) && $data['status'] != 0) {
            /* 状态值修改 by heike 2017-09-14
			switch ($data['status']) {
                case 1:
                    $status = [3, 4, 6];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [7];
                    break;
                case 4:
                    $status = [8, 9, 10];
                    break;
                case 5:
                    $status = [2, 11];
                    break;
            } */
			switch($data['status']){
				case 1:
                    $status = [6];
                    break;
                case 2:
                    $status = [4];
                    break;
                case 3:
                    $status = [7];
                    break;
                case 4:
                    $status = [8, 9, 10];
                    break;
                case 5:
                    $status = [2, 11];
                    break;
				case 6:
					$status = [1];
					break;
				case 7:
					$status = [3];
					break;
				case 8:
					$status = [4];
					break;
                case 9:
					$status = [5,6];
					break;
 				case 10:
					$status = [7];
					break;
                case 11:
					$status = [11];
					break;
                case 12:
					$status = [8,9];
					break;
                case 13:
					$status = [10];
					break; 
                case 14:
					$status = [8,9,10];
					break;
                case 15:
                    $status = [3];
                    break;
			}
            $query->whereIn('task.status', $status);
        }
        //时间段筛选
        if (isset($data['time'])) {
            switch ($data['time']) {
                case 1:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-1 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 2:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-3 month')), date('Y-m-d H:i:s', time())]);
                    break;
                case 3:
                    $query->whereBetween('task.created_at', [date('Y-m-d H:i:s', strtotime('-6 month')), date('Y-m-d H:i:s', time())]);
                    break;
            }

        }
        //任务模式筛选
		if(isset($data['type'])){
			$query->where('type_id',$data['type']);
		}
        $data = $query->join('task_type as tt', 'task.type_id', '=', 'tt.id')
            ->leftjoin('district as province', 'province.id', '=', 'task.province')
            ->leftjoin('district as city', 'city.id', '=', 'task.city')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'task.uid')
            ->leftjoin('cate as tc', 'tc.id', '=', 'task.cate_id')
            ->orderBy('task.created_at', 'desc')
            ->paginate(5);
        return $data;
    }
    /**
     * 任务筛选
     * @param $data
     * @param $paginate
     * @return mixed
     * author: muker（qq:372980503）
     */
    public static function findBy($data,$paginate=10)
    {
        $query = self::select('task.*', 'b.name as type_name', 'b.alias as type_alias', 'us.name as user_name')->where('task.status', '>', 2)
            ->where(function($query){
				 $query->where(function($querys){
					 $querys->where('task.bounty_status',1)->where('b.alias','xuanshang');
				 })->orwhere(function($querys){
					 $querys->whereIn('task.bounty_status',[0,1])->where('b.alias','zhaobiao');
				 });
			})
			->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->orderBy('task.top_status', 'desc');
        //关键词筛选
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
		//任务模式筛选
		if(isset($data['taskType']) && $data['taskType']!=0){
			$query->where('task.type_id', $data['taskType']);
		}
        //类别筛选
        if (isset($data['category']) && $data['category'] != 0) {
            //查询所有的底层id
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('task.cate_id', $category_ids);
        }
        //地区筛选
        if (isset($data['province'])) {
            $query->where('task.province', intval($data['province']));
        }
        if (isset($data['city'])) {
            $query->where('task.city', intval($data['city']));
        }
        if (isset($data['area'])) {
            $query->where('task.area', intval($data['area']));
        }
        //任务状态
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    //$status = [4];
					$status=[3, 4, 6];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
				case 12:
				   $status = [8, 9,10];
                   break;
            }
            $query->whereIn('task.status', $status);
        }
        //排序
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy('task.'.$data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('task.created_at');
        } else {
            $query->orderBy('task.created_at', 'desc');
        }
        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate($paginate);
        return $data;
    }

    /**
     * 任务筛选
     * @param $data
     * @return mixed
     * author: muker（qq:372980503）
     */
    static function findByCity($data, $city)
    {
        $query = self::select('task.*', 'b.name as type_name', 'us.name as user_name')->where('task.status', '>', 2)
            ->where('task.bounty_status', 1)->where('task.status', '<=', 9)->where('begin_at', "<=", date('Y-m-d H:i:s', time()))
            ->where('task.region_limit', 1)
            ->orderBy('top_status', 'desc');
        //关键词筛选
        if (isset($data['keywords'])) {
            $query = $query->where('task.title', 'like', '%' . e($data['keywords']) . '%');
        }
        //类别筛选
        if (isset($data['category']) && $data['category'] != 0) {
            //查询所有的底层id
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('cate_id', $category_ids);
        }
        //地区筛选
        if (isset($city)) {
            $query->where(function ($query) use ($city) {
                $query->where('province', $city)->orwhere('city', $city);
            });
        }

        if (isset($data['area'])) {
            $query->where(function ($query) use ($data) {
                $query->where('city', $data['area'])->orwhere('area', $data['area']);
            });
        }
        //任务状态
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 1:
                    $status = [4];
                    break;
                case 2:
                    $status = [5];
                    break;
                case 3:
                    $status = [6, 7];
                    break;
                case 4:
                    $status = [8, 9];
                    break;
            }
            $query->whereIn('task.status', $status);
        }
        //排序
        if (isset($data['desc']) && $data['desc'] != 'created_at') {
            $query->orderBy($data['desc'], 'desc');
        } elseif (isset($data['desc']) && $data['desc'] == 'created_at') {
            $query->orderBy('created_at');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $data = $query->join('task_type as b', 'task.type_id', '=', 'b.id')
            ->leftjoin('users as us', 'us.id', '=', 'task.uid')
            ->paginate(10);

        return $data;
    }

    /**
     * 创建一个任务
     * @param $data
     * @return mixed
     */
    static public function createTask($data)
    {
        $status = DB::transaction(function () use ($data) {
            $taskTypeAlias = 'xuanshang';
            $taskType = TaskTypeModel::find($data['type_id']);
            if(!empty($taskType)){
                $taskTypeAlias = $taskType['alias'];
            }
			if(isset($data['task_id'])){
                self::where("id",$data['task_id'])->update([
                     'phone'=>$data['phone'],
                     'cate_id'=>$data['cate_id'],
                     'province'=>$data['province'],
                     'city'=>$data['city'],
                     'area'=>$data['area'],
                     'title'=>$data['title'],
                     'bounty'=>$data['bounty'],
                     'worker_num'=>$data['worker_num'],
                     'type_id'=>$data['type_id'],
                     'begin_at'=>$data['begin_at'],
                     'delivery_deadline'=>$data['delivery_deadline'],
                     'desc'=>$data['desc'],
                     'created_at'=>$data['created_at'],
                     'show_cash'=>$data['show_cash'],
                     'status'=>$data['status'],
                     'task_success_draw_ratio'=>$data['task_success_draw_ratio'],
                     'task_fail_draw_ratio'=>$data['task_fail_draw_ratio'],
                     'kee_status' => $data['kee_status']
			   ]);
			    $result['id']=$data['task_id'];
			}else{
				$result = self::create($data);
			}
            if (!empty($data['file_id'])) {
                //查询用户的附件记录，排除掉用户删除的附件记录
                $file_able_ids = AttachmentModel::fileAble($data['file_id']);
                $file_able_ids = array_flatten($file_able_ids);
                if(isset($data['task_id'])){
					TaskAttachmentModel::where('task_id',$data['task_id'])->delete();
				}
                foreach ($file_able_ids as $v) {
                    $attachment_data = [
                        'task_id' => $result['id'],
                        'attachment_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskAttachmentModel::create($attachment_data);
                }
                //修改附件的发布状态
                $attachmentModel = new AttachmentModel();
                $attachmentModel->statusChange($file_able_ids);
            }

            if (!empty($data['product'])) {
                if(isset($data['task_id'])){
                    TaskServiceModel::where('task_id',$data['task_id'])->delete();
                }
                foreach ($data['product'] as $k => $v) {
                    if($taskTypeAlias == 'xuanshang'){
                        $server = ServiceModel::where('id', $v)->first();
                        if ($server['identify'] == 'ZHIDING') {
                            self::where('id', $result['id'])->increment('top_status',1);
                        }
                        if ($server['identify'] == 'JIAJI') {
                            self::where('id', $result['id'])->increment('top_status',1);
                        }
                        if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                            self::where('id', $result['id'])->update(['engine_status' => 1]);
                        }
                        if ($server['identify'] == 'GAOJIANPINGBI') {
                            self::where('id', $result['id'])->update(['work_status' => 1]);
                        }
                    }

                    $service_data = [
                        'task_id' => $result['id'],
                        'service_id' => $v,
                        'created_at' => date('Y-m-d H:i:s', time()),
                    ];

                    TaskServiceModel::create($service_data);
					                 
                }
            }

            switch($taskTypeAlias){
                case 'xuanshang'://托管赏金后增加用户发布任务数量
                    break;
                case 'zhaobiao':
                    //增加用户的发布任务数量
                    UserDetailModel::where('uid', $data['uid'])->increment('publish_task_num', 1);
                    break;
            }
            return $result;
        });
        return $status;
    }


    /**
     * 根据id查询任务
     * @param $id
     */
    static function findById($id)
    {
        $data = self::select('task.*', 'b.name as cate_name', 'c.name as type_name')
            ->where('task.id', '=', $id)
            ->leftjoin('cate as b', 'task.cate_id', '=', 'b.id')
            ->leftjoin('task_type as c', 'task.type_id', '=', 'c.id')
            ->first();

        return $data;
    }

    /**
     *
     * 计算用户的任务金额 悬赏模式
     */
    public function taskMoney($id)
    {
        $bounty = self::select('task.bounty')->where('id', '=', $id)->first();
        $bounty = $bounty['bounty'];
        $service = TaskServiceModel::select('task_service.service_id')
            ->where('task_id', '=', $id)->get()->toArray();
        $service = array_flatten($service);
        $serviceModel = new ServiceModel();
        $service_money = $serviceModel->serviceMoney($service);
        $money = $bounty + $service_money;

        return $money;
    }

    static function employbounty($money, $task_id, $uid, $code, $type = 2)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            //扣除用户的余额
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            //修改任务的赏金托管状态
            $data = self::where('id', $task_id)->update(['bounty_status' => 1]);
            //生成财务记录，action 1表示发布任务
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            //修改订单状态
            OrderModel::where('code', $code)->update(['status' => 1]);

            //修改用户的托管状态
            self::where('id', '=', $task_id)->update(['status' => 0]);

            //增加用户的发布任务数量
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });

        return is_null($status) ? true : false;
    }

    /**
     * 赏金托管数据操作
     * @param float $money 订单金额
     * @param int $uid 购买人uid
     * @param int $task_id 任务id
     * @param string $code 订单编号
     * @param int $type 支付方式 1:余额
     * @return bool
     */
    static function bounty($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            //扣除用户的余额
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            //修改任务的赏金托管状态
            $data = self::where('id', $task_id)->update(['bounty_status' => 1]);
            //生成财务记录，action 1表示发布任务
            $financial = [
                'action' => 1,
                'pay_type' => $type,
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
        });
        //如果托管成功就发送一条系统消息
        if (is_null($status)) {
            //判断当前的任务发布成功之后是否需要发送系统消息
            $task_publish_success = MessageTemplateModel::where('code_name', 'task_publish_success')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_publish_success) {
                $task = self::where('id', $task_id)->first()->toArray();
                $task_status = [
                    'status' => [
                        0 => '暂不发布',
                        1 => '已经发布',
                        2 => '赏金托管',
                        3 => '审核通过',
                        4 => '威客交稿',
                        5 => '雇主选稿',
                        6 => '任务公示',
                        7 => '交付验收',
                        8 => '双方互评'
                    ]
                ];
                $task = \CommonClass::intToString([$task], $task_status);
                $task = $task[0];
                $user = UserModel::where('id', $uid)->first();//必要条件
                $site_name = \CommonClass::getConfig('site_name');//必要条件
                $domain = \CommonClass::getDomain();
                //组织好系统消息的信息
                //发送系统消息
                $messageVariableArr = [
                    'username' => $user['name'],
                    'task_number' => $task['id'],
                    'task_title' => $task['title'],
                    'task_status' => $task['status_text'],
                    'website' => $site_name,
                    'href' => $domain . '/task/' . $task['id'],
                    'task_link' => $task['title'],
                    'start_time' => $task['begin_at'],
                    'manuscript_end_time' => $task['delivery_deadline'],
                ];
                $message = MessageTemplateModel::sendMessage('task_publish_success', $messageVariableArr);
                $data = [
                    'message_title' => $task_publish_success['name'],
                    'code' => 'task_publish_success',
                    'message_content' => $message,
                    'js_id' => $user['id'],
                    'message_type' => 2,
                    'receive_time' => date('Y-m-d H:i:s', time()),
                    'status' => 0,
                ];
                MessageReceiveModel::create($data);
            }
        }
        return is_null($status) ? true : false;
    }

    /**
     * 查询任务详情
     * @param $id
     */
    static function detail($id)
    {
        $query = self::select('task.*', 'a.name as user_name', 'b.name as type_name', 'c.name as cate_name')
            ->where('task.id', '=', $id);
        //赏金已经托管
        /* $query = $query->where(function ($query) {
            $query->where('task.status', '>=', 2);
        }); */
		//添加招标模式 该方法修改中
		$query=$query->where(function($query){
			$query->where(function($querys){
				$querys->where('task.bounty_status',1)->where('b.alias','xuanshang');
			})->orwhere(function($querys){
				$querys->whereIn('task.bounty_status',[0,1])->where('b.alias','zhaobiao');
			});
		});
        $data = $query->join('users as a', 'a.id', '=', 'task.uid')
            ->leftjoin('task_type as b', 'b.id', '=', 'task.type_id')
            ->leftjoin('cate as c', 'c.id', '=', 'task.cate_id')
            ->first();
        return $data;
    }


    /**
     * 查找相似的任务
     * @param $cate_id
     */
    static function findByCate($cate_id, $id)
    {
        $query = self::where('cate_id', '=', $cate_id);
        $query = $query->where(function ($query) use ($id) {
            $query->where('id', '!=', $id);
        });
        //赏金已经托管的任务
        $query = $query->where(function ($query) {
            $query->where('status', '>', 2);
        });
        //没有到截稿时间
        $query = $query->where(function ($query) {
            $query->where('delivery_deadline', '>', date('Y-m-d H:i:s', time()));
        });
        $data = $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        return $data;
    }

    /**
     * 判断是不是雇主
     */
    static function isEmployer($task_id, $uid)
    {
        $data = self::where('id', $task_id)->first();
        if ($data['uid'] == $uid)
            return true;
        return false;
    }

    /**
     * 赏金分配
     * @param $id
     * @param $uid
     */
    static public function distributeBounty($id, $uid)
    {
        //计算赏金
        $bounty = self::where('id', $id)->first();
        $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - sprintf("%.2f", $bounty['task_success_draw_ratio'] / 100));

        $status = DB::transaction(function () use ($bounty, $uid) {
            //增加用户余额
            UserDetailModel::where('uid', $uid)->increment('balance', $bounty);
            //产生一笔财务流水 表示接受任务产生的钱
            $finance_data = [
                'action' => 2,
                'pay_type' => 1,
                'cash' => $bounty,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($finance_data);
        });

        return is_null($status) ? true : false;
    }


    /**
     * 任务验收通过和任务验收失败
     * @param array $task 相关任务数据
     * @param int $type 操作类型1表示验收通过2表示验收失败
     */
    static function employAccept($task, $type)
    {
        $status = DB::transeaction(function () use ($task, $type) {
            //验收通过
            if ($type == 1) {
                //将任务状态修改成3验收通过
                TaskModel::where('id', $task['id'])->update(['status' => 3]);
                //将任务的稿件修改成验收通过
                $employee_user = EmployUserModel::where('task_id', $task['id'])->first();
                //将任务的托管金打给威客，并生成记录
                self::distributeBounty($task['id'], $employee_user['uid']);
                $bounty = self::where('id', $task['id'])->first();
                $bounty = ($bounty['bounty'] / $bounty['worker_num']) * (1 - $bounty['task_success_draw_ratio']);
                //增加用户余额
                UserDetailModel::where('uid', $employee_user['uid'])->increment('balance', $bounty);
                //产生一笔财务流水 表示接受任务产生的钱
                $finance_data = [
                    'action' => 2,
                    'pay_type' => 1,
                    'cash' => $bounty,
                    'uid' => $employee_user['uid'],
                    'created_at' => date('Y-m-d H:i:s', time())
                ];
                FinancialModel::create($finance_data);

            } else if ($type == 2) {

            }
        });
    }

    public function test($data)
    {
        $this->where('status','>',2);
    }

    /**
     * 招标模式 发布任务购买增值服务
     * @param float $money 订单金额
     * @param int $task_id 任务id
     * @param int $uid 购买人uid
     * @param string $code 订单编号
     * @param int $type 支付方式
     * @return bool
     */
    static function buyServiceTaskBid($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            //扣除用户的余额
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);

            //生成财务记录，action 1:发布任务 2:接受任务 3:用户充值 4:用户提现 5:购买增值服务 6:购买用户商品 7:任务失败退款 8:提现失败退款 9:出售商品 10：维权退款 11:推荐商品到威客商城失败退款 12:打赏 13:被打赏 14：推广注册赏金) 15：开通vip店铺
            $financial = [
                'action' => 5,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            //查询购买的增值服务
            $product = TaskServiceModel::where('task_id',$task_id)
                ->select('service_id')->get()->toArray();
            $product = array_flatten($product);

            if (!empty($product)) {
                foreach ($product as $k => $v) {
                    $server = ServiceModel::where('id', $v)->first();
                    if ($server['identify'] == 'ZHIDING') {
                        self::where('id', $task_id)->increment('top_status', 1);
                    }
                    if ($server['identify'] == 'JIAJI') {
                        self::where('id', $task_id)->increment('top_status', 1);
                    }
                    if ($server['identify'] == 'SOUSUOYINGQINGPINGBI') {
                        self::where('id', $task_id)->update(['engine_status' => 1]);
                    }
                    if ($server['identify'] == 'GAOJIANPINGBI') {
                        self::where('id', $task_id)->update(['work_status' => 1]);
                    }

                }
            }


            //修改订单状态
            OrderModel::where('code', $code)->update(['status' => 1]);
        });

        return is_null($status) ? true : false;
    }


    /**
     * 招标模式 托管赏金 进入交付验收状态
     * @param float$money 托管金额
     * @param int $task_id 任务id
     * @param int $uid 支付人id
     * @param string $code 订单编号
     * @param int $type 支付方式
     * @return bool
     */
    static function bidBounty($money, $task_id, $uid, $code, $type = 1)
    {
        $status = DB::transaction(function () use ($money, $task_id, $uid, $code, $type) {
            //扣除用户的余额
            $query = DB::table('user_detail')->where('uid', '=', $uid);
            $query->where(function ($query) {
                $query->where('balance_status', '!=', 1);
            })->decrement('balance', $money);
            //修改任务的赏金托管状态 任务状态
            self::where('id', $task_id)->update(['bounty_status' => 1,'status' => 7,'updated_at' => date('Y-m-d H:i:s'),'publicity_at'=>date('Y-m-d H:i:s',time())]);
            //生成财务记录，action 1表示发布任务
            $financial = [
                'action' => 1,
                'pay_type' => $type,
                'cash' => $money,
                'uid' => $uid,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            FinancialModel::create($financial);
            //修改订单状态
            OrderModel::where('code', $code)->update(['status' => 1]);

            //增加用户的发布任务数量
            UserDetailModel::where('uid', $uid)->increment('publish_task_num', 1);
        });
        //如果托管成功就发送一条系统消息
        if (is_null($status)) {
            //判断当前的任务发布成功之后是否需要发送系统消息
            $task_publish_success = MessageTemplateModel::where('code_name', 'task_publish_success')->where('is_open', 1)->where('is_on_site', 1)->first();
            if ($task_publish_success) {
                $task = self::where('id', $task_id)->first()->toArray();
                $task_status = [
                    'status' => [
                        0 => '暂不发布',
                        1 => '已经发布',
                        2 => '赏金托管',
                        3 => '审核通过',
                        4 => '威客交稿',
                        5 => '雇主选稿',
                        6 => '任务公示',
                        7 => '交付验收',
                        8 => '双方互评'
                    ]
                ];
                $task = \CommonClass::intToString([$task], $task_status);
                $task = $task[0];
                $user = UserModel::where('id', $uid)->first();//必要条件
                $site_name = \CommonClass::getConfig('site_name');//必要条件
                $domain = \CommonClass::getDomain();
                //组织好系统消息的信息
                //发送系统消息
                $messageVariableArr = [
                    'username' => $user['name'],
                    'task_number' => $task['id'],
                    'task_title' => $task['title'],
                    'task_status' => $task['status_text'],
                    'website' => $site_name,
                    'href' => $domain . '/task/' . $task['id'],
                    'task_link' => $task['title'],
                    'start_time' => $task['begin_at'],
                    'manuscript_end_time' => $task['delivery_deadline'],
                ];
                $message = MessageTemplateModel::sendMessage('task_publish_success', $messageVariableArr);
                $data = [
                    'message_title' => $task_publish_success['name'],
                    'code' => 'task_publish_success',
                    'message_content' => $message,
                    'js_id' => $user['id'],
                    'message_type' => 2,
                    'receive_time' => date('Y-m-d H:i:s', time()),
                    'status' => 0,
                ];
                MessageReceiveModel::create($data);
            }

            //给中标人发送中标消息
            $work = WorkModel::where('task_id',$task_id)->where('status',1)->first();
            if(!empty($work)){
                $arr = [
                    'task_id' => $task_id,
                    'work_id' => $work['id']
                ];
                WorkModel::sendTaskWidMessage($arr);
            }
        }
        return is_null($status) ? true : false;
    }
}
