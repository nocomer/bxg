<?php

namespace App\Console\Commands;

use App\Modules\Employ\Models\EmployModel;
use Illuminate\Console\Command;

class EmployDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmployDelivery';

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
        //查询验收过期的雇佣任务
        $employ_data = EmployModel::where('status',2)->where('bounty_status',1)->where('accept_deadline','<',date('Y-m-d H:i:s',time()))->get()->toArray();
        $employ = new EmployModel();
        //处理验收过期的任务
        foreach($employ_data as $v)
        {
            $result = $employ->employDelivery($v);
        }
    }
}
