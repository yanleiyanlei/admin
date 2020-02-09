<?php
namespace app\index\controller;

use think\Controller;
use QL\QueryList;
use think\Cache;
use QL\Ext\PhantomJs;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class Qulist extends Controller
{
    public function index()
    {
        echo __DIR__;exit;
        //采集某页面所有的图片
        //$data = QueryList::get('http://cms.querylist.cc/bizhi/453.html')->find('img')->attrs('src');
        //打印结果
        //print_r($data->all());

        $url = 'https://www.ithome.com/html/discovery/358585.htm';
// 定义采集规则
        $rules = [
            // 采集文章标题
            'title' => ['h1', 'text'],
            // 采集文章作者
            'author' => ['#author_baidu>strong', 'text'],
            // 采集文章内容
            'content' => ['.post_content', 'html']
        ];
        $rt = QueryList::get($url)->rules($rules)->query()->getData();

        print_r($rt->all());
    }

    /**
     * @return array
     * 获取列表 主要是要url
     */
    public function toutiao()
    {
        $keyword = input('key');
        $seachword = urlencode(input('word'));
        if ($touarray = Cache::get($keyword)) {
            echo '读取缓存';
print_r($touarray);

        } else {

//        $headers = array(
//            'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
//            'Referer'    => 'http://www.163.com'
//        );
//        $url = "https://www.toutiao.com/api/search/content/?aid=24&app_name=web_search&offset=20&format=json&keyword=seo";
//        $cookie = "tt_webid=6789084339227493896;s_v_web_id=k67k4h3i_9A7QNkJV_i833_4VdM_8eRN_kBXJqayYWpOg;csrftoken=5791ba30302460b16f95130ece4e2863;__tasessionId=26yzul0xn1580884971238";
//        $data = curl_get($url,$cookie,$headers);
//        $data = json_decode($data,true);
//            //$data = curl_get("https://www.baidu.com");
//        Cache::set('toutiao1',$data,7200);


            $headers = array(
                'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/2008052906 Firefox/3.0',
                'Referer' => 'http://www.163.com'
            );

            $cookie = "tt_webid=6789084339227493896;s_v_web_id=k67k4h3i_9A7QNkJV_i833_4VdM_8eRN_kBXJqayYWpOg;csrftoken=5791ba30302460b16f95130ece4e2863;__tasessionId=26yzul0xn1580884971238";

            $k = 10000;//先循环10000/20 = 500次吧
            for ($i = 0; $i <= $k; $i = $i + 20) {

                 $url = "https://www.toutiao.com/api/search/content/?aid=24&app_name=web_search&offset=$i&format=json&keyword=".$seachword;
                $data = curl_get($url, $cookie, $headers);
                $datall = json_decode($data, true);

                $data = $datall['data'];
                //print_r($data);exit;
                $has_more = $datall['has_more'];
                if ($has_more == 0) break;
                foreach ($data as $key => $valdata) {
                    if ($valdata['abstract'] && $valdata['title'] && $valdata['share_url']) {
                        $toutitle['title'] = $valdata['title'];
                        $toutitle['url'] = "https://www.toutiao.com/a" . $valdata['id'];
                        $touarray[] = $toutitle;
                    }

                }
            }
            Cache::set($keyword, $touarray, 7200);
            echo 'offset值：' . $i;
        }
            echo '----end';
//        print_r($touarray);
    }


    public function  cont(){

//        $ql = QueryList::get('https://www.baidu.com/s?wd=QueryList')->find()->html();
//       // $titles = $ql->find('#content_left')->html(); //获取搜索结果标题列表
//        echo 'test';
//        print_r($ql);
//        exit;


        $url = array(
            array( 'url'=> 'https://www.toutiao.com/a6788025204116292104'),
            array( 'url'=> 'https://www.toutiao.com/a6679938807476781579')
            );
        $key="tou";
        //$url = Cache::get($key);
//print_r($url);exit;
foreach ($url as $key=>$vurl) {
    $rt = QueryList::get($vurl['url'])->find()->html();
//    $rt = file_get_contents($vurl['url']);
//    $rt = $this->curl_tou($vurl['url']);
//    $rt = 'aaa';
//    print_r($rt);exit;
    $preg = "#title: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
    preg_match_all($preg, $rt, $result);//php正则表达式
    $news[$key]['title'] = trim(htmlspecialchars_decode($result[1][0]), " \" ");

    $preg = "#content: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
    preg_match_all($preg, $rt, $res);//php正则表达式
    $str = preg_replace("/\\\\u([0-9a-f]{3,4})/i", "&#x\\1;", $res[1][0]);

    $str = html_entity_decode($str, null, 'UTF-8');

    $str = preg_replace("/<img.*?>/si", "", $str);

    $baiduseach = $this->testbai($news[$key]['title']);
    $news[$key]['bai'] = $baiduseach ? $baiduseach:" a";
    //内容
    $news[$key]['con'] = trim($str, "&quot;");

//    print_r($baiduseach);
}

//$this->wexcel($news);
//echo 'enda';
print_r($news);

    }




    public function test()
    {

        $url = 'https://www.toutiao.com/a6679938807476781579/';

        $rt = QueryList::get($url)->find()->html();
        $preg="#title: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
        preg_match_all( $preg,$rt,$result);//php正则表达式
        //$data = json_decode($result[1], true);
        $new = $result[1][0];
        //print_r($new);
        echo '-----------------------------------------';
        print_r($result[1]);
        echo '::::';
        echo htmlspecialchars_decode($result[1][0]);

        $preg="#content: '(.*)'.slice#isU";//正则的规则是寻找一个title标签的内容
        preg_match_all( $preg,$rt,$res);//php正则表达式
        $str = preg_replace("/\\\\u([0-9a-f]{3,4})/i", "&#x\\1;", $res[1][0]);

         $str = html_entity_decode($str, null, 'UTF-8');

        $str = preg_replace("/<img.*?>/si","",$str);

        echo $str;


        //$rt = $this->html2text($rt);
        //print_r($rt);

        $ql = QueryList::getInstance();
// 安装时需要设置PhantomJS二进制文件路径
        $ql->use(PhantomJs::class,'E:\phantomjs\phantomjs-2.1.1-windows\bin\phantomjs.exe');
//or Custom function name
       // $ql->use(PhantomJs::class,'E:\phantomjs\phantomjs-2.1.1-windows\bin\phantomjs.exe','browser');

        //这种 方式 可以用
//        $rules = array(
//                'title' => ['.article-title', 'text'],
//                'content' => ['.article-content>div', 'html']
//
//
//        );
//        $html = $ql->browser($url)->rules($rules)->query()->getData();
//        print_r($html->all());



/*        $data['title'] = $ql->browser($url)->find('.article-title')->text();
        $eles= $ql->browser($url)->find('.article-content>div');
        $eles->find('img')->remove();//过滤img标签
        $data['content'] = $eles->html();
        print_r($data);*/










//        $ql = QueryList::get('https://www.ithome.com/html/discovery/358585.htm');
//
//        $rt = [];
//// 采集文章标题
//        $rt['title'] = $ql->find('h1')->text();
//// 采集文章作者
//        $rt['author'] = $ql->find('#author_baidu>strong')->text();
//// 采集文章内容
//        $rt['content'] = $ql->find('.post_content')->html();
//
//        print_r($rt);
    }

    public function wexcel($datan)
    {



       /* $datan = array(
                   array('title'=>'abc','bai'=>'test','con'=>'con'),
                   array('title'=>'abc','bai'=>'test','con'=>'con'),

        );*/
//        print_r($datan);exit;
        $title = ['第一行标题', '第二行标题'];

        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 方法一，使用 setCellValueByColumnAndRow
        //表头
        //设置单元格内容
        foreach ($title as $key => $value) {
            // 单元格内容写入   第一行
            //$sheet->setCellValueByColumnAndRow($key + 1, 1, $value);
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
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="1.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');


    }

    /**
     * 抓取百度 相关搜索
     * 使用 phantomjs  效率不高  不推荐使用
     *
     */

    public function testb()
    {
$url = 'https://www.baidu.com/s?wd=php';
        $ql = QueryList::getInstance();
// 安装时需要设置PhantomJS二进制文件路径
        $ql->use(PhantomJs::class,'E:\phantomjs\phantomjs-2.1.1-windows\bin\phantomjs.exe');

        $rules = array(
            'title'=>array('h3','text'),
            'link'=>array('h3>a','href')


        );
        $data = $ql->browser($url)->find('#rs th')->texts();
        print_r($data);

    }


  public  function html2text($str) {
        $str = preg_replace("/<style .*?<\\/style>/is", "", $str);
        $str = preg_replace("/<script .*?<\\/script>/is", "", $str);
        $str = preg_replace("/<br \\s*\\/>/i", ">>>>", $str);
        $str = preg_replace("/<\\/?p>/i", ">>>>", $str);
        $str = preg_replace("/<\\/?td>/i", "", $str);
        $str = preg_replace("/<\\/?div>/i", ">>>>", $str);
        $str = preg_replace("/<\\/?blockquote>/i", "", $str);
        $str = preg_replace("/<\\/?li>/i", ">>>>", $str);
        $str = preg_replace("/ /i", " ", $str);
        $str = preg_replace("/ /i", " ", $str);
        $str = preg_replace("/&/i", "&", $str);
        $str = preg_replace("/&/i", "&", $str);
        $str = preg_replace("/</i", "<", $str);
        $str = preg_replace("/</i", "<", $str);
        $str = preg_replace("/“/i", '"', $str);
        $str = preg_replace("/&ldquo/i", '"', $str);
        $str = preg_replace("/‘/i", "'", $str);
        $str = preg_replace("/&lsquo/i", "'", $str);
        $str = preg_replace("/'/i", "'", $str);
        $str = preg_replace("/&rsquo/i", "'", $str);
        $str = preg_replace("/>/i", ">", $str);
        $str = preg_replace("/>/i", ">", $str);
        $str = preg_replace("/”/i", '"', $str);
        $str = preg_replace("/&rdquo/i", '"', $str);
        $str = strip_tags($str);
        $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/&#.*?;/i", "", $str);
        return $str;
    }

// 有待再研究

    public function bai(){

$data = QueryList::get('http://www.baidu.com/s?wd=safe',[
            'headers' => [
//                'Accept'=>'*/*',
//                'Cache-Control'=>'no-cache',
//                'Host'=>'www.baidu.com',
//                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',
//                "Accept-Language"=>"zh-CN,zh;q=0.9,en;q=0.8,la;q=0.7",
                'Accept-Encoding'=>'gzip, deflate, br',
                'Connection'=>'keep-alive',
//                'Cookie'    => 'BIDUPSID=4CDF5B76EEC376E49BD904BE86A0AC59;PSTM=1581053579;
//                              BAIDUID=4CDF5B76EEC376E466A37151B534BAFD%3AFG%3D1;delPer=0;
//                              BD_CK_SAM=1;PSINO=1;BDSVRTM=10;H_PS_PSSID=1441_21111'
                 "User-Agent"=> "Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0 20" ,
                 "Accept-Language"=>"zh-CN,zh;q=0.9,en;q=0.8,la;q=0.7"
                  ]
               ])->find()->html();


        print_r($data);

    }

    /**
     * @param $word
     * @return string
     * 还行， 抓取百度相关搜索
     */


    public function testbai($word){
        //echo $word = "网站SEO优化，7个基本的方案与步骤";
        $url = "https://www.baidu.com/s?ie=utf-8&wd=".$word;
        $ua = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11".rand(1,99);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        $res = curl_exec($ch);

        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];

        $str = implode(',',$reach_word);
        return  $str;
//print_r($reach_word);
    }



    /**
     * curl 抓取百度 相关搜索
     * 不太好用
     */

    public function baidu(){
        $str =  "SEO已死，真的吗，十年SEO从业者的思考";
        echo $str;
        $key_word = urlencode($str);//需要对关键词进行url解析,否者部分带字符的标题会返回空
        $url = 'https://www.baidu.com/s?ie=UTF-8&wd='.$key_word;

        $res = $this->curl_request($url);
//echo $res;

        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];


 print_r(  $reach_word);


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
            'User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.5)',
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




    /**
     * @param $word
     * @return string
     * 还行， 抓取百度相关搜索
     */


    public function testbaidu(){
        echo $word = "SEO已死，真的吗，十年SEO从业者的思考";
        $url = "https://www.baidu.com/s?ie=utf-8&wd=".$word;
        $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36".rand(1,99);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, $ua);

        $res = curl_exec($ch);

        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

        preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
        $reach_word = @$match[1];

        $str = implode(',',$reach_word);
//        return  $str;
print_r($reach_word);
    }


   public function getArray($kw){
         $url="http://www.baidu.com/s?wd=".$kw;
     $curl=curl_init();
     curl_setopt($curl,CURLOPT_URL,$url);
     curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
     //curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
     $data = curl_exec($curl);
     $data = preg_replace("/[\r\n\t]+/","",$data);
     return $data;
    }

// 域名转换 因为默认是有些图片不显示 是因为域名不对
   public function getHtmlByContent($html)
   {
       preg_match_all('/<table cellpadding="0">.*<\/table>/', $html, $res);
       //  print_r($res[0][0]);
       preg_match_all('/<th>.*?<\/th>/', $res[0][0], $result);
       $result = $result[0];
       if (!empty($result) && is_array($result)) {
           foreach ($result as $k => $v) {
               $result[$k] = strip_tags($v);
           }
       }//print_r($result);
       return $result;
   }


    /**
     *
     * 比较稳定,最好用
     * 从postman 里复制的，也许有关系
     */

public function testbaidua()
{
    echo 'test闫磊';
    $str = "SEO已死，真的吗，十年SEO从业者的思考";
    $key_word = urlencode($str);
    $url = "https://www.baidu.com/s?ie=utf-8&wd=".$key_word;

    $header = array (
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',
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


    //$result = mb_convert_encoding($content, 'utf-8', 'GBK,UTF-8,ASCII,gb2312');
//输出结果
    $reach_word = substr($res,strpos($res, '<div id="rs"><div class="tt">相关搜索'),strpos($res, '<div id="page" >')-strpos($res, '<div id="rs"><div class="tt">相关搜索') );//截取需要的内容
//print_r($reach_word);

    preg_match_all('/<a.*?">(.*?)<\/a>/', $reach_word,$match);//正则匹配第一个搜索词
    $reach_word = @$match[1];

    $str = implode(',',$reach_word);
//        return  $str;
    print_r($reach_word);
//    echo $content;
}


public function curl_tou($url){

    $header = array (
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36',
        'Accept: */*',
        'Cache-Control:no-cache',
        'Postman-Token:a2326fdc-34fa-4daa-b801-5f74f8721184',
        'Host: www.toutiao.com',
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

    return $res;

}
}
