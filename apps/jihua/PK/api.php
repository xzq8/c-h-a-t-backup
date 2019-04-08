<?php 
require_once( './functions.php' );

//设置post的数据 
$post = array ( 
    '__EVENTTARGET' => 'Button_Login', 
    '__EVENTARGUMENT' => '', 
    '__VIEWSTATE' => '/wEPDwUKLTE3NzczMDUxN2RkH3Ab2TF6Ovs63nHYK72PUotcRVgLpDKn/7VhXpoClUM=', 
    '__VIEWSTATEGENERATOR' => 'C2EE9ABB', 
    '__EVENTVALIDATION' => '/wEdAAPkHPLoLyOoKZPi55F3NtPZpKc4oMUu2BhIsJefmk76YO7Av5ap82LVMZmO6D22yKEvAhZw1Yb1e9DwiyUGsvNiQ0qbWk9hY69iOpjLigTrPw==', 
    'pwdfield' => 'faf3f8',
); 

if( $_GET['apiname'] == 'pk10' ){
	// pk10
	$post['pwdfield'] = '106955';

	$url = "http://p.softrgb.com/login.aspx?prev=http%3a%2f%2fp.softrgb.com%2fdefault.aspx%3fu%3dmark88888%26t%3dpks%26s%3d5eaac55685&u=mark88888&t=pks&s=5eaac55685"; 

	$cookie = dirname(__FILE__) . '/pk10.txt'; 

	$url2 = "http://p.softrgb.com/PlanData.ashx?u=mark88888&t=pks&s=5eaac55685&d=&random=".time().mt_rand(1000,9999); 
	
} else if( $_GET['apiname'] == 'ssc'){

	// zhixuan
	$post['pwdfield'] = '82d61b';

	//登录地址 
	$url = "http://p.softrgb.com/login.aspx?prev=http%3a%2f%2fp.softrgb.com%2fdefault.aspx%3fu%3dmark99999%26t%3dcqssc%26s%3d10f68e35f0&u=mark99999&t=cqssc&s=10f68e35f0"; 

	$cookie = dirname(__FILE__) . '/zhixuan.txt'; 
	$url2 = "http://p.softrgb.com/PlanData.ashx?u=mark99999&t=cqssc&s=10f68e35f0&d=&random=".time().mt_rand(1000,9999);

	/*
	// ssc
	$post['pwdfield'] = '557484';

	//登录地址 
	$url = "http://p.softrgb.com/login.aspx?prev=http%3a%2f%2fp.softrgb.com%2fdefault.aspx%3fu%3dxiongsan888%26t%3dcqssc%26s%3d4ecd25db40&u=xiongsan888&t=cqssc&s=4ecd25db40"; 

	$cookie = dirname(__FILE__) . '/ssc.txt'; 

	$url2 = "http://p.softrgb.com/PlanData.ashx?u=xiongsan888&t=cqssc&s=4ecd25db40&d=&random=".time().mt_rand(1000,9999); 
	*/
} else {

	// zhixuan
	$post['pwdfield'] = '82d61b';

	//登录地址 
	$url = "http://p.softrgb.com/login.aspx?prev=http%3a%2f%2fp.softrgb.com%2fdefault.aspx%3fu%3dmark99999%26t%3dcqssc%26s%3d10f68e35f0&u=mark99999&t=cqssc&s=10f68e35f0"; 

	$cookie = dirname(__FILE__) . '/zhixuan.txt'; 
	$url2 = "http://p.softrgb.com/PlanData.ashx?u=mark99999&t=cqssc&s=10f68e35f0&d=&random=".time().mt_rand(1000,9999); 
}

//获取登录页的信息 
$jsonStr = get_content($url2, $cookie); 
$json = json_decode($jsonStr,true);
$content = $json['PlanData'];

if( empty( $content ) ){

    //模拟登录 
    login_post($url, $cookie, $post); 
    //获取登录页的信息 
    $jsonStr = get_content($url2, $cookie); 
	$json = json_decode($jsonStr,true);
	$content = $json['PlanData'];


    if( empty($content) ){
        exit('获取失败');
    }
}

switch ($_GET['api']) {
	// pk10
	case 'pk10kaijiang':
		$kaijiang = kaijianghaoma( $content );
		echo json_encode($kaijiang);
		exit();
		break;

	case 'pk10jilu':
		$jilu['content'] = $content;
		preg_match(GUIZE2, $content,$dengkai);
		$jilu['dengkai'] = $dengkai[0];
		echo json_encode($jilu);
		exit();
		break;

	// pk10 end

	// ssc 
	case 'ssckaijiang':
		
		$kaijiang = kaijianghaoma( $content );
		$search = date('ymd',$_SERVER['REQUEST_TIME']);
		$kaijiang['qishu'] = str_replace( $search, '', $kaijiang['qishu'] );
		echo json_encode($kaijiang);
		break;

	case 'sscjilu':
		$aArr = explode('==============================', $content);
		$bArr = explode('------------------------------', $aArr[1] );
		$aArr[1] = $bArr[0];
		$newStr = implode('==============================',$aArr);
		$jilu['content'] = $newStr;
		preg_match(GUIZE3, $content,$dengkai);
		$jilu['dengkai'] = $dengkai[0];
		echo json_encode($jilu);
		exit();
		break;
	// ssc end
	
	case 'houer':
		$aArr = explode('==============================', $content);
		$bArr = explode('------------------------------', $aArr[1] );
		$aArr[1] = $bArr[1];
		$newStr = implode('==============================',$aArr);
		$jilu['content'] = $newStr;
		preg_match(GUIZE3, $content,$dengkai);
		$jilu['dengkai'] = $dengkai[0];
		echo json_encode($jilu);
		break;

	case 'housan':
		$aArr = explode('==============================', $content);
		$bArr = explode('------------------------------', $aArr[1] );
		$aArr[1] = $bArr[2];
		$newStr = implode('==============================',$aArr);
		$jilu['content'] = $newStr;
		preg_match(GUIZE3, $content,$dengkai);
		$jilu['dengkai'] = $dengkai[0];
		echo json_encode($jilu);
		break;

	case 'houliu':
		$aArr = explode('==============================', $content);
		$bArr = explode('------------------------------', $aArr[1] );
		$aArr[1] = $bArr[3];
		$newStr = implode('==============================',$aArr);
		$jilu['content'] = $newStr;
		preg_match(GUIZE3, $content,$dengkai);
		$jilu['dengkai'] = $dengkai[0];
		echo json_encode($jilu);
		break;
	
	default:
		exit('error');
		break;
}

 ?>