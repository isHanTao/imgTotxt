<?php

namespace App\Console\Commands;

use AipFace;
use AipOcr;
use Illuminate\Console\Command;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use Intervention\Image\Facades\Image;

class AiTest extends Command
{
//
//    protected $appID = "22577435";
//    protected $appKey = "5n66WNGB0ECMqzYhgGtp8sL3";
//    protected $appSec = "8rE45GDx0fvWOWgYkN5SDK6R3dBxBp0T";
    protected $appID = "22977510";
    protected $appKey = "FbPfkOUiOgTISp0Osy6qjwoy";
    protected $appSec = "zGF2GQooXkF3UAHSz75onf30mAlC0EYr";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    private $base_path = 'D:/试卷';
    private $max_time = 1;


    /**
     * Execute the console command.
     *
     * @return int
     * @throws \ImagickException
     */
    public function handle()
    {
        $dirs = opendir($this->base_path);
        if ($dirs) {
            while (($file = readdir($dirs)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    if (!is_dir($file)) {
                        $time = 0;
                        do {
                            if ($time < $this->max_time){
                                $res = $this->do($file);
                                $time++;
                                $this->comment('开始,第'.$time.'次' . $file);
                            }
                            else
                                break;
                        } while (!$res);
                    }
                }
            }
            closedir($dirs);
        }

    }

    function do($image)
    {
        try {
            $api = new AipOcr($this->appID, $this->appKey, $this->appSec);
            $file = file_get_contents($this->base_path . '/' . $image);
            $res = $api->general($file);
            file_put_contents(__DIR__.'resp.txt',json_encode($res, JSON_UNESCAPED_UNICODE).PHP_EOL,FILE_APPEND);
            $res = json_decode(json_encode($res, JSON_UNESCAPED_UNICODE))->words_result;
            $page = [];
            foreach ($res as $re) {
                if ($re->location->left < 1200) {
                    $page[0][] = $re->words;
                } else if ($re->location->left > 1200 && $re->location->left < 2400) {
                    $page[1][] = $re->words;
                } else {
                    $page[2][] = $re->words;
                }
            }
            $str = '';
            foreach ($page as $pa) {
                foreach ($pa as $p) {
                    $str .= $p . PHP_EOL;
                }
                $str .= '---------------------------------------------' . PHP_EOL;
            }
            $name = $page[0][0] . $page[0][1] . $page[0][2] . $page[0][3];
            if (!is_dir($this->base_path . '/out')) {
                mkdir($this->base_path . '/out');
            }
            file_put_contents($this->base_path . '/out/' . $name . '.txt', $str);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }


    protected function draw($res, $image, $name = 'res.png')
    {
        $draw = new ImagickDraw();
        $image = new Imagick();
        foreach ($res->result->face_list[0]->landmark72 as $index => $item) {
            $draw->setStrokeColor(new \ImagickPixel('#69c'));
            $draw->circle($item->x, $item->y, $item->x + 2, $item->y + 2);
        }
        $image->drawImage($draw);
        $image = Image::make($image->getImageBlob());
        $image->blur(20);
        $image->save(__DIR__ . $name);
    }

    function pngMerge($o_pic, $out_pic)
    {
        $begin_r = 255;
        $begin_g = 250;
        $begin_b = 250;
        list($src_w, $src_h) = getimagesize($o_pic);// 获取原图像信息 宽高
        $src_im = imagecreatefromjpeg($o_pic); //读取png图片
        print_r($src_im);
        //imagesavealpha($src_im,true);//这里很重要 意思是不要丢了$src_im图像的透明色
        $src_white = imagecolorallocatealpha($src_im, 255, 255, 255, 127); // 创建一副白色透明的画布
        for ($x = 0; $x < $src_w; $x++) {
            for ($y = 0; $y < $src_h; $y++) {
                $rgb = imagecolorat($src_im, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                if ($r > 144 && $g > 144 && $b > 144) {
                    imagefill($src_im, $x, $y, $src_white); //填充某个点的颜色
                    imagecolortransparent($src_im, $src_white); //将原图颜色替换为透明色
//                    imageline($src_im,$x,$y-1,$x+1,$y-1,0xFFFFFF);
                }
                if (!($r <= $begin_r && $g <= $begin_g && $b <= $begin_b)) {
                    imagefill($src_im, $x, $y, $src_white);//替换成白色
                    imagecolortransparent($src_im, $src_white); //将原图颜色替换为透明色
                }
            }
        }


        $target_im = imagecreatetruecolor($src_w, $src_h);//新图

        imagealphablending($target_im, false);//这里很重要,意思是不合并颜色,直接用$target_im图像颜色替换,包括透明色;
        imagesavealpha($target_im, true);//这里很重要,意思是不要丢了$target_im图像的透明色;
        $tag_white = imagecolorallocatealpha($target_im, 255, 255, 255, 0);//把生成新图的白色改为透明色 存为tag_white
        imagefill($target_im, 0, 0, $tag_white);//在目标新图填充空白色
        imagecolortransparent($target_im, $tag_white);//替换成透明色
        imagecopymerge($target_im, $src_im, 0, 0, 0, 0, $src_w, $src_h, 100);//合并原图和新生成的透明图
        imagepng($target_im, $out_pic);
        return $out_pic;

    }

}
