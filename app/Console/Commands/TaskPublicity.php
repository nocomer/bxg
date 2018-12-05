<?php

namespace App\Console\Commands;

use App\Modules\User\Model\TaskModel;
use Illuminate\Console\Command;

class TaskPublicity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taskPublicity';

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
     * 处理公示期过期的task
     *
     * @return mixed
     */
    public function handle()
    {
        //查询所有公示期的task
        $task = TaskModel::where('status',6)->get()->toArray();
        //查询所有公示期到期的任务
        $expire = Self::expireTask($task);
        //将所有公示期到期的任务状态修改成交付验收
        TaskModel::whereIn('id',$expire)->update(['status'=>7,'checked_at'=>date('Y-m-d H:i:s',time())]);
    }
    //将公示期到期的处理
    private function expireTask($task)
    {
        //查询系统配置公示期时间
        $task_publicity_day = \CommonClass::getConfig('task_publicity_day');
        $task_publicity_day = $task_publicity_day*24*3600;
        $expire_task = [];
        foreach($task as $v)
        {
            //判断当前的任务是否公示期到期
            if((strtotime($v['publicity_at'])+$task_publicity_day)<=time())
            {
                $expire_task[] = $v['id'];
            }
        }
        return $expire_task;
    }
}
