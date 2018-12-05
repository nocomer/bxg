<?php

namespace App\Console\Commands;

use App\Modules\Employ\Models\EmployModel;
use App\Modules\Manage\Model\ConfigModel;
use Illuminate\Console\Command;
use File;

class GetSmsTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GetSmsTemplate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取程序中短信模板信息';

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
     * 处理任务逾期未交稿的雇佣任务
     */
    public function handle()
    {
        $registerCode = '';
        $findCode = '';
        $bindCode = '';
        $unBindCode = '';
        //获取注册短信模板
        $scheme = ConfigModel::phpSmsConfig('phpsms_scheme');
        $registerPath = app_path().'/Modules/User/Http/Controllers/Auth/AuthController.php';
        $content = File::get($registerPath);
        $contentArr = $this->matching($content,$scheme,'];');
        if(!empty($contentArr)){
            $str = $contentArr[0];
            $code = $this->matching($str,$scheme."' => '","'");
            if(!empty($code) && isset($code[0])){
                $codeArr = explode('=>',$code[0]);
                if(isset($codeArr[1])){
                    $registerCode = $this->getNeedBetween($codeArr[1],"'","'");
                }
            }
        }
        //获取找回密码短信模板
        $findPath = app_path().'/Modules/User/Http/Controllers/Auth/PasswordController.php';
        $content1 = File::get($findPath);
        $contentArr1 = $this->matching($content1,$scheme,'];');

        if(!empty($contentArr1)){
            $str1 = $contentArr1[0];
            $code1 = $this->matching($str1,$scheme."' => '","'");

            if(!empty($code1) && isset($code1[0])){
                $codeArr1 = explode('=>',$code1[0]);
                if(isset($codeArr1[1])){
                    $findCode = $this->getNeedBetween($codeArr1[1],"'","'");
                }
            }
        }
        //获取绑定手机和取消绑定短信模板
        $bindPath = app_path().'/Modules/User/Http/Controllers/AuthController.php';
        $content2 = File::get($bindPath);
        $contentArr2 = $this->matching($content2,$scheme,'];');
        if(!empty($contentArr2)){
            foreach($contentArr2 as $k => $v){
                if($k == 0 || $k = count($contentArr2)-1 ){
                    $code2 = $this->matching($v,$scheme."' => '","'");
                    if(!empty($code2) && isset($code2[0])){
                        $codeArr2 = explode('=>',$code2[0]);
                        if(isset($codeArr2[1])){
                            if($k == 0){
                                $bindCode = $this->getNeedBetween($codeArr2[1],"'","'");
                            }else{
                                $unBindCode = $this->getNeedBetween($codeArr2[1],"'","'");
                            }
                        }
                    }
                }

            }
        }
        $config = [
            'sendMobileCode' => $registerCode,
            'sendMobilePasswordCode' => $findCode,
            'sendBindSms' => $bindCode,
            'sendUnbindSms' => $unBindCode
        ];
        if(!empty($config)){
            foreach($config as $k => $v){
                $isExits = ConfigModel::where('alias',$k)->first();
                if($isExits){
                    ConfigModel::where('alias',$k)->update(['rule' => $v]);
                }else{
                    $newArr = [
                        'alias' => $k,
                        'rule' => $v,
                        'type' => 'phone'
                    ];
                    ConfigModel::create($newArr);
                }
            }
        }
    }

    private function matching($str, $a, $b)
    {
        $pattern = '/('.$a.').*?('.$b.')/is';
        preg_match_all($pattern, $str, $m);
        //var_dump($m,$pattern);
        return ($m[0]);
    }

    private function getNeedBetween($kw,$mark1,$mark2){
        $st = strpos($kw,$mark1);
        $ed = strripos($kw,$mark2);
        if(($st == false || $ed == false )||$st >= $ed){
            return 0;
        }
        $kw = substr($kw,($st+1),($ed-$st-1));
        return $kw;
    }
}
