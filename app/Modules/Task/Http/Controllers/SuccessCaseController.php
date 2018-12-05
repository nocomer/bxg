<?php
namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\IndexController;
use App\Http\Requests;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\AdModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use Illuminate\Support\Facades\Auth;


class SuccessCaseController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('main');
    }
    /**
     * 成功案例页面
     */
    public function index(Request $request)
    {
        //接收筛选条件
        $data = $request->all();
        $this->theme->setTitle('成功案例');
        //根据任务类型更新任务类型
        if(isset($data['category']))
        {
            $category = TaskCateModel::findByPid([$data['category']]);
            $pid = $data['category'];
            if(empty($category))
            {
                $category_data = TaskCateModel::findById($data['category']);
                $category = TaskCateModel::findByPid([$category_data['pid']]);
                $pid = $category_data['pid'];
            }
        }else {
            //查询一级的分类,默认的是一级分类
            $category = TaskCateModel::findByPid([0]);
            $pid = 0;
        }
        //查询已经成功的任务列表
        $query = SuccessCaseModel::select('success_case.*','tc.name as cate_name','ud.avatar as user_avatar','us.name as nickname');

        //类别筛选
        if(isset($data['category']) && $data['category']>0)
        {
            //查询所有的底层id
            $category_ids = TaskCateModel::findCateIds($data['category']);
            $query->whereIn('success_case.cate_id',$category_ids);
        }
        if(isset($data['searche']))
        {
            //搜索
            $query->where('success_case.title','like','%'.e($data['searche']).'%');
        }
        //排序
        if(isset($data['desc']))
        {
            $query->orderBy($data['desc'],'desc');
        }
        $paginate = ($this->themeName = 'black') ? 14 :12;
        $list =$query->join('cate as tc','success_case.cate_id','=','tc.id')
            ->leftjoin('users as us','us.id','=','success_case.uid')
            ->leftjoin('user_detail as ud','ud.uid','=','success_case.uid')
            ->paginate($paginate);
        $status = [
                0=>'暂不发布',
                1=>'已经发布',
                2=>'赏金托管',
                3=>'审核通过',
                4=>'威客交稿',
                5=>'雇主选稿',
                6=>'任务公示',
                7=>'交付验收',
                8=>'双方互评'
        ];
//        $list['data'] = \CommonClass::intToString($list['data'],$status);
        $domain = \CommonClass::getDomain();
        //成功案例底部广告
        $ad = AdTargetModel::getAdInfo('CASELIST_BOTTOM');
        $view = [
            'list'=>$list,
            'merge'=>$data,
            'category'=>$category,
            'pid'=>$pid,
            'domain'=>$domain,
            'ad'=>$ad,
        ];
        $this->theme->set('now_menu','/task/successCase');
        return $this->theme->scope('task.success', $view)->render();
    }

    /**
     * 成功案例详情页面
     */
    public function detail($id)
    {
        $this->theme->setTitle('成功案例详情');
        $success_case = SuccessCaseModel::select('success_case.*','tc.name')
            ->where('success_case.id',$id)
            ->leftJoin('cate as tc','tc.id','=','success_case.cate_id')
            ->first();
        $view = [
            'success_case'=>$success_case,
        ];
        //如果使用户发布，查询发布用户的信息
        if($success_case['type']==1)
        {
            $user_data = UserModel::where('id',$success_case['uid'])->first();
            $user_detail = UserDetailModel::where('uid',$success_case['uid'])->first();
            $view = array_add($view,'user_data',$user_data);
            $view = array_add($view,'user_detail',$user_detail);
            $view = array_add($view,'domain',\CommonClass::getDomain());

            $tags = TagsModel::getUserTags($user_data['id']);
            $view['tags'] = $tags;
        }
        //成功案例详情底部广告
        $ad = AdTargetModel::getAdInfo('CASEINFO_BOTTOM');

        //成功案例详情右上方广告
        $rightAd = AdTargetModel::getAdInfo('CASEINFO_RIGHT_TOP');

        //成功案例右侧推荐位
        $reTarget = RePositionModel::where('code','CASEINFO_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $successCaseInfo = SuccessCaseModel::leftJoin('cate','cate.id','=','success_case.cate_id')
                        ->where('success_case.id',$v['recommend_id'])->select('success_case.view_count','cate.name')->first();
                    if($successCaseInfo){
                        $v['view_count'] = $successCaseInfo->view_count;
                        $v['cate_name'] = $successCaseInfo->name;
                    }
                    else{
                        $v['view_count'] = 0;
                        $v['cate_name'] = '';
                    }

                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }


        $view['ad'] = $ad;
        $view['rightAd'] = $rightAd;
        $view['hotList'] = $hotList;
        $view['targetName'] = $reTarget->name;
        return $this->theme->scope('task.successdetail', $view)->render();
    }

    /**
     * 判断当前的跳转地址
     */
    public function  jump($id)
    {
        $successCase = SuccessCaseModel::where('id',$id)->first();

        if(!$successCase)
            return redirect()->back()->with(['参数错误！']);

        //增加案例浏览次数
        SuccessCaseModel::where('id',$id)->increment('view_count',1);

        if(Auth::check() && Auth::user()->id == $successCase['uid'])
        {
            //查询是否有店铺
            $shop = ShopModel::where('uid',Auth::id())->first();
            if($shop){
                return redirect()->to('/shop/successDetail/'.$id);
            }else{
                return redirect()->to('/user/personevaluationdetail/'.$id);
            }
        }elseif( !empty($successCase['url']))
        {
            return redirect()->to($successCase['url']);
        }else{
            return redirect()->to('/task/successDetail/'.$id);
        }

    }
}
