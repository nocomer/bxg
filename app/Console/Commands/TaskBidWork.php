<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskBidWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskBidWork';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //获取任务类型id
        $taskTypeId = TaskTypeModel::getTaskTypeIdByAlias('zhaobiao');
        //查询正在进行投稿的任务
        $tasks = TaskModel::where('type_id',$taskTypeId)->where('status','=',3)->orWhere('status','=',4)->get()->toArray();

        //查询系统设定的时间规则筛选筛选出交稿时间到期的任务
        $expireTasks = self::expireTasks($tasks);
        if(!empty($expireTasks)){
            //没有稿件的任务 失败退款
            $works = WorkModel::whereIn('task_id',$expireTasks)->lists('task_id')->toArray();
            $worked = array_unique($works);
            $not_worked = array_diff($expireTasks,$worked);
            if(!empty($not_worked)){
                //没有稿件的任务直接失败，赏金退还
                foreach($not_worked as $v){
                    $status = DB::transaction(function() use($v){
                        //修改当前任务状态
                        TaskModel::where('id',$v)->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
                        $task = TaskModel::where('id',$v)->first();
                        //赏金分配
                        //查询当前的任务失败抽成比
                        $task_fail_percentage = $task['task_fail_draw_ratio'];
                        if($task_fail_percentage!=0)
                        {
                            $balance = $task['bounty']*(1-$task_fail_percentage/100);
                        }else{
                            $balance = $task['bounty'];
                        }
                        UserDetailModel::where('uid',$task['uid'])->increment('balance',$balance);
                        //产生一条财务记录
                        $finance_data = [
                            'action'=>1,
                            'pay_type'=>1,
                            'cash'=>$balance,
                            'uid'=>$task['uid'],
                            'created_at'=>date('Y-m-d H:i:s',time()),
                            'updated_at'=>date('Y-m-d H:i:s',time()),
                        ];
                        FinancialModel::create($finance_data);

                        self::sendMassage($v);
                    });
                }
            }

        }


    }

    /**
     *  超过截稿期
     * @param $data
     * @return array
     */
    private function expireTasks($data)
    {
        $expireTasks = [];
        foreach($data as $k=>$v)
        {
            $time = time();
            //判断当前到期的任务
            if(strtotime($v['delivery_deadline'])<=$time)
            {
                $expireTasks[] = $v['id'];
            }
        }
        return $expireTasks;
    }

    /**
     * 发送任务失败的消息
     * @param $task_id
     */
    private function sendMassage ($task_id)
    {
        //判断当前的任务发布成功之后是否需要发送系统消息
        $ids = WorkModel::where('task_id',$task_id)->where('status',0)->lists('uid');
        $ids = array_flatten($ids);
        foreach($ids as $v)
        {
            $task_publish_success = MessageTemplateModel::where('code_name','task_failed')->where('is_open',1)->where('is_on_site',1)->first();
            if($task_publish_success)
            {
                $task = TaskModel::where('id',$task_id)->first();
                $user = UserModel::where('id',$v)->first();//必要条件
                //组织好系统消息的信息
                $messageVariableArr = [
                    'task_title'=>$task['title'],
                    'reason'=>'超过选稿限制时间没有选择稿件中标',
                ];
                $message = MessageTemplateModel::sendMessage('task_failed',$messageVariableArr);
                $data = [
                    'message_title'=>$task_publish_success['name'],
                    'code'=>'task_failed',
                    'message_content'=>$message,
                    'js_id'=>$user['id'],
                    'message_type'=>2,
                    'receive_time'=>date('Y-m-d H:i:s',time()),
                    'status'=>0,
                ];
                MessageReceiveModel::create($data);
            }
        }

    }
}
