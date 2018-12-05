<?php
/**
 * Created by PhpStorm.
 * User: KEKE-1003
 * Date: 2016/10/19
 * Time: 10:08
 */
namespace App\Modules\Api\Http\Controllers;

use App\Http\Requests;
use App\Modules\Employ\Models\EmployCommentsModel;
use App\Modules\Employ\Models\EmployModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Order\Model\ShopOrderModel;
use App\Modules\Shop\Models\GoodsCommentModel;
use App\Modules\Shop\Models\GoodsModel;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Shop\Models\ShopTagsModel;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\AuthRecordModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\EnterpriseAuthModel;
use App\Modules\User\Model\RealnameAuthModel;
use App\Modules\User\Model\SkillTagsModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Guzzle\Tests\Http\CommaAggregatorTest;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use App\Modules\Shop\Models\ShopFocusModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShopController extends ApiBaseController
{
    /**
     * 收藏店铺
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function collectShop(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $shopId = $request->get('shop_id');
        $uid = $tokenInfo['uid'];
        $shopInfo = ShopModel::where(['uid' => $uid,'id' => $shopId,'status' => 1])->first();
        if(!empty($shopInfo)){
            return $this->formateResponse(1007,'不能收藏自己的店铺');
        }
        $data = [
            'uid' => $uid,
            'shop_id' => $shopId,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $res = ShopFocusModel::create($data);
        if($res){
            return $this->formateResponse(1000,'收藏成功',$res);
        }else{
            return $this->formateResponse(1008,'收藏失败');
        }
    }


    /**
     * 取消收藏店铺
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function cancelCollect(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $shopId = $request->get('shop_id');
        $uid = $tokenInfo['uid'];
        $res = ShopFocusModel::where(['uid' => $uid,'shop_id' => $shopId])->delete();
        if($res){
            return $this->formateResponse(1000,'取消成功');
        }else{
            return $this->formateResponse(1009,'取消失败');
        }
    }


    /**
     * 查看店铺被收藏的状态
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function collectStatus(Request $request){
        if(!$request->get('token')){
            $status = 0;
        }else{
            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            $uid = $tokenInfo['uid'];
            $shopId = $request->get('shop_id');
            $shopFocusInfo = ShopFocusModel::where(['uid' => $uid,'shop_id' => $shopId])->first();
            if(empty($shopFocusInfo)){
                $status = 0;
            }else{
                $status = 1;
            }
        }
        return $this->formateResponse(1000,'获取店铺被收藏状态成功',['status' => $status]);
    }


    /**
     * 查看是否可以进入雇佣页面
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function isEmploy(Request $request){
        if(!$request->get('token')){
            return $this->formateResponse(1010,'请先登录');
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        if($uid == $request->get('id')){
            return $this->formateResponse(1011,'您不能雇佣你自己');
        }
        return $this->formateResponse(1000,'success');
    }

    /**
     * 获取店铺信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function shopInfo(Request $request){
        $shopId = intval($request->get('shop_id'));
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('id','uid','shop_pic','shop_name','shop_bg','province','city')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1012,'传送数据错误');
        }
        /*$userInfo = UserModel::where('id',$shopInfo->uid)->where('status','<>',2)->select('name')->first();
        if(empty($userInfo)){
            return $this->formateResponse(1013,'用户id不存在');
        }
        $userDetail = UserDetailModel::where('uid',$shopInfo->uid)->select('avatar')->first();
        if(empty($userDetail)){
            return $this->formateResponse(1014,'用户信息不存在');
        }*/
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        //$shopInfo->name = $userInfo->name;
        //$shopInfo->avatar = $userDetail->avatar?$domain->rule.'/'.$userDetail->avatar:$userDetail->avatar;
        //$shopInfo->shop_desc = htmlspecialchars_decode($shopInfo->shop_desc);
        $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
        $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
        $shopInfo->cate_name = [];
        $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
        if(!empty($shopTags)){
            $tagIds = array_unique(array_flatten($shopTags));
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            if(!empty($tags)){
                $shopInfo->cate_name = array_unique(array_flatten($tags));
            }
        }
        $shopInfo->username = '';
        if($shopInfo->uid){
            $user = UserModel::where('id',$shopInfo->uid)->first();
            if(!empty($user)){
                $shopInfo->username = $user->name;
            }
        }
        //查询地址
        if($shopInfo->province){
            $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
            $provinceName = $province->name;
        }else{
            $provinceName = '';
        }
        if($shopInfo->city){
            $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
            $cityName = $city->name;
        }else{
            $cityName = '';
        }
        $shopInfo->city_name = $provinceName.$cityName;
        //查询是否是企业店铺
        //判断店铺是否进行企业认证
        $companyInfo = EnterpriseAuthModel::where('uid', $shopInfo->uid)->where('status',1)->orderBy('created_at', 'desc')->first();
        if($companyInfo){
            $shopInfo->isEnterprise = 1;//企业店铺
        }else{
            $shopInfo->isEnterprise = 0;//个人店铺
        }
        //上架作品
       // $shopInfo->workNum = GoodsModel::where(['shop_id' => $shopId,'status' => 1,'type' => 1])->count();
        //上架服务
       // $shopInfo->serviceNum = GoodsModel::where(['shop_id' => $shopId,'status' => 1,'type' => 2])->count();
        return $this->formateResponse(1000,'获取威客店铺信息成功',$shopInfo);


    }


    /**
     * 获取店铺全部作品信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function workList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1015,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodsList = GoodsModel::where(['shop_id' => $shopId,'type' => 1,'status' => 1,'is_delete' => 0])
            ->orderBy('created_at','desc')
            ->select('id','title','unit','cash','cover','sales_num')
            ->paginate(4)
            ->toArray();
        if($goodsList['total']){
           foreach($goodsList['data'] as $k=>$v){
               $goodsList['data'][$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
               switch($v['unit']){
                   case '0':
                       $goodsList['data'][$k]['unit'] = '件';
                       break;
                   case '1':
                       $goodsList['data'][$k]['unit'] = '时';
                       break;
                   case '2':
                       $goodsList['data'][$k]['unit'] = '份';
                       break;
                   case '3':
                       $goodsList['data'][$k]['unit'] = '个';
                       break;
                   case '4':
                       $goodsList['data'][$k]['unit'] = '张';
                       break;
                   case '5':
                       $goodsList['data'][$k]['unit'] = '套';
                       break;
               }
           }
        }
        return $this->formateResponse(1000,'获取商品信息成功',$goodsList);

    }

    /**
     * 获取店铺全部服务信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function serviceList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1015,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodsList = GoodsModel::where(['shop_id' => $shopId,'type' => 2,'status' => 1,'is_delete' => 0])
                ->orderBy('created_at','desc')
                ->select('id','title','cash','cover','sales_num','cate_id','unit')
                ->paginate(4)
                ->toArray();
        if($goodsList['total']){
            $cate_ids = array_pluck($goodsList['data'],'cate_id');
            $cateInfo = TaskCateModel::whereIn('id',$cate_ids)->select('id','name')->get()->toArray();
            $cateInfo = collect($cateInfo)->pluck('name','id')->all();
            foreach($goodsList['data'] as $k=>$v){
                $goodsList['data'][$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
                $goodsList['data'][$k]['cate_name'] = isset($cateInfo[$v['cate_id']])?$cateInfo[$v['cate_id']]:null;
                switch($v['unit']){
                    case '0':
                        $goodsList['data'][$k]['unit'] = '件';
                        break;
                    case '1':
                        $goodsList['data'][$k]['unit'] = '时';
                        break;
                    case '2':
                        $goodsList['data'][$k]['unit'] = '份';
                        break;
                    case '3':
                        $goodsList['data'][$k]['unit'] = '个';
                        break;
                    case '4':
                        $goodsList['data'][$k]['unit'] = '张';
                        break;
                    case '5':
                        $goodsList['data'][$k]['unit'] = '套';
                        break;
                }
            }
        }
        return $this->formateResponse(1000,'获取商品信息成功',$goodsList);

    }


    /**
     * 获取店铺成功案例信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function successList(Request $request){
        $shopId = $request->get('shop_id');
        $shopInfo = ShopModel::where(['id' => $shopId,'status' => 1])->select('shop_name','shop_pic','uid')->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1016,'传送参数错误');
        }
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $caseInfo = SuccessCaseModel::where('uid',$shopInfo['uid'])->select('id','pic','title')->orderBy('created_at','desc')
            ->paginate(3)->toArray();
        if($caseInfo['total']){
            foreach($caseInfo['data'] as $k=>$v){
                $caseInfo['data'][$k]['pic'] = $v['pic']?$domain->rule.'/'.$v['pic']:$v['pic'];
            }
        }
        return $this->formateResponse(1000,'获取成功案例信息成功',$caseInfo);
    }


    /**
     * 获取商品详情
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function goodDetail(Request $request){
        $type = $request->get('type');
        $id = $request->get('id');
        $goodDetail = GoodsModel::where(['id' => $id,'type' => $type,'status' => 1,'is_delete' => 0])->select('id','uid','shop_id','desc')->first();
        if(empty($goodDetail)){
            return $this->formateResponse(1017,'传送参数错误');
        }
        $desc = htmlspecialchars_decode($goodDetail->desc);
        $goodInfo = [
            'id' => $goodDetail->id,
            'uid' => $goodDetail->uid,
            'shop_id' => $goodDetail->shop_id,
            'desc' => $desc
        ];
        return $this->formateResponse(1000,'获取商品详情信息成功',$goodInfo);
    }


    /**
     * 获取商品评价
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function goodComment(Request $request){
        $type = $request->get('type');
        $id = $request->get('id');
        if(!$id or !$type){
            return $this->formateResponse(1017,'传送参数不能为空');
        }
        $goodDetail = GoodsModel::where(['id' => $id,'type' => $type])->select('cash','unit','id','uid','shop_id')->first();
        if(empty($goodDetail)){
            return $this->formateResponse(1018,'传送参数错误');
        }
        switch($goodDetail->unit){
            case '0':
                $goodDetail->unit = '件';
                break;
            case '1':
                $goodDetail->unit = '时';
                break;
            case '2':
                $goodDetail->unit = '份';
                break;
            case '3':
                $goodDetail->unit = '个';
                break;
            case '4':
                $goodDetail->unit = '张';
                break;
            case '5':
                $goodDetail->unit = '套';
                break;
        }
        $good_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 0])->count();
        $middle_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 1])->count();
        $bad_num = GoodsCommentModel::where(['goods_id' => $id,'type' => 2])->count();
        $commentInfo = [];
        $comment = GoodsCommentModel::where('goods_id',$id);
        if($request->get('sorts')){
            $sorts = $request->get('sorts');
            switch($sorts){
                case '1':
                    $classify = 0;
                    $comment = $comment->where('type',$classify);
                    break;
                case '2':
                    $classify = 1;
                    $comment = $comment->where('type',$classify);
                    break;
                case '3':
                    $classify = 2;
                    $comment = $comment->where('type',$classify);
                    break;
            }

        }
        $comment = $comment->select('id','uid','speed_score','quality_score','attitude_score','comment_desc','type','created_at')->paginate(3)->toArray();
        if($comment['total']){
            $uids = array_pluck($comment['data'],'uid');
            $userInfo = UserModel::whereIn('id',$uids)->where('status',1)->select('id','name')->get()->toArray();
            /*if(empty($userInfo)){
                return $this->formateResponse(1019,'找不到相关的用户信息');
            }*/
            $userInfo = collect($userInfo)->pluck('name','id')->all();
            $userDetail = UserDetailModel::whereIn('uid',$uids)->select('uid','avatar')->get()->toArray();
            /*if(empty($userDetail)){
                return $this->formateResponse(1020,'找不到用户详情信息');
            }*/
            $userDetail = collect($userDetail)->pluck('avatar','uid')->all();
            $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
            foreach($comment['data'] as $k=>$v){
                $comment['data'][$k]['name'] = $userInfo[$v['uid']];
                $comment['data'][$k]['avatar'] = $userDetail[$v['uid']]?$domain->rule.'/'.$userDetail[$v['uid']]:$userDetail[$v['uid']];
                $comment['data'][$k]['comment_desc'] = htmlspecialchars_decode($v['comment_desc']);
                $comment['data'][$k]['total_score'] = number_format(($v['speed_score']+$v['quality_score']+$v['attitude_score'])/3,1);
                $comment['data'][$k]['cash'] = $goodDetail->cash;
                $comment['data'][$k]['unit'] = $goodDetail->unit;
                $comment['data'][$k]['created_at'] = date('Y-m-d',strtotime($v['created_at']));
            }
            $commentInfo = $comment;

        }
        $commentList = [
            'good_id' => $goodDetail->id,
            'user_id' => $goodDetail->uid,
            'shop_id' => $goodDetail->shop_id,
            'good_num' => $good_num,
            'middle_num' => $middle_num,
            'bad_num' => $bad_num,
            'commentInfo' => $commentInfo
        ];
        return $this->formateResponse(1000,'获取商品评价信息成功',$commentList);
    }


    /**
     * 获取商品内容
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function goodContent(Request $request){
        $id = $request->get('id');
        $type = $request->get('type');
        if(!$id or !$type){
            return $this->formateResponse(1021,'传送参数不能为空');
        }
        $goodInfo = GoodsModel::where(['id' => $id,'type' => $type])
            ->select('id','shop_id','title','unit','cash','cover','sales_num','comments_num','good_comment','uid','status','is_delete')
            ->first();
        if(empty($goodInfo)){
            return $this->formateResponse(1022,'传送参数错误');
        }
        if(in_array($goodInfo->status,['0','2','3']) || $goodInfo->is_delete == 1 ){
            $goodInfo->is_buy = 0; //不可购买
        }else{
            $goodInfo->is_buy = 1;//可以购买
        }
        switch($goodInfo->unit){
            case '0':
                $goodInfo->unit = '件';
                break;
            case '1':
                $goodInfo->unit = '时';
                break;
            case '2':
                $goodInfo->unit = '份';
                break;
            case '3':
                $goodInfo->unit = '个';
                break;
            case '4':
                $goodInfo->unit = '张';
                break;
            case '5':
                $goodInfo->unit = '套';
                break;
        }
        $shopInfo = ShopModel::where(['id' => $goodInfo->shop_id,'status' => 1])->first();
        if(empty($shopInfo)){
            return $this->formateResponse(1023,'店铺信息不存在');
        }
        //查询作品所有人的名称
        $user = UserModel::where('id',$goodInfo->uid)->first();
        if($user){
            $goodInfo->username = $user->name;
        }else{
            $goodInfo->username = '';
        }
        //查询地址
        if($shopInfo->province){
            $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
            $provinceName = $province->name;
        }else{
            $provinceName = '';
        }
        if($shopInfo->city){
            $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
            $cityName = $city->name;
        }else{
            $cityName = '';
        }
        //$goodInfo->desc = htmlspecialchars_decode($goodInfo->desc);
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $goodInfo->cover = $goodInfo->cover?$domain->rule.'/'.$goodInfo->cover:$goodInfo->cover;
        $goodInfo->city_name = $provinceName.$cityName;
        //$goodInfo->shop_name = $shopInfo->shop_name;
        //$goodInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
        if($goodInfo->comments_num > 0){
            $goodInfo->percent = ceil($goodInfo->good_comment/$goodInfo->comments_num*100);
        }
        else{
            $goodInfo->percent = 100;
        }
        $comment = GoodsCommentModel::where('goods_id',$id)->select('speed_score','quality_score','attitude_score')->first();
        if(empty($comment)){
            $goodInfo->speed_score = 0;
            $goodInfo->quality_score = 0;
            $goodInfo->attitude_score = 0;
        }else{
            $goodInfo->speed_score = $comment->speed_score;
            $goodInfo->quality_score = $comment->quality_score;
            $goodInfo->attitude_score = $comment->attitude_score;
        }
        return $this->formateResponse(1000,'获取商品内容成功',$goodInfo);
    }


    /**
     * 威客商城(店铺)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function shopList(Request $request){
        $name = $request->get('name');//名称
        $cate_id = $request->get('cate_id');//行业分类id
        $new = $request->get('new');//最新
        $good_order = $request->get('good_order');//好评数排序
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $shopList = ShopModel::where('status',1);
        if($name){
            $shopList = $shopList->where('shop_name','like','%'.$name.'%');
        }
        if($cate_id){
            $shopTags = ShopTagsModel::join('skill_tags','tag_shop.tag_id','=','skill_tags.id')
                ->where('skill_tags.cate_id',intval($cate_id))
                ->select('tag_shop.shop_id')->get()->toArray();
            $shopIds = array_flatten($shopTags);
            $shopList = $shopList->whereIn('id',$shopIds);
        }
        if($new){
            $shopList = $shopList->orderBy('created_at','desc');
        }
        if($good_order){
            if($good_order == 1){
                $good_order = 'desc';
            }else{
                $good_order = 'asc';
            }
            $shopList = $shopList->orderBy('good_comment',$good_order);
        }
        $shopList = $shopList->select('id','uid','shop_pic','shop_name','total_comment','good_comment')->paginate()->toArray();
        if($shopList['total']){
            /*
             * TODO
             * 1. 根据店铺获取其对应的tagid
             * 2. 根据店铺所有的tagid获取tagname
             * 3. 建立店铺和tagname的关联关系
            */

            // 1
            $shop_ids = array_pluck($shopList['data'],'id');
            $shopInfoTags = ShopTagsModel::whereIn('shop_id',$shop_ids)->get()->toArray();
            if(!empty($shopInfoTags)){

                //获取所有的tagid
                $tagIds = array_unique(array_pluck($shopInfoTags,'tag_id'));
                //查询技能详情
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('id','tag_name')->get()->toArray();
                $tagsArr = [];
                foreach($tags as $key=>$value) {
                    $tagsArr[$value['id']] = $value['tag_name'];
                }
                $shopInfoTags = collect($shopInfoTags)->groupBy('shop_id')->toArray();
                $shopInfoDetail = [];
                foreach($shopInfoTags as $key=>$value) {
                    foreach($value as $k=>$v){
                        $shopInfoDetail[$key][] = isset($tagsArr[$v['tag_id']])?$tagsArr[$v['tag_id']]:0;
                    }
                }
            }
            //店铺类型
            $uidArr = array_pluck($shopList['data'],'uid');
            $companyInfo = EnterpriseAuthModel::whereIn('uid', $uidArr)->where('status',1)->orderBy('created_at', 'desc')->get()->toArray();
            if(!empty($companyInfo)){
                $enterpriseIds = array_unique(array_pluck($companyInfo,'uid'));
            }else{
                $enterpriseIds = [];
            }
            //店铺地址
            $provinceInfo = ShopModel::join('district', 'shop.province', '=', 'district.id')
                ->select('shop.id','district.name')
                ->whereIn('shop.id', $shop_ids)
                ->where('shop.status',1)
                ->get()->toArray();
            $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
                ->select('shop.id','district.name')
                ->whereIn('shop.id', $shop_ids)
                ->where('shop.status',1)
                ->get()->toArray();
            $provinceInfo = collect($provinceInfo)->pluck('name','id')->all();
            $cityInfo = collect($cityInfo)->pluck('name','id')->all();

            foreach($shopList['data'] as $k=>$v){
                $shopList['data'][$k]['shop_pic'] = $v['shop_pic']?$domain->rule.'/'.$v['shop_pic']:$v['shop_pic'];
                if(isset($provinceInfo[$v['id']])){
                    $cityeName = $provinceInfo[$v['id']];
                }else{
                    $cityeName = '';
                }
                if(isset($cityInfo[$v['id']])){
                    $cityeName.=$cityInfo[$v['id']];
                }else{
                    $cityeName.='';
                }

                $shopList['data'][$k]['city_name'] = $cityeName;
                $shopList['data'][$k]['cate_name'] = isset($shopInfoDetail[$v['id']])?$shopInfoDetail[$v['id']]:null;
                $shopList['data'][$k]['total_comment'] = $v['total_comment']?$v['total_comment']:0;
                $shopList['data'][$k]['good_comment'] = $v['good_comment']?$v['good_comment']:0;
                if(in_array($v['uid'],$enterpriseIds)){
                    $shopList['data'][$k]['isEnterprise'] = 1;
                }else{
                    $shopList['data'][$k]['isEnterprise'] = 0;
                }
            }
        }
    return $this->formateResponse(1000,'获取店铺信息成功',$shopList);
    }


    /**
     * 威客商城(作品或服务)
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function commodityList(Request $request){
        $type = $request->get('type');//分类   传1代表作品  传2代表服务
        $name = $request->get('name');//名称
        $cate_id = $request->get('cate_id');//行业分类id
        $new = $request->get('new');//最新
        $sales_order = $request->get('sales_order');//销量排序
        $cash_order = $request->get('cash_order');//金额排序
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $shopList = GoodsModel::where(['status' => 1,'is_delete' => 0,'type' => $type]);
        if($name){
            $shopList = $shopList->where('title','like','%'.$name.'%');
        }
        if($cate_id){
            $shopList = $shopList->where('cate_id',$cate_id);
        }
        if($new){
            $shopList = $shopList->orderBy('created_at','desc');
        }
        if($sales_order){
            if($sales_order == 1){
                $sales_order = 'desc';
            }else{
                $sales_order = 'asc';
            }
            $shopList = $shopList->orderBy('sales_num',$sales_order);
        }
        if($cash_order){
            if($cash_order == 1){
                $cash_order = 'desc';
            }else{
                $cash_order = 'asc';
            }
            $shopList = $shopList->orderBy('cash',$cash_order);
        }
        $shopList = $shopList->select('id','uid','shop_id','cate_id','title','unit','cash','cover','sales_num')->paginate()->toArray();
        if($shopList['total']){
            $shop_ids = array_pluck($shopList['data'],'shop_id');
            $cate_ids = array_pluck($shopList['data'],'cate_id');
            $cateInfo = TaskCateModel::whereIn('id',$cate_ids)->select('id','name')->get()->toArray();
            $cateInfo = collect($cateInfo)->pluck('name','id')->all();
            $provinceInfo = ShopModel::join('district', 'shop.province', '=', 'district.id')
                ->select('shop.id','district.name')
                ->whereIn('shop.id', $shop_ids)
                ->where('shop.status',1)
                ->get()->toArray();
            $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
                ->select('shop.id','district.name')
                ->whereIn('shop.id', $shop_ids)
                ->where('shop.status',1)
                ->get()->toArray();
            $provinceInfo = collect($provinceInfo)->pluck('name','id')->all();
            $cityInfo = collect($cityInfo)->pluck('name','id')->all();
            foreach($shopList['data'] as $k=>$v){
                $shopList['data'][$k]['cover'] = $v['cover']?$domain->rule.'/'.$v['cover']:$v['cover'];
                $shopList['data'][$k]['city_name'] = (isset($provinceInfo[$v['shop_id']]) || isset($cityInfo[$v['shop_id']]))? $provinceInfo[$v['shop_id']].$cityInfo[$v['shop_id']]:null;
                $shopList['data'][$k]['cate_name'] = isset($cateInfo[$v['cate_id']])?$cateInfo[$v['cate_id']]:null;
                switch($v['unit']){
                    case '0':
                        $shopList['data'][$k]['unit'] = '件';
                        break;
                    case '1':
                        $shopList['data'][$k]['unit'] = '时';
                        break;
                    case '2':
                        $shopList['data'][$k]['unit'] = '份';
                        break;
                    case '3':
                        $shopList['data'][$k]['unit'] = '个';
                        break;
                    case '4':
                        $shopList['data'][$k]['unit'] = '张';
                        break;
                    case '5':
                        $shopList['data'][$k]['unit'] = '套';
                        break;
                }
            }
        }
        return $this->formateResponse(1000,'获取威客商城信息成功',$shopList);

    }


    /**
     * 获取店铺设置信息信息成功
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShop(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        //判断用户是否实名认证
        $realName = RealnameAuthModel::where('uid',$uid)->where('status',1)->first();
        if(empty($realName)){
            return $this->formateResponse(1001,'请先进行实名认证');
        }
        //判断店铺是否进行企业认证
        $companyInfo = EnterpriseAuthModel::where('uid', $uid)->orderBy('created_at', 'desc')->first();
        if (isset($companyInfo->status)) {
            switch ($companyInfo->status) {
                case 0:
                    $companyAuth = 2;//等待审核
                    break;
                case 1:
                    $companyAuth = 1;//审核通过
                    break;
                case 2:
                    $companyAuth = 3;//审核失败
                    //删除失败的认证记录
                    DB::transaction(function () use ($uid){
                        EnterpriseAuthModel::where('uid', $uid)->delete();
                        AuthRecordModel::where('auth_code', 'enterprise')->where('uid', $uid)->delete();
                    });
                    break;
            }
        }else{
            $companyAuth = 0;//没有申请
        }

        //查询店铺详情
        $shopInfo = ShopModel::where('uid',$uid)->first();
        if(!empty($shopInfo)){
            $domain = \CommonClass::getDomain();
            $shopInfo = array_except($shopInfo,array('type','created_at','updated_at','total_comment','good_comment','seo_title',
                'seo_keyword','seo_desc','is_recommend','nav_rules','nav_color','banner_rules','central_ad','footer_ad','shop_bg'));
            //查询地区信息
            $province = DistrictModel::where('id',$shopInfo['province'])->select('name')->first();
            $city = DistrictModel::where('id',$shopInfo['city'])->select('name')->first();
            if(!empty($province)){
                $province = $province->name;
            }else{
                $province = '';
            }
            if(!empty($city)){
                $city = $city->name;
            }else{
                $city = '';
            }
            $shopInfoTags = ShopTagsModel::where('shop_id',$shopInfo->id)->get()->toArray();
            if(!empty($shopInfoTags)){
                $tagIds = array();
                foreach($shopInfoTags as $key => $val){
                    $tagIds[] = $val['tag_id'];
                }
                //查询技能详情
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            }else{
                $tags = array();
            }
            $shopInfo['cate_name'] = array_flatten($tags);
            $shopInfo['city_name'] = $province.$city;
            $shopInfo['shop_pic'] = $domain.'/'.$shopInfo['shop_pic'];
            $shopInfo['type'] = $companyAuth == 1 ? '企业店铺' : '个人店铺';

        }
        $data = array(
            'shop_info'       => $shopInfo,
            'is_company_auth' => $companyAuth
        );
        return $this->formateResponse(1000,'获取店铺设置信息信息成功',$data);
    }

    /**
     * 获取店铺标签
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getShopSkill(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        //查询所有标签
        $hotTag = TagsModel::findAll();
        //查询店铺
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop)){
            //查询店铺是否设置技能标签
            $shopInfoTags = ShopTagsModel::where('shop_id',$shop->id)->get()->toArray();
            if(!empty($shopInfoTags)){
                $tagIds = array();
                foreach($shopInfoTags as $key => $val){
                    $tagIds[] = $val['tag_id'];
                }
                //查询技能详情
                $tags = SkillTagsModel::whereIn('id',$tagIds)->get()->toArray();
            }else{
                $tags = array();
            }
            $data = array(
                'all_tag' => $hotTag,
                'tags'    => $tags
            );
        }else{
            $data = array(
                'all_tag' => $hotTag,
                'tags'    => array()
            );
        }
        return $this->formateResponse(1000,'获取店铺标签成功',$data);
    }

    /**
     * 保存店铺设置信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postShopInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required|min:2|max:10',
            'shop_desc' => 'required',

        ],[
            'shop_name.required' => '请输入店铺名称',
            'shop_name.min' => '店铺名称最少2个字符',
            'shop_name.max' => '店铺名称最多10个字符',
            'shop_desc.required' => '请输入店铺介绍',
        ]);
        $error = $validator->errors()->all();
        if(count($error)){
            return $this->formateResponse(1003,'参数有误', $error);
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        $data['uid'] = $uid;
        $data['type'] = 1;
        $data['shop_name'] = $request->get('shop_name') ? $request->get('shop_name') : '';
        $data['shop_desc'] = $request->get('shop_desc') ? $request->get('shop_desc') : '';
        $data['province'] = $request->get('province') ? $request->get('province') : '';
        $data['city'] = $request->get('city') ? $request->get('city') : '';
        $data['tags'] = $request->get('tags') ? $request->get('tags') : '';
        //查询店铺
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop) && !$request->get('id')){
            return $this->formateResponse(1002,'参数缺少', '编辑是缺少店铺id');
        }
        if($request->get('id') && $request->get('id') != ''){
            //编辑店铺设置
            $data['id'] = $request->get('id');
            $shop = ShopModel::where('id',$data['id'])->first();
            $file = $request->file('shop_pic');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_pic'] = $result['data']['url'];
            }else{
                $data['shop_pic'] = $shop->shop_pic;
            }
            $data['province'] = $request->get('province') ?  $request->get('province') : $shop->province;
            $data['city'] = $request->get('city') ?  $request->get('city') : $shop->city;
            $res = ShopModel::updateShopInfo($data);
        }else{
            //新建店铺设置
            $file = $request->file('shop_pic');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_pic'] = $result['data']['url'];
            }else{
                $data['shop_pic'] = '';
            }
            $res = ShopModel::createShopInfo($data);
        }
        if($res){
            return $this->formateResponse(1000,'保存成功');
        }else{
            return $this->formateResponse(1001,'保存失败');
        }


    }


    /**
     * 我的店铺信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function myShop(Request $request){
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        //查询我的店铺
        $shopInfo = ShopModel::where('uid',$uid)
            ->select('id','status','uid','shop_pic','shop_desc','shop_name','shop_bg','province','city','total_comment','good_comment')->first();
        if(empty($shopInfo)){
            //查询是否实名认证
            $realName = RealnameAuthModel::where('uid',$uid)->where('status',1)->first();
            if(empty($realName)){
                return $this->formateResponse(1001,'请先进行实名认证');
            }
            return $this->formateResponse(1002,'请先进行店铺设置');
        }
        $shopId = $shopInfo->id;
        $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
        $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
        $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
        $shopInfo->cate_name = [];
        $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
        if(!empty($shopTags)){
            $tagIds = array_unique(array_flatten($shopTags));
            $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
            if(!empty($tags)){
                $shopInfo->cate_name = array_unique(array_flatten($tags));
            }
        }
        //查询地址
        if($shopInfo->province){
            $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
            $provinceName = $province->name;
        }else{
            $provinceName = '';
        }
        if($shopInfo->city){
            $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
            $cityName = $city->name;
        }else{
            $cityName = '';
        }
        $shopInfo->city_name = $provinceName.$cityName;
        //店铺描述处理
        $shopInfo['shop_desc'] = htmlspecialchars_decode($shopInfo['shop_desc']);
        //店铺好评率
        if(!empty($shopInfo->total_comment)){
            $shopInfo->good_comment_rate = intval($shopInfo->good_comment/$shopInfo->total_comment*100);
        }else{
            $shopInfo->good_comment_rate = 100;
        }
        //统计接受雇佣 卖出服务 上架商品（作品+服务） 已售作品
        //上架商品
        $shopInfo['goods_num'] = GoodsModel::where('uid',$uid)->where('status',1)->count();
        //店铺累计已售作品
        $shopInfo['sale_goods_num'] = GoodsModel::where(['shop_id' => $shopId, 'type' => 1])->select('id')->sum('sales_num');
        //店铺累计服务
        $shopInfo['service_num'] = GoodsModel::where(['shop_id' => $shopId, 'type' => 2])->select('id')->sum('sales_num');
        //店铺接受雇佣
        $employNum = UserDetailModel::where('uid',$uid)->select('employee_num')->first();
        if(!empty($employNum)){
            $employNum = $employNum->employee_num;
        }else{
            $employNum = 0;
        }
        $shopInfo['employ_num'] = (($employNum - $shopInfo['service_num'])> 0) ? $employNum - $shopInfo['service_num'] : 0;
        return $this->formateResponse(1000,'获取我的店铺信息成功',$shopInfo);


    }


    /**
     * 威客店铺信息详情
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function shopDetail(Request $request){

        if(!$request->get('shop_id')){
            return $this->formateResponse(1001,'缺少参数');
        }
        $shopId = $request->get('shop_id');
        //查询我的店铺
        $shopInfo = ShopModel::where('id',$shopId)
            ->select('id','uid','status','shop_pic','shop_desc','shop_name','shop_bg','province','city','total_comment','good_comment')->first();
        if(!empty($shopInfo)){
            $domain = ConfigModel::where('alias','site_url')->where('type','site')->select('rule')->first();
            $shopInfo->shop_pic = $shopInfo->shop_pic?$domain->rule.'/'.$shopInfo->shop_pic:$shopInfo->shop_pic;
            $shopInfo->shop_bg = $shopInfo->shop_bg?$domain->rule.'/'.$shopInfo->shop_bg:$shopInfo->shop_bg;
            $shopInfo->cate_name = [];
            $shopTags = ShopTagsModel::where('shop_id',$shopId)->select('tag_id')->get()->toArray();
            if(!empty($shopTags)){
                $tagIds = array_unique(array_flatten($shopTags));
                $tags = SkillTagsModel::whereIn('id',$tagIds)->select('tag_name')->get()->toArray();
                if(!empty($tags)){
                    $shopInfo->cate_name = array_unique(array_flatten($tags));
                }
            }
            //查询地址
            if($shopInfo->province){
                $province = DistrictModel::where('id',$shopInfo->province)->select('id','name')->first();
                $provinceName = $province->name;
            }else{
                $provinceName = '';
            }
            if($shopInfo->city){
                $city = DistrictModel::where('id',$shopInfo->city)->select('id','name')->first();
                $cityName = $city->name;
            }else{
                $cityName = '';
            }
            $shopInfo->city_name = $provinceName.$cityName;
            //店铺描述处理
            $shopInfo['shop_desc'] = htmlspecialchars_decode($shopInfo['shop_desc']);
            //店铺好评率
            if(!empty($shopInfo->total_comment)){
                $shopInfo->good_comment_rate = intval($shopInfo->good_comment/$shopInfo->total_comment*100);
            }else{
                $shopInfo->good_comment_rate = 100;
            }
            //上架商品总数
            $shopInfo['goods_num'] = GoodsModel::where('shop_id',$shopId)->where('status',1)->count();
            //查询店铺商品id数组
            $goods = GoodsModel::select('id')->where('shop_id',$shopId)->where('type',1)->get()->toArray();
            $goodsId = array_flatten($goods);
            //计算店铺（作品和雇佣）评价（态度、质量、速度）
            //作品
            $goodsCommentAtt = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('attitude_score'),1);
            $goodsCommentSpeed = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('speed_score'),1);
            $goodsCommentQuality = number_format(GoodsCommentModel::whereIn('goods_id',$goodsId)->avg('quality_score'),1);
            //雇佣
            $employCommentAtt = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->avg('attitude_score'),1);
            $employCommentSpeed = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->avg('speed_score'),1);
            $employCommentQuality = number_format(EmployCommentsModel::where('to_uid',$shopInfo->uid)->avg('quality_score'),1);
            $shopInfo['attitude_score'] = number_format(($goodsCommentAtt + $employCommentAtt)/2,1);
            $shopInfo['speed_score'] = number_format(($goodsCommentSpeed + $employCommentSpeed)/2,1);
            $shopInfo['quality_score'] = number_format(($goodsCommentQuality + $employCommentQuality)/2,1);
            return $this->formateResponse(1000,'获取威客店铺信息成功',$shopInfo);
        }else{
            return $this->formateResponse(1002,'参数有误',$shopInfo);
        }

    }

    /**
     * 修改店铺背景
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function saveShopBg(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        //查询店铺信息
        $shop = ShopModel::where('uid',$uid)->first();
        if(!empty($shop)){
            //上传店铺背景图片
            $file = $request->file('shop_bg');
            if ($file) {
                $result = \FileClass::uploadFile($file, 'user');
                $result = json_decode($result, true);
                $data['shop_bg'] = $result['data']['url'];
            }else{
                return $this->formateResponse(1002,'缺少参数');
            }
            $res = ShopModel::where('uid',$uid)->update($data);
            if($res){
                return $this->formateResponse(1000,'保存成功');
            }else{
                return $this->formateResponse(1001,'保存失败');
            }
        }else{
            return $this->formateResponse(1003,'店铺不存在');
        }
    }


    /**
     * 开启或关闭店铺
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function changeShopStatus(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
        $uid = $tokenInfo['uid'];
        $shopInfo = ShopModel::where('uid', $uid)->first();
        $data = [
            'uid' => $uid,
            'shopId' => $shopInfo->id
        ];
        if ($shopInfo['status'] == 1) {
            //关闭店铺并下架作品和服务
            $res = DB::transaction(function () use ($data) {
                ShopModel::where('id', $data['shopId'])->update(['status' => 2, 'updated_at' => date('Y-m-d H:i:s', time())]);
                UserDetailModel::where('uid', $data['uid'])->update(['shop_status' => 2, 'updated_at' => date('Y-m-d H:i:s', time())]);
                $auditInfo = GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 0])->get();
                if (!empty($auditInfo)) {
                    GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 0])->update(['status' => 3]);
                }
                $salesInfo = GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 1])->get();
                if (!empty($salesInfo)) {
                    GoodsModel::where(['shop_id' => $data['shopId'], 'status' => 1])->update(['status' => 2]);
                }
                return true;
            });
            if($res){
                $info = array(
                    'msg' =>'店铺关闭，商品全部下架'
                );
            }
        } else {
            //开启店铺
            $res = DB::transaction(function () use ($data) {
                ShopModel::where('id', $data['shopId'])->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
                UserDetailModel::where('uid', $data['uid'])->update(['shop_status' => 1, 'updated_at' => date('Y-m-d H:i:s', time())]);
                return true;
            });
            if($res){
                $info = array(
                    'msg' =>'店铺开启'
                );

            }

        }
        if($res){
            return $this->formateResponse(1000,'保存成功',$info);
        }else{
            return $this->formateResponse(1001,'保存失败');
        }
    }

}