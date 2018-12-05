<?php
namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\IndexController;
use App\Http\Requests;
use App\Modules\Manage\Model\AgreementModel;
use App\Modules\Manage\Model\MessageTemplateModel;
use App\Modules\Task\Http\Requests\CommentRequest;
use App\Modules\Task\Http\Requests\WorkRequest;
use App\Modules\Task\Model\TaskAttachmentModel;
use App\Modules\Task\Model\TaskModel;
use App\Modules\Task\Model\TaskPaySectionModel;
use App\Modules\Task\Model\TaskPayTypeModel;
use App\Modules\Task\Model\TaskReportModel;
use App\Modules\Task\Model\TaskRightsModel;
use App\Modules\Task\Model\TaskServiceModel;
use App\Modules\Task\Model\TaskTypeModel;
use App\Modules\Task\Model\WorkCommentModel;
use App\Modules\Task\Model\WorkModel;
use App\Modules\User\Model\AttachmentModel;
use App\Modules\User\Model\CommentModel;
use App\Modules\User\Model\DistrictModel;
use App\Modules\User\Model\MessageReceiveModel;
use App\Modules\User\Model\UserDetailModel;
use App\Modules\User\Model\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\Modules\Advertisement\Model\AdTargetModel;
use App\Modules\Advertisement\Model\AdModel;
use App\Modules\Advertisement\Model\RePositionModel;
use App\Modules\Advertisement\Model\RecommendModel;
use App\Modules\Manage\Model\ConfigModel;
use Teepluss\Theme\Theme;


class DetailController extends IndexController
{
    public function __construct()
    {
        parent::__construct();
        $this->user = Auth::user();
        $this->initTheme('main');
    }
    /**
     * 任务详情
     * @return \Illuminate\Http\RedirectResponse
     */
	public function index($id,Request $request)
    {
        $this->theme->setTitle('任务详情');

        $data = $request->all();
        //查询任务详情
        $detail = TaskModel::detail($id);
        //搜索引擎屏蔽
        if($detail['engine_status']==1){
            $this->theme->set('engine_status',1);
        }
        //非法访问重定向
        if(is_null($detail)){
            return redirect()->to('task')->with(['error'=>'您访问的任务未托管！']);
        }
        //查询任务模式
        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($detail['type_id']);

        //增加一次任务的访问次数
        TaskModel::where('id',$id)->increment('view_count',1);

        //判断用户的类型是游客还是威客还是雇主
        $user_type = 3;//默认是游客
        $is_win_bid = 0;//默认投稿人没有中标
        $is_delivery = 0;//是否交付
        $is_rights = 0;//是否维权
        $hasBid = 0;
        $delivery_count = 0;
        $works_rights_count = 0;
        $work_data = [];
        //判断当前状态是否需要区别三种角色,登陆之后的
        if($detail['status']>2 && Auth::check())
        {
            //判断当前角色是否是投稿人
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                //判断用户投稿人是否入围
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
                $is_delivery = WorkModel::where('task_id',$id)->where('status','>',1)->where('uid',$this->user['id'])->first();
                $is_rights = WorkModel::where('task_id',$id)->where('status','=',4)->where('uid',$this->user['id'])->first();

            }
            //判断当前的角色是否是发布人,任务角色的优先级最高
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
                $hasBid = WorkModel::where('task_id',$id)->where('status',1)->first();
                if($hasBid){
                    $hasBid = 1;
                }
            }
        }
        $payCaseStatus = 0;//默认没有付款方式
        $paySectionStatus = 0;//默认没有待审核的阶段交付稿件
        if($taskTypeAlias == 'zhaobiao'){
            $payCase = TaskPayTypeModel::where('task_id',$id)->where('status',1)->first();
            if(!empty($payCase)){
                $payCaseStatus = 1;//已经有付款方式
            }
            //查询是否有阶段交付待审核
            $paySection = TaskPaySectionModel::where('task_id',$id)->where('verify_status',0)->where('section_status',1)->first();
            if(!empty($paySection)){
                $paySectionStatus = 1;
            }
        }
        //查询投稿记录
        $works = WorkModel::findAll($id,$data);
        $works_count = WorkModel::where('task_id',$id)->where('status','<=',1)->where('forbidden',0)->count();//投稿记录个数统计
        $works_bid_count = WorkModel::where('task_id',$id)->where('status','=',1)->where('forbidden',0)->count();//中标记录个数统计
        $works_winbid_count = WorkModel::where('task_id',$id)->where('status','=',1)->where('forbidden',0)->count();

        //判断那些稿件需要隐藏
//        if($user_type!=1 && $detail['work_status']==1){
//            $works_show = [];
//            foreach($works['data'] as $k=>$v)
//            {
//                if(Auth::user()['id']==$v['uid'])
//                {
//                    $works_show[] = $v;
//                }
//            }
//            $works['data'] = $works_show;
//        }
        //查询交付记录
            //根据角色判断查询交付内容
        $delivery = [];
        if(Auth::check())
        {
            if($user_type==2)
            {
                if($taskTypeAlias == 'zhaobiao'){
                    $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar','tp.sort','tp.desc as pay_desc')
                        ->where('work.uid',$this->user['id'])
                        ->where('work.task_id',$id)
                        ->where('work.status','>=',2)
                        ->with('childrenAttachment')
                        ->join('user_detail as a','a.uid','=','work.uid')
                        ->leftjoin('users as us','us.id','=','work.uid')
                        ->leftJoin('task_pay_section as tp','tp.work_id','=','work.id')
                        ->paginate(5)->setPageName('delivery_page')->toArray();
                }else{
                    $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar')
                        ->where('work.uid',$this->user['id'])
                        ->where('work.task_id',$id)
                        ->where('work.status','>=',2)
                        ->with('childrenAttachment')
                        ->join('user_detail as a','a.uid','=','work.uid')
                        ->leftjoin('users as us','us.id','=','work.uid')
                        ->paginate(5)->setPageName('delivery_page')->toArray();
                }
                $delivery_count = count($delivery['data']);
            }elseif($user_type==1){
                $delivery = WorkModel::findDelivery($id,$data);
                if($taskTypeAlias == 'zhaobiao'){
                    if(!empty($delivery['data'])){
                        $paySectionWork = TaskPaySectionModel::where('task_id',$id)->where('work_id','!=','')
                            ->select('work_id','sort','desc')->get()->toArray();
                        if(!empty($paySectionWork)){
                            foreach($delivery['data'] as $k => $v){
                                foreach($paySectionWork as $key => $val){
                                    if($v['id'] == $val['work_id']){
                                        $delivery['data'][$k]['sort'] = $val['sort'];
                                        $delivery['data'][$k]['pay_desc'] = $val['desc'];
                                    }
                                }
                            }
                        }
                    }
                }
                $delivery_count = WorkModel::where('task_id',$id)->where('status','>=',2)->count();

            }
        }
        //查询任务评价
        $comment = CommentModel::taskComment($id,$data);

        $comment_count = CommentModel::where('task_id',$id)->count();
        //统计好中差评个数
        $good_comment = CommentModel::where('task_id',$id)->where('type',1)->count();
        $middle_comment = CommentModel::where('task_id',$id)->where('type',2)->count();
        $bad_comment = CommentModel::where('task_id',$id)->where('type',3)->count();
        //查询任务的附件
        $attatchment_ids = TaskAttachmentModel::where('task_id','=',$id)->lists('attachment_id')->toArray();
        $attatchment_ids = array_flatten($attatchment_ids);
        $attatchment = AttachmentModel::whereIn('id',$attatchment_ids)->get();
        //查询相似类型的任务
        $alike_task = TaskModel::findByCate($detail['cate_id'],$id);

        //查询维权稿件
        $works_rights = [];
        if(Auth::check())
        {
            if($user_type==2)
            {
                $works_rights = WorkModel::select('work.*','us.name as nickname','ud.avatar')
                    ->where('work.uid',$this->user['id'])
                    ->where('task_id',$id)->where('work.status',4)
                    ->with('childrenAttachment')
                    ->join('user_detail as ud','ud.uid','=','work.uid')
                    ->leftjoin('users as us','us.id','=','work.uid')
                    ->paginate(5)->setPageName('delivery_page')->toArray();
                $works_rights_count = 1;
            }elseif($user_type==1)
            {
                $works_rights = WorkModel::findRights($id);
                $works_rights_count = WorkModel::where('task_id',$id)->where('status',4)->count();
            }

            if(!empty($works_rights['data'])){
                foreach($works_rights['data'] as $k => $v){
                    $rights = TaskRightsModel::where('task_id',$id)->where('from_uid',$v['uid'])->where('work_id',$v['id'])->first();
                    $works_rights['data'][$k]['rights_desc'] = $rights['desc'];
                }
            }
        }

        $domain = \CommonClass::getDomain();

        //任务详情右侧广告
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');

        //任务详情右侧推荐位
        $reTarget = RePositionModel::where('code','TASKDETAIL_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }
        //文件交付协议
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        $view = [
            'detail'=>$detail,
            'attatchment'=>$attatchment,
            'alike_task'=>$alike_task,
            'user_type'=>$user_type,
            'works'=> $works,
            'file_type'=>'jpg',
            'is_win_bid'=>$is_win_bid,
            'is_delivery'=>$is_delivery,
            'merge'=>$data,
            'delivery'=>$delivery,
            'domain'=>$domain,
            'comment'=>$comment,
            'good_comment'=>$good_comment,
            'middle_comment'=>$middle_comment,
            'bad_comment'=>$bad_comment,
            'works_count'=>$works_count,
            'delivery_count'=>$delivery_count,
            'comment_count'=>$comment_count,
            'works_bid_count'=>$works_bid_count,
            'works_rights'=>$works_rights,
            'works_rights_count'=>$works_rights_count,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'is_rights'=>$is_rights,
            'works_winbid_count'=>$works_winbid_count,
            'agree' => $agree,

            'task_type_alias' => $taskTypeAlias,
            'pay_case_status' => $payCaseStatus,
            'pay_section' => $paySectionStatus,
            'has_bid' => $hasBid
        ];
        //地区查询
        if($detail['region_limit']==1 && $detail['province'] && $detail['city'] && $detail['area'])
        {
            $province = DistrictModel::whereIn('id',[$detail['province'],$detail['city'],$detail['area']])->get()->toArray();
            $province = \CommonClass::keyBy($province,'id');
            $view['area'] = $province;
        }
        return $this->theme->scope('task.detail', $view)->render();
    }

    /**
     * 悬赏竞标投稿页
     * @param $id
     * @return mixed
     */
    public function work($id)
    {
        $this->theme->setTitle('竞标投稿');

        //投稿协议
        $agree = AgreementModel::where('code_name','task_draft')->first();

        $task_data = TaskModel::where('id',$id)->first();

        $view =[
            'task'=>$task_data,
            'agree' => $agree
        ];

        return $this->theme->scope('task.work', $view)->render();
    }
    /**
     *竞标投稿
     * @param Request $request
     */
    public function workCreate(WorkRequest $request)
    {
        $domain = \CommonClass::getDomain();
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        $data['created_at'] = date('Y-m-d H:i:s',time());


        //判断当前用户是否有资格投标
        $is_work_able = $this->isWorkAble($data['task_id']);
        //返回为何不能投标的原因
        if(!$is_work_able['able'])
        {
            return redirect()->back()->with('error',$is_work_able['errMsg']);
        }
        //创建一个新的稿件
        $workModel = new WorkModel();
        $result = $workModel->workCreate($data);

        if(!$result) return redirect()->back()->with('error','投稿失败！');
        //发送系统消息
        //判断当前的任务发布成功之后是否需要发送系统消息
        $task_delivery = MessageTemplateModel::where('code_name','task_delivery')->where('is_open',1)->where('is_on_site',1)->first();
        if($task_delivery)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();//必要条件

            $site_name = \CommonClass::getConfig('site_name');//必要条件
            $user_name = Auth::user()['name'];
            //组织好系统消息的信息
            //发送系统消息
            $messageVariableArr = [
                'username'=>$user['name'],
                'name'=>$user_name,
                'href' => $domain.'/task/'.$data['task_id'],
                'task_title'=>$task['title'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('task_delivery',$messageVariableArr);
            $messages = [
                'message_title'=>$task_delivery['name'],
                'code'=>'task_delivery',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }

    /**
     * 悬赏模式 中标控制器
     */
    public function winBid($work_id,$task_id)
    {
        $data['task_id'] = $task_id;
        $data['work_id'] = $work_id;

        //检查当前选标的人是不是任务的发布者
            //查询任务的发布者
        $task_user = TaskModel::where('id',$task_id)->lists('uid');

        if($task_user[0]!=$this->user['id'])
        {
            return redirect()->back()->with(['error'=>'非法操作,你不是任务的发布者不能选择中标人选！']);
        }
        //判断当前的任务的入围人数是否用完
        $worker_num = TaskModel::where('id',$task_id)->lists('worker_num');
        //当前任务的入围人数统计
        $win_bid_num = WorkModel::where('task_id',$task_id)->where('status',1)->count();

        //判断当前是否可以选择中标
        if($worker_num[0]>$win_bid_num)
        {
            $data['worker_num'] = $worker_num[0];
            $data['win_bid_num'] = $win_bid_num;
            $workModel = new WorkModel();
            $result = $workModel->winBid($data);

            if(!$result) return redirect()->back()->with(['error'=>'操作失败！']);
        }else{
            return redirect()->back()->with(['error'=>'当前中标人数已满！']);
        }

        return redirect()->back()->with(['massage'=>'选稿成功！']);
    }

    /**
     *交稿页面
     */
    public function delivery($id)
    {
        $this->theme->setTitle('交付稿件');

        $task_data = TaskModel::where('id',$id)->first();

        //任务竞标投稿右侧广告
        $ad = AdTargetModel::getAdInfo('TASKDELIVERY_RIGHT_BUTTOM');

        //交付协议
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        //任务竞标投稿右侧推荐位
        $reTarget = RePositionModel::where('code','TASKDELIVERY_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }

        $view =[
            'task'=>$task_data,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'agree' => $agree

        ];

        return $this->theme->scope('task.delivery', $view)->render();
    }

    /**
     * 交付稿件提交
     */
    public function deliverCreate(WorkRequest $request)
    {
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        $data['status'] = 2;//表示用户
        $data['created_at'] = date('Y-m-d H:i:s',time());
        //判断数据合法性
        if(empty($data['task_id']) || empty($data['work_id']))
        {
            return redirect()->back()->with(['error'=>'投稿失败']);
        }
        //判断当前用户是否有验收投稿资格
        if(!WorkModel::isWinBid($data['task_id'],$this->user['id']))
        {
            return redirect()->back()->with('error','您的稿件没有中标不能通过交付！');
        }
        $is_delivery = WorkModel::where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->where('status','>=',2)->first();


        //判断当前用户是否已经交付
        if($is_delivery)
        {
            return redirect()->back()->with('error','您已经交付过了！');
        }

        $result = WorkModel::delivery($data);

        if(!$result) return redirect()->back()->with('error','交付失败！');
        //发送系统消息
        //判断当前的任务发布成功之后是否需要发送系统消息
        $agreement_documents = MessageTemplateModel::where('code_name','agreement_documents')->where('is_open',1)->where('is_on_site',1)->first();
        if($agreement_documents)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();//必要条件
            $site_name = \CommonClass::getConfig('site_name');//必要条件
            $user_name = $this->user['name'];
            $domain = \CommonClass::getDomain();
            //组织好系统消息的信息
            //发送系统消息
            $messageVariableArr = [
                'username'=>$user['name'],
                'initiator'=>$user_name,
                'agreement_link'=>$domain.'/task/'.$task['id'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('agreement_documents',$messageVariableArr);
            $messages = [
                'message_title'=>$agreement_documents['name'],
                'code'=>'agreement_documents',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }

    /**
     * 稿件通过验收 悬赏模式
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function workCheck(Request $request)
    {
        $data = $request->except('_token');
        $data['work_status'] = 3;
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        $data['uid'] = $work_data['uid'];
        //验证用户是否是雇主
        if(!TaskModel::isEmployer($work_data['task_id'],$this->user['id']))
        return redirect()->back()->with(['error'=>'您不是雇主，您的操作有误！']);
        //验证当前稿件是否符合验收标准

        if($work_data['status']!=2)
        return redirect()->back()->with(['error'=>'当前稿件不具备验收资格！']);
        //查询任务需要的人数
        $worker_num = TaskModel::where('id',$work_data['task_id'])->first();
        $worker_num = $worker_num['worker_num'];
        //任务验收通过人数
        $win_check = WorkModel::where('work.task_id',$work_data['task_id'])->where('status','>',2)->count();

        $data['worker_num'] = $worker_num;
        $data['win_check'] = $win_check;
        $data['task_id'] = $work_data['task_id'];

        $workModel = new WorkModel();
        $result = $workModel->workCheck($data);
        if(!$result) return redirect()->back()->with(['error'=>'验收失败！']);

        if($result) return redirect()->to('task/'.$data['task_id'])->with(['manage'=>'验收成功！']);
    }
    /**
     * 稿件验收失败
     */
    public function lostCheck(Request $request)
    {
        $data = $request->except('_token');
        $data['work_status'] = 4;
        //验证用户是否是雇主
        if(!TaskModel::isEmployer($data['task_id'],$this->user['id']))
            return response()->json(['errCode'=>0,'error'=>'您不是雇主，您的操作有误！']);
        //验证当前稿件是否符合验收标准
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        if($work_data['status']!=2)
            return response()->json(['errCode'=>0,'error'=>'当前稿件不具备验收资格！']);

        $workModel = new WorkModel();
        $result = $workModel->lostCheck($data);
        if(!$result) return response()->back()->with('error','验收失败！');
        //刷新页面
        return response()->json(['errCode'=>1,'id'=>$data['work_id']]);
    }
    /**
     * 判断当前用户是否有投稿的资格,便于扩展
     */
    private function isWorkAble($task_id)
    {
        //判断当前任务是否处于投稿期间
        $task_data = TaskModel::where('id',$task_id)->first();
        if($task_data['status']!=(3||4) || strtotime($task_data['begin_at'])>time())
        {
            return ['able' => false, 'errMsg' => '当前任务还未开始投稿！'];
        }
        //判断当前用户是否登录
        if (!isset($this->user['id'])) {
            return ['able' => false, 'errMsg' => '请登录后再操作！'];
        }
        //判断用户是否为当前任务的投稿人，如果已经是的，就不能投稿
        if (WorkModel::isWorker($this->user['id'], $task_id)) {
            return ['able' => false, 'errMsg' => '你已经投过稿了'];
        }
        //判断当前用户是否为任务的发布者，如果是用户的发布者，就不能投稿
        if (TaskModel::isEmployer($task_id, $this->user['id']))
        {
            return ['able' => false, 'errMsg'=>'你是任务发布者不能投稿！'];
        }
        return ['able'=>true];
    }

    /**
     * ajax上传附件
     */
    public function ajaxWorkAttatchment(Request $request)
    {
        $file = $request->file('file');
        //将文件上传的数据存入到attachment表中
        $attachment = \FileClass::uploadFile($file,'task');
        $attachment = json_decode($attachment,true);
        //判断文件是否上传
        if($attachment['code']!=200)
        {
            return response()->json(['errCode' => 0, 'errMsg' => $attachment['message']]);
        }
        $attachment_data = array_add($attachment['data'],'status',1);
        $attachment_data['created_at'] = date('Y-m-d H:i:s',time());
        //将记录写入到attchement表中
        $result = AttachmentModel::create($attachment_data);
        $result = json_decode($result,true);

        if(!$result)
        {
            return response()->json(['errCode'=>0,'errMsg'=>'文件上传失败！']);
        }
        //回传附件id
        return response()->json(['id'=>$result['id']]);
    }
    /**
     * 附件删除
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delAttatchment(Request $request)
    {
        $id = $request->get('id');
        $result = AttachmentModel::where('user_id',$this->user['id'])->where('id',$id)->delete();
        if(!$result)
        {
            return response()->json(['errCode'=>0,'errMsg'=>'删除失败！']);
        }
        return response()->json(['errCode'=>1,'errMsg'=>'删除成功！']);
    }

    /**
     * 下载附件
     * @param $id
     */
    public function download($id)
    {
        $pathToFile = AttachmentModel::where('id',$id)->first();
        $pathToFile = $pathToFile['url'];
        return response()->download($pathToFile);
    }

    /**
     * ajax获取稿件的评论
     * @param $id
     */
    public function getComment($id)
    {
        $workComment = WorkCommentModel::where('work_id',$id)
            ->with('parentComment')
            ->with('user')
            ->with('users')
            ->get()->toArray();
        //给头像加绝对路径
        $domain = \CommonClass::getDomain();
        foreach($workComment as $k=>$v)
        {
            $workComment[$k]['avatar_md5'] = $domain.'/'.$v['user']['avatar'];
            $workComment[$k]['nickname'] = $v['users']['name'];
            if(is_array($v['parent_comment']))
            {
                $workComment[$k]['parent_user'] = $v['parent_comment']['nickname'];
            }
        }
        $data['errCode'] = 1;
        $data['comment'] = $workComment;
        $data['onerror_img'] = \CommonClass::getDomain().'/'.$this->theme->asset()->url('images/defauthead.png');

        return response()->json($data);
    }

    /**
     * 提交回复
     * @param Request $request
     */
    public function ajaxComment(CommentRequest $request)
    {
        $data = $request->except('_token');
        $data['comment'] = htmlspecialchars($data['comment']);
        $data['uid'] = $this->user['id'];
        $user = UserDetailModel::where('uid',$this->user['id'])->first();
        $users = UserModel::where('id',$this->user['id'])->first();
        $data['nickname'] = $users['name'];

        $data['created_at'] = date('Y-m-d H:i:s',time());

        //将数据存入数据库
        $result = WorkCommentModel::create($data);

        if(!$result) return response()->json(['errCode'=>0,'errMsg'=>'提交回复失败！']);
        //查询回复数据
        $comment_data = WorkCommentModel::where('id',$result['id'])->with('parentComment')->with('user')->with('users')->first()->toArray();
        $domain = \CommonClass::getDomain();
        $comment_data['avatar_md5'] = $domain.'/'.$user['avatar'];

        if(is_array($comment_data['parent_comment']))
        {
            $comment_data['parent_user'] = $comment_data['parent_comment']['nickname'];
        }
        $comment_data['errCode'] = 1;
        $comment_data['onerror_img'] = \CommonClass::getDomain().'/'.$this->theme->asset()->url('images/defauthead.png');

        return response()->json($comment_data);
    }

    /**
     * 评价页面
     */
    public function evaluate(Request $request)
    {
        //主题初始化
        $this->theme->setTitle('交易互评');
        $data = $request->all();
        //判断当前评价的人是否具有评价资格
        $is_checked = WorkModel::where('task_id',$data['id'])
            ->where('uid',$this->user['id'])
            ->where('status',3)->first();
        //查询雇主信息
        $task = TaskModel::where('id',$data['id'])->first();

        if(!$is_checked && $task['uid']!=$this->user['id'])
        {
            return redirect()->back()->with('error','你不具备评价资格！');
        }
        //查询相似类型的任务
        $alike_task = TaskModel::findByCate($task['cate_id'],$data['id']);
        //判断是威客评价还是雇主评价
        if($is_checked)
        {
            $evaluate_people = UserDetailModel::select('user_detail.*','us.name as nickname')
                ->where('uid',$task['uid'])
                ->join('users as us','user_detail.uid','=','us.id')
                ->first();
            $work = WorkModel::where('id',$data['work_id'])->first();
            $comment_people = UserDetailModel::where('uid',$work['uid'])->first();
            $evaluate_from = 0;//来自威客的评价
        }else if($task['uid']==$this->user['id'])
        {
            $work = WorkModel::where('id',$data['work_id'])->first();
            $evaluate_people = UserDetailModel::select('user_detail.*','us.name as nickname')
                ->where('uid',$work['uid'])
                ->join('users as us','user_detail.uid','=','us.id')
                ->first();
            $comment_people = UserDetailModel::where('uid',$task['uid'])->first();
            $evaluate_from = 1;//来自雇主的评价
        }
        $domain = \CommonClass::getDomain();

        //任务详情右侧广告
        $ad = AdTargetModel::getAdInfo('TASKINFO_RIGHT');

        //任务详情右侧推荐位
        $reTarget = RePositionModel::where('code','TASKDETAIL_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }
            else{
                $hotList = [];
            }
        }
        //查询威客信息
        $view = [
            'evaluate_people'=>$evaluate_people,
            'task_id'=>$data['id'],
            'work_id'=>$data['work_id'],
            'domain'=>$domain,
            'comment_people'=>$comment_people,
            'evaluate_from'=>$evaluate_from,
            'alike_task'=>$alike_task,
            'hoteList'=>$hotList,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name
        ];

        return $this->theme->scope('task.evaluate', $view)->render();
    }


    /**
     * 交易评论
     */
    public function evaluateCreate(Request $request)
    {
        $data = $request->except('token');

        //判断当前评价的人是否具有评价资格
        $is_checked = WorkModel::where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->where('status',3)->first();
        //查询雇主信息
        $task = TaskModel::where('id',$data['task_id'])->first();

        if(!$is_checked && $task['uid']!=$this->user['id']){
            return redirect()->back()->with('error','你不具备评价资格！');
        }
        //保存评论数据
        $data['from_uid'] = $this->user['id'];
        $data['comment'] = e($data['comment']);
        $data['created_at'] = date('Y-m-d H:i:s',time());
        //评论雇主
        if($is_checked) {
            $data['to_uid'] = $task['uid'];
            $data['comment_by'] = 0;
        }else if($task['uid']==$this->user['id']) {
            $work = WorkModel::where('id',$data['work_id'])->first();
            $data['to_uid'] = $work['uid'];
            $data['comment_by'] = 1;
        }

        $is_evaluate =  CommentModel::where('from_uid',$this->user['id'])
            ->where('task_id',$data['task_id'])->where('to_uid',$data['to_uid'])
            ->first();

        if($is_evaluate){
            return redirect()->back()->with(['error'=>'你已经评论过了！']);
        }


        $result = CommentModel::commentCreate($data);

        if(!$result) {
            return redirect()->back()->with('error','评论失败！');
        }

        return redirect()->to('task/'.$data['task_id'])->with('massage','评论成功！');
    }

    /**
     * 交易维权提交 悬赏模式
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ajaxRights(Request $request)
    {
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $data['status'] = 0;
        $data['created_at'] = date("Y-m-d H:i:s", time());
        $work = WorkModel::where('id',$data['work_id'])->first();
        if($work['status']==4)
        {
            return redirect()->back()->with(['error'=>'当前稿件正在维权']);
        }
        //判断当前评价的人是否具有评价资格
        $is_checked = WorkModel::where('id',$data['work_id'])
            ->where('status',2)
            ->where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->first();
        //查询雇主信息
        $task = TaskModel::where('id',$data['task_id'])->first();
        //验证是否维权过
        if(!$is_checked && $task['uid']!=$this->user['id'])
        {
            return redirect()->back()->with(['error'=>'你不具备维权资格！']);
        }
        //判断当前维权的是雇主还是威客
        if($is_checked)
        {
            $data['role'] = 0;
            $data['from_uid'] = $this->user['id'];
            $data['to_uid'] = $task['uid'];
        }else if($task['uid']==$this->user['id'])
        {
            $data['role'] = 1;
            $data['from_uid']  = $this->user['id'];

            $data['to_uid'] = $work['uid'];
        }
        $result = TaskRightsModel::rightCreate($data);

        if(!$result)
            return redirect()->back()->with(['error'=>'维权失败！']);
        //维权提交完成，发送系统消息
        $trading_rights = MessageTemplateModel::where('code_name','trading_rights')->where('is_open',1)->where('is_on_site',1)->first();
        if($trading_rights)
        {

            $task = TaskModel::where('id',$data['task_id'])->first();
            $from_user = UserModel::where('id',$this->user['id'])->first();//必要条件
            $site_name = \CommonClass::getConfig('site_name');//必要条件
            //组织好系统消息的信息
            //发送系统消息
            $fromMessageVariableArr = [
                'username'=>$from_user['name'],
                'tasktitle'=>$task['title'],
                'website'=>$site_name,
            ];
            $fromMessage = MessageTemplateModel::sendMessage('trading_rights',$fromMessageVariableArr);
            $messages = [
                'message_title'=>$trading_rights['name'],
                'code'=>'trading_rights',
                'message_content'=>$fromMessage,
                'js_id'=>$from_user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id'])->with(['error'=>'维权成功！']);
    }

    /**
     * 举报
     */
    public function report(Request $request)
    {
        $domain = \CommonClass::getDomain();
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $files = $request->file('file');
        //判断当前用户是否举报过此当前投稿
        $is_report = TaskReportModel::where('from_uid',$this->user['id'])
            ->where('task_id',$data['task_id'])
            ->where('work_id',$data['work_id'])
            ->first();

        if($is_report)
        {
            return redirect()->back()->with('error','您已经成功举报过，请等候平台处理!');
        }

        $attachement_ids = [];
        if(!empty($files[0]))
        {
            foreach($files as $v){
                $attachment = \FileClass::uploadFile($v,'task');
                $attachment = json_decode($attachment,true);
                $attachment_data = array_add($attachment['data'],'status',1);
                $attachment_data['created_at'] = date('Y-m-d H:i:s',time());
                //将记录写入到attchement表中
                $result = AttachmentModel::create($attachment_data);
                $attachement_ids[] = $result['id'];
            }
        }
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        //保存举报信息
        $data['status'] = 0;
        $data['from_uid'] = $this->user['id'];
        $data['to_uid'] = $work_data['uid'];
        $data['attachment_ids'] = json_encode($attachement_ids);
        $data['created_at'] = date('Y-m-d H:s:i',time());
        $result2 = TaskReportModel::create($data);
        if(!$result2)
        {
            return redirect()->back()->with('error','举报失败，请联系管理员!');
        }
        //举报成功，发送系统消息
        $task_publish_success = MessageTemplateModel::where('code_name','report')->where('is_open',1)->where('is_on_site',1)->first();
        if($task_publish_success)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $site_name = \CommonClass::getConfig('site_name');//必要条件
            //组织好系统消息的信息
            $messageVariableArr = [
                'username'=>$this->user['name'],
                'href' => $domain.'/task/'.$data['task_id'],
                'task_title'=>$task['title'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('report ',$messageVariableArr);
            $message = [
                'message_title'=>$task_publish_success['name '],
                'code'=>'report',
                'message_content'=>$message,
                'js_id'=>$this->user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($message);
        }
        return redirect()->back()->with('message','举报成功!');
    }

    /**
     * ajax分页投稿内容
     */
    public function ajaxPageWorks($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);

        $taskTypeAlias = TaskTypeModel::getTaskTypeAliasById($detail['type_id']);

        //判断用户的类型是游客还是威客还是雇主
        $user_type = 3;//默认是游客
        $is_win_bid = 0;//默认投稿人没有中标
        //判断当前状态是否需要区别三种角色
        if($detail['status']>2)
        {
            //判断当前角色是否是投稿人
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                //判断用户投稿人是否入围
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
            }
            //判断当前的角色是否是发布人,任务角色的优先级最高
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }

        $works_data = WorkModel::findAll($id,$data);
        $works_count = WorkModel::where('task_id',$id)->where('status','<=',1)->count();//投稿记录个数统计
        $works_bid_count = WorkModel::where('task_id',$id)->where('status','=',1)->count();//中标记录个数统计

        $view = [
            'detail'=>$detail,
            'works'=>$works_data,
            'merge'=>$data,
            'works_count'=>$works_count,
            'works_bid_count'=>$works_bid_count,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'task_type_alias' => $taskTypeAlias
        ];
        return $this->theme->scope('task.pagework', $view)->render();
    }
    //ajax分页交付内容
    public function ajaxPageDelivery($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);

        //判断用户的类型是游客还是威客还是雇主
        $user_type = 3;//默认是游客
        $is_win_bid = 0;//默认投稿人没有中标
        $is_delivery = 0;
        //判断当前状态是否需要区别三种角色
        if($detail['status']>2)
        {
            //判断当前角色是否是投稿人
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                //判断用户投稿人是否入围
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
                $is_delivery = WorkModel::where('task_id',$id)->where('status','>',1)->where('uid',$this->user['id'])->first();
            }
            //判断当前的角色是否是发布人,任务角色的优先级最高
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }

        //查询交付记录
        //查询交付记录
        //根据角色判断查询交付内容
        $delivery = [];
        if(Auth::check())
        {
//          $delivery = WorkModel::findDelivery($id,$data);
//          $delivery_count = WorkModel::where('task_id',$id)->where('status','>=',2)->count();
            if($user_type==2)
            {
                $delivery = WorkModel::select('work.*','us.name as nickname','a.avatar')
                    ->where('work.uid',$this->user['id'])
                    ->where('work.task_id',$id)
                    ->where('work.status','>=',2)
                    ->with('childrenAttachment')
                    ->join('user_detail as a','a.uid','=','work.uid')
                    ->leftjoin('users as us','us.id','=','work.uid')
                    ->paginate(5)->setPageName('delivery_page')->toArray();
                $delivery_count = 1;
            }elseif($user_type==1){
                $delivery = WorkModel::findDelivery($id,$data);
                $delivery_count = WorkModel::where('task_id',$id)->where('status','>=',2)->count();
            }
        }
        $works_data = WorkModel::findAll($id,$data);

        $domain = \CommonClass::getDomain();
        $view = [
            'detail'=>$detail,
            'delivery'=>$delivery,
            'delivery_count'=>$delivery_count,
            'is_delivery'=>$is_delivery,
            'merge'=>$data,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'domain'=>$domain,
            'works'=>$works_data
        ];
        return $this->theme->scope('task.pagedelivery', $view)->render();
    }
    //ajax分页评价
    public function ajaxPageComment($id,Request $request)
    {
        $this->initTheme('ajaxpage');
        $data = $request->all();
        $detail = TaskModel::detail($id);
        $data['task_user_id'] = $detail['uid'];
        //判断用户的类型是游客还是威客还是雇主
        $user_type = 3;//默认是游客
        $is_win_bid = 0;//默认投稿人没有中标
        //判断当前状态是否需要区别三种角色
        if($detail['status']>2)
        {
            //判断当前角色是否是投稿人
            if(WorkModel::isWorker($this->user['id'],$detail['id']))
            {
                $user_type = 2;
                //判断用户投稿人是否入围
                $is_win_bid = WorkModel::isWinBid($id,$this->user['id']);
            }
            //判断当前的角色是否是发布人,任务角色的优先级最高
            if($detail['uid']==$this->user['id'])
            {
                $user_type = 1;
            }
        }
        $delivery = WorkModel::findDelivery($id,$data);
        $works_data = WorkModel::findAll($id,$data);
        //查询任务评价
        $comment = CommentModel::taskComment($id,$data);
        $comment_count = CommentModel::where('task_id',$id)->count();
        //统计好中差评个数
        $good_comment = CommentModel::where('task_id',$id)->where('type',1)->count();
        $middle_comment = CommentModel::where('task_id',$id)->where('type',2)->count();
        $bad_comment = CommentModel::where('task_id',$id)->where('type',3)->count();
        $domain = \CommonClass::getDomain();

        $view = [
            'detail'=>$detail,
            'merge'=>$data,
            'user_type'=>$user_type,
            'is_win_bid'=>$is_win_bid,
            'comment'=>$comment,
            'comment_count'=>$comment_count,
            'good_comment'=>$good_comment,
            'middle_comment'=>$middle_comment,
            'bad_comment'=>$bad_comment,
            'delivery'=>$delivery,
            'domain'=>$domain,
            'works'=>$works_data,
            'merge'=>$data
        ];

        return $this->theme->scope('task.pageComment', $view)->render();
    }
    public function rememberTable(Request $request)
    {
        if($request->get('index'))
        {
            setcookie('table_index',$request->get('index'),time()+3600);
        }else{
            setcookie('table_index',1,time()+3600);
        }
    }

    /**
     * 招标竞标投稿页
     * @param $id 任务id
     * @return mixed
     */
    public function tenderWork($id)
    {
        $this->theme->setTitle('竞标投稿');

        $uid = Auth::id();
        $task = TaskModel::where('id',$id)->whereIn('status',[3,4,5])->first();
        if(empty($task)){
            return redirect('/task')->with(array('message' => '任务不存在或不能投稿'));
        }
        //投稿协议
        $agree = AgreementModel::where('code_name','task_draft')->first();

        $view = [
            'uid' => $uid,
            'task' => $task,
            'agree' => $agree
        ];


        return $this->theme->scope('task.bid.tenderWork',$view)->render();
    }

    /**
     * 招标模式 雇主选中威客
     * @param int $work_id 交稿id
     * @param int $task_id 任务id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bidWinBid($work_id,$task_id)
    {
        $data['task_id'] = $task_id;
        $data['work_id'] = $work_id;

        //检查当前选标的人是不是任务的发布者
        //查询任务的发布者
        $task = TaskModel::where('id',$task_id)->first();

        if($task['uid'] != $this->user['id']){
            return redirect()->back()->with(['error'=>'非法操作,你不是任务的发布者不能选择中标人选！']);
        }
        //当前任务的入围人数统计
        $win_bid_num = WorkModel::where('task_id',$task_id)->where('status',1)->count();

        //判断当前是否可以选择中标
        if($task['worker_num']>$win_bid_num){
            $data['worker_num'] = $task['worker_num'];
            $data['win_bid_num'] = $win_bid_num;
            $result = WorkModel::bidWinBid($data);

            if(!$result) {
                return redirect()->back()->with(['error'=>'操作失败！']);
            }else{
                return redirect('/task/bidBounty/'.$task_id);
            }
        }else{
            return redirect()->back()->with(['error'=>'操作失败！']);
        }

    }


    /**
     * 招标确认付款方式
     * @param int $id 任务id
     * @return mixed
     */
    public function payType($id)
    {
        $this->theme->setTitle('竞标付款方式');
        $task = TaskModel::find($id);
        $taskPayType = TaskPayTypeModel::where('task_id',$id)->first();
        $paySection = TaskPaySectionModel::where('task_id',$id)->get()->toArray();

        $userType = 3;//默认是游客
        $isWinBid = WorkModel::isWinBid($id,$this->user['id']);
        if($isWinBid){
            $userType = 2;//威客
        }
        if($task['uid']==$this->user['id'])
        {
            $userType = 1;
        }
        $view = [
            'task' => $task,
            'pay_type' => $taskPayType,
            'pay_section' => $paySection,
            'user_type' => $userType
        ];

        return $this->theme->scope('task.bid.payType',$view)->render();
    }

    /**
     * 选择支付方式
     * @param Request $request
     * @return array
     */
    public function ajaxPaySection(Request $request)
    {
        $data = $request->all();
        //支付方式
        $payType = [
            1 => '100',
            2 => '50:50',
            3 => '50:30:20',
            4 => '自定义'
        ];

        $type = $data['type'];
        $taskId = $data['task_id'];
        $price = TaskModel::find($taskId)->bounty;
        $pay_type_append = isset($data['pay_type_append']) ? $data['pay_type_append'] : array();

        if ($type == 4) {
            $arrPercent = $pay_type_append;
        } else {
            $arrPercent = explode(':', $payType[$type]);
        }

        $html = TaskPaySectionModel::getPaySectionHtml($arrPercent, $price);
        $result = array();
        if ($html) {
            $result['status'] = 'success';
            $result['html'] = $html;
        } else {
            $result['status'] = 'failure';
        }

        return $result;
    }

    /**
     * 招标模式 雇主保存支付方式
     * @param Request $request
     * @return mixed
     */
    public function postPayType(Request $request)
    {
        $data = $request->except('_token');

        $task = TaskModel::find($data['task_id']);
        if($task['uid'] != $this->user['id']){
            return redirect()->to('/task/'.$data['task_id'])->with(['message' => '没有权限']);
        }
        $status = TaskPayTypeModel::saveTaskPayType($data);
        if($status){
            return redirect()->to('/task/'.$data['task_id']);
        }else{
            return redirect()->to('/task/'.$data['task_id'])->with(['message' => '操作失败']);
        }
    }

    /**
     * 威客操作 是否同意付款方式
     * @param int $taskId 任务id
     * @param int $status 1:同意  2:不同意
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkPayType($taskId,$status)
    {
        $isWinBid = WorkModel::isWinBid($taskId,$this->user['id']);
        if(!$isWinBid){
            return redirect()->to('/task/'.$taskId)->with(['message' => '不是威客,没有权限']);
        }
        $uid = $this->user['id'];
        $status = TaskPayTypeModel::checkTaskPayType($taskId,$status,$uid);
        if($status){
            return redirect()->to('/task/'.$taskId);
        }else{
            return redirect()->to('/task/'.$taskId)->with(['message' => '操作失败']);
        }
    }

    /**
     * 雇主再次编辑支付方式
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function payTypeAgain($id)
    {
        $res = TaskPayTypeModel::where('task_id',$id)->delete();
        $result = TaskPaySectionModel::where('task_id',$id)->delete();
        if($res && $result){
            return redirect('/task/payType/'.$id);
        }
    }


    /**
     * 招标任务 威客交付稿件
     * @param int $id 任务id
     * @return mixed
     */
    public function bidDelivery($id)
    {
        $this->theme->setTitle('交付稿件');

        $task = TaskModel::where('id',$id)->first();

        //任务竞标投稿右侧广告
        $ad = AdTargetModel::getAdInfo('TASKDELIVERY_RIGHT_BUTTOM');

        //查看是第几阶段交稿
        $sort = 1;
        $paySection = TaskPaySectionModel::where('task_id',$id)->orderby('sort','asc')->get()->toArray();
        if(!empty($paySection)){
            foreach($paySection as $k => $v){
                if((!empty($v['work_id']) && $v['verify_status'] == 2) || empty($v['work_id'])){
                    $sort = $v['sort'];
                    break;
                }
            }
        }

        //交付协议
        $agree = AgreementModel::where('code_name','task_delivery')->first();

        //任务竞标投稿右侧推荐位
        $hotList = [];
        $reTarget = RePositionModel::where('code','TASKDELIVERY_SIDE')->where('is_open','1')->select('id','name')->first();
        if($reTarget->id){
            $recommend = RecommendModel::getRecommendInfo($reTarget->id)->select('*')->get();
            if(count($recommend)){
                foreach($recommend as $k=>$v){
                    $taskInfo = TaskModel::where('id',$v['recommend_id'])->select('bounty','created_at')->first();
                    if($taskInfo){
                        $v['bounty'] = $taskInfo->bounty;
                        $v['create_time'] = $taskInfo->created_at;
                    }
                    else{
                        $v['bounty'] = 0;
                        $v['create_time'] = 0;
                    }
                    $recommend[$k] = $v;
                }
                $hotList = $recommend;
            }

        }

        $view =[
            'task' => $task,
            'ad'=>$ad,
            'hotList' => $hotList,
            'targetName' => $reTarget->name,
            'agree' => $agree,
            'sort' => $sort

        ];

        return $this->theme->scope('task.bid.delivery', $view)->render();
    }


    /**
     * 招标模式 交付稿件(工作中)
     * @param WorkRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bidDeliverCreate(WorkRequest $request)
    {
        $data = $request->except('_token');
        $data['desc'] = \CommonClass::removeXss($data['desc']);
        $data['uid'] = $this->user['id'];
        //判断数据合法性
        if(empty($data['task_id']) || empty($data['sort']))
        {
            return redirect()->back()->with(['error'=>'投稿失败']);
        }
        //判断当前用户是否有验收投稿资格
        if(!WorkModel::isWinBid($data['task_id'],$this->user['id']))
        {
            return redirect()->back()->with('error','您的稿件没有中标不能通过交付！');
        }

        $result = WorkModel::bidDelivery($data);

        if(!$result) return redirect()->back()->with('error','交付失败！');
        //发送系统消息
        //判断当前的任务发布成功之后是否需要发送系统消息
        $agreement_documents = MessageTemplateModel::where('code_name','agreement_documents')->where('is_open',1)->where('is_on_site',1)->first();
        if($agreement_documents)
        {
            $task = TaskModel::where('id',$data['task_id'])->first();
            $user = UserModel::where('id',$task['uid'])->first();//必要条件
            $site_name = \CommonClass::getConfig('site_name');//必要条件
            $user_name = $this->user['name'];
            $domain = \CommonClass::getDomain();
            //组织好系统消息的信息
            //发送系统消息
            $messageVariableArr = [
                'username'=>$user['name'],
                'initiator'=>$user_name,
                'agreement_link'=>$domain.'/task/'.$task['id'],
                'website'=>$site_name,
            ];
            $message = MessageTemplateModel::sendMessage('agreement_documents',$messageVariableArr);
            $messages = [
                'message_title'=>$agreement_documents['name'],
                'code'=>'agreement_documents',
                'message_content'=>$message,
                'js_id'=>$user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id']);
    }

    /**
     * 招标模式 交付稿件验收
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bidWorkCheck(Request $request)
    {
        $data = $request->except('_token');
        $work_data = WorkModel::where('id',$data['work_id'])->first();
        $data['uid'] = $work_data['uid'];
        //验证用户是否是雇主
        if(!TaskModel::isEmployer($work_data['task_id'],$this->user['id']))
            return redirect()->back()->with(['error'=>'您不是雇主，您的操作有误！']);
        //验证当前稿件是否符合验收标准

        if($work_data['status']!=2){
            return redirect()->back()->with(['error'=>'当前稿件不具备验收资格！']);
        }
        $data['task_id'] = $work_data['task_id'];

        $result = WorkModel::BidWorkCheck($data);
        if(!$result) {
            return redirect()->back()->with(['error'=>'操作失败！']);
        }else{
            return redirect()->to('task/'.$data['task_id'])->with(['manage'=>'操作成功！']);
        }
    }

    /**
     * 招标模式任务维权
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ajaxBidRights(Request $request)
    {
        $data = $request->except('_token');
        $data['desc'] = e($data['desc']);
        $data['status'] = 0;
        $data['created_at'] = date("Y-m-d H:i:s", time());
        $work = WorkModel::where('id',$data['work_id'])->first();
        if($work['status'] == 4){
            return redirect()->back()->with(['error'=>'当前稿件正在维权']);
        }
        //判断当前维权的人是否具有维权资格
        $is_checked = WorkModel::where('id',$data['work_id'])
            ->whereIn('status',[2,5])
            ->where('task_id',$data['task_id'])
            ->where('uid',$this->user['id'])
            ->first();
        //查询任务信息
        $task = TaskModel::where('id',$data['task_id'])->first();
        //验证是否维权过
        if(!$is_checked && $task['uid']!=$this->user['id']){
            return redirect()->back()->with(['error'=>'你不具备维权资格！']);
        }
        //判断当前维权的是雇主还是威客
        if($is_checked){
            $data['role'] = 0;
            $data['from_uid'] = $this->user['id'];
            $data['to_uid'] = $task['uid'];
        }else if($task['uid']==$this->user['id']){
            $data['role'] = 1;
            $data['from_uid']  = $this->user['id'];
            $data['to_uid'] = $work['uid'];
        }
        $result = TaskRightsModel::bidRightCreate($data);

        if(!$result)
            return redirect()->back()->with(['error'=>'维权失败！']);
        //维权提交完成，发送系统消息
        $trading_rights = MessageTemplateModel::where('code_name','trading_rights')->where('is_open',1)->where('is_on_site',1)->first();
        if($trading_rights)
        {

            $task = TaskModel::where('id',$data['task_id'])->first();
            $from_user = UserModel::where('id',$this->user['id'])->first();//必要条件
            $site_name = \CommonClass::getConfig('site_name');//必要条件
            //组织好系统消息的信息
            //发送系统消息
            $fromMessageVariableArr = [
                'username'=>$from_user['name'],
                'tasktitle'=>$task['title'],
                'website'=>$site_name,
            ];
            $fromMessage = MessageTemplateModel::sendMessage('trading_rights',$fromMessageVariableArr);
            $messages = [
                'message_title'=>$trading_rights['name'],
                'code'=>'trading_rights',
                'message_content'=>$fromMessage,
                'js_id'=>$from_user['id'],
                'message_type'=>2,
                'receive_time'=>date('Y-m-d H:i:s',time()),
                'status'=>0,
            ];
            MessageReceiveModel::create($messages);
        }
        return redirect()->to('task/'.$data['task_id'])->with(['error'=>'维权成功！']);
    }

    /**
     * 去kee交付台查看
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toKee($id)
    {
        $task = TaskModel::find($id);
        if(!$task){
            return redirect()->back()->with(['message' => '参数错误']);
        }
        //查询项目承接人
        $reUid = '';
        $work = WorkModel::where('task_id',$id)->where('status',1)->first();
        if($work){
            $reUid = $work['uid'];
        }
        $res = false;
        if($task['kee_status'] == 2){
            $userStaue = 0;
            $uid = Auth::id();
            if($uid == $task['uid']){
                $userStaue = 4;
            }elseif($uid == $reUid){
                $userStaue = 3;
            }

            if(in_array($userStaue,[3,4])){
                $res = TaskPayTypeModel::toKeeLook($id, $uid, $userStaue);
            }
        }
        if($res){
            return redirect($res);
        }
        return redirect()->back()->with(['message' => '查看失败']);

    }
}
