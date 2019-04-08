<?php

// [CH] 以下变量请根据空间商提供的账号参数修改,如有疑问,请联系服务器提供商
// workman 计划中也存在数据库  同时请修改workerman-chat\Applications\cpplan\include\db_mysql6.class.php
	$dbhost = 'localhost';			// 数据库服务器
	$dbuser = 'root';			// 数据库用户名
	$dbpw = 'root';				// 数据库密码
	$dbname = '46465_com';			// 数据库名开

// [CH] 投入使用后不能修改的变量

	$tablepre = 'chat_';   			// 表名前缀, 同一数据库安装多个聊天室请修改此处

// [CH] 小心修改以下变量, 否则可能导致无法正常使用

	$dbcharset = '';			// MySQL 字符集, 可选 'gbk', 'big5', 'utf8', 'latin1', 留空为按照论坛字符集设定
	$charset = 'utf-8';			// 页面默认字符集, 可选 'gbk', 'big5', 'utf-8'
	$def_cfg='1';
	$goldname="金币";
	$discount=0.5;//礼物折扣率
	$adminemail = 'admin@nuoyun.tv';		// 系统管理员 Email
	date_default_timezone_set("Asia/Shanghai");
	$timeoffset = 0; //时差 单位 秒
	$upgrade=15; //15小时升一级
	$tserver_key="2w0p1q7u8i9o";//服务器连接密钥！
	
	$guest=true;//游客登录 true开启 false关闭
	$reg_unallowable="|江泽民|毛泽东|邓小平"; //注册屏蔽关键字 并为空以"|" 开头并隔开
	$msg_unallowable="黑平台|返佣|iframe|script|傻逼"; //聊天屏蔽关键字 空以"|" 并隔开
	$ipmax=5;//同一IP每天限制注册次数
	$default_roomid=6001; //默认房间ID
        $sign_index_credit=50; //签到获取积分的阶梯
        $socket_key='8i2w9$^p8iTY1q72';//通信连接秘钥
    // +----------------------------------------------------------------------
    // | redis设置
    // +----------------------------------------------------------------------
        $redis_state=false;  //是否开启redis
        $redis_host='127.0.0.1';
        $redis_port='6379';
        $redis_password='nuoyuntv2017';

// 前台页面连接地址
$GLOBALS['websocket_port'] = 7006;
$GLOBALS['websocket_ip'] = '192.168.0.200';
	

?>
