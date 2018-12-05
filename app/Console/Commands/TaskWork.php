<?php

namespace App\Console\Commands;

use App\Modules\Finance\Model\FinancialModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\UserDetailModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TaskWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskWork';

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
        //查询正在进行投稿的任务
        $tasks = TaskModel::where('type_id',$taskTypeId)->where('status','=',3)->orWhere('status','=',4)->get()->toArray();

        //查询系统设定的时间规则筛选筛选出交稿时间到期的任务
        $expireTasks = self::expireTasks($tasks);

        //将任务分为两组一组是有稿件的，一组是没有稿件的
        $works = WorkModel::whereIn('task_id',$expireTasks)->lists('task_id')->toArray();
        $worked = array_unique($works);
        $not_worked = array_diff($expireTasks,$worked);

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
            });
        }
        //有稿件的进入选稿期
        $result2 = TaskModel::whereIn('id',$worked)->update(['status'=>5,'selected_work_at'=>date('Y-m-d H:i:s',time())]);
    }
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

}
