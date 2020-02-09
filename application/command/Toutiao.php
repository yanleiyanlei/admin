<?php
namespace app\command;
require __DIR__."/../../public/vendor/autoload.php";
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Cache;
use QL\QueryList;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Toutiao extends Command
{
    protected function configure()
    {
        $this->setName('toutiao');//定义命令的名字
        $this->addArgument('keyword', null, "The name of the cache");//命令行参数
    }

    protected function execute(Input $input, Output $output)
    {
//        $output->writeln('Hello World');//在命令行界面输出内容
          echo '开始计时：'.date("Y-m-d H:i:s",time())."\n\r";
        $keyword = $input->getArgument('keyword');

        $url = array(
            array( 'url'=> 'https://www.toutiao.com/a6788025204116292104'),
            array( 'url'=> 'https://www.toutiao.com/a6679938807476781579')
        );
        $url = Cache::get($keyword);
//print_r($url);exit;
        foreach ($url as $key=>$vurl) {
//          if($key>30)break;
//            if($key<50 || $key >70)continue;
            $start_time = microtime(true);//  开始执行时间
            $rt = QueryList::get($vurl['url'])->find()->html();
            $preg = "#title: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
            preg_match_all($preg, $rt, $result);//php正则表达式
            $title = trim(htmlspecialchars_decode($result[1][0]), " \" ");
            echo 'key: '.$key.'   ---'.$title;
            if(!$title) {
                $end_time = microtime(true);
                $execution_time = $end_time - $start_time;
                echo "   ---当前end ".round($execution_time,1).'s'. "\n\r";
                continue;
            }
            $news[$key]['title'] = $title;

            $preg = "#content: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
            preg_match_all($preg, $rt, $res);//php正则表达式
            $str = preg_replace("/\\\\u([0-9a-f]{3,4})/i", "&#x\\1;", $res[1][0]);

            $str = html_entity_decode($str, null, 'UTF-8');

            $str = preg_replace("/<img.*?>/si", "", $str);
//           拿title去搜索，抓取百度页面下面的相关搜索     百度有安全验证，针对UA 应该设置的时间
            $baiduseach = $this->testbaidua($news[$key]['title']);
            if($key>0 && $key%20==0)
            {
                sleep(10);
            }else {
                sleep(1);
            }
            $news[$key]['bai'] = $baiduseach ? $baiduseach:$news[$key]['title'];
            //内容
            $news[$key]['con'] = trim($str, "&quot;");
            $end_time = microtime(true);
            $execution_time = $end_time - $start_time;
            echo "   ---end  ".round($execution_time,1).'s'."\n\r";
//    print_r($baiduseach);
        }

        $this->wexcel($news);




        //$this->wexcel($news);
        echo 'end :'.date("Y-m-d H:i:s",time());
    }

    /**
     * @param $word
     * @return bool|string
     * 一般
     * 也能用
     */


    public function testbai($word){
        //echo $word = "网站SEO优化，7个基本的方案与步骤";
        $url = "https://www.baidu.com/s?ie=utf-8&wd=".$word;
        $ua = "Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/".rand(10,99).".0";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        $res = curl_exec($ch);

        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if(strpos($res, "相关搜索")===false)return false;

        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];

        $str = implode(',',$reach_word);
        return  $str;
//print_r($reach_word);
    }


    /**
     * @param $word
     * @return string
     *  比较稳定,最好用
     * 从postman 里复制的，也许有关系
     */

    public function testbaidua($word)
    {
        //echo 'test闫磊';
//        $str = "Shopify SEO终极指南（巨详细的操作教程，赶快收藏！）";
        $key_word = urlencode($word);
        $url = "https://www.baidu.com/s?ie=utf-8&wd=".$key_word;

        $header = array (
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.'.rand(10,99),
            'Accept: */*',
            'Cache-Control:no-cache',
//        'Postman-Token:a2326fdc-34fa-4daa-b801-5f74f8721184',
            'Host: www.baidu.com',
//        'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Connection: keep-alive',
            /*
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'is_referer: https://www.baidu.com/',
            'is_xhr: 1',
            'Referer: https://www.baidu.com/',
            'User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)',
            'X-Requested-With: XMLHttpRequest',*/
        );

        $ch = curl_init ();

        curl_setopt ( $ch, CURLOPT_URL, $url );

        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header );

        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        $res = curl_exec ( $ch );
        curl_close ( $ch );
        if(strpos($res, "相关搜索")===false)return false;

        //$result = mb_convert_encoding($content, 'utf-8', 'GBK,UTF-8,ASCII,gb2312');
//输出结果
        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];

        $str = implode(',',$reach_word);
        return  $str;
//        print_r($reach_word);
//    echo $content;
    }





    public function baidu($str){

        //echo 'test';
//        $str = "闫磊";
        $key_word = urlencode($str);//需要对关键词进行url解析,否者部分带字符的标题会返回空
        $url = 'https://www.baidu.com/s?ie=UTF-8&wd='.$key_word;

        $res = $this->curl_request($url);


        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
print_r($reach_word);
        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];


        return $reach_word;


    }



    public function wexcel($datan)
    {

        /* $datan = array(
                    array('title'=>'abc','bai'=>'test','con'=>'con'),
                    array('title'=>'abc','bai'=>'test','con'=>'con'),

         );*/

        $title = ['第一行标题', '第二行标题'];

        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 方法一，使用 setCellValueByColumnAndRow
        //表头
        //设置单元格内容
        foreach ($title as $key => $value) {
            // 单元格内容写入   第一行  表头  暂时不要
//            $sheet->setCellValueByColumnAndRow($key + 1, 1, $value);
        }
        $row = 1; // 从第几行开始
        foreach ($datan as $item) {
            $column = 1;
            foreach ($item as $value) {
                // 单元格内容写入
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }
            $row++;
        }



        // Redirect output to a client’s web browser (Xlsx)
   /*     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');*/
        // If you're serving to IE 9, then the following may be needed
//        header('Cache-Control: max-age=1');



        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        //$writer->save('php://output');
        $fileName = 'seo';
        $fileType = 'xlsx';
        $writer->save($fileName.'.'.$fileType);


    }


    public function curl_request($url, $data=null, $method='get', $https=true)
    {
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
        curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        }
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');//百度返回的内容进行了gzip压缩,需要用这个设置解析
        //curl模拟头部信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: */*',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
            'Connection: keep-alive',
            'Host: www.baidu.com',
            'is_referer: https://www.baidu.com/',
            'is_xhr: 1',
            'Referer: https://www.baidu.com/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
            'X-Requested-With: XMLHttpRequest',
        ));
        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
        }
        $result = curl_exec($ch);//执行请求
        curl_close($ch);//关闭curl，释放资源
        $result = mb_convert_encoding($result, 'utf-8', 'GBK,UTF-8,ASCII,gb2312');//百度默认编码是gb2312 这个设置转化为utf8编码
        return $result;
    }
}



?>