<?php
namespace App\Modules\Manage\Http\Controllers;

use App\Http\Controllers\BasicController;
use App\Http\Controllers\ManageController;
use App\Modules\Manage\Model\ArticleCategoryModel;
use App\Modules\Manage\Model\ArticleModel;
use App\Http\Requests;
use App\Modules\Manage\Http\Requests\ArticleRequest;
use Illuminate\Http\Request;
use Theme;
use Illuminate\Support\Facades\Auth;


class ArticleController extends ManageController
{
    public function __construct()
    {
        parent::__construct();
        $this->initTheme('manage');
        $this->theme->set('manageType', 'article');

    }

    /**
     * 后台文章列表
     * @param $upID 文章分类父id
     * @param Request $request
     * @return mixed
     */
    public function articleList(Request $request, $upID)
    {
        //查询分类名称
        $title = ArticleCategoryModel::where('id',$upID)->first()->cate_name;
        if($upID == 1){
            $this->theme->setTitle('文章管理');
        }elseif($upID == 3){
            $this->theme->setTitle('页脚管理');
        }
        $arr = $request->all();
        $upID = intval($upID);
        //查询所有分类
        $m = ArticleCategoryModel::get()->toArray();
        $res = ArticleCategoryModel::_reSort($m,$upID);
        //文章列表
        $articleList = ArticleModel::whereRaw('1 = 1');
        //分类筛选
        if ($request->get('catID')) {

            //获取所有子分类
            $r = ArticleCategoryModel::_children($m, $request->get('catID'));
            if (empty($r)) {
                $articleList = $articleList->where('article.cat_id', $request->get('catID'));
            } else {
                $catIds = array_merge($r, array($request->get('catID')));
                $articleList = $articleList->whereIn('article.cat_id', $catIds);
            }
        } else {
            //获取所有子分类
            $r = ArticleCategoryModel::_children($m, $upID);
            $catIds = array_merge($r, array($upID));
            $articleList = $articleList->whereIn('article.cat_id', $catIds);

        }
        //编号筛选
        if ($request->get('artID')) {
            $articleList = $articleList->where('article.id', $request->get('artID'));
        }
        //标题筛选
        if ($request->get('title')) {
            $articleList = $articleList->where('article.title', 'like', "%" . e($request->get('title')) . '%');
        }
        //作者筛选
        if ($request->get('author')) {
            $articleList = $articleList->where('article.author', 'like', '%' . e($request->get('author')) . '%');
        }
        if($request->get('start')){
            $start = date('Y-m-d H:i:s',strtotime($request->get('start')));
            $articleList = $articleList->where('article.created_at','>',$start);
        }
        if($request->get('end')){
            $end = date('Y-m-d H:i:s',strtotime($request->get('end')));
            $articleList = $articleList->where('article.created_at','<',$end);
        }
        $by = $request->get('by') ? $request->get('by') : 'article.created_at';
        $order = $request->get('order') ? $request->get('order') : 'desc';
        $paginate = $request->get('paginate') ? $request->get('paginate') : 10;


        $list = $articleList->join('article_category as c', 'article.cat_id', '=', 'c.id')
            ->select('article.id', 'article.cat_id', 'article.title', 'article.view_times', 'article.author', 'article.created_at', 'c.cate_name as cate_name')
            ->orderBy($by, $order)->paginate($paginate);
        $listArr = $list->toArray();

        $data = array(
            'merge' => $arr,
            'upID' => $upID,
            'artID' => $request->get('artID'),
            'title' => $request->get('title'),
            'catID' => $request->get('catID'),
            'author' => $request->get('author'),
            'paginate' => $request->get('paginate'),
            'order' => $request->get('order'),
            'by' => $request->get('by'),
            'article_data' => $listArr,
            'article' => $list,
            'category' => $res

        );
        return $this->theme->scope('manage.articlelist', $data)->render();

    }

    /**
     * 删除一条资讯消息
     * @param $upID 分类父id
     * @param $id
     */
    public function articleDelete($id, $upID)
    {
        $upID = intval($upID);
        switch($upID){
            case 1:
                $url = '/manage/article/';
                break;
            case 3:
                $url = '/manage/articleFooter/';
                break;
            default:
                $url = '/manage/article/';
        }
        $result = ArticleModel::where('id', $id)->delete();
        if (!$result) {
            return redirect()->to($url . $upID)->with(array('message' => '操作失败'));
        }
        return redirect()->to($url . $upID)->with(array('message' => '操作成功'));

    }

    /**
     * 批量删除
     * @param Request $request
     */
    public function allDelete(Request $request)
    {
        $data = $request->except('_token');

        $res = ArticleModel::destroy($data);
        if ($res) {
            return redirect()->to('/manage/article/1')->with(array('message' => '操作成功'));
        }
        return redirect()->to('/manage/article/1')->with(array('message' => '操作失败'));
    }

    /**
     * 新建资讯文章视图
     * @param $upID 文章分类父id
     * @param Request $request
     * @return mixed
     */
    public function addArticle(Request $request, $upID)
    {
        $upID = intval($upID);
        //查询分类名称
        $title = ArticleCategoryModel::where('id',$upID)->first()->cate_name;
        $this->theme->setTitle('文章新建');
        //查询所有分类
        $m = ArticleCategoryModel::get()->toArray();
        $res = ArticleCategoryModel::_reSort($m,$upID);
        $parentCate = ArticleCategoryModel::where('id',$upID)->first();
        $data = array(
            'category' => $res,
            'parent_cate' => $parentCate,
            'upID' => $upID
        );
        return $this->theme->scope('manage.addarticle', $data)->render();
    }

    /**
     * 新建资讯文章
     * @param ArticleRequest $request
     */
    public function postArticle(ArticleRequest $request)
    {
        //获取文章信息
        $data = $request->except('_token', 'pic','upID');
        $upID = $request->get('upID');
        switch($upID){
            case 1:
                $url = '/manage/article/';
                break;
            case 3:
                $url = '/manage/articleFooter/';
                break;
            default:
                $url = '/manage/article/';
        }
        $data['cat_id'] = $data['catID'];
        $data['created_at'] = date('Y-m-d H:i:s',time());
        $data['updated_at'] = date('Y-m-d H:i:s',time());
        $data['display_order'] = $request->get('displayOrder');
        $data['content'] = htmlspecialchars($data['content']);
        if(mb_strlen($data['content']) > 4294967295/3){
            $error['content'] = '文章内容太长，建议减少上传图片';
            if (!empty($error)) {
                return redirect('/manage/addArticle')->withErrors($error);
            }
        }
        //添加信息
        $res = ArticleModel::create($data);
        if ($res) {
            return redirect($url . $upID)->with(array('message' => '操作成功'));
        }
        return false;
    }

    /**
     * 编辑文章视图
     * @param Request $request
     * @param $id 文章id
     * @param $upID 文章分类父id
     * @return mixed
     */
    public function editArticle(Request $request, $id, $upID)
    {
        $id = intval($id);
        $upID = intval($upID);
        //查询分类名称
        $title = ArticleCategoryModel::where('id',$upID)->first()->cate_name;
        $this->theme->setTitle($title);
        $arr = ArticleCategoryModel::where('pid', $upID)->get()->toArray();
        foreach ($arr as $k => &$v) {
            $res = ArticleCategoryModel::where('pid', $v['id'])->get()->toArray();
            $arr[$k]['res'] = $res;
        }
        //查询所有分类
        $m = ArticleCategoryModel::get()->toArray();
        $res = ArticleCategoryModel::_reSort($m,$upID);
        $parentCate = ArticleCategoryModel::where('id',$upID)->first();
        //根据文章id查询文章信息
        $article = ArticleModel::where('id', $id)->first();
        $data = array(
            'article' => $article,
            'parent_cate' => $parentCate,
            'upID' => $upID,
            'cate' => $res
        );
        $this->theme->setTitle('页脚编辑');
        return $this->theme->scope('manage.editarticle', $data)->render();
    }

    /**
     * 编辑文章
     * @param Request $request
     */
    public function postEditArticle(ArticleRequest $request)
    {
        $data = $request->except('_token');
        switch($data['upID']){
            case 1:
                $url = '/manage/article/';
                break;
            case 3:
                $url = '/manage/articleFooter/';
                break;
            default:
                $url = '/manage/article/';
        }
        $data['content'] = htmlspecialchars($data['content']);
        if(mb_strlen($data['content']) > 4294967295/3){
            $error['content'] = '文章内容太长，建议减少上传图片';
            if (!empty($error)) {
                return redirect('/manage/addArticle')->withErrors($error);
            }
        }
        $arr = array(
            'title' => $data['title'],
            'cat_id' => $data['catID'],
            'author' => $data['author'],
            'display_order' => $data['displayOrder'],
            'content' => $data['content'],
            'summary' => $data['summary'],
            'seotitle' => $data['seotitle'],
            'keywords' => $data['keywords'],
            'description' => $data['description'],
            'updated_at' => date('Y-m-d H:i:s',time()),
        );
        //修改信息
        $res = ArticleModel::where('id', $data['artID'])->update($arr);
        if ($res) {
            return redirect($url . $data['upID'])->with(array('message' => '操作成功'));
        }
    }


}
