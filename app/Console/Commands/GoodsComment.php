<?php

namespace App\Console\Commands;

use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\Shop\Models\GoodsCommentModel;
use Illuminate\Console\Command;

class GoodsComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GoodsComment';

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
     * 购买商品确认源文件N天后没有评论自动好评
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //查询所有处于已经确认源文件的商品订单
        $goodsOrder = ShopOrderModel::where('object_type',2)->where('status',2)->get()->toArray();
        if(!empty($goodsOrder)){
            $expireGoodsOrder = self::expireGoodsComment($goodsOrder);
            if(!empty($expireGoodsOrder)){
                foreach($expireGoodsOrder as $key => $val){
                    //查询该订单是否已经评价
                    $res = GoodsCommentModel::where('uid',$val['uid'])->where('goods_id',$val['object_id'])->first();
                    if(empty($res)){
                        $arr = array(
                            'uid' => $val['uid'],
                            'goods_id' => $val['object_id'],
                            'comment_by' => 0,//系统评价
                            'speed_score' => 5,
                            'quality_score' => 5,
                            'attitude_score' => 5,
                            'comment_desc' => '作品很棒!',
                            'type' => 0,//默认好评
                            'created_at' => date('Y-m-d H:i:s')
                        );
                        GoodsCommentModel::createGoodsComment($arr,$val);
                    }
                }
            }
        }

    }


    /**
     * 查询已经超出评论时间的订单
     * @param $goodsOrder
     * @return array
     */
    private function expireGoodsComment($goodsOrder)
    {
        //查询系统配置的自动确认源文件的时间限制
        $commentDaysArr = ConfigModel::getConfigByAlias('comment_days');
        if(!empty($commentDaysArr) && $commentDaysArr->rule != 0){
            $commentDays = intval($commentDaysArr->rule);
        }else{
            $commentDays = 7;
        }
        $limitTime = $commentDays*24*60*60;
        $expireGoodsOrder = array();
        if(!empty($goodsOrder)){
            foreach($goodsOrder as $k => $v){
                //判断当前商品订单是否超过评价时间
                if((strtotime($v['confirm_time'])+$limitTime)<= time()){
                    $expireGoodsOrder[] = $v;
                }
            }
        }
        return $expireGoodsOrder;

    }
}
