<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ERROR | E_PARSE );
 function curl_get($url,$cookie=0,$header=0)
{

    $info = curl_init();
    curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($info,CURLOPT_HEADER,0);
    curl_setopt($info,CURLOPT_NOBODY,0);
    curl_setopt($info,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($info,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($info,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($info,CURLOPT_URL,$url);
    if($cookie){
        curl_setopt($info, CURLOPT_COOKIE, $cookie);
    }
    if($header){
        curl_setopt($info, CURLOPT_HTTPHEADER, $header);
    }

    $output = curl_exec($info);
    curl_close($info);
    return $output;
}
