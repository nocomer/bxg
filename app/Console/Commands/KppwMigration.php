<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Exception;

class KppwMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:kppw30';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update KPPW3.0 database to KPPW3.3';

    //kppw27数据库对象
    protected $kppw30;
    //kppw30数据库对象
    protected $kppw33;


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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit','1024M');// 修改php内存配置


        $this->warn('转移数据前请先做好备份！并移动KPPW3.0 public/attachment子目录移动到KPPW3.0 public/attachment目录下');

        //kppw3.0数据库配置数组
        $v30database = [];
        $v30database['host'] = $this->ask('请输入KPPW3.0数据库主机地址');
        $v30database['database'] = $this->ask('请输入KPPW3.0数据库名称');
        $v30database['username'] = $this->ask('请输入KPPW3.0数据库用户名');
        $v30database['password'] = $this->ask('请输入KPPW3.0数据库密码');
        $v30database['prefix'] = $this->ask('请输入KPPW3.0数据库表前缀');
        /*$v30database['host'] = '112.124.47.72';
        $v30database['database'] = 'kppw30_demo';
        $v30database['username'] = 'kekezu';
        $v30database['password'] = 'kekezu1234';
        $v30database['prefix'] = 'kppw_';*/

        //kppw3.3数据库配置数组
        $v33database = [];
        $v33database['host'] = $this->ask('请输入KPPW3.3数据库主机地址');
        $v33database['database'] = $this->ask('请输入KPPW3.3数据库名称');
        $v33database['username'] = $this->ask('请输入KPPW3.3数据库用户名');
        $v33database['password'] = $this->ask('请输入KPPW3.3数据库密码');
        $v33database['prefix'] = $this->ask('请输入KPPW3.3数据库表前缀');
        /*$v33database['host'] = '127.0.0.1';
        $v33database['database'] = 'kppwmajor';
        $v33database['username'] = 'root';
        $v33database['password'] = 'root';
        $v33database['prefix'] = 'kppw_';*/

        $this->setDatabase('kppw30', $v30database);
        $this->setDatabase('kppw33', $v33database);

        $data = [
            'host' => [$v30database['host'], $v33database['host']],
            'database' => [$v30database['database'], $v33database['database']],
            'username' => [$v30database['username'], $v33database['username']],
            'password' => [$v30database['password'], $v33database['password']],
            'prefix' => [$v30database['prefix'], $v33database['prefix']]
        ];

        $this->table(['KPPW3.0', 'KPPW3.3'], $data);

        if ($this->confirm('确定开始迁移数据么?', true)){

            $this->kppw30 = DB::connection('kppw30'); //要被迁移的数据库连接
            $this->kppw33 = DB::connection('kppw33'); //迁移到的数据库连接


            $this->info('正在执行迁移...');


            //获取kppw3.0数据库所有数据表(旧的数据库)
            $table30 = $this->kppw30->select("SELECT DISTINCT table_name as NAME FROM information_schema.KEY_COLUMN_USAGE where CONSTRAINT_SCHEMA ='".$v30database['database']."' ORDER BY LENGTH(CONSTRAINT_name) asc");

            //获取kppw3.3数据库所有数据表(新的数据库)
            $table33 = $this->kppw33->select("SELECT DISTINCT table_name as NAME FROM information_schema.KEY_COLUMN_USAGE where CONSTRAINT_SCHEMA ='".$v33database['database']."' ORDER BY LENGTH(CONSTRAINT_name) asc");

            //不用迁移的数据表
            $noMigrate = [
                'kppw_menu','kppw_menu_permission','kppw_permissions',//后台菜单权限相关
                'kppw_laravrl_sms',
                'kppw_bre_action','kppw_bre_rule','kppw_decision'//没有使用的数据表
            ];
            //开始进度条
            $count = count($table30) - count($noMigrate);


            $this->output->progressStart($count);

            if(!empty($table30) && is_array($table30) && !empty($table33) && is_array($table33)){
                foreach($table30 as $k => $v){
                    $this->info('正在迁移'.$v->NAME);
                    foreach($table33 as $k3 => $v3){
                        if($v->NAME == $v3->NAME && !in_array($v->NAME,$noMigrate)){
                            //查询表字段
                            $field30 = $this->kppw30->select("select TABLE_NAME as TABLENAME,COLUMN_NAME As COLUMNSNAME,DATA_TYPE As COLUMNSTYPE,IFNULL(CHARACTER_MAXIMUM_LENGTH,0) As COLUMNSLENGTH from information_schema.`COLUMNS` where TABLE_SCHEMA ='".$v30database['database']."' and TABLE_NAME ='".($v->NAME)."'");
                            $field33 = $this->kppw33->select("select TABLE_NAME as TABLENAME,COLUMN_NAME As COLUMNSNAME,DATA_TYPE As COLUMNSTYPE,IFNULL(CHARACTER_MAXIMUM_LENGTH,0) As COLUMNSLENGTH from information_schema.`COLUMNS` where TABLE_SCHEMA ='".$v33database['database']."' and TABLE_NAME ='".($v3->NAME)."'");
                            if(!empty($field30) && is_array($field30)){
                                $field30 = array_reduce($field30,function(&$field30,$fv){
                                    $field30[] = $fv->COLUMNSNAME;
                                    return $field30;
                                });
                            }
                            if(!empty($field33) && is_array($field33)){
                                $field33 = array_reduce($field33,function(&$field33,$fv){
                                    $field33[] = $fv->COLUMNSNAME;
                                    return $field33;
                                });
                            }
                            //获取要删除的字段
                            $diff30 = array_diff($field30,$field33);
                            //获取要添加的字段
                            $diff33 = array_diff($field33,$field30);
                            //去掉表前缀的表名称
                            $tabLeName30 = substr($v->NAME,strlen($v30database['prefix']));
                            $tabLeName33 = substr($v3->NAME,strlen($v33database['prefix']));

                            $limitCount = 1000;
                            $index = 0;
                            while(true){
                                $lists = $this->kppw30->table($tabLeName30)->skip($index)->take($limitCount)->get();
                                //数据处理迁移
                                $lists = array_reduce($lists,function(&$lists,$item){
                                    $lists[] = $this->object_array($item);
                                    return $lists;
                                });
                                if(!empty($lists) && is_array($lists)){
                                    foreach ($lists as $i => $list) {
                                        //
                                        if(!empty($diff33)){
                                            foreach($diff33 as $key => $value){
                                                $lists[$i][$value] = 0;
                                            }
                                        }
                                        if(!empty($diff30)){
                                            foreach($diff30 as $key30 => $value30){
                                                unset($lists[$i][$value30]);
                                            }
                                        }
                                    }
                                }

                                if(in_array($v->NAME,['kppw_config','kppw_promote_type','kppw_recommend_position'])){
                                    $alias = '';
                                    switch($v->NAME){
                                        case 'kppw_config':
                                            $alias = 'alias';
                                            break;
                                        case 'kppw_promote_type':
                                            $alias = 'code_name';
                                            break;
                                        case 'kppw_recommend_position':
                                            $alias = 'code';
                                            break;
                                    }
                                    if($alias){
                                        $lists33 = $this->kppw33->table($tabLeName33)->select($alias)->get();
                                        $lists33 = array_reduce($lists33,function(&$lists33,$item33){
                                            $lists33[] = $this->object_array($item33);
                                            return $lists33;
                                        });
                                        $lists33 = array_flatten($lists33);
                                        if(!empty($lists33) && is_array($lists33) && !empty($lists) && is_array($lists)){
                                            foreach ($lists as $key30 => $value30) {
                                                unset($value30['id']);
                                                if(in_array($value30[$alias],$lists33)){
                                                    //修改配置项
                                                    $this->kppw33->table($tabLeName33)->where($alias,$value30[$alias])->update($value30);
                                                }else{
                                                    //增加配置项
                                                    $this->kppw33->table($tabLeName33)->insert($value30);
                                                }
                                            }

                                        }else{
                                            if(!empty($lists) && is_array($lists)){
                                                try{
                                                    if($index == 0){
                                                        //删除kppw33表数据
                                                        $this->kppw33->table($tabLeName33)->delete();
                                                    }
                                                    //给kppw33插入数据
                                                    $this->kppw33->table($tabLeName33)->insert($lists);
                                                }catch  (\Exception $e) {
                                                    echo $e->getMessage();
                                                }
                                            }
                                        }
                                    }

                                }else{
                                    if(!empty($lists) && is_array($lists)){
                                        try{
                                            if($v3->NAME == 'kppw_permission_role'){
                                                $this->kppw33->statement(" ALTER TABLE ".$v3->NAME." DROP FOREIGN KEY permission_role_permission_id_foreign");
                                                $this->kppw33->statement(" ALTER TABLE ".$v3->NAME." DROP FOREIGN KEY permission_role_role_id_foreign");
                                            }
                                            if($v3->NAME == 'kppw_role_user'){
                                                $this->kppw33->statement(" ALTER TABLE ".$v3->NAME." DROP FOREIGN KEY role_user_role_id_foreign");
                                                $this->kppw33->statement(" ALTER TABLE ".$v3->NAME." DROP FOREIGN KEY role_user_user_id_foreign");
                                            }
                                            if($v3->NAME == 'kppw_users'){
                                                $this->kppw33->statement(" ALTER TABLE ".$v3->NAME." DROP INDEX users_email_unique");
                                            }

                                            if($index == 0){

                                                //删除kppw33表数据
                                                $this->kppw33->table($tabLeName33)->delete();
                                            }
                                            //给kppw33插入数据
                                            $this->kppw33->table($tabLeName33)->insert($lists);

                                        }catch  (\Exception $e) {
                                            echo $e->getMessage();
                                        }

                                    }
                                }


                                $index =  $index + $limitCount;
                                if(empty($lists)){
                                    break;
                                }

                            }


                        }
                    }
                    //进度条前进
                    $this->output->progressAdvance();
                }
            }
            //进度条结束
            $this->output->progressFinish();

            $this->info('转移完成');
        }
    }

    /**
     * 动态设置数据库配置信息
     *
     * @param $connectionParam
     * @param $databaseInfo
     */
    public function setDatabase($connectionParam, $databaseInfo)
    {
        Config::set('database.connections.' . $connectionParam . '.host', $databaseInfo['host']);
        Config::set('database.connections.' . $connectionParam . '.database', $databaseInfo['database']);
        Config::set('database.connections.' . $connectionParam . '.username', $databaseInfo['username']);
        Config::set('database.connections.' . $connectionParam . '.password', $databaseInfo['password']);
        Config::set('database.connections.' . $connectionParam . '.prefix', $databaseInfo['prefix']);
    }

    /**
     * 对象转数组
     * @param $array
     * @return array
     */
    public function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }
}
