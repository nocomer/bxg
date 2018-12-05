<?php

namespace App\Console\Commands;

use App\Modules\Employ\Models\EmployModel;
use Illuminate\Console\Command;

class EmployComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'EmployComment';

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
        //查询过期没有评价的任务
        $employ_data = EmployModel::where('status',3)->where('comment_deadline','<',date('Y-m-d H:i:s',time()))->get();
        //处理过期未评论的
        foreach($employ_data as $v)
        {
            EmployModel::employComment($v);
        }
    }
}
