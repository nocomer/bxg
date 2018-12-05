<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use File;

class UpdateKPPW extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:kppw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this is kppw update engine';

    //更新文件目录位置
    protected $updatePath;
    //更新时间
    protected $updateTime;
    //迁移文件路径
    protected $migrationPath;
    //填充数据文件目录
    protected $seederPath;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
		
		$this->updateTime = config('kppw.kppw_update_time');

        $this->updatePath = base_path('update');

        $this->seederPath = database_path('seeds/' . $this->updateTime);

        $this->migrationPath = 'database/migrations/' . $this->updateTime;


    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $start = $this->confirm('Please back up the database and the program before you upgrade!!!');

        if ($start){
            Artisan::call('GetSmsTemplate');

            $status = File::copyDirectory($this->updatePath, base_path());

            if ($status){

                //判断执行哪些migrations目录下的文件
                $dirList = File::directories(database_path('migrations'));
                $dirName = [];
                if(!empty($dirList) && is_array($dirList)){
                    foreach ($dirList as $dirNameV){
                        $dirName[] = basename($dirNameV);
                    }
                }

                //判断执行哪些seeds目录下的文件
                $dirSeedList = File::directories(database_path('seeds'));
                $dirSeedName = [];
                if(!empty($dirSeedList) && is_array($dirSeedList)){
                    foreach ($dirSeedList as $dirNameS){
                        $dirSeedName[] = basename($dirNameS);
                    }
                }

                //获取最新的更新日期
                $dataArr = array_unique(array_merge($dirName,$dirSeedName));
                if(!empty($dataArr)){
                    $maxDateKey = array_search(max($dataArr),$dataArr);
                    $maxDate = $dataArr[$maxDateKey];
                }else{
                    $maxDate = '';
                }


                if(!empty($dirName)){
                    foreach($dirName as $itemDir){
                        if($itemDir <= $this->updateTime){
                            //删除已经执行的migrations目录
                            File::deleteDirectory(database_path('migrations').'/'.$itemDir);
                        }else{
                            //执行字段修改
                            $this->call('migrate', [
                                '--path' => 'database/migrations/'.$itemDir
                            ]);
                        }
                    }
                }
                $now = date('Ymd',time());
                $seedsDirPath = database_path('seeds').'/'.$now;
                if(!File::exists($seedsDirPath)){
                    File::makeDirectory($seedsDirPath);
                }

                if(!empty($dirSeedName)){

                    foreach($dirSeedName as $itemSeedDir){
                        if($itemSeedDir <= $this->updateTime){
                            //删除已经执行的seeds目录
                            File::deleteDirectory(database_path('seeds').'/'.$itemSeedDir);
                        }else{
                            //把要执行的seeder存入当前目录
                            File::copyDirectory(database_path('seeds').'/'.$itemSeedDir, $seedsDirPath);
                            File::copyDirectory(database_path('seeds').'/'.$itemSeedDir, database_path('seeds'));
                            if($itemSeedDir != $now){
                                File::deleteDirectory(database_path('seeds').'/'.$itemSeedDir);
                            }
                        }
                    }

                    //执行数据填充
                    $files = File::files($seedsDirPath);
                    if(!empty($files)){
                        foreach ($files as $file){
                            $filename[] = basename($file, '.' . File::extension($file));
                        }
                        if(!empty($filename)){
                            foreach ($filename as $seed){
                                Artisan::call('db:seed', [
                                    '--class' => $seed
                                ]);
                            }
                        }
                        //执行完成删除
                        File::deleteDirectory($seedsDirPath);
                    }


                }

                $oldVersion = config('kppw.kppw_version');
                $newVersion = '3.3';
                if(!empty($maxDate) && $this->updateTime < $maxDate){
                    //修改config/kppw.php 更新日期
                    $kppwFile = File::get(config_path().'/kppw.php');

                    $strSearch = "'kppw_update_time' => '".$this->updateTime."',";
                    $strReplace = "'kppw_update_time' => '".$maxDate."',";

                    $newKppw = str_replace($strSearch,$strReplace,$kppwFile);

                    File::put(config_path().'/kppw.php',$newKppw);
                }
                if($oldVersion != $newVersion){
                    //修改config/kppw.php 版本号
                    $kppwFile = File::get(config_path().'/kppw.php');

                    $strOldVersion = "'kppw_version' => env('KPPW_VERSION', '".$oldVersion."'),";
                    $strNewVersion = "'kppw_version' => env('KPPW_VERSION', '".$newVersion."'),";
                    $newKppw = str_replace($strOldVersion,$strNewVersion,$kppwFile);
                    File::put(config_path().'/kppw.php',$newKppw);
                }

                //执行完毕清理安装文件
                File::deleteDirectory($this->updatePath);
            }
			$this->info('update success');
        }

        
    }
}
