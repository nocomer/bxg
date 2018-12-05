<?php

namespace App\Console\Commands;

use App\Modules\Employ\Models\EmployModel;
use Illuminate\Console\Command;

class EmployDeadline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmployDeadline';

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
     * 处理任务逾期未交稿的雇佣任务
     */
    public function handle()
    {
        //查找所有过期但是没有交付的雇佣任务
        $employ_data = EmployModel::where('status',1)->where('delivery_deadline','<',date('Y-m-d H:i:s',time()))->get()->toArray();

        $employ = new EmployModel();
        //处理任务
        foreach($employ_data as $v)
        {
           $employ->employDeadline($v);
        }
    }
}
