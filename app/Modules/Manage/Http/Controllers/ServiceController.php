<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\BasicController;
use App\Http\Controllers\ManageController;
use App\Http\Requests;
use App\Modules\Manage\Model\ServiceModel;
use App\Modules\Manage\Model\SubOrderModel;
use Illuminate\Http\Request;
use App\Modules\Manage\Http\Requests\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceController extends ManageController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->setTitle('增值工具管理');
        $this->theme->set('manageType', 'service');

    }

    /**
     * 增值工具列表
     * @param Request $request
     * @return mixed
     */
    public function serviceList(Request $request)
    {
        $serviceRes = ServiceModel::whereRaw('1 = 1');
        $by = $request->get('by') ? $request->get('by') : 'updated_at';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;
        $serviceRes = $serviceRes->orderBy($by, $order)->paginate($paginate);
        $data = array(
            'service_list' => $serviceRes,
            'paginate' => $paginate
        );
        $this->theme->setTitle('工具列表');
        return $this->theme->scope('manage.servicelist', $data)->render();
    }

    /**
     * 新建增值工具视图
     * @return mixed
     */
    public function addService()
    {
        return $this->theme->scope('manage.addservice')->render();
    }

    /**
     * 添加增值工具
     * @param Request $request
     * @return mixed
     */
    public function postAddService(ServiceRequest $request)
    {
        $data = $request->all();
        $data['created_at'] = date('Y-m-d H:i:s',time());
        $data['updated_at'] = date('Y-m-d H:i:s',time());
        //添加信息
        $res = ServiceModel::create($data);
        if($res)
        {
            return redirect('manage/serviceList')->with(array('message' => '操作成功'));
        }
    }

    /**
     * 编辑增值工具视图
     * @param $id 自定义导航id
     * @return mixed
     */
    public function editService($id)
    {
        $id = intval($id);
        $serviceInfo = ServiceModel::where('id',$id)->first();
        $data = array(
            'serviceInfo' => $serviceInfo
        );
        return $this->theme->scope('manage.editservice',$data)->render();
    }

    /**
     * 编辑增值工具
     * @param Request $request
     * @return mixed
     */
    public function postEditService(ServiceRequest $request)
    {
        $data = $request->all();
        $arr = array(
            'title' => $data['title'],
            'price' => $data['price'],
            'description' => $data['description'],
            'status' => $data['status'],
            'updated_at' => date('Y-m-d H:i:s',time())
        );
        //修改信息
        $res = ServiceModel::where('id',$data['id'])->update($arr);
        if($res)
        {
            return redirect('manage/serviceList')->with(array('message' => '操作成功'));
        }
    }

    /**
     * 删除一个增值工具
     * @param $id 自定义导航id
     * @return mixed
     */
    public function deleteService($id)
    {
        $id = intval($id);
        $res = ServiceModel::where('id',$id)->delete();
        if(!$res)
        {
            return redirect()->to('/manage/serviceList')->with(array('message' => '操作失败'));
        }
        return redirect()->to('/manage/serviceList')->with(array('message' => '操作成功'));
    }

    /**
     * 购买记录
     * @param Request $request
     * @return mixed
     */
    public function serviceBuy(Request $request)
    {
        $arr = $request->all();
        $buyList = SubOrderModel::whereRaw('1 = 1');
        //编号筛选
        if($request->get('id'))
        {
            $buyList = $buyList->where('sub_order.id',$request->get('id'));
        }
        //购买用户
        if($request->get('name'))
        {
            $buyList = $buyList->where('u.name','like',"%".$request->get('name')."%");
        }
        //工具名称
        if($request->get('title'))
        {
            $buyList = $buyList->where('s.title','like',"%".$request->get('title')."%");
        }
        $by = $request->get('by') ? $request->get('by') : 'sub_order.id';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;


        $list = $buyList->where('sub_order.product_type',2)->join('service as s','sub_order.product_id','=','s.id' )->leftJoin('users as u','sub_order.uid','=','u.id')
            ->select('sub_order.id','sub_order.cash','sub_order.created_at','s.title','s.price','u.name')
            ->orderBy($by, $order)->paginate($paginate)->toArray();
        $data = array(
            'list' => $list,
            'merge'=> $arr,
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'title' => $request->get('title'),
            'by' => $request->get('by'),
            'order' => $request->get('order')
        );
        return $this->theme->scope('manage.servicebuylist',$data)->render();
    }
}

