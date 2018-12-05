<?php
namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\UserCenterController;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\User\Model\AuthRecordModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\TaskModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserTagsModel;
use Illuminate\Http\Request;
use Auth;

class UserController extends UserCenterController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('userfinance');//主题初始化
    }

    /**
     * 个人空间--成功案例
     * @return mixed
     */
    public function getPersonCase()
    {
        $uid = Auth::User()->id;
        $userInfo = UserDetailModel::where('uid', $uid)->first();
        //查询技能标签
        $tag = UserTagsModel::getTagsByUserId($uid);
        //获取地区标签
        $addr = UserDetailModel::getAreaByUserId($uid);
        //查询用户的绑定关系
        $authUser = AuthRecordModel::getAuthByUserId($uid);
        $query = SuccessCaseModel::select('success_case.*', 'tc.name as cate_name', 'ud.avatar as user_avatar');
        $list = $query->leftJoin('cate as tc', 'success_case.cate_id', '=', 'tc.id')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'success_case.uid')->where('ud.uid', $uid)
            ->paginate(8);
        $listO = $list->toArray();
        $tcName = SuccessCaseModel::select('tc.name')->join('cate as tc', 'success_case.cate_id', '=', 'tc.id')->where('success_case.uid', $uid)->first();
        $domain = \CommonClass::getDomain();

        $data = array(
            'domain' => $domain,
            'addr' => $addr,
            'list' => $listO,
            'list_ob' => $list,
            'introduce' => $userInfo,
            'auth_user' => $authUser,
            'skill_tag' => $tag
        );
        $this->theme->set('TYPE',3);
        $this->theme->setTitle(Auth::User()->name);
        $this->theme->set('keywords',Auth::User()->name);
        $this->theme->set('description',Auth::User()->name);
        return $this->theme->scope('user.space.personcase', $data)->render();
    }


    /**
     * 个人空间--评价详情
     * @return mixed
     */
    public function getPersonEvaluation()
    {
        $uid = Auth::User()->id;
        $userInfo = UserDetailModel::where('uid', $uid)->first();
        //查询技能标签
        $tag = UserTagsModel::getTagsByUserId($uid);
        //获取地区标签
        $addr = UserDetailModel::getAreaByUserId($uid);
        $commentList = CommentModel::join('task', 'comments.task_id', '=', 'task.id')->join('user_detail', 'task.uid', '=', 'user_detail.uid')->where('comments.to_uid', $uid)
            ->leftJoin('users', 'users.id', '=', 'comments.from_uid')->paginate(8);
        //总评论数
        $counts = CommentModel::groupBy('to_uid')->where('to_uid', $uid)->count();
        //总好评数
        $count = CommentModel::groupBy('type')->where('to_uid', $uid)->havingRaw('type=1')->count();
        //好评率
        if ($counts != 0)
            $feedbackRate = ceil($count / $counts * 100);
        else
            $feedbackRate = 100;
        //工作速度平均分
        $avgspeed = round(CommentModel::where('to_uid', $uid)->avg('speed_score'), 1);
        //工作质量平均分
        $avgquality = round(CommentModel::where('to_uid', $uid)->avg('quality_score'), 1);
        //工作态度平均分
        $avgattitude = round(CommentModel::where('to_uid', $uid)->avg('attitude_score'), 1);
        $domain = \CommonClass::getDomain();
        //查询用户的绑定关系
        $authUser = AuthRecordModel::getAuthByUserId($uid);
        $data = array(
            'domain' => $domain,
            'addr' => $addr,
            'introduce' => $userInfo,
            'avgquality' => $avgquality,
            'avgattitude' => $avgattitude,
            'avgspeed' => $avgspeed,
            'feedbackRete' => $feedbackRate,
            'count' => $count,
            'commentList' => $commentList,
            'auth_user' => $authUser,
            'skill_tag' => $tag
        );
        $this->theme->set('TYPE',3);
        $this->theme->setTitle(Auth::User()->name);
        $this->theme->set('keywords',Auth::User()->name);
        $this->theme->set('description',Auth::User()->name);
        return $this->theme->scope('user.space.personevaluation', $data)->render();
    }

    /**
     * @param $id
     * @return mixed
     * 成功案例详情页
     */
    public function getPersonEvaluationDetail($id)
    {
        $uid = Auth::User()->id;
        //$comment = TaskModel::where('id',$id)->first();
        $comment = TaskModel::join('cate', 'task.cate_id', '=', 'cate.id')->where('task.id', $id)->first();
        $successCase = SuccessCaseModel::join('cate', 'success_case.cate_id', '=', 'cate.id')->where('success_case.id', $id)->first();
        $viewTimes = array(
            'view_count' => $successCase->view_count + 1
        );
        SuccessCaseModel::where('id', $id)->update($viewTimes);
        $userInfo = UserDetailModel::where('uid', $uid)->first();
        //查询技能标签
        $tag = UserTagsModel::getTagsByUserId($uid);
        //获取地区标签
        $addr = UserDetailModel::getAreaByUserId($uid);

        $domain = \CommonClass::getDomain();
        //查询用户的绑定关系
        $authUser = AuthRecordModel::getAuthByUserId($uid);
        $data = array(
            'successCase' => $successCase,
            'domain' => $domain,
            'addr' => $addr,
            'introduce' => $userInfo,
            'comment' => $comment,
            'auth_user' => $authUser,
            'skill_tag' => $tag
        );
        $this->theme->set('TYPE',3);
        $this->theme->setTitle(Auth::User()->name);
        $this->theme->set('keywords',Auth::User()->name);
        $this->theme->set('description',Auth::User()->name);
        return $this->theme->scope('user.space.personevaluationdetail', $data)->render();
    }

    /**
     * @param $id
     * @return mixed
     * 添加成功案例表单
     */
    public function getAddPersonCase($id)
    {
        $uid = Auth::User()->id;
        $userInfo = UserDetailModel::where('uid', $uid)->first();
        //查询技能标签
        $tag = UserTagsModel::getTagsByUserId($uid);
        //获取地区标签
        $addr = UserDetailModel::getAreaByUserId($uid);
        //查询热门分类
        $hotCate = TaskCateModel::hotCate(6);
        //查询所有的末级分类
        $category_all = TaskCateModel::findByPid([0],['id']);
        $category_all = array_flatten($category_all);
        $category_all = TaskCateModel::findByPid($category_all);

        //查询用户的绑定关系
        $authUser = AuthRecordModel::getAuthByUserId($uid);

        $domain = \CommonClass::getDomain();
        $data = array(
            'domain' => $domain,
            'addr' => $addr,
            'introduce' => $userInfo,
            'hotcate' => $hotCate,
            'category_all' => $category_all,
            'id' => $id,
            'auth_user' => $authUser,
            'skill_tag' => $tag

        );
        $this->theme->set('TYPE',3);
        $this->theme->setTitle(Auth::User()->name);
        $this->theme->set('keywords',Auth::User()->name);
        $this->theme->set('description',Auth::User()->name);
        return $this->theme->scope('user.space.addpersoncase', $data)->render();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * 添加成功案例
     */
    public function postAddCase(Request $request)
    {
        $data = $request->except('_token');
        $user = Auth::User();

        $file = $request->file('pic');
        if (!$file) {
            return redirect()->back()->with('error', '上传文件不能为空');
        }
        if (!$request->cate_id) {
            return redirect()->back()->with('error', '案例分类不能为空');
        }

        $result = \FileClass::uploadFile($file, 'sys');
        $result = json_decode($result, true);
        $data = array(
            'pic' => $result['data']['url'],
            'uid' => $user->id,
            'username'=>$user->name,
            'title' => $request->title,
            'desc' =>\CommonClass::removeXss($request->description),
            'type' => 1,
            'url' => $request->url,
            'cate_id' => $request->get('cate_id'),
            'created_at' => date('Y-m-d H:i:s', time()),
        );
        $result2 = SuccessCaseModel::insert($data);

        if (!$result2)
            return redirect()->back()->with('error', '成功案例添加失败！');


        return redirect('/user/personCase')->with('massage', '成功案例添加成功！');
    }

    /**
     * 编辑成功案例视图
     * @param $id 案例id
     * @return mixed
     */
    public function getEditPersonCase($id)
    {
        $uid = Auth::User()->id;
        $userInfo = UserDetailModel::where('uid', $uid)->first();
        //查询技能标签
        $tag = UserTagsModel::getTagsByUserId($uid);
        //获取地区标签
        $addr = UserDetailModel::getAreaByUserId($uid);
        //查询热门分类
        $hotCate = TaskCateModel::hotCate(6);
        //查询所有的末级分类
        $category_all = TaskCateModel::findByPid([0],['id']);
        $category_all = array_flatten($category_all);
        $category_all = TaskCateModel::findByPid($category_all);

        //查询用户的绑定关系
        $authUser = AuthRecordModel::getAuthByUserId($uid);

        //查询成功案例详情
        $successCase = SuccessCaseModel::join('cate', 'success_case.cate_id', '=', 'cate.id')->where('success_case.id', $id)
            ->select('success_case.*','cate.name','cate.id')->first();

        $domain = \CommonClass::getDomain();
        $data = array(
            'domain' => $domain,
            'addr' => $addr,
            'introduce' => $userInfo,
            'hotcate' => $hotCate,
            'category_all' => $category_all,
            'id' => $id,
            'auth_user' => $authUser,
            'skill_tag' => $tag,
            'successCase' => $successCase

        );
        $this->theme->set('TYPE',3);
        $this->theme->setTitle(Auth::User()->name);
        $this->theme->set('keywords',Auth::User()->name);
        $this->theme->set('description',Auth::User()->name);
        return $this->theme->scope('user.space.editpersoncase', $data)->render();
    }

    /**
     * 编辑成功案例
     * @param Request $request
     * @return mixed
     */
    public function postEditCase(Request $request)
    {
        $data = $request->except('_token');
        //查询成功案例详情
        $successCase = SuccessCaseModel::join('cate', 'success_case.cate_id', '=', 'cate.id')->where('success_case.id', $data['id'])
            ->select('success_case.*','cate.name','cate.id')->first();
        $user = Auth::User();
        $file = $request->file('pic');
        if ($file) {
            $result = \FileClass::uploadFile($file, 'sys');
            $result = json_decode($result, true);
            $pic = $result['data']['url'];
        }else{
            $pic = $successCase['pic'];
        }
        if (!$request->cate_id) {
            $cateId = $successCase['cate_id'];
        }else{
            $cateId = $request->cate_id;
        }

        $arr = array(
            'pic' => $pic,
            'uid' => $user->id,
            'title' => $request->title,
            'desc' => e($request->description),
            'url' => $request->url,
            'cate_id' => $cateId,
            'created_at' => date('Y-m-d H:i:s', time()),
        );
        $res = SuccessCaseModel::where('success_case.id', $data['id'])->update($arr);
        if (!$res)
            return redirect()->back()->with('error', '成功案例编辑失败！');

        return redirect('/user/personCase')->with('massage', '成功案例编辑成功！');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 开启关闭服务商
     */
    public function ajaxUpdateCase(Request $request)
    {
        $uid = $request->id;
        $userinfo = UserDetailModel::where('uid', $uid)->first();
        if ($userinfo['shop_status'] == 1) {
            $result = UserDetailModel::where('uid', $uid)->update(['shop_status' => 2]);
        } else {
            $result = UserDetailModel::where('uid', $uid)->update(['shop_status' => 1]);
        }
        if (!$result)
            return response()->json(['error' => '修改失败！']);

        return response()->json(['massage' => '修改成功！', 'window.reload()']);
    }

    /**
     * 修改背景图片
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxUpdatePic(Request $request)
    {
        $user = Auth::User();
        $file = $request->back;

        $result = \FileClass::uploadFile($file, 'user', array('jpg', 'png', 'jpeg', 'bmp', 'png'));
        $result = json_decode($result, true);
        $backgroundurl = $result['data']['url'];
        $domain = \CommonClass::getDomain();
        return response()->json(['path' => $backgroundurl, 'domain' => $domain]);
    }

    /**
     * 删除背景图片
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxDelPic(Request $request)
    {
        $uid = $request->id;
        $result = UserDetailModel::where('uid', $uid)->update(['backgroundurl' => '']);
        $domain = \CommonClass::getDomain();
        return response()->json(['domain' => $domain]);
    }


    public function ajaxUpdateBack(Request $request)
    {
        $user = Auth::User();
        echo $backgroundurl = $request->src;
        $data = array(
            'backgroundurl' => $backgroundurl
        );
        $result = UserDetailModel::where('uid', $user->id)->update($data);
        $domain = \CommonClass::getDomain();
        return response()->json(['path' => $backgroundurl, 'domain' => $domain]);
    }

    public function ajaxDeleteSuccess(Request $request)
    {
        $id = $request->get('id');
        $user = Auth::User();
        $uid = $user->id;
        //判断成功案例是否属于该用户
        $successCase = SuccessCaseModel::where('id',$id)->where('uid',$uid)->first();
        if(empty($successCase)){
            $data = array(
                'code' => 0,
                'msg' => '参数错误'
            );
        }else{
            $res = SuccessCaseModel::where('id',$id)->delete();
            if($res){
                $data = array(
                    'code' => 1,
                    'msg' => '删除成功'
                );
            }else{
                $data = array(
                    'code' => 0,
                    'msg' => '删除失败'
                );
            }
        }
        return response()->json($data);

    }
}
