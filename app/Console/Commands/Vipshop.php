<?php

namespace App\Console\Commands;

use App\Modules\Shop\Models\ShopModel;
use App\Modules\Vipshop\Models\ShopPackageModel;
use Illuminate\Console\Command;

class Vipshop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vipshop';

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
        //
        $now = date('Y-m-d H:i:s', time());

        ShopPackageModel::where('end_time','<',$now)->update(['status' => 1]);
    }
}
