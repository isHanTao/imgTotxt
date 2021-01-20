<?php

namespace App\Http\Controllers;

use AipOcr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // 百度云api https://cloud.baidu.com/product/ocr_general
    protected $appID = "****";
    protected $appKey = "***";
    protected $appSec = "***";
    private $max_time = 1;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function upload(Request $request){
        $file = $request->file('file')->get();
        if (!$file){
            return json_encode(['code'=>1,'msg'=>'文件有问题']);
        }
        $time = 0;
        do {
            if ($time < $this->max_time){
                $res = $this->do($file);
                $time++;
            }
            else
                break;
        } while (!$res);
        if ($res){
            return json_encode(['code'=>0,'data'=>$res]);
        }else{
            return json_encode(['code'=>1]);
        }
    }

    function do($file)
    {
        try {
            $api = new AipOcr($this->appID, $this->appKey, $this->appSec);
            $res = $api->basicGeneral($file);
            $res = json_decode(json_encode($res, JSON_UNESCAPED_UNICODE));
            return $res;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
