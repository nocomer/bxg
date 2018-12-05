<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Task\Model\TaskPaySectionModel;
use App\Modules\Task\Model\TaskPayTypeModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\UserDetailModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskBidDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskBidDelivery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '招标任务交付超时';

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
        //扫描当前处于交付验收期的任务
        $task = TaskModel::where('type_id',$taskTypeId)->where('status',7)->get()->toArray();
        //判断当前任务是否有稿件交付如果没有就直接将任务失败
        $filled_tasks = self::filledTasks($task);
        //处理交付期过期但是没有交付的任何稿件的
        if(count($filled_tasks)!=0){
            foreach($filled_tasks as $v){
                DB::transaction(function() use($v){
                    //修改当前任务状态
                    TaskModel::where('id',$v['id'])->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
                    //赏金分配
                    //查询当前的任务失败抽成比
                    $task_fail_percentage = TaskModel::where('id',$v['id'])->first();
                    $task_fail_percentage = $task_fail_percentage['task_fail_draw_ratio'];
                    if($task_fail_percentage!=0){
                        $balance = $v['bounty']*(1-$task_fail_percentage/100);
                    }else{
                        $balance = $v['bounty'];
                    }
                    UserDetailModel::where('uid',$v['uid'])->increment('balance',$balance);
                    //产生一条财务记录 任务失败产生一条财务记录
                    $finance_data = [
                        'action'=>7,
                        'pay_type'=>1,
                        'cash'=>$balance,
                        'uid'=>$v['uid'],
                        'created_at'=>date('Y-m-d H:i:s',time()),
                        'updated_at'=>date('Y-m-d H:i:s',time()),
                    ];
                    FinancialModel::create($finance_data);
                });
            }
        }
        $successed_tasks = self::filledTasks($task,2);
        if(!empty($successed_tasks)){
            //查找需要交付的稿件且过期没有交付的
            //直接将稿件作废掉(相当于审核失败审核)
            $woker_expired = self::expireTaskWorker($successed_tasks);

            foreach($woker_expired as $k=>$v){
                WorkModel::where('task_id',$k)->whereIn('uid',$v)->update(['status'=>5]);
            }

            //查找需要验收的稿件 验收失败 结束任务
            $onwer_expired = self::expireTaskOwner($successed_tasks);
            $onwer_expired = array_flatten($onwer_expired);
            foreach($onwer_expired as $v){
                $work_data = WorkModel::where('id',$v)->first();

                $data['task_id'] = $work_data['task_id'];
                $data['uid'] = $work_data['uid'];
                $data['work_id'] = $v;

                //验收失败
                $paySectionInfo = [
                    'verify_status' => 2,//稿件审核失败
                    'section_status' => 1,//阶段结束
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                TaskPaySectionModel::where('task_id',$data['task_id'])
                    ->where('work_id',$data['work_id'])->update($paySectionInfo);
                //修改稿件的状态
                WorkModel::where('id', $data['work_id'])->update(['status' => 5]);

                //退款 产生财务记录
                $money = TaskPaySectionModel::where('task_id',$data['task_id'])->whereIn('section_status',[0,1])->sum('price');

                //赏金分配
                //查询当前的任务失败抽成比
                $taskInfo = TaskModel::where('id',$data['task_id'])->first();
                $task_fail_percentage = $taskInfo['task_fail_draw_ratio'];
                if($task_fail_percentage!=0){
                    $balance = $money*(1-$task_fail_percentage/100);
                }else{
                    $balance = $money;
                }
                UserDetailModel::where('uid',$taskInfo['uid'])->increment('balance',$balance);
                //产生一条财务记录 任务失败产生一条财务记录
                $finance_data = [
                    'action'=>7,
                    'pay_type'=>1,
                    'cash'=>$balance,
                    'uid'=>$taskInfo['uid'],
                    'created_at'=>date('Y-m-d H:i:s',time()),
                    'updated_at'=>date('Y-m-d H:i:s',time()),
                ];
                FinancialModel::create($finance_data);
                //修改当前任务状态
                TaskModel::where('id',$data['task_id'])->update(['status'=>10,'end_at'=>date('Y-m-d H:i:s',time())]);
            }
        }


    }
    //判断任务没有交付也没有维权的稿件
    private function expireTaskWorker($data)
    {
        $task_delivery_max_time = \CommonClass::getConfig('bid_delivery_max_time');
        $task_delivery_max_time = $task_delivery_max_time*24*3600;
        $expired_works = [];
        foreach($data as $v)
        {
            if((strtotime($v['checked_at'])+$task_delivery_max_time)>=time())
            {
                //查询任务所有需要交付的稿件
                $works = WorkModel::where('task_id',$v['id'])
                    ->where('status',1)
                    ->orWhere('status',0)
                    ->lists('uid')
                    ->toArray();
                //查询任务所有已经交付的稿件
                $works_delivery = WorkModel::where('task_id',$v['id'])
                    ->where('status','>',1)
                    ->where('forbidden',0)->lists('uid')->toArray();
                $works_diff = array_diff($works,$works_delivery);
                $expired_works[$v['id']][] = $works_diff;
            };
        }
        return $expired_works;
    }

    private function expireTaskOwner($data)
    {
        $task_check_time_limit = \CommonClass::getConfig('bid_check_time_limit');
        $task_check_time_limit = $task_check_time_limit*24*3600;
        $expired_works = [];
        foreach($data as $v)
        {
            //查询任务所有需要验收的稿件
            $works = WorkModel::where('task_id',$v['id'])->where('status',2)->get()->toArray();
            $works_expired = [];
            if(!empty($works)){
                foreach($works as $v) {
                    if((strtotime($v['created_at']) + $task_check_time_limit)<=time()){
                        $works_expired[] = $v['id'];
                    }
                }
            }

            //查询任务所有已经验收通过的稿件，以及维权中的稿件
            $works_delivery = WorkModel::where('task_id',$v['id'])->where('status','>',2)->lists('id')->toArray();
            $works_diff = array_diff($works_expired,$works_delivery);
            if(count($works_diff)>0)
            {
                $expired_works[] = $works_diff;
            }
        }
        return $expired_works;
    }

    /**
     * 获取超过交付期内是否交稿的所有任务
     * @param array $data 任务数组
     * @param int $type 1:没有任何稿件交付的
     * @return array
     */
    private function filledTasks($data,$type=1)
    {
        $task_delivery_max_time = \CommonClass::getConfig('bid_delivery_max_time');

        //交稿限制时间
        $task_delivery_max_time = $task_delivery_max_time*24*3600;
        $filled = [];
        $successed = [];
        foreach($data as $k=>$v)
        {
            //判断是否确认交付方式
            $taskPayType = TaskPayTypeModel::where('task_id',$v['id'])
                ->where('status',1)->first();
            if(!empty($taskPayType)){
                if((strtotime($v['checked_at'])+$task_delivery_max_time)<=time())
                {
                    //查询当前的任务是否有交付的work
                    $query = WorkModel::where('task_id', $v['id'])->whereIn('status',[2,3,4,5]);
                    $work = $query->count();
                    if ($work == 0) {
                        $filled[] = $v;
                    } else {
                        $successed[] = $v;
                    }
                }
            }

        }
        if($type==1){
            return $filled;
        }else{
            return $successed;
        }
    }
}
