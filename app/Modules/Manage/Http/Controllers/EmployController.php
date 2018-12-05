<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\ManageController;
use App\Http\Requests;
use App\Modules\Employ\Models\EmployGoodsModel;
use App\Modules\Employ\Models\EmployModel;
use App\Modules\Employ\Models\UnionAttachmentModel;
use App\Modules\Manage\Model\ConfigModel;
use App\Modules\User\Model\AttachmentModel;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployController extends ManageController
{

    public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->setTitle('');
        $this->theme->set('manageType', '');
    }

    /**
     * 雇佣列表
     * @param Request $request
     * @return mixed
     */
    public function employList(Request $request)
    {
        $data = $request->all();
        $pagesize = 10;
        //查询所有的employ_id
        $employ_ids = EmployGoodsModel::lists('employ_id')->toArray();
        //查询所有的雇佣任务
        $employ = EmployModel::select('employ.*','ur.name as employer_name','us.name as employee_name')
            ->whereNotIn('employ.id',$employ_ids)
            ->join('users as ur','ur.id','=','employ.employer_uid')
            ->leftjoin('users as us','us.id','=','employ.employee_uid');
        //雇主信息
        if(!empty($data['employer_name']))
        {
            $employ = $employ->where('ur.name','like',"%".e($data['employer_name'])."%");
        }
        //雇佣需求名称
        if(!empty($data['employ_title']))
        {
            $employ = $employ->where('employ.title','like',"%".e($data['employ_title'])."%");
        }
        //筛选service状态
        if(!empty($data['service_status']) && $data['service_status']!=100)
        {
            $employ = $employ->whereIn('employ.status',explode(',',$data['service_status']));
        }
        if(!empty($data['service_status']) && $data['service_status']==100)
        {
            $employ = $employ->where('employ.bounty_status',0);
        }
        //排序
        $orderBy = 'id';
        if(!empty($data['orderby']))
        {
            $orderBy = $data['orderby'];
        }
        $orderByType = 'ACS';
        if(!empty($data['ordertype']))
        {
            $orderByType = $data['ordertype'];
        }
        //每页显示条数
        if(!empty($data['pagesize']))
        {
            $pagesize = $data['pagesize'];
        }
        $employ_page = $employ->orderBy($orderBy,$orderByType)->paginate($pagesize);
        $employ = $employ_page->toArray();
        $map = [
            'status'=>[
                0=>'待受理',
                1=>'工作中',
                2=>'验收中',
                3=>'待评价',
                4=>'交易完成',
                5=>'交易失败',
                6=>'交易失败',
                7=>'交易维权',
                8=>'交易维权',
                9=>'交易失败',
            ]
        ];
        $employ['data'] = \CommonClass::intToString($employ['data'],$map);
        $data = [
            'result'=>$employ,
            'employ_page'=>$employ_page
        ];
        $this->theme->setTitle('订单管理');

        return $this->theme->scope('manage.employlist',$data)->render();
    }


    /**
     * 删除雇佣
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employDelete($id)
    {
        $result = EmployModel::where('id',$id)->delete();
        if(!$result)
            return redirect()->back()->with(['error'=>'删除失败！']);

        return redirect()->back()->with(['message'=>'删除成功！']);
    }

    /**
     * 编辑雇佣页面
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employEdit($id)
    {
        //查询当前编辑数据
        $data = EmployModel::select('employ.*','ur.name as employer_name','us.name as employee_name')
            ->where('employ.id',$id)
            ->join('users as ur','ur.id','=','employ.employer_uid')
            ->leftjoin('users as us','us.id','=','employ.employee_uid')
            ->first();
        //查询当前任务的所有附件
        $attachment = UnionAttachmentModel::where('object_id',$id)->where('object_type',2)->get()->toArray();

        if(!$data)
            return redirect()->back()->with(['error'=>'数据不存在！']);

        $view = [
            'data'=>$data,
            'attachment'=>$attachment,
        ];

        return $this->theme->scope('manage.employEdit',$view)->render();
    }
    /**
     * 编辑数据提交控制
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employUpdate(Request $request)
    {
        $data = $request->except('_token');

        $result = EmployModel::where('id',$data['id'])->update($data);

        if(!$result)
            return redirect()->back()->with(['error'=>'编辑失败！']);

        return redirect('manage/employList')->with(['message'=>'编辑成功！']);
    }
    /**
     * 雇佣配置
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function employConfig(Request $request)
    {
        //查询数据表中的雇佣配置
        $employ_config = ConfigModel::where('type','employ')->get()->toArray();
        $employ_config = \CommonClass::keyBy($employ_config,'alias');

        $view = [
            'config'=>$employ_config
        ];
        $this->theme->setTitle('流程配置');
        return $this->theme->scope('manage.employconfig',$view)->render();
    }

    /**
     * 雇佣设置提交
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function configUpdate(Request $request)
    {
        $data = $request->except('_token');
        if(!empty($data['change_ids']))
        {
            $change_ids = explode(',',$data['change_ids']);
            foreach($change_ids as $v)
            {
                $result = ConfigModel::where('id',$v)->update(['rule'=>$data[$v]]);

                if(!$result)
                    return redirect()->back()->with(['error'=>'修改失败！']);
            }
        }else{
            return redirect()->back()->with(['error'=>'请修改后再提交！']);
        }

        return redirect()->back()->with(['message'=>'修改成功！']);
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
}
