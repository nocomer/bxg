<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskSelectWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskSelectWork';

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
        $taskTypeId = TaskTypeModel::getTaskTypeIdByAlias('xuanshang');
        //查询所有处于选稿期的任务
        $tasks = TaskModel::where('type_id',$taskTypeId)->where('status',5)->get()->toArray();

        //将选稿期结束的任务挑选出来
        $expireTasks = self::expireTasks($tasks);

        $task_sys_help_switch = \CommonClass::getConfig('task_sys_help_switch');

        //判断系统的辅助流程是否开启
        if($task_sys_help_switch==1)
        {
            //查询系统辅助流程规则，按照三种规则给予不同的处理
            $task_sys_help_rule = \CommonClass::getConfig('task_sys_help_rule');
            switch($task_sys_help_rule){
                case 1:
                    Self::workFirst($expireTasks);
                    break;
                case 2:
                    Self::commentFirst($expireTasks);
                    break;
                case 3:
                    Self::taskFirst($expireTasks);
                    break;
            }
        }elseif($task_sys_help_switch==0)
        {
            foreach($expireTasks as $k=>$v)
            {
                $status = DB::transaction(function() use($v)
                {
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
                    //产生一条财务记录 任务失败
                    $finance_data = [
                        'action'=>7,
                        'pay_type'=>1,
                        'cash'=>$balance,
                        'uid'=>$task['uid'],
                        'created_at'=>date('Y-m-d H:i:s',time()),
                        'updated_at'=>date('Y-m-d H:i:s',time()),
                    ];
                    FinancialModel::create($finance_data);

                });
                if(is_null($status))
                {
                    Self::sendMassage($v);
                }
            }
        }
          //辅助流程开启则将任务赏金分配
          //没有开启则任务失败,赏金分配回去

    }

    private function expireTasks($data)
    {
        //查询系统配置选稿时间
        $task_select_work = \CommonClass::getConfig('task_select_work');
        $time = time();
        $expireTasks = [];
        foreach($data as $v)
        {
            if((strtotime($v['selected_work_at'])+$task_select_work)<=$time)
            {
                $expireTasks[] = $v['id'];
            }
        }

        return $expireTasks;
    }

    private function workFirst($data)
    {
        //按照最先交稿的选取稿件为中标
        foreach($data as $v)
        {
            $status = DB::transaction(function() use($v){
                //查询当前任务
                $task = TaskModel::where('id',$v)->first()->toArray();
                //筛选最先交稿的几个稿件
                $works = Self::workTime($task);
                //将投稿时间靠前的稿件选取为中标
                WorkModel::whereIn('id',$works)->update(['status'=>1,'bid_at'=>date('Y-m-d H:i:s',time()),'bid_by'=>1]);
                //修改当前任务的状态为公示期
                TaskModel::where('id',$v)->update(['status'=>6,'publicity_at'=>date('Y-m-d H:i:s',time())]);
            });
            if(is_null($status))
            {
                Self::sendMassage($v);
            }
        }
    }
    private function commentFirst($data)
    {
        //按照好评率选取稿件中标
        foreach($data as $v)
        {
            $status = DB::transaction(function() use($v){
                //查询当前任务
                $task = TaskModel::where('id',$v)->first()->toArray();
                //统计当前任务中好评率靠前的稿件
                $works = Self::applyRate($task);
                //将好评率靠前的稿件选取为中标
                WorkModel::whereIn('id',$works)->update(['status'=>1,'bid_at'=>date('Y-m-d H:i:s',time()),'bid_by'=>1]);
                //修改当前任务的状态为公示期
                TaskModel::where('id',$v)->update(['status'=>6,'publicity_at'=>date('Y-m-d H:i:s',time())]);
            });
            if(is_null($status))
            {
                Self::sendMassage($v);
            }
        }
    }
    private function taskFirst($data)
    {
        //按照参与任务数选取稿件中标
        foreach($data as $v)
        {
            $status = DB::transaction(function() use($v)
            {
                //查询当前任务
                $task = TaskModel::where('id',$v)->first()->toArray();
                //统计当前任务中符合条件的稿件
                $works = Self::taskNum($task);
                //将稿件选取为中标
                WorkModel::whereIn('id',$works)->update(['status'=>1,'bid_at'=>date('Y-m-d H:i:s',time()),'bid_by'=>1]);
                //修改当前任务的状态为公示期
                TaskModel::where('id',$v)->update(['status'=>6,'publicity_at'=>date('Y-m-d H:i:s',time())]);
            });
            if(is_null($status))
            {
                Self::sendMassage($v);
            }
        }
    }
    //根据好评率统计中标的稿件
    private function applyRate($data)
    {
        //查询当前任务的稿件
        $works = WorkModel::where('task_id',$data['id'])->where('status',0)->get()->toArray();

        //选取中标的稿件
        if($data['worker_num']<count($works)){
            //统计当前任务稿件的好评率
            foreach($works as $k=>$v)
            {
                $works[$k]['applause_rate'] = CommentModel::applauseRate($v['uid']);
            }
            //按照好评率给任务排序
            $works = array_values(array_sort($works,function($value){
                return $value['applause_rate'];
            }));
            $works = array_slice($works,0,$data['worker_num']);
        }
        //取出当前选中稿件的id
        $works_id = [];
        foreach($works as $v){
            $works_id[] = $v['id'];
        }
        return $works_id;
    }
    //根据投稿时间判定中标
    private function workTime($data)
    {
        //查询当前任务的稿件
        $works = WorkModel::where('task_id',$data['id'])->where('status',0)->orderBy('created_at','asc')->get()->toArray();
        if(count($works)>$data['worker_num'])
        {
            $works = array_slice($works,0,$data['worker_num']);
        }
        $works_id=[];
        foreach($works as $v)
        {
            $works_id[] = $v['id'];
        }
        return $works_id;
    }
    //根据参与任务数选取当前稿件
    private function taskNum($data)
    {
        //查询当前任务的稿件
        $works = WorkModel::where('task_id',$data['id'])->where('status',0)->get()->toArray();
        if(count($works)>$data['worker_num'])
        {
            foreach($works as $k=>$v)
            {
                $works[$k]['task_num'] = WorkModel::where('uid',$v['uid'])->count();
            }
            //按照参与数给予排序
            $works = array_values(array_sort($works,function($value){
                return $value['task_num'];
            }));
            $works = array_slice($works,0,$data['worker_num']);
        }
        $works_id = [];
        foreach($works as $v)
        {
            $works_id[] = $v['id'];
        }

        return $works_id;
    }
    //发送系统自动选稿的消息
    private function sendMassage ($task_id)
    {
        $domain = \CommonClass::getDomain();
        //判断当前的任务发布成功之后是否需要发送系统消息
        $ids = WorkModel::where('task_id',$task_id)->where('status',0)->lists('uid');
        $ids = array_flatten($ids);
        foreach($ids as $v)
        {
            $task_publish_success = MessageTemplateModel::where('code_name','Automatic_choose')->where('is_open',1)->where('is_on_site',1)->first();
            if($task_publish_success)
            {
                $task = TaskModel::where('id',$task_id)->first();
                $user = UserModel::where('id',$v)->first();//必要条件
                $site_name = \CommonClass::getConfig('site_name');//必要条件
                //组织好系统消息的信息
                $messageVariableArr = [
                    'username'=>$user['name'],
                    'task_number'=>$task['id'],
                    'href' => $domain.'/task/'.$task_id,
                    'task_titles'=>$task['title'],
                    'website'=>$site_name,
                ];
                $message = MessageTemplateModel::sendMessage('Automatic_choose',$messageVariableArr);
                $data = [
                    'message_title'=>$task_publish_success['name'],
                    'code'=>'Automatic_choose',
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
