<?php
require_once './include/common.inc.php';

// 限制ip;
//if (!in_array($onlineip, $iparr)) {
//    exit('false');
//}

$roomid = $request->get('roomid'); // 房间号
$msg = $request->get('msg');
$plan = urlencode($msg);        // 消息
//echo $roomid;
if(!empty($plan)){
    $msgid = 'b2d5cc35';
    $uname = '计划员';
    $tname = '大家';
    $muid = '3468';
    $rid = $roomid;
    $tid = 'ALL';
    $style = '';
    $tanmu = 'false';
    $privacy = 'false';
    $ugid = "4";
    $onlineip = '127.0.0.1';
    //判断是否为空
    if (empty($msg)) {
        return;
    }
    //数据库存储用户名 过滤 后三位
    $sql = "insert into {$tablepre}chatlog(rid,uid,tuid,uname,tname,p,style,msg,mtime,ugid,msgid,ip,state,type,device)
				  values('$rid','$muid','$tid','$uname','$tname','$privacy','$style','$msg'," . gdate() . ",'$ugid','$msgid','$onlineip','0','0','')";
    $db->query($sql);
    $_SESSION['last_msg_content'] = $msg;
    $_SESSION['last_msg_time'] = time();
    $data['state'] = 'success';
    echo json_encode($data);
}
echo $plan;
exit;
    ?>

