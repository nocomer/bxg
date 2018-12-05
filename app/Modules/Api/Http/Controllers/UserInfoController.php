<?php
/**
 * Created by PhpStorm.
 * User: KEKE-1003
 * Date: 2016/7/1
 * Time: 15:24
 */
namespace App\Modules\Api\Http\Controllers;

use App\Http\Requests;
use App\Modules\Shop\Models\ShopModel;
use App\Modules\Shop\Models\ShopTagsModel;
use App\Modules\User\Model\EnterpriseAuthModel;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use Validator;
use App\Modules\Task\Model\TaskFocusModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\TagsModel;
use App\Modules\User\Model\UserFocusModel;
use App\Modules\User\Model\UserTagsModel;
use App\Modules\Task\Model\TaskCateModel;
use App\Modules\Task\Model\SuccessCaseModel;
use App\Modules\Im\Model\ImAttentionModel;
use App\Modules\Im\Model\ImMessageModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\Advertisement\Model\AdModel;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\UserModel;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\Task\Model\WorkAttachmentModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\Manage\Model\FeedbackModel;
use App\Modules\Manage\Model\ArticleCategoryModel;
use App\Modules\Manage\Model\ArticleModel;
use App\Modules\Order\Model\OrderModel;
use Omnipay;
use Config;
use Illuminate\Support\Facades\Crypt;
use DB;
Use QrCode;
Use Cache;

class UserInfoController extends ApiBaseController
{
    /**
     * 我收藏的任务列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function myfocus(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        //查询我收藏的任务
        $query = TaskFocusModel::select('task_focus.id as focus_id', 'tk.*', 'tc.name as category_name', 'ud.avatar')
            ->where('task_focus.uid', $tokenInfo['uid'])
            ->join('task as tk', 'tk.id', '=', 'task_focus.task_id')
            ->leftjoin('cate as tc', 'tc.id', '=', 'tk.cate_id')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'tk.uid')
            ->orderBy('task_focus.created_at', 'desc')
            ->paginate()->toArray();

        $task_focus = $query;
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        if (!empty($task_focus['data']) && is_array($task_focus['data'])) {
            foreach ($task_focus['data'] as $k => $v) {
                $task_focus['data'][$k]['avatar'] = $v['avatar'] ? $domain->rule . '/' . $v['avatar'] : $v['avatar'];
                $provinceName = DistrictModel::getDistrictName($v['province']);
                $cityName = DistrictModel::getDistrictName($v['city']);
                $task_focus['data'][$k]['province_name'] = $provinceName;
                $task_focus['data'][$k]['city_name'] = $cityName;
            }
        }
        $status = [
            'status' => [
                0 => '暂不发布',
                1 => '已经发布',
                2 => '赏金托管',
                3 => '审核通过',
                4 => '威客交稿',
                5 => '雇主选稿',
                6 => '任务公示',
                7 => '交付验收',
                8 => '双方互评'
            ]
        ];
        $task_focus['data'] = \CommonClass::intToString($task_focus['data'], $status);
        return $this->formateResponse(1000, 'success', $task_focus);
    }


    /**
     * 删除我收藏的某个任务
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteFocus(Request $request)
    {
        if (!$request->get('id')) {
            return $this->formateResponse(1035, '传送数据错误');
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $result = TaskFocusModel::where('uid', $tokenInfo['uid'])
            ->where('task_id', intval($request->get('id')))->delete();
        if (!$result) {
            return $this->formateResponse(1036, '删除失败');
        }
        return $this->formateResponse(1000, 'success');
    }


    /**
     * 删除我关注的某个人
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUser(Request $request)
    {
        if (!$request->get('id')) {
            return $this->formateResponse(1037, '传送数据错误');
        }
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $result = UserFocusModel::where('uid', $tokenInfo['uid'])
            ->where('focus_uid', intval($request->get('id')))->delete();
        if (!$result) {
            return $this->formateResponse(1038, '删除失败');
        }
        return $this->formateResponse(1000, 'success');
    }

    /**
     * 收藏任务
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function insertFocusTask(Request $request)
    {
        $data = $request->all();
        $tokenInfo = Crypt::decrypt(urldecode($data['token']));
        $uid = $tokenInfo['uid'];
        if ($uid && $data['task_id']) {
            $arrFocus = array(
                'uid' => $uid,
                'task_id' => $data['task_id'],
                'created_at' => date('Y-m-d H:i:s'),
            );
            $result = TaskFocusModel::create($arrFocus);
            if ($result) {
                return $this->formateResponse(1000, 'success');
            } else {
                return $this->formateResponse(1040, '收藏失败');
            }
        }
    }


    /**
     * 获取行业分类的一级分类信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function skill(Request $request)
    {
        $category_data = TaskCateModel::findByPid([0]);

        if (empty($category_data)) {
            return $this->formateResponse(1039, '暂无信息');
        }
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        foreach ($category_data as $k => $v) {
            $category_data[$k]['pic'] = $category_data[$k]['pic'] ? $domain->rule . '/' . $category_data[$k]['pic'] : $category_data[$k]['pic'];

        }
        //$category_one = json_decode($category_data,true);
        return $this->formateResponse(1000, 'success', $category_data);

    }

    /**
     * 获取行业分类一级分类信息下的子分类信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function secondSkill(Request $request)
    {

        if (!$request->get('id')) {
            return $this->formateResponse(1040, '传送参数不能为空');
        }
        $category_detail = TaskCateModel::findByPid([$request->get('id')]);
        if (empty($category_detail)) {
            return $this->formateResponse(1039, '暂无信息');
        }
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
            $tags = UserTagsModel::where('uid', $tokenInfo['uid'])->select('tag_id')->get()->toArray();
            if (count($tags)) {
                $tagId = array_flatten($tags);
                $cateId = TagsModel::whereIn('id', $tagId)->select('cate_id')->get()->toArray();

                if (count($cateId)) {
                    $cateId = array_flatten($cateId);
                }
            }
            foreach ($category_detail as $k => $v) {
                if (isset($cateId)) {
                    if (in_array($v['id'], $cateId)) {
                        $category_detail[$k]['isChecked'] = 1;
                    } else {
                        $category_detail[$k]['isChecked'] = 0;
                    }
                } else {
                    $category_detail[$k]['isChecked'] = 0;
                }
                $category_detail[$k]['pic'] = $category_detail[$k]['pic'] ? $domain->rule . '/' . $category_detail[$k]['pic'] : $category_detail[$k]['pic'];

            }
        } else {
            foreach ($category_detail as $k => $v) {
                $category_detail[$k]['pic'] = $category_detail[$k]['pic'] ? $domain->rule . '/' . $category_detail[$k]['pic'] : $category_detail[$k]['pic'];

            }
        }

        return $this->formateResponse(1000, 'success', $category_detail);

    }


    /**
     * 用户技能标签存储
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function skillSave(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        if ($request->get('id')) {
            $num = UserTagsModel::where('uid', $tokenInfo['uid'])->count();
            if ($num >= 3) {
                return $this->formateResponse(1042, '二级标签数量不能超过3个');
            }
            $tagInfo = TagsModel::where('cate_id', $request->get('id'))->select('id')->first();
            if (!isset($tagInfo)) {
                return $this->formateResponse(1043, '传送参数错误');
            }
            $addInfo = [
                'tag_id' => $tagInfo->id,
                'uid' => $tokenInfo['uid']
            ];
            $res = UserTagsModel::create($addInfo);

            if (!isset($res)) {
                return $this->formateResponse(1008, '标签添加失败');
            }
            return $this->formateResponse(1000, 'success');
        }
        if ($request->get('cancel_id')) {
            $tagInfo = TagsModel::where('skill_tags.cate_id', $request->get('cancel_id'))
                ->where('tag_user.uid', $tokenInfo['uid'])
                ->leftjoin('tag_user', 'skill_tags.id', '=', 'tag_user.tag_id')
                ->select('tag_user.tag_id')
                ->first();
            if (!isset($tagInfo)) {
                return $this->formateResponse(1043, '传送参数错误');
            }
            $res = UserTagsModel::where('tag_id', $tagInfo->tag_id)->where('uid', $tokenInfo['uid'])->delete();
            if (!isset($res)) {
                return $this->formateResponse(1008, '标签删除失败');
            }
            return $this->formateResponse(1000, 'success');
        }
    }


    /**
     * 用户个人案例信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function personCase(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $query = SuccessCaseModel::select('success_case.id', 'success_case.title', 'success_case.pic');
        $list = $query->leftJoin('cate as tc', 'success_case.cate_id', '=', 'tc.id')
            ->leftjoin('user_detail as ud', 'ud.uid', '=', 'success_case.uid')->where('ud.uid', $tokenInfo['uid'])
            ->orderBy('success_case.created_at', 'desc')
            ->paginate(8)->toArray();
        if ($list['total']) {
            $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
            foreach ($list['data'] as $k => $v) {
                $list['data'][$k]['pic'] = $v['pic'] ? $domain->rule . '/' . $v['pic'] : $v['pic'];
            }
        }
        return $this->formateResponse(1000, 'success', $list);
    }


    /**
     * 添加个人案例信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addCase(Request $request)
    {

        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $file = $request->file('pic');
        if (!$request->get('cate_id') or !$file or !$request->get('title')) {
            return $this->formateResponse(1045, '传送数据不能为空');
        }

        $result = \FileClass::uploadFile($file, 'sys');
        $result = json_decode($result, true);
        $data = array(
            'pic' => $result['data']['url'],
            'uid' => $tokenInfo['uid'],
            'title' => $request->get('title'),
            'desc' => e($request->get('desc')),
            'cate_id' => $request->get('cate_id'),
            'created_at' => date('Y-m-d H:i:s', time()),
        );
        $result2 = SuccessCaseModel::create($data);

        if (!$result2) {
            return $this->formateResponse(1046, '成功案例添加失败');
        }
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $result2->pic = $result2->pic ? $domain->rule . '/' . $result2->pic : $result2->pic;
        return $this->formateResponse(1000, 'success', $result2);
    }


    /**
     * 获取个人案例信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function caseInfo(Request $request)
    {
        if (!$request->get('id')) {
            return $this->formateResponse(1047, '传送参数不能为空');
        }
        $successCaseInfo = SuccessCaseModel::find(intval($request->get('id')));
        if (empty($successCaseInfo)) {
            return $this->formateResponse(1048, '传送数据有误');
        }
        $successCaseInfo->desc = htmlspecialchars_decode($successCaseInfo->desc);
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $successCaseInfo->pic = $successCaseInfo->pic ? $domain->rule . '/' . $successCaseInfo->pic : $successCaseInfo->pic;
        return $this->formateResponse(1000, 'success', $successCaseInfo);
    }


    /**
     * 修改个人案例信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function caseUpdate(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $file = $request->file('pic');
        if (!$request->get('cate_id') or !$file or !$request->get('title') or !$request->get('id')) {
            return $this->formateResponse(1045, '传送数据不能为空');
        }

        $result = \FileClass::uploadFile($file, 'sys');
        $result = json_decode($result, true);
        $data = array(
            'pic' => $result['data']['url'],
            'uid' => $tokenInfo['uid'],
            'title' => $request->get('title'),
            'desc' => e($request->get('desc')),
            'cate_id' => $request->get('cate_id'),
            'created_at' => date('Y-m-d H:i:s', time()),
        );
        $result2 = SuccessCaseModel::where('id', intval($request->get('id')))->update($data);

        if (!$result2) {
            return $this->formateResponse(1046, '成功案例修改失败');
        }

        return $this->formateResponse(1000, 'success');
    }


    /**
     * 获取临时消息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function myTalk(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $myTalker = ImAttentionModel::where('im_attention.uid', $tokenInfo['uid'])
            ->where('im_attention.friend_uid', '<>', $tokenInfo['uid'])
            ->leftjoin('users', 'im_attention.friend_uid', '=', 'users.id')
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid');
        if ($request->get('nickname')) {
            $myTalker = $myTalker->where('users.name', 'like', '%' . $request->get('nickname') . '%');
        }
        $myTalker = $myTalker->select('im_attention.friend_uid', 'users.name as nickname', 'user_detail.avatar')
            ->groupBy('im_attention.friend_uid')->get();
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();

        if (!empty($myTalker)) {
            foreach ($myTalker as $k => $v) {
                $v->avatar = $v->avatar ? $domain->rule . '/' . $v->avatar : $v->avatar;
                $num = ImMessageModel::where('to_uid', $tokenInfo['uid'])
                    ->where('from_uid', $v->friend_uid)
                    ->where('status', 1)
                    ->count();
                $v->num = $num;
                $myTalker[$k] = $v;
            }
        }
        return $this->formateResponse(1000, 'success', $myTalker);
    }


    /**
     * 获取我的关注
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function myAttention(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $myAttention = UserFocusModel::where('user_focus.uid', $tokenInfo['uid'])
            ->where('user_focus.focus_uid', '<>', $tokenInfo['uid'])
            ->leftjoin('user_detail', 'user_focus.focus_uid', '=', 'user_detail.uid')
            ->leftjoin('users', 'user_detail.uid', '=', 'users.id');
        if ($request->get('nickname')) {
            $myAttention = $myAttention->where('users.name', 'like', '%' . $request->get('nickname') . '%');
        }
        $myAttention = $myAttention->select('user_focus.focus_uid', 'users.name as nickname', 'user_detail.avatar')
            ->groupBy('user_focus.focus_uid')->paginate()->toArray();
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        if ($myAttention['total']) {
            foreach ($myAttention['data'] as $k => $v) {
                $v['avatar'] = $v['avatar'] ? $domain->rule . '/' . $v['avatar'] : $v['avatar'];
                $num = ImMessageModel::where('to_uid', $tokenInfo['uid'])
                    ->where('status', 1)
                    ->where('from_uid', $v['focus_uid'])
                    ->count();
                $v['num'] = $num;
                $myAttention['data'][$k] = $v;
            }
        }
        return $this->formateResponse(1000, 'success', $myAttention);
    }

    /**
     * 加关注
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addAttention(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        if (!$request->get('focus_uid')) {
            return $this->formateResponse(1047, '传送数据不能为空');
        }
        $focusInfo = [
            'uid' => $tokenInfo['uid'],
            'focus_uid' => $request->get('focus_uid'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $info = UserFocusModel::where('uid', $tokenInfo['uid'])->where('focus_uid', $request->get('focus_uid'))->first();
        if (empty($info)) {
            $useFocusInfo = UserFocusModel::create($focusInfo);

            //成为IM好友
            $res = ImAttentionModel::where(['uid' => $tokenInfo['uid'], 'friend_uid' => $request->get('focus_uid')])->first();
            if (empty($res)) {
                ImAttentionModel::insert([
                    [
                        'uid' => $tokenInfo['uid'],
                        'friend_uid' => $request->get('focus_uid')
                    ],
                    [
                        'uid' => $request->get('focus_uid'),
                        'friend_uid' => $tokenInfo['uid']
                    ]

                ]);
            }
            if (empty($useFocusInfo)) {
                return $this->formateResponse(1048, '加关注失败');
            } else {
                $useFocus = UserFocusModel::find($useFocusInfo->id);
                return $this->formateResponse(1000, 'success', $useFocus);
            }
        } else {
            $useFocus = UserFocusModel::find($info->id);
            return $this->formateResponse(1000, 'success', $useFocus);
        }

    }


    /**
     * 提交聊天信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addMessage(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        if (!$request->get('to_uid') or !$request->get('content')) {
            return $this->formateResponse(1047, '传送数据不能为空');
        }
        $focusInfo = [
            'from_uid' => $tokenInfo['uid'],
            'to_uid' => $request->get('focus_uid'),
            'content' => $request->get('content'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $useFocusInfo = ImMessageModel::create($focusInfo);
        if (empty($useFocusInfo)) {
            return $this->formateResponse(1048, '创建消息失败');
        }
        $useFocus = ImMessageModel::find($useFocusInfo->id);
        return $this->formateResponse(1000, 'success', $useFocus);
    }


    /**
     * 将未读消息修改为已读
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateMessStatus(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        if (!$request->get('from_uid')) {
            return $this->formateResponse(1047, '传送数据不能为空');
        }
        $messageInfo = ImMessageModel::where('from_uid', $request->get('from_uid'))->where('to_uid', $tokenInfo['uid'])->get();
        if (empty($messageInfo)) {
            return $this->formateResponse(1048, '传送数据错误');
        }
        foreach ($messageInfo as $k => $v) {
            ImMessageModel::find($v->id)->update(['status' => 2]);
        }
        return $this->formateResponse(1000, 'success');
    }


    /**
     * 删除好友
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deleteTalk(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $friend_uid = $request->get('friend_uid');
        if (is_int($friend_uid) && $friend_uid > 0) {
            return $this->formateResponse(1049, '用户ID类型错误');
        }
        $talkInfo = ImAttentionModel::where('uid', $tokenInfo['uid'])->where('friend_uid', $friend_uid)->get()->toArray();
        if (!isset($talkInfo[0])) {
            return $this->formateResponse(1050, '需要删除的好友不存在');
        }
        $res = ImAttentionModel::where('id', $talkInfo[0]['id'])->delete();
        if ($res) {
            return $this->formateResponse(1000, '删除好友成功');
        }
        return $this->formateResponse(1051, '删除好友失败');
    }


    /**
     * 获取我是雇主首页顶部幻灯片信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function slideInfo(Request $request)
    {
        $adTargetInfo = AdTargetModel::where('code', 'HOME_TOP_SLIDE')->select('target_id')->first()->toArray();
        if (count($adTargetInfo)) {
            $adInfo = AdModel::where('target_id', $adTargetInfo['target_id'])
                ->where('is_open', '1')
                ->where(function ($adInfo) {
                    $adInfo->where('end_time', '0000-00-00 00:00:00')
                        ->orWhere('end_time', '>', date('Y-m-d h:i:s', time()));
                })
                ->select('*')
                ->get()
                ->toArray();
            $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
            foreach ($adInfo as $k => $v) {
                $adInfo[$k]['ad_file'] = $v['ad_file'] ? $domain->rule . '/' . $v['ad_file'] : $v['ad_file'];
            }
            return $this->formateResponse(1000, '获取广告幻灯片信息成功', $adInfo);

        }
        return $this->formateResponse(1052, '暂无广告位信息');
    }


    /**
     * 获取首页热门服务信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function hotService(Request $request)
    {
        $reTarget = RePositionModel::where('code', 'HOME_MIDDLE')->where('is_open', '1')->select('id', 'name')->first();
        if ($reTarget->id) {
            $recommend = RecommendModel::where('position_id', $reTarget->id)
                ->where('is_open', 1)
                ->where(function ($recommend) {
                    $recommend->where('end_time', '0000-00-00 00:00:00')
                        ->orWhere('end_time', '>', date('Y-m-d h:i:s', time()));
                })
                ->select('*')
                ->get()
                ->toArray();

            if (count($recommend)) {
                $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
                foreach ($recommend as $k => $v) {
                    $v['recommend_pic'] = $v['recommend_pic'] ? $domain->rule . '/' . $v['recommend_pic'] : $v['recommend_pic'];
                    $tag_ids = UserTagsModel::where('uid', $v['recommend_id'])->select('tag_id')->get()->toArray();
                    if (count($tag_ids)) {
                        $tags = TagsModel::whereIn('id', $tag_ids)->select('tag_name')->get()->toArray();
                        if (count($tags)) {
                            $v['tags'] = $tags;
                        } else {
                            $v['tags'] = [];
                        }
                    } else {
                        $v['tags'] = [];
                    }

                    $comment = CommentModel::where('to_uid', $v['recommend_id'])->count();
                    $goodComment = CommentModel::where('to_uid', $v['recommend_id'])->where('type', 1)->count();
                    if ($comment) {
                        $v['percent'] = number_format($goodComment / $comment, 3) * 100;
                    } else {
                        $v['percent'] = 100;
                    }
                    $recommend[$k] = $v;
                }
            }
            return $this->formateResponse(1000, '获取热门服务信息成功', $recommend);

        } else {
            return $this->formateResponse(1053, '暂无热门服务信息');
        }

    }

    /**
     * 首页推荐热门店铺
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function hotShop(Request $request)
    {
        $reTarget = RePositionModel::where('code', 'HOME_MIDDLE_SHOP')->where('is_open', '1')->select('id', 'name')->first();
        if ($reTarget->id) {
            $recommend = RecommendModel::getRecommendInfo($reTarget['id'], 'shop')
                ->where('shop.status', 1)
                ->leftJoin('shop', 'shop.id', '=', 'recommend.recommend_id')
                ->select('shop.id', 'shop.uid', 'shop.shop_pic',
                    'shop.shop_name', 'shop.total_comment', 'shop.good_comment', 'recommend.url')
                ->orderBy('recommend.created_at', 'DESC')
                ->get()->toArray();
            if (count($recommend)) {
                $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
                foreach ($recommend as $k => $v) {
                    $v['shop_pic'] = $v['shop_pic'] ? $domain->rule . '/' . $v['shop_pic'] : $domain->rule . '/' . $v['shop_pic'];
                    $tag_ids = ShopTagsModel::where('shop_id', $v['id'])->select('tag_id')->get()->toArray();
                    if (count($tag_ids)) {
                        $tags = TagsModel::whereIn('id', $tag_ids)->select('tag_name')->get()->toArray();
                        if (count($tags)) {
                            $v['tags'] = array_flatten($tags);
                        } else {
                            $v['tags'] = [];
                        }
                    } else {
                        $v['tags'] = [];
                    }
                    $shop_ids[] = $v['id'];
                    if ($v['total_comment']) {
                        $v['percent'] = number_format($v['good_comment'] / $v['total_comment'], 3) * 100;
                    } else {
                        $v['percent'] = 100;
                    }
                    $recommend[$k] = $v;
                }
                //店铺类型
                $uidArr = array_pluck($recommend,'uid');
                $companyInfo = EnterpriseAuthModel::whereIn('uid', $uidArr)->where('status',1)
                    ->orderBy('created_at', 'desc')->get()->toArray();
                if(!empty($companyInfo)){
                    $enterpriseIds = array_unique(array_pluck($companyInfo,'uid'));
                }else{
                    $enterpriseIds = [];
                }
                if (!empty($shop_ids)) {
                    $provinceInfo = ShopModel::join('district', 'shop.province', '=', 'district.id')
                        ->select('shop.id', 'district.name')
                        ->whereIn('shop.id', $shop_ids)
                        ->where('shop.status', 1)
                        ->get()->toArray();
                    $cityInfo = ShopModel::join('district', 'shop.city', '=', 'district.id')
                        ->select('shop.id', 'district.name')
                        ->whereIn('shop.id', $shop_ids)
                        ->where('shop.status', 1)
                        ->get()->toArray();
                    $provinceInfo = collect($provinceInfo)->pluck('name', 'id')->all();
                    $cityInfo = collect($cityInfo)->pluck('name', 'id')->all();
                    foreach ($recommend as $k => $v) {
                        $recommend[$k]['city_name'] = (isset($provinceInfo[$v['id']]) || isset($cityInfo[$v['id']])) ? $provinceInfo[$v['id']] . $cityInfo[$v['id']] : null;
                        if(in_array($v['uid'],$enterpriseIds)){
                            $recommend[$k]['isEnterprise'] = 1;
                        }else{
                            $recommend[$k]['isEnterprise'] = 0;
                        }
                    }

                }

            }
            return $this->formateResponse(1000, '获取热门店铺信息成功', $recommend);

        } else {
            return $this->formateResponse(1053, '暂无热门店铺信息');
        }
    }


    /**
     * 获取分类下的热门服务商信息
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function serviceByCate(Request $request)
    {
        $cate_id = intval($request->get('cate_id'));
        if (!$cate_id) {
            return $this->formateResponse('1054', '传送数据不能为空');
        }
        $tagInfo = $cateKey = $cateValue = $userKey = $userValue = $serverInfo = [];
        $cateInfo = TaskCateModel::where('pid', $cate_id)->select('id')->get()->toArray();
        if (isset($cateInfo)) {
            $cateInfo = array_flatten($cateInfo);
            $tagInfo = TagsModel::whereIn('cate_id', $cateInfo)->select('id', 'tag_name')->get()->toArray();
        }
        if (count($tagInfo)) {
            foreach ($tagInfo as $k => $v) {
                $cateKey[$k] = $v['id'];
                $cateValue[$v['id']] = $v['tag_name'];
            }
        }
        $userTagRelation = UserTagsModel::whereIn('tag_id', $cateKey)->select('tag_id', 'uid')->get()->toArray();
        if (count($userTagRelation)) {
            foreach ($userTagRelation as $key => $value) {
                $userKey[$key] = $value['uid'];
                $userValue[$value['uid']] = $value['tag_id'];
            }
        }
        $userInfo = UserModel::whereIn('users.id', $userKey)
            ->where('users.status', '<>', 2)
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->select('users.id', 'users.name', 'user_detail.avatar')
            ->paginate()
            ->toArray();
        if ($userInfo['total']) {
            $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
            foreach ($userInfo['data'] as $uKey => $uValue) {
                $userInfo['data'][$uKey]['avatar'] = $userInfo['data'][$uKey]['avatar'] ? $domain->rule . '/' . $userInfo['data'][$uKey]['avatar'] : $userInfo['data'][$uKey]['avatar'];
                $userInfo['data'][$uKey]['tags_id'] = $userValue[$uValue['id']];
                $userInfo['data'][$uKey]['tags'] = $cateValue[$userValue[$uValue['id']]];  //服务商标签
                $comment = CommentModel::where('to_uid', $uValue['id'])->count();
                $goodComment = CommentModel::where('to_uid', $uValue['id'])->where('type', 1)->count();
                if ($comment) {
                    $userInfo['data'][$uKey]['percent'] = number_format($goodComment / $comment, 3) * 100;
                } else {
                    $userInfo['data'][$uKey]['percent'] = 100;
                }
            }
            $serverInfo = $userInfo;
        }
        return $this->formateResponse(1000, '获取类型下的服务商信息成功', $serverInfo);
    }


    /**
     * 获取任务分类，父级分类及其对应的子分类
     * @return \Illuminate\Http\Response
     */
    public function taskCate()
    {
        $parentCate = TaskCateModel::findAll();
        return $this->formateResponse(1000, 'success', $parentCate);
    }

    /**
     * 获取热门任务分类
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function hotCate(Request $request)
    {
        $num = $request->get('limit') ? $request->get('limit') : 6;
        $hotCate = TaskCateModel::hotCate($num);
        return $this->formateResponse(1000, 'success', $hotCate);
    }

    /**
     * 根据任务id查询任务详情信息
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function showTaskDetail(Request $request)
    {
        $id = intval($request->get('id'));
        $task = TaskModel::findById($id);
        if (!$task) {
            return $this->formateResponse(2001, '未找到与之对应ID的任务信息');
        }
        $task->update(['view_count' => $task->view_count + 1]);
        $task->desc = htmlspecialchars_decode($task->desc);
        $task->bidnum = WorkModel::where('task_id', $id)->where('status', '1')->count();
        if ($task->status == 3) {
            $task->status = 4;
        }
        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            $focusTask = TaskFocusModel::where('uid', $tokenInfo['uid'])->where('task_id', $id)->first();
            if (isset($focusTask)) {
                $task->focused = 1;
            } else {
                $task->focused = 0;
            }
        } else {
            $task->focused = 0;
        }

        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $userInfo = UserModel::select('users.name', 'user_detail.avatar')
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->where('users.id', $task->uid)
            ->first();

        if(isset($userInfo)&&isset($userInfo->name)){
            $task->name = $userInfo->name;
        }else{
            $task->name = null;
        }
        if(isset($userInfo)&&isset($userInfo->name)){
            $task->avatar = $userInfo->avatar ? $domain->rule . '/' . $userInfo->avatar : $userInfo->avatar;
        }else{
            $task->avatar = null;
        }


        //获取任务附件
        $arrAttachmentIDs = TaskAttachmentModel::findByTid($id);
        if (count($arrAttachmentIDs)) {
            $attachment = AttachmentModel::findByIds($arrAttachmentIDs);

            if (isset($attachment)) {
                foreach ($attachment as $k => $v) {
                    $attachment[$k]['url'] = $attachment[$k]['url'] ? $domain->rule . '/' . $attachment[$k]['url'] : $attachment[$k]['url'];
                }
            }
        } else {
            $attachment = '';
        }
        //获取任务对应的投稿稿件
        $work = WorkModel::select('work.*', 'users.id as uid', 'users.name as nickname', 'user_detail.avatar')
            ->where('work.task_id', $id)
            ->where('work.status', '<', 2)
            ->leftjoin('users', 'users.id', '=', 'work.uid')
            ->leftjoin('user_detail', 'user_detail.uid', '=', 'work.uid')
            ->get()->toArray();
        if (count($work)) {
            foreach ($work as $k => $v) {
                if ($v['status'] == 1) {
                    $agreeInfo = WorkModel::where('status', '>=', 2)->where('task_id', $v['task_id'])->where('uid', $v['uid'])->select('id', 'status')->first();
                    if (isset($agreeInfo)) {
                        if ($agreeInfo->status == 2) {
                            $work[$k]['agreeStatus'] = 1;
                        } elseif ($agreeInfo->status == 3) {
                            $work[$k]['agreeStatus'] = 2;
                        } else {
                            $work[$k]['agreeStatus'] = 3;
                        }

                    } else {
                        $work[$k]['agreeStatus'] = 0;
                    }
                } else {
                    $work[$k]['agreeStatus'] = 0;
                }
                $work[$k]['avatar'] = $work[$k]['avatar'] ? $domain->rule . '/' . $work[$k]['avatar'] : $work[$k]['avatar'];
                $comment = CommentModel::where('to_uid', $v['uid'])->count();
                $goodComment = CommentModel::where('to_uid', $v['uid'])->where('type', 1)->count();
                if ($comment) {
                    $work[$k]['percent'] = number_format($goodComment / $comment, 3) * 100;
                } else {
                    $work[$k]['percent'] = 100;
                }
            }
        }
        $task->attachment = $attachment;
        $task->workInfo = $work;
        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            if (empty($work)) {
                $mywork = null;
            } else {
                foreach ($work as $wk => $wv) {
                    if ($tokenInfo['uid'] == $wv['uid']) {
                        $mywork = $wv;
                        break;
                    }
                }
                if (!isset($mywork)) {
                    $mywork = null;
                }
            }
            switch ($task->status) {
                case '4':
                    if (empty($mywork)) {
                        $task->buttonStatus = 1;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 3;
                        } else {
                            $task->buttonStatus = 4;
                        }

                    }
                    break;
                case '5':
                    if (empty($mywork)) {
                        $task->buttonStatus = 2;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 3;
                        } else {
                            $task->buttonStatus = 4;
                        }

                    }
                    break;
                case '6':
                    if (empty($mywork)) {
                        $task->buttonStatus = 2;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 5;
                        } else {
                            $task->buttonStatus = 4;
                        }

                    }
                    break;
                case '7':
                    if (empty($mywork)) {
                        $task->buttonStatus = 2;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 5;
                        } else {
                            switch ($mywork['agreeStatus']) {
                                case '0':
                                    $task->buttonStatus = 6;
                                    break;
                                case '1':
                                    $task->buttonStatus = 7;
                                    break;
                                case '2':
                                    $workerComment = CommentModel::where('task_id', $task->id)
                                        ->where('from_uid', $tokenInfo['uid'])
                                        ->where('to_uid', $task->uid)
                                        ->where('comment_by', 0)
                                        ->first();
                                    if (isset($workerComment)) {
                                        $buyerComment = CommentModel::where('task_id', $task->id)
                                            ->where('from_uid', $task->uid)
                                            ->where('to_uid', $tokenInfo['uid'])
                                            ->where('comment_by', 1)
                                            ->first();
                                        if (isset($buyerComment)) {
                                            $task->buttonStatus = 10;
                                        } else {
                                            $task->buttonStatus = 9;
                                        }

                                    } else {
                                        $task->buttonStatus = 8;
                                    }
                                    break;
                                case '3':
                                    $task->buttonStatus = 11;
                                    break;
                            }
                        }

                    }
                    break;
                case '8':
                    if (empty($mywork)) {
                        $task->buttonStatus = 2;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 5;
                        } else {
                            switch ($mywork['agreeStatus']) {
                                case '0':
                                    $task->buttonStatus = 13;
                                    break;
                                case '2':
                                    $workerComment = CommentModel::where('task_id', $task->id)
                                        ->where('from_uid', $tokenInfo['uid'])
                                        ->where('to_uid', $task->uid)
                                        ->where('comment_by', 0)
                                        ->first();
                                    if (isset($workerComment)) {
                                        $buyerComment = CommentModel::where('task_id', $task->id)
                                            ->where('from_uid', $task->uid)
                                            ->where('to_uid', $tokenInfo['uid'])
                                            ->where('comment_by', 1)
                                            ->first();
                                        if (isset($buyerComment)) {
                                            $task->buttonStatus = 10;
                                        } else {
                                            $task->buttonStatus = 9;
                                        }

                                    } else {
                                        $task->buttonStatus = 8;
                                    }
                                    break;
                                case '3':
                                    $task->buttonStatus = 11;
                                    break;
                            }
                        }

                    }
                    break;
                case '9':
                    if (empty($mywork)) {
                        $task->buttonStatus = 12;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 12;
                        } else {
                            switch ($mywork['agreeStatus']) {
                                case '0':
                                    $task->buttonStatus = 12;
                                    break;
                                case '2':
                                    $task->buttonStatus = 10;
                                    break;
                                case '3':
                                    $task->buttonStatus = 11;
                                    break;
                            }
                        }

                    }
                    break;
                case '10':
                    if (empty($mywork)) {
                        $task->buttonStatus = 12;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 12;
                        } else {
                            switch ($mywork['agreeStatus']) {
                                case '0':
                                    $task->buttonStatus = 12;
                                    break;
                                case '3':
                                    $task->buttonStatus = 11;
                                    break;
                            }
                        }

                    }
                    break;
                case '11':
                    if (empty($mywork)) {
                        $task->buttonStatus = 12;
                    } else {
                        if ($mywork['status'] == 0) {
                            $task->buttonStatus = 12;
                        } else {
                            $task->buttonStatus = 11;
                        }

                    }
                    break;

            }
        } else {
            $task->buttonStatus = 0;
        }


        return $this->formateResponse(1000, 'success', $task);
    }

    /**
     * 稿件详情
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function showWorkDetail(Request $request)
    {
        $work = WorkModel::select('work.*', 'users.name as nickname', 'user_detail.avatar')
            ->where('work.id', intval($request->get('id')))
            ->leftjoin('users', 'users.id', '=', 'work.uid')
            ->leftjoin('user_detail', 'user_detail.uid', '=', 'work.uid')
            ->first();

        if (!$work) {
            return $this->formateResponse(2001, '未找到对应ID的稿件信息');
        }
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        if ($work->status == 1) {
            $agreeInfo = WorkModel::where('status', '>=', 2)->where('task_id', $work->task_id)->where('uid', $work->uid)->select('id', 'status')->first();
            if (isset($agreeInfo)) {
                if ($agreeInfo->status == 2) {
                    $work->agreeStatus = 1;

                } elseif ($agreeInfo->status == 3) {
                    $work->agreeStatus = 2;
                } else {
                    $work->agreeStatus = 3;
                }

            } else {
                $work->agreeStatus = 0;
            }
        } else {
            $work->agreeStatus = 0;
        }

        $taskInfo = TaskModel::select('users.name', 'task.status', 'task.title', 'user_detail.avatar as headPic')
            ->leftjoin('users', 'task.uid', '=', 'users.id')
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->where('task.id', $work->task_id)
            ->first();
        if (isset($taskInfo)) {
            $work->taskStatus = $taskInfo->status;
            $work->name = $taskInfo->name;
            $work->taskName = $taskInfo->title;
            $work->headPic = $taskInfo->headPic ? $domain->rule . '/' . $taskInfo->headPic : $taskInfo->headPic;
        } else {
            $work->taskStatus = null;
            $work->name = null;
            $work->taskName = null;
            $work->headPic = null;
        }
        //获取稿件附件
        $arrWorkIds = WorkAttachmentModel::findById($work->id);

        $work->avatar = $work->avatar ? $domain->rule . '/' . $work->avatar : $work->avatar;
        if (count($arrWorkIds)) {
            $attachment = AttachmentModel::findByIds($arrWorkIds);
            if (isset($attachment)) {
                foreach ($attachment as $k => $v) {
                    $attachment[$k]['url'] = $attachment[$k]['url'] ? $domain->rule . '/' . $attachment[$k]['url'] : $attachment[$k]['url'];
                }
            }
        } else {
            $attachment = '';
        }

        //好评率
        $work->applauseRate = \CommonClass::applauseRate($work->uid);
        //完成任务数
        $complete = WorkModel::leftjoin('task', 'task.id', '=', 'work.task_id')
            ->leftjoin('users', 'users.id', '=', 'work.uid')
            ->where('work.status', 3)
            ->where('task.status', 9)
            ->where('work.uid', $work->uid)
            ->count();
        $work->complete = $complete;
        $work->attachment = $attachment;

        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt(urldecode($request->get('token')));
            switch ($work->taskStatus) {
                case '4':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 2;
                            break;
                        case '1':
                            $work->buttonStatus = 3;
                            break;
                    }
                    break;
                case '5':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 2;
                            break;
                        case '1':
                            $work->buttonStatus = 3;
                            break;
                    }
                    break;
                case '6':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 1;
                            break;
                        case '1':
                            $work->buttonStatus = 3;
                            break;
                    }
                    break;
                case '7':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 1;
                            break;
                        case '1':
                            switch ($work->agreeStatus) {
                                case '0':
                                    $work->buttonStatus = 4;
                                    break;
                                case '1':
                                    $work->buttonStatus = 5;
                                    break;
                                case '2':
                                    $buyerComment = CommentModel::where('task_id', $work->task_id)
                                        ->where('from_uid', $tokenInfo['uid'])
                                        ->where('to_uid', $work->uid)
                                        ->where('comment_by', 1)
                                        ->first();
                                    if (isset($buyerComment)) {
                                        $workerComment = CommentModel::where('task_id', $work->task_id)
                                            ->where('from_uid', $work->uid)
                                            ->where('to_uid', $tokenInfo['uid'])
                                            ->where('comment_by', 0)
                                            ->first();
                                        if (isset($workerComment)) {
                                            $work->buttonStatus = 8;
                                        } else {
                                            $work->buttonStatus = 6;
                                        }

                                    } else {
                                        $work->buttonStatus = 7;
                                    }
                                    break;
                                case '3':
                                    $work->buttonStatus = 10;
                                    break;

                            }
                            break;

                    }
                    break;
                case '8':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 1;
                            break;
                        case '1':
                            switch ($work->agreeStatus) {
                                case '0':
                                    $work->buttonStatus = 9;
                                    break;
                                case '2':
                                    $buyerComment = CommentModel::where('task_id', $work->task_id)
                                        ->where('from_uid', $tokenInfo['uid'])
                                        ->where('to_uid', $work->uid)
                                        ->where('comment_by', 1)
                                        ->first();
                                    if (isset($buyerComment)) {
                                        $workerComment = CommentModel::where('task_id', $work->task_id)
                                            ->where('from_uid', $work->uid)
                                            ->where('to_uid', $tokenInfo['uid'])
                                            ->where('comment_by', 0)
                                            ->first();
                                        if (isset($workerComment)) {
                                            $work->buttonStatus = 8;
                                        } else {
                                            $work->buttonStatus = 6;
                                        }

                                    } else {
                                        $work->buttonStatus = 7;
                                    }
                                    break;
                                case '3':
                                    $work->buttonStatus = 10;
                                    break;
                            }
                            break;
                    }
                    break;
                case '9':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 1;
                            break;
                        case '1':
                            switch ($work->agreeStatus) {
                                case '0':
                                    $work->buttonStatus = 9;
                                    break;
                                case '2':
                                    $work->buttonStatus = 8;
                                    break;
                                case '3':
                                    $work->buttonStatus = 10;
                                    break;
                            }
                            break;
                    }
                    break;
                case '10':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 1;
                            break;
                        case '1':
                            switch ($work->agreeStatus) {
                                case '0':
                                    $work->buttonStatus = 1;
                                    break;
                                case '3':
                                    $work->buttonStatus = 10;
                                    break;
                            }
                            break;
                    }
                    break;
                case '11':
                    switch ($work->status) {
                        case '0':
                            $work->buttonStatus = 11;
                            break;
                        case '1':
                            $work->buttonStatus = 10;
                            break;
                    }
                    break;
            }
        } else {
            $work->buttonStatus = 0;
        }

        return $this->formateResponse(1000, 'success', $work);
    }

    /**
     * 获取省市区信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function district(Request $request)
    {
        $area_data = DistrictModel::where('upid', 0)->select('id', 'upid', 'name', 'spelling')->get()->toArray();
        if (empty($area_data)) {
            return $this->formateResponse(2002, '暂无省份信息');
        }
        $province = [];
        $province = array_filter(array_flatten($area_data));
        $city = DistrictModel::whereIn('upid', $province)->select('id', 'name', 'upid', 'spelling')->get()->toArray();
        if (empty($city)) {
            return $this->formateResponse(2003, '暂无城市信息');
        }
        foreach ($area_data as $pk => $pv) {
            foreach ($city as $ck => $cv) {
                if ($pv['id'] == $cv['upid']) {
                    $area_data[$pk]['child'][] = $cv;
                }
            }
        }
        return $this->formateResponse(1000, '获取地区信息成功', $area_data);
    }

    /**
     * 获取服务商列表信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function serviceList(Request $request)
    {
        $userInfo = userModel::where('users.status', '<>', 2)
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid');
        if ($request->get('name')) {
            $userInfo = $userInfo->where('users.name', 'like', '%' . $request->get('name') . '%');
        }
        if ($request->get('type')) {
            if ($request->get('type') == '1') {
                $userInfo = $userInfo->orderBy('users.created_at', 'desc');//按照最新排序
            } else {
                $userInfo = $userInfo->orderBy('user_detail.employee_praise_rate', 'desc');//按照好评量排序
            }
        }
        if ($request->get('category')) {
            $category = intval($request->get('category'));
            $category_data = TaskCateModel::findById($category);
            //检查二级
            if ($category_data['pid'] == 0) {
                return $this->formateResponse('1065', '筛选失败，不能直接筛选一级！');
            }
            //查询tagid
            $tag_ids = TagsModel::where('cate_id', $category_data['id'])->first();
            //查询用户id
            $user_ids = UserTagsModel::where('tag_id', $tag_ids['id'])->lists('uid');
            $userInfo = $userInfo->whereIn('users.id', $user_ids);
        }
        $userInfo = $userInfo->select('users.id', 'users.name', 'user_detail.avatar')->paginate()->toArray();
        if ($userInfo['total']) {
            $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();//获取域名信息
            foreach ($userInfo['data'] as $k => $v) {
                $userInfo['data'][$k]['avatar'] = $userInfo['data'][$k]['avatar'] ? $domain->rule . '/' . $userInfo['data'][$k]['avatar'] : $userInfo['data'][$k]['avatar'];
                $userTagRelation = UserTagsModel::where('uid', $v['id'])->select('tag_id')->get()->toArray();
                if (count($userTagRelation)) {
                    $tagId = array_unique(array_flatten($userTagRelation));
                    $tagNameInfo = TagsModel::whereIn('id', $tagId)->select('tag_name')->get()->toArray();
                    $tagName = array_unique(array_flatten($tagNameInfo));
                    $userInfo['data'][$k]['tags'] = $tagName;
                } else {
                    $userInfo['data'][$k]['tags'] = [];
                }

                $comment = CommentModel::where('to_uid', $v['id'])->count();
                $goodComment = CommentModel::where('to_uid', $v['id'])->where('type', 1)->count();
                if ($comment) {
                    $userInfo['data'][$k]['percent'] = number_format($goodComment / $comment, 3) * 100;
                } else {
                    $userInfo['data'][$k]['percent'] = 100;
                }
            }
        }
        return $this->formateResponse(1000, '获取服务商列表信息成功', $userInfo);

    }

    /**
     * 获取雇主的我的信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function buyerInfo(Request $request)
    {
        // if($request->input('token')){
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $userInfo = UserModel::leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')->where('users.id', $tokenInfo['uid'])->select('users.name as nickname', 'avatar')->first()->toArray();
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $userInfo['avatar'] = $userInfo['avatar'] ? $domain->rule . '/' . $userInfo['avatar'] : $userInfo['avatar'];
        $taskNum = TaskModel::where('uid', $tokenInfo['uid'])->count();
        $userInfo['taskNum'] = $taskNum;
        $speedScore = CommentModel::where('to_uid', $tokenInfo['uid'])->where('comment_by', 0)->avg('speed_score');
        $qualityScore = CommentModel::where('to_uid', $tokenInfo['uid'])->where('comment_by', 0)->avg('quality_score');
        $speedScore = number_format($speedScore, 1);
        $qualityScore = number_format($qualityScore, 1);
        $userInfo['speed_score'] = $speedScore != 0.0 ? $speedScore : 5.0;
        $userInfo['attitude_score'] = $qualityScore != 0.0 ? $qualityScore : 5.0;
        /*}
        else{
            $userInfo = [];
            $userInfo['nickname'] = '游客';
            $userInfo['avatar'] = null;
            $userInfo['taskNum'] = 0;
            $userInfo['speed_score'] = 5.0;
            $userInfo['attitude_score'] = 5.0;
        }*/
        return $this->formateResponse(1000, '获取雇主信息成功', $userInfo);

    }


    /**
     * 获取威客的我的信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function workerInfo(Request $request)
    {
        //if($request->input('token')){
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $userInfo = UserModel::leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')->where('users.id', $tokenInfo['uid'])->select('users.name as nickname', 'avatar')->first()->toArray();
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $userInfo['avatar'] = $userInfo['avatar'] ? $domain->rule . '/' . $userInfo['avatar'] : $userInfo['avatar'];
        $taskNum = WorkModel::where('uid', $tokenInfo['uid'])->where('status', 3)->count();
        $userInfo['taskNum'] = $taskNum;
        $speedScore = CommentModel::where('to_uid', $tokenInfo['uid'])->where('comment_by', 1)->avg('speed_score');
        $qualityScore = CommentModel::where('to_uid', $tokenInfo['uid'])->where('comment_by', 1)->avg('quality_score');
        $attitudeScore = CommentModel::where('to_uid', $tokenInfo['uid'])->where('comment_by', 1)->avg('attitude_score');
        $speedScore = number_format($speedScore, 1);
        $qualityScore = number_format($qualityScore, 1);
        $attitudeScore = number_format($attitudeScore, 1);
        $userInfo['speed_score'] = $speedScore != 0.0 ? $speedScore : 5.0;
        $userInfo['attitude_score'] = $attitudeScore != 0.0 ? $attitudeScore : 5.0;
        $userInfo['quality_score'] = $qualityScore != 0.0 ? $qualityScore : 5.0;
        /*}
        else{
            $userInfo = [];
            $userInfo['nickname'] = '游客';
            $userInfo['avatar'] = null;
            $userInfo['taskNum'] = 0;
            $userInfo['speed_score'] = 5.0;
            $userInfo['attitude_score'] = 5.0;
            $userInfo['quality_score'] = 5.0;
        }*/
        return $this->formateResponse(1000, '获取威客信息成功', $userInfo);

    }


    /**
     * 意见反馈
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function feedbackInfo(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $validator = Validator::make($request->all(), [
            'desc' => 'required|max:255'
        ],
            [
                'desc.required' => '请输入投诉建议',
                'desc.max' => '投诉建议字数超过限制'
            ]);
        $error = $validator->errors()->all();
        if (count($error)) {
            return $this->formateResponse(1001, '输入信息有误', $error);
        }
        $newdata = [
            'desc' => $request->get('desc'),
            'created_time' => date('Y-m-d h:i:s', time()),
            'uid' => $tokenInfo['uid']
        ];
        $userInfo = UserModel::where('users.id', $tokenInfo['uid'])
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->select('user_detail.mobile')
            ->first();
        if (isset($userInfo)) {
            $newdata['phone'] = $userInfo->mobile;
        }
        $res = FeedbackModel::create($newdata);
        if ($res) {
            return $this->formateResponse(1000, '反馈意见提交成功');
        }
        return $this->formateResponse(1060, '反馈意见提交失败');
    }


    /**
     * 帮助中心
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function helpCenter(Request $request)
    {
        $categoryInfo = ArticleCategoryModel::where('cate_name', '常见问题')->select('id')->first();
        if (isset($categoryInfo)) {
            $category = ArticleCategoryModel::where('pid', $categoryInfo->id)->select('id')->get()->toArray();
            if (count($category)) {
                $category = array_flatten($category);
                $articleInfo = ArticleModel::whereIn('cat_id', $category)->select('title', 'content')->paginate()->toArray();
                if (!$articleInfo['total']) {
                    $articleInfo = [];
                } else {
                    foreach ($articleInfo['data'] as $k => $v) {
                        $articleInfo['data'][$k]['content'] = htmlspecialchars_decode($v['content']);
                    }
                }
            } else {
                $articleInfo = [];
            }
        } else {
            $articleInfo = [];
        }

        return $this->formateResponse(1000, '获取帮助中心信息成功', $articleInfo);
    }


    /**
     * 威客详情
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function workerDetail(Request $request)
    {
        if (!$request->get('id')) {
            return $this->formateResponse(1061, '传送参数不能为空');
        }
        $tagName = $userInfo = [];
        $domain = ConfigModel::where('alias', 'site_url')->where('type', 'site')->select('rule')->first();
        $userDetail = UserModel::select('users.name as nickname', 'user_detail.avatar')
            ->leftjoin('user_detail', 'users.id', '=', 'user_detail.uid')
            ->where('users.id', intval($request->get('id')))
            ->first();
        if ($request->get('token')) {
            $tokenInfo = Crypt::decrypt($request->get('token'));
            $userFocus = UserFocusModel::where('uid', $tokenInfo['uid'])->where('focus_uid', intval($request->get('id')))->first();
            if (isset($userFocus)) {
                $userInfo['focused'] = 1;
            } else {
                $userInfo['focused'] = 0;
            }
        } else {
            $userInfo['focused'] = 0;
        }

        if (!isset($userDetail)) {
            return $this->formateResponse(1062, '传送参数错误');
        }
        $userInfo['nickname'] = $userDetail->nickname;
        $userInfo['avatar'] = $userDetail->avatar ? $domain->rule . '/' . $userDetail->avatar : $userDetail->avatar;
        $userTagRelation = UserTagsModel::where('uid', intval($request->get('id')))->select('tag_id')->get()->toArray();
        if (count($userTagRelation)) {
            $tagId = array_unique(array_flatten($userTagRelation));
            $tagNameInfo = TagsModel::whereIn('id', $tagId)->select('tag_name')->get()->toArray();
            $tagName = array_unique(array_flatten($tagNameInfo));
        }
        $userInfo['tagName'] = $tagName;

        $comment = CommentModel::where('to_uid', $request->get('id'))->count();
        $goodComment = CommentModel::where('to_uid', $request->get('id'))->where('type', 1)->count();
        if ($comment) {
            $userInfo['percent'] = number_format($goodComment / $comment, 3) * 100;
        } else {
            $userInfo['percent'] = 0;
        }
        $taskNum = WorkModel::where('uid', $request->get('id'))->where('status', 3)->count();
        $userInfo['taskNum'] = $taskNum;
        $commentInfo = CommentModel::where('to_uid', $request->get('id'))->where('comment_by', 1)->select('speed_score', 'quality_score', 'attitude_score')->first();
        if (isset($commentInfo)) {
            $userInfo['speed_score'] = $commentInfo->speed_score;
            $userInfo['attitude_score'] = $commentInfo->attitude_score;
            $userInfo['quality_score'] = $commentInfo->quality_score;
        } else {
            $userInfo['speed_score'] = 5.0;
            $userInfo['attitude_score'] = 5.0;
            $userInfo['quality_score'] = 5.0;
        }
        $caseInfo = SuccessCaseModel::where('uid', intval($request->get('id')))->select('*')->get()->toArray();
        if (count($caseInfo)) {
            foreach ($caseInfo as $k => $v) {
                $caseInfo[$k]['pic'] = $caseInfo[$k]['pic'] ? $domain->rule . '/' . $caseInfo[$k]['pic'] : $caseInfo[$k]['pic'];
                $caseInfo[$k]['desc'] = htmlspecialchars_decode($caseInfo[$k]['desc']);
            }
        }
        $userInfo['caseInfo'] = $caseInfo;
        return $this->formateResponse(1000, '获取威客信息成功', $userInfo);
    }

    /**
     * 检查是否需要修改密码
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function passwordCheck(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $userInfo = UserModel::where('id', $tokenInfo['uid'])->select('password', 'alternate_password')->first();
        if (!isset($userInfo)) {
            return $this->formateResponse(1062, '传送参数错误');
        }
        $status = 0;
        if ($userInfo->password == $userInfo->alternate_password) {
            $status = 1;
        }
        return $this->formateResponse(1000, '获取状态成功', ['status' => $status]);
    }

    /**
     *用户余额和每日提现金额查询
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function moneyConfig(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $userInfo = UserDetailModel::where('uid', $tokenInfo['uid'])->select('balance')->first();
        if (!isset($userInfo)) {
            return $this->formateResponse(1063, '传送参数错误');
        }
        $config = ConfigModel::getConfigByAlias('cash')->toArray();
        $money = json_decode($config['rule'], true);
        $data = array(
            'balance' => $userInfo->balance,
            'withdrawals' => $money['withdraw_max']
        );
        return $this->formateResponse(1000, '获取金额信息成功', $data);
    }


    /**
     *获取用户充值信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCash(Request $request)
    {
        $tokenInfo = Crypt::decrypt(urldecode($request->input('token')));
        $userInfo = UserDetailModel::select('balance')->where('uid', $tokenInfo['uid'])->first();
        $payConfig = ConfigModel::getConfigByType('thirdpay');

        if (!empty($userInfo)) {
            $data = array(
                'balance' => $userInfo->balance,
                'payConfig' => $payConfig
            );
        }
        return $this->formateResponse(1000, '获取用户充值信息成功', $data);
    }


    /**
     * 获取首页热门任务
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function hotTask(Request $request)
    {
        $reTarget = RePositionModel::where('code', 'APP_HOT_TASK')->where('is_open', '1')->select('id', 'name')->first();
        if ($reTarget->id) {
            $recommend = RecommendModel::where('position_id', $reTarget->id)
                ->where('is_open', 1)
                ->where(function ($recommend) {
                    $recommend->where('end_time', '0000-00-00 00:00:00')
                        ->orWhere('end_time', '>', date('Y-m-d h:i:s', time()));
                })
                ->select('recommend_id')
                ->get()
                ->toArray();
            if (isset($recommend)) {
                $task_id = array_flatten($recommend);
                $recommend = TaskModel::whereIn('task.id', $task_id)
                    ->leftjoin('cate', 'task.cate_id', '=', 'cate.id')
                    ->select('task.id', 'task.title', 'task.view_count', 'task.delivery_count', 'task.created_at', 'task.bounty', 'cate.name', 'task.uid')
                    ->orderBy('task.created_at', 'desc')
                    ->get()
                    ->toArray();
            }
            return $this->formateResponse(1000, '获取热门任务信息成功', $recommend);

        } else {
            return $this->formateResponse(1053, '暂无热门任务信息');
        }
    }


    /**
     * 更新地区表中的拼音字段
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateSpelling()
    {
        $provinceIds = DistrictModel::where('upid', 0)->select('id')->get()->toArray();
        $id = array_flatten($provinceIds);
        $province = DistrictModel::where('upid', 0)->select('id', 'name')->get()->toArray();
        $city = DistrictModel::whereIn('upid', $id)->select('id', 'name')->get()->toArray();
        $area_data = array_merge($province, $city);


        set_time_limit(180);
        $except = [
            '深水埗区', '埇桥区', '浉河区', '浭阳街道', '临洺关镇', '洺州镇', '勍香镇', '牤牛营子乡', '濛江乡', '栟茶镇', '澥浦镇', '浬浦镇', '富堨镇'
        ];
        foreach ($area_data as $k => $v) {
            if (!in_array($v['name'], $except)) {
                $py = \StringHandleClass::encode($v['name'], 'all');
                $py = str_replace(' ', '', trim($py));
                $newSpelling = [//拼音字段
                    'spelling' => $py
                ];
                DistrictModel::where('id', $v['id'])->update($newSpelling);
            }

        }
        return $this->formateResponse(1000, '更新成功');
    }


    /**
     * 获取一级分类下的任务信息
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function taskByCate(Request $request)
    {
        if (!$request->get('cate_id')) {
            return $this->formateResponse(1052, '传送参数不能为空');
        }
        $cate_id = TaskCateModel::where('pid', $request->get('cate_id'))->select('id')->get()->toArray();
        $cate_id = array_flatten($cate_id);
        $reTarget = RePositionModel::where('code', 'TASKDETAIL_SIDE')->where('is_open', '1')->select('id', 'name')->first();
        if ($reTarget->id) {
            $recommend = RecommendModel::where('position_id', $reTarget->id)
                ->where('is_open', 1)
                ->where(function ($recommend) {
                    $recommend->where('end_time', '0000-00-00 00:00:00')
                        ->orWhere('end_time', '>', date('Y-m-d h:i:s', time()));
                })
                ->select('recommend_id')
                ->get()
                ->toArray();
            if (isset($recommend)) {
                $task_id = array_flatten($recommend);
                $recommend = TaskModel::whereIn('task.id', $task_id)
                    ->whereIn('cate.id', $cate_id)
                    ->leftjoin('cate', 'task.cate_id', '=', 'cate.id')
                    ->select('task.id', 'task.title', 'task.view_count', 'task.delivery_count', 'task.created_at', 'task.bounty', 'cate.name', 'task.uid')
                    ->orderBy('task.created_at', 'desc')
                    ->get()
                    ->toArray();
            }
            return $this->formateResponse(1000, '获取分类下的任务信息成功', $recommend);

        } else {
            return $this->formateResponse(1053, '暂无该分类下的任务信息');
        }
    }

    /**
     * 关于我们
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function aboutUs(Request $request)
    {
        $categoryInfo = ArticleCategoryModel::where('cate_name', '关于我们')->select('id')->first();
        if (isset($categoryInfo)) {
            $articleInfo = ArticleModel::where('cat_id', $categoryInfo->id)->select('title', 'content')->first();
            if (!empty($articleInfo)) {
                $articleInfo->content = htmlspecialchars_decode($articleInfo->content);
            } else {
                $articleInfo = [];
            }
        } else {
            $articleInfo = [];
        }

        return $this->formateResponse(1000, '获取关于我们信息成功', $articleInfo);
    }

}