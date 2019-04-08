<?php
date_default_timezone_set('PRC'); 

define('GUIZE1',"/<div id\=\"PlanDataDiv\".*?>(.*?)<\/div>/is");
define('GUIZE2',"/\d+\-\d+期 冠军\【[\d|\s]+】\d+期 等开/i");
define('GUIZE3',"/\d+\-\d+期 个位\【[\d|\s]+】\d+期 等开/i");

function kaijianghaoma( $content )
{
    preg_match("/第\s*(\d+).*?/is", $content, $qishu );

    preg_match_all("/期开奖号码\：(.*?)<br\/>/i", $content, $haoma );

    return array('qishu'=>$qishu[1],'haoma'=>$haoma[1][0]);
}

//模拟登录 
function login_post($url, $cookie, $post) { 
    $curl = curl_init();//初始化curl模块 
    curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址 
    curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息 
    curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中 
    curl_setopt($curl, CURLOPT_POST, 1);//post方式提交 
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息 
    $buf = curl_exec($curl);//执行cURL 
    curl_close($curl);//关闭cURL资源，并且释放系统资源 
}

//登录成功后获取数据 
function get_content($url, $cookie) { 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HEADER, 0); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie 
    $rs = curl_exec($ch); //执行cURL抓取页面内容 
    curl_close($ch); 
    return $rs; 
} 

 ?>