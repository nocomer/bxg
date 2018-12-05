<?php

namespace App\Modules\User\Model;
use Illuminate\Database\Eloquent\Model;

class AttachmentModel extends Model
{
    protected $table = 'attachment';

    public $timestamps = false;

    protected $fillable = [
        'name', 'type', 'size', 'url', 'status', 'user_id', 'disk', 'created_at'
    ];

    public function work()
    {
        return $this->morphedByMany('App\Modules\Task\Model\WorkModel', 'work_attachment');
    }
    /**
     * 创建一条附件记录
     */
    static function createOne($data)
    {
        $attatchment = new AttachmentModel();
        $attatchment->name = $data['name'];
        $attatchment->type = $data['type'];
        $attatchment->size = $data['size'];
        $attatchment->url = $data['url'];
        $attatchment->created_at = date('Y-m-d H:i:s',time());
        $result = $attatchment->save();
        return $result;
    }

    /**
     * 删除一条附件记录
     */
    static function del($id,$uid)
    {
        $result = UserAttachmentModel::del($uid,$id);
        if(!$result)
        {
            return false;
        }
        $result2 = Self::where('id','=',$id)->delete();
        return $result2;
    }

    /**
     * 通过id查询附件信息
     * @array $ids
     * @return mixed
     */
    static function findByIds($ids)
    {
        $data = Self::whereIn('id',$ids)->get();
        return $data;
    }

    /**
     * 检查附件是否上传成功
     * @param $ids
     */
    static function fileAble($ids)
    {
        $data = Self::select('attachment.id')->whereIn('id',$ids)->get()->toArray();
        return $data;
    }
    /**
     * 修改没有生效的附件记录
     * @param $ids
     */
    public function statusChange($ids)
    {
        $query = Self::where('status',0);
        if(is_array($ids))
        {
            $query = $query->whereIn('id',$ids);
        }else
        {
            $query = $query->where('id',$ids);
        }
        $result = $query->update(['status'=>1]);

        return $result;
    }
}