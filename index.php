<?php
require_once './include/common.inc.php';
//判断是否保存了密码,保存密码到cookie了，就自动登录
if (!isset($_SESSION['login_uid']) && !empty($_COOKIE["userstring"])) {
    $userstring = decrypt($_COOKIE["userstring"], $tserver_key);
    $saveuser = explode("++", $userstring);
    $msg = user_login($saveuser['0'], $saveuser['1'], $rid);
}
if ($cfg['config']['loginguest'] == '0' && (!isset($_SESSION['login_uid']) || $_SESSION['login_uid'] == '0')) header('location:./logging.php?rid=' . $rid);

//房间状态

if ($cfg['config']['state'] == '2' && $_SESSION['room_' . $cfg['config']['roomid']] != true) {
    header("location:room/login.php?rid=" . $rid);
    exit();
}
if ($cfg['config']['state'] == '0') {
    exit("<script>location.href='room/error.php?rid=" . $rid . "&msg=房间处于关闭状态！请稍候……'</script>");
    exit();
}
if ($cfg['config']['state'] == '3') {
    system_open_time($cfg['config']['sysstart'], $cfg['config']['sysend'], $rid);
}
//游客登录
if (!isset($_SESSION['login_uid']) && $cfg['config']['loginguest'] == "1") {
    gusetLogin();
}

//是否登录
if (!isset($_SESSION['login_uid'])) {
    header("location:./logging.php?rid=" . $rid);
    exit();
}

//用户信息
$uid = $_SESSION['login_uid'];
$db->query("update {$tablepre}members set regip='$onlineip' where uid='{$uid}'");
$userinfo = $db->fetch_row($db->query("select m.*,ms.* from {$tablepre}members m,{$tablepre}memberfields ms  where m.uid=ms.uid and m.uid='{$uid}'"));
$_SESSION['login_gid'] = $userinfo['gid'];
//游客

if ($_SESSION['login_uid'] == 0) {
    $userinfo['username'] = $userinfo['nickname'] = $_SESSION['login_nick'];
    $userinfo['sex'] = $_SESSION['login_sex'];
    $userinfo['uid'] = $_SESSION['login_guest_uid'];
    $userinfo['fuser'] = $_SESSION['guest_fuser'];
    $userinfo['redbags'] = '0';
    $userinfo['banspeak'] = $_SESSION['guest_banspeak'];
}

//黑名单
$query = $db->query("select * from {$tablepre}ban where (username='{$userinfo['username']}' or ip='{$onlineip}') and losttime>" . gdate() . " limit 1");
while ($row = $db->fetch_row($query)) {
    exit("<script>location.href='room/error.php?msg=用户名或IP受限！过期时间" . date("Y-m-d H:i:s", $row['losttime']) . "'</script>");
    exit();
}
//聊天过滤词汇
$msg_unallowable = $msg_unallowable . $global['config']['msgban'];
$msg_unallowable = json_encode($msg_unallowable);
//用户组
$query = $db->query("select * from {$tablepre}auth_group order by ov desc");
while ($row = $db->fetch_row($query)) {
    if ($row['id'] == 0) {
        $groupli .= '<div id="group_rebots"></div>';
    }
    $groupli .= "<div id='group_{$row['id']}'></div>";
    $grouparr .= "grouparr['{$row['id']}']=" . json_encode($row) . ";\n";
    $group["m" . $row['id']] = $row;
}
//聊天历史记录
//$query=$db->query("select * from {$tablepre}chatlog where rid='".$rid."' and p='false' and state!='1' and `type`='0' order by id desc limit 0,30 ");
$query = $db->query("select c.*,m.touxiang from {$tablepre}chatlog c,{$tablepre}members m where c.uid=m.uid  and c.rid='" . $rid . "' and c.p='false' and c.state!='1' and c.type='0' order by c.mtime desc,c.id desc limit 0,20 ");

while ($row = $db->fetch_row($query)) {
    if (empty($row['touxiang'])) {
        $row['touxiang'] = '/face/p1/null.jpg';
    }
    $row['msg'] = html_entity_decode($row['msg']);
    $who = " notmine ";
    if ($row['ugid'] == 2) {
        $who = " manage";
    } else if ($row['ugid'] == 3 || $row['ugid'] == 4) {
        $who = " teacher";
    }
    if ($row['tuid'] == "Sendredbag") {
        $who = " redbag";
    }
    if ($row['style'] == "appsend") {
        $who = " appsend";
    }
    if ($row['tuid'] != "ALL" && $row['tuid'] != "Sendredbag") {
        if ($row['tuid'] == "hongbao") {
            $omsg = $row['msg'] . $omsg;

        } else {
            $omsg = "<div style='clear:both;'></div><div class='msg{$who}'  id='{$row[msgid]}'><div class='msg_head'><img src='" . $row['touxiang'] . "' class='msg_group_ico' title='" . $group["m" . $row[ugid]][title] . "-" . $group["m" . $row[ugid]][sn] . "'></div><div class='msg_content'><div><font class='u'  onclick='ToUser.set(\"{$row[uid]}\",\"{$row[uname]}\")'>{$row[uname]}</font> &nbsp;&nbsp;  <font class='dui'>对</font> <font class='u' onclick='ToUser.set(\"{$row[tuid]}\",\"{$row[tname]}\")'>{$row[tname]}</font> 说 <font class='date'>" . date('Y-m-d H:i:s', $row[mtime]) . "</font></div><div class='layim_chatsay'><font>{$row[msg]}</font></div></div></div><div style='clear:both;'></div>" . $omsg;
            // $omsg="<div style='clear:both;'></div><div class='lts_right3' id='{$row[msgid]}' fromconnid=''><span class='time'>".date('H:i:s',$row[mtime])."</span><img src='".$group["m".$row[ugid]][ico]."' title='".$group["m".$row[ugid]][title]."-".$group["m".$row[ugid]][sn]."' class='RoomUserRole'><a class='name' href='javascript:void(0)' onclick='ToUser.set(\"{$row[uid]}\",\"{$row[uname]}\")'>{$row[uname]}</a><font class='dui'>对</font><a class='name' href='javascript:void(0)' onclick='ToUser.set(\"{$row[tuid]}\",\"{$row[tname]}\")'>{$row[tname]}</a><span class='to_m' style='{$row[style]};'>{$row[msg]}</span></div>".$omsg;
        }
    } else {
        $first = "<div style='clear:both;'></div><div class='msg{$who}' id='{$row[msgid]}'><div class='msg_head'><img src='" . $row['touxiang'] . "' class='msg_group_ico' title='" . $group["m" . $row[ugid]][title] . "-" . $group["m" . $row[ugid]][sn] . "'></div><div class='msg_content'><div><font class='u'  onclick='ToUser.set(\"{$row[uid]}\",\"{$row[uname]}\")'>{$row[uname]}</font>&nbsp;&nbsp; <font class='date'>" . date('Y-m-d H:i:s', $row[mtime]) . "</font>";

        $deviceType = $row[device];
        $middle = '';
        if ($deviceType != null && $deviceType != '' && $deviceType != 'computer') {
            $transDeviceType = '安卓手机';
            if ($deviceType == 'ios') {
                $transDeviceType = '苹果手机';
            }
            $middle = '<img src="/images/ny_' . $deviceType . '_icon.png" title="' . $transDeviceType . '">';
        }
        $omsg = $first . $middle . "</div><div class='layim_chatsay'><font>{$row[msg]}</font></div></div></div><div style='clear:both;'></div>" . $omsg;
        //  $omsg="<div style='clear:both;'></div><div class='lts_right3' id='{$row[msgid]}' fromconnid=''><span class='time'>".date('H:i:s',$row[mtime])."</span><img src='".$group["m".$row[ugid]][ico]."' title='".$group["m".$row[ugid]][title]."-".$group["m".$row[ugid]][sn]."' class='RoomUserRole'><a class='name' href='javascript:void(0)' onclick='ToUser.set(\"{$row[uid]}\",\"{$row[uname]}\")'>{$row[uname]}</a><span class='to_m' style='{$row[style]};'>{$row[msg]}</span></div>".$omsg;
    }
}
$omsg = $omsg . '<li class="history-hr-wrap"><div class="history-hr"></div><div class="history-hr-text">以上是历史消息</div></li>';
//游客观看直播时间
if (!isset($_COOKIE['first_access_time'])) {

    setcookie("first_access_time", time(), time() + 315360000);

}


//其他处理
$ts = explode(':', $global['config']['tserver']);
if (!isset($_SESSION['room_' . $uid . '_' . $cfg['config']['roomid']])) {
    $laiyuan = $_SERVER['HTTP_REFERER']; //获取访客来源url
    if ($laiyuan == '') {
        $laiyuan = '直接输入网址或打开标签';
    }
    $db->query("insert into  {$tablepre}msgs(rid,ugid,uid,uname,tuid,tname,mtime,ip,msg,`type`,laiyuan,roomid)values('{$rid}','{$userinfo[gid]}','{$userinfo[uid]}','{$userinfo[username]}','{$cfg[config][defvideo]}','{$cfg[config][defvideonick]}','" . gdate() . "','{$onlineip}','进入直播间','3','{$laiyuan}','{$rid}')");
    $_SESSION['room_' . $uid . '_' . $cfg['config']['roomid']] = 1;
}
//视频直播调用
$videolive = ($cfg['config']['livetype'] == 1) ? $cfg['config']['yyfp'] : $cfg['config']['livefp'];

$videolive = html_entity_decode($videolive);
$bet = $cfg['config']['yyfp'];
$bet = html_entity_decode($bet);

//是否为房间管理
$room_admin = check_auth('room_admin');
$_SESSION['room_admin'] = $room_admin;
//房间二维码地址
$qrcode = 'http://' . $_SERVER['HTTP_HOST'] . '/room/m/?rid=' . $rid;
//如果没有头像调用默认头像
if (empty($userinfo['touxiang'])) {
    $userinfo['touxiang'] = '/face/p1/null.jpg';
}

$domain = $_SERVER['SERVER_NAME'];
$domain_bind = $db->fetch_row($db->query("select *  from {$tablepre}domain_bind where domain='{$domain}'"));
$planurl = empty($domain_bind) ? $videolive : $domain_bind['url'];


?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta name="renderer" content="webkit">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=7; IE=EDGE"/>
    <title><?= $cfg['config']['title'] ?></title>
    <meta name="keywords" content="<?= $cfg['config']['keyss'] ?>">
    <meta name="description" content="<?= $cfg['config']['dc'] ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= $global['config']['ico'] ?>"/>
    <link href="room/skins/nuoyun.css" rel="stylesheet" type="text/css"/>
    <link href="room/skins/gift.css" rel="stylesheet" type="text/css"/>
    <link href="room/skins/ranklist.css" rel="stylesheet" type="text/css"/>
    <link href="room/images/layim.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="room/libs/css/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css"/>
    <!--[if lt IE 9]>
    <link href="skins/less.css" rel="stylesheet" type="text/css">
    <![endif]-->
    <script src="room/script/jquery.min.js"></script>
    <script type="text/javascript" src="room/script/swfobject.js"></script>
    <script type="text/javascript" src="room/script/web_socket.js"></script>
    <script src="room/script/layer.js"></script>
    <script src="room/script/jquery.nicescroll.min.js"></script>
    <script src="room/script/pastepicture.js"></script>
    <script src="room/script/function.js?<?= time() ?>" type="text/javascript" charset="utf-8"></script>
    <script src="room/script/init.js?<?= time() ?>" type="text/javascript" charset="utf-8"></script>
    <script src="room/script/device.min.js"></script>
    <script>
        if (!device.desktop()) {
            window.location = './room/m/index.php?rid=<?=$rid?>';
        }
        layer.config({extend: ['skin/layer.ext.css']});
        var UserList;
        var ToUser;
        var VideoLoaded = false;
        var My = {
            rid: '<?=$rid?>',
            roomid: '<?=$rid?>',
            chatid: '<?=$userinfo['uid']?>',
            name: '<?=$userinfo['username']?>',
            nick: '<?=$userinfo['nickname']?>',
            sex: '<?=$userinfo['sex']?>',
            touxiang: '<?=$userinfo['touxiang']?>',
            age: '<?=$userinfo['banspeak']?>',
            fuser: '<?=$userinfo['fuser']?>',
            qx: '<?=$room_admin ? '1' : '0'?>',
            ip: '<?=$onlineip?>',
            vip: '<?=$userinfo['fuser']?>',
            color: '<?=$userinfo['gid']?>',
            cam: '<?=$userinfo['face']?>',
            state: '0',
            mood: '<?=$userinfo['mood']?>',
            rst: '<?=$time?>',
            redbags_num: '<?=$userinfo['redbags']?>',
            banspeak: '<?=$userinfo['banspeak']?>'
        }

        var RoomInfo = {
            loginTip: '<?=$cfg['config']['logintip']?>',
            Msglog: '<?=$cfg['config']['msglog']?>',
            msgBlock: '<?=$cfg['config']['msgblock']?>',
            msgAudit: '<?=$cfg['config']['msgaudit']?>',
            defaultTitle: document.title,
            MaxVideo: '10',
            VServer: '<?=$cfg['config']['vserver']?>',
            VideoQ: '',
            TServer: '<?= $GLOBALS['websocket_ip'] ?>',
            TSPort: '<?= $GLOBALS['websocket_port'] ?>',
            PVideo: '<?=$cfg['config']['defvideo']?>',
            AutoPublicVideo: '0',
            AutoSelfVideo: '0',
            type: '1',
            PVideoNick: '<?=$cfg['config']['defvideonick']?>',
            OtherVideoAutoPlayer: '<?=$cfg['config']['livetype']?>',
            r: '<?=$cfg['config']['rebots']?>',
            tiyantime: '<?=$cfg['config']['tiyantime']?>',
            fayanjiange: '<?=$cfg['config']['fayanjiange']?>',
            logintc: '<?=$cfg['config']['logintc']?>',
            bg: '<?=$cfg['config']['bg']?>',
            banspeak: '<?=$cfg['config']['banspeak']?>',
            autoplan: '<?=$global['config']['autoplan']?>'
        }
        var grouparr = new Array();
        <?=$grouparr?>
        var ReLoad;
        var tbox;
        var isIE = document.all;
        var aColor = ['#FFF', '#FFF', '#FFF'];
        var msg_unallowable =<?=$msg_unallowable?>;
        if (typeof console == "undefined") {
            this.console = {
                log: function (msg) {
                }
            };
        }
        WEB_SOCKET_SWF_LOCATION = "room/script/WebSocketMain.swf";
        WEB_SOCKET_DEBUG = true;
        var ws, name, client_list = {}, timeid, reconnect = false;

    </script>
</head>
<body onresize="OnResize()"
      style="background:url(<?= $cfg['config']['bg'] ?>) repeat 0 0 #663366; background-size: 100%;">

<div id="UI_MainBox">
    <script>if (getCookie('bg_img_' + My.rid)) {
            $(document.body).css({
                'background': 'url(' + getCookie('bg_img_' + My.rid) + ')  #408080',
                'background-size': '100%'
            });
        }</script>
    <div id="UI_Head">
        <div class="head" style="background: rgba(50, 50, 50, 0.7) none repeat scroll 0% 0% / auto 50px;">
            <div id="head_box" class="head_box">
                <div class="logo_bg" style="BACKGROUND: url(<?= $cfg['config']['logo'] ?>) no-repeat">
          <span class="f_left" id="favlink">
	<!-- <a class="link1 left1" href="room/ico.php?rid=<?= $rid ?>"><b>保存到桌面</b></a>
         <a class="link1"  id="view-zhibo">
         <i class="fa fa-mobile"></i> 
          <b>手机看直播</b>
          <div class="ewm" id="ewm"><img src="room/qrcode.php?data=<?= $qrcode ?>">手机扫一扫<br>随时随地看直播<i class="sanjiao"></i></div>
          
         </a>-->
              <?php
              $query = $db->query("select * from {$tablepre}apps_manage where s='0' and position='top' order by ov desc ");
              while ($row = $db->fetch_row($query)) {

                  if ($row['id'] == 1) {
                      $row['url'] = '/apps/hd.php?rid=' . $rid;
                  }
                  if ($row['id'] == 16) {
                      $row['url'] = '/apps/sysvote.php?rid=' . $rid;
                  }
                  if ($row['id'] == 8) {
                      $row['url'] = '/apps/course.php?rid=' . $rid;
                  }
                  $obj = json_encode($row);
                  echo "<a class='link1' href='javascript://'  onClick='openApp({$obj})' id='app_$row[id]'><img src='$row[ico]' />{$row[title]}</a>";
              }
              ?>

	
</span>

                    <ul id="nav">
                        <li><img src="room/images/onlineQQ.png">
                            <ul>
                                <?php
                                if ($cfg['config']['viewkefu'] == 1) {
                                    $query = $db->query("select * from  {$tablepre}room_kefu where roomid={$rid} order by id desc ");
                                    while ($row = $db->fetch_row($query)) {
                                        $kefulist2 .= "<li><a  href='http://wpa.qq.com/msgrd?v=3&amp;uin={$row['qqnum']}&amp;site=qq&amp;menu=yes' target='_blank'>{$row['qqtitle']}</a></li>";
                                    }
                                } else {
                                    $time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                                    $time = time() - $time;
                                    $query = $db->query("select m.realname,ms.nickname from {$tablepre}members m,{$tablepre}memberfields ms  where m.uid=ms.uid  and m.gid=3 and (m.roomid={$rid} or m.roomid='') and ((onlinestart<=onlineend  and ms.onlinestart<=$time and  ms.onlineend>=$time) or ( onlinestart>onlineend and ms.onlinestart<=$time and  ms.onlineend<=$time) or ( onlinestart>onlineend and ms.onlinestart>=$time and  ms.onlineend>=$time))  order by m.lastactivity DESC  ");
                                    while ($row = $db->fetch_row($query)) {
                                        $kefulist2 .= "<li><a  href='http://wpa.qq.com/msgrd?v=3&amp;uin={$row['realname']}&amp;site=qq&amp;menu=yes' target='_blank'>{$row['nickname']}</a></li>";
                                    }

                                }
                                echo $kefulist2;
                                ?>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="head_skin"><a href="#" class="dropdown-toggle" id="changeskin"> <i
                                class="fa fa-eyedropper"></i> 换肤<span class="caret"></span></a></div>
                <div class="head_user">

                    <?php
                    if ($_SESSION['login_uid'] > 0) {
                        ?>
                        <div class="userinfo">
                            <a href="javascript:void(0)">
                                <img src='<?= $userinfo['touxiang'] ?>' border="0" class="userimg"/>
                                <?= $userinfo['username'] ?>
                                ▼</a>
                            <div class="account-info-box">
                                <div class="account-pro">
                                    <img src='<?= $userinfo['touxiang'] ?>' class="avatar">
                                    <p class="username"><?= $userinfo['username'] ?></p>
                                    <!-- <p class="account-momoid">余额：<span>0.00</span></p> -->
                                </div>
                                <ul class="account-list">
                                    <li class="account-item oneself-profile"
                                        onClick="openWin(2,false,'room/profiles.php?uid=<?= $userinfo['uid'] ?>&rid=<?= $rid ?>',460,630)">
                                        个人资料
                                    </li>
                                    <li class="account-item exit-btn">
                                        <a href='../logging.php?act=logout&rid=<?= $rid ?>'>退出登录</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <a href="javascript:void(0)" class="login"
                           onClick="openWin(2,false,'room/minilogin.php?rid=<?= $rid ?>',370,340)">登录</a><a
                                href="javascript:void(0)" class="reg"
                                onClick="openWin(2,false,'room/minilogin.php?a=0&rid=<?= $rid ?>',370,525)">注册</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div id="UI_Left">

        <div id="UI_Left2" class="bg_png1">
            <div>
                <!-- <iframe height="78" width="100%" allowTransparency="true" marginwidth="0" marginheight="0"  frameborder="0" scrolling="no" src="./apps/vote.php?rid=<?= $cfg['config']['roomid'] ?>"></iframe> -->
            </div>
            <div id="modules">
                <ul id="modules-box">
                    <?php
                    $query = $db->query("select * from {$tablepre}apps_manage where s='0' and position='left' order by ov desc ");
                    $h = 1;
                    while ($row = $db->fetch_row($query)) {
                        $style = ($h % 3 == 0) ? 'style="margin: 0px 0px 2px;"' : '';
                        if ($row['id'] == 1) {
                            $row['url'] = '/apps/hd.php?rid=' . $rid;
                        }
                        if ($row['id'] == 16) {
                            $row['url'] = '/apps/sysvote.php?rid=' . $rid;
                        }
                        if ($row['id'] == 8) {
                            $row['url'] = '/apps/course.php?rid=' . $rid;
                        }
                        $obj = json_encode($row);
                        echo "<li {$style}><a id='app_$row[id]' href='javascript://'  onClick='openApp({$obj})'><img src='$row[ico]' ><span>{$row[title]}</span></a></li>";
                        $h++;

                    }
                    ?>

                </ul>
            </div>
            <div class="title_tab"><a href="javascript:void(0)" class="bg_png2" onClick="bt_SwitchListTab('User')"
                                      id="listTab1">在线会员<font style="display: block;">(<span id="OnlineUserNum"></span>)</font></a>
                <a href="javascript:void(0)" onClick="bt_SwitchListTab('Other')"
                   id="listTab2"><?= ($userinfo['gid'] == '3' ? "我的客户" : "在线管理") ?><font style="display: block;">(<span
                                id="OnlineOtherNum"></span>)</font></a></div>
            <div style="clear:both"></div>
            <div id="OnlineUser_Find"
                 style="height:25px; margin:0px; padding:2px; overflow:hidden; line-height:25px; border:1px solid #999"
                 class="bg_png2">
                <span style="float:right; margin-right:5px;"><i class="fa fa-search" onClick="bt_FindUser()"></i></span>
                <input name="finduser" type="text" id="finduser"
                       style="border:0px; width:150px; height:25px; line-height:25px; padding:0px; background:none; color:#FFF; "/>
            </div>
            <div id="OnLineUser_OhterList" class="OnLineUser" style="height:50px;display:none">
                <div id="group_myuser"></div>
            </div>
            <div id="OnLineUser_FindList" class="OnLineUser" style="height:50px;display:none"></div>
            <div id="OnLineUser" class="OnLineUser" style="height:480px;">
                <div id="group_my"></div>
                <?= $groupli ?>

            </div>
            <!-- 进入直播间提醒 -->
            <div class="abs-top-left" id="whocomein"
                 style="bottom: 20px;display:block;position: absolute;left:0;z-index:2;">
            </div>
            <!--二维码图片-->
            <div style="height:160px;  margin-bottom: 3px;text-align: center;display: none;"><img
                        src="<?= $cfg['config']['ewm'] ?>" width=100></div>
        </div>
        <span class="js-main-meta-toggle main-meta-toggle" title="点击隐藏用户列表"><i class="fa fa-angle-double-left"
                                                                               aria-hidden="true"></i></span>
    </div>
    <div id="UI_Right" class="bg_png">
        <div id="RoomMV" style="position: relative; overflow:hidden">
            <div class="title_bar">
                <div class="btn-right">
                    <a class="btn btn-text-lottery" data-id="idTextLottery"><i
                                class="fa fa-video-camera"></i><span>文字开奖</span></a>
                    <a class="btn btn-lottery active" data-id="idLotteryLive"><i class="fa fa-video-camera"></i><span>开奖直播</span></a>
                    <a class="btn btn-bet" data-id="idBet"><i class="fa fa-spinner"></i><span>在线投注</span></a>
                    <a class="btn btn-beauty-live" data-id="OnLine_MV"><i class="fa fa-eye"></i><span>美女直播</span></a>
                    <!-- <a class="btn btn-broadcast" onclick="$('.broadcastMain').show();"><i class="fa fa-video-camera"></i>点播</a> -->
                </div>
            </div>
            <div class="broadcastMain" style="display: none;">
                <div class="broadcastTitle f18">
                    <span class="fl">录播</span>
                    <span class="fr closeBroadCastBtn" onclick="$('.broadcastMain').hide();"></span>
                </div>
                <div class="broadcastCon">
                    <ul>
                    </ul>
                </div>
            </div>
            <div id="mvMask"></div>
            <!-- 开奖直播 -->

            <div id="idLotteryLive" style="overflow:hidden; overflow-y:scroll">
                <script type="text/javascript">
                    var src = [
                        'https://kj.kai861.com/view/video/PK10/video.html?10001?1680218.com',
                        'http://kj.kai861.com/view/video/SSC/index.html?10036',
                        'http://kj.kai861.com/view/video/jisuft_video/index.html?10035',
                        'http://kj.kai861.com/view/video/cqnc/index.html?10009',
                        'http://kj.kai861.com/view/video/kuai3_video/Kuai3.html?10007?1680218.com'

                    ];

                    function setTab(name, cursel, n) {
                        var iframe = document.getElementsByTagName('iframe');
                        for (k = 0; k < iframe.length; k++) {
                            console.log(iframe[k]);
                            iframe[k].src = '';
                        }
                        for (i = 1; i <= n; i++) {
                            var menu = document.getElementById(name + i);
                            var con = document.getElementById("con_" + name + "_" + i);
                            menu.className = i == cursel ? "curr" : "";
                            con.style.display = i == cursel ? "block" : "none";
                            iframe[cursel - 1].src = src[cursel - 1];
                        }
                    }
                </script>
                <div id="con_a_1" class="cccc">
                    <iframe scrolling="no" src="https://kj.kai861.com/view/video/PK10/video.html?10001?1682016.cn"
                            width="450" height="290" frameborder="0"></iframe>
                </div>
                <div id="con_a_2" class="cccc" style="display:none;">
                    <iframe src="" width="450" height="290" scrolling="no" frameborder="0"></iframe>
                </div>
                <div id="con_a_3" class="cccc" style="display:none;">
                    <iframe src="" width="450" height="290" scrolling="no" frameborder="0"></iframe>
                </div>
                <div id="con_a_4" class="cccc" style="display:none;">
                    <iframe src="" width="450" height="290" scrolling="no" frameborder="0"></iframe>
                </div>
                <div id="con_a_5" class="cccc" style="display:none;">
                    <iframe src="" width="450" height="290" scrolling="no" frameborder="0"></iframe>
                </div>
                <div class="pptab">
                    <ul>
                        <li id="a1" onclick="setTab('a',1,5)" class="curr">北京PK10</li>
                        <li id="a2" onclick="setTab('a',2,5)">重庆时时彩</li>
                        <li id="a3" onclick="setTab('a',3,5)">幸运飞艇</li>
                        <li id="a4" onclick="setTab('a',4,5)">幸运农场</li>
                        <li id="a5" onclick="setTab('a',5,5)">江苏快3</li>
                    </ul>
                </div>
                <div class="pcaizlb">
                    <div class="pcaizlbz"><img src="room/images/caip.png"><br>更多彩种</div>
                    <div class="pcaizlby">
                        <li><a href="http://www.46465.com/" target="_blank">北京赛车pk10</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">重庆时时彩</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">广东快乐十分</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">北京快乐8</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">江苏快3</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">重庆幸运农场</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">广东11选5</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">天津时时彩</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">新疆时时彩</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">广西快乐十分</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">广西快3</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">安徽快3</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">山东11选5</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">幸运飞艇</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">上海时时乐</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">香港⑥合彩</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">福彩3D</a></li>
                        <li><a href="http://www.46465.com/" target="_blank">体彩排列3</a></li>
                    </div>
                </div>
            </div>
            <!-- 文字开奖 -->
            <div id="idTextLottery" style="height: 598px;overflow-y:auto;display:none">
<!--                <iframe id="nyiframe" src="https://www.400603.com/apps/kaijiang/cplive.html" width="100%"-->
<!--                        frameborder="0"></iframe>-->
            </div>
            <!-- 在线投注 -->
            <div id="idBet" style="height: 598px;overflow-y:auto;display:none">
<!--                <iframe id="nyiframe" src="--><?//= $planurl ?><!--" width="100%" frameborder="0"></iframe>-->
            </div>
            <!-- 美女直播 -->
            <div id="OnLine_MV" style="display:none">
                <?= $bet ?>
            </div>
            <a href="javascript:showLive()" class="btn-return-video" onclick="returnVideo()"><i
                        class="fa fa-video-camera"></i>返回直播</a>
            <? if ($_SESSION['login_uid'] == 0 && $cfg['config']['tiyantime'] > 0) { ?>
                <div class="videoTime">
                    <div class="videoTimeTxt">您可以观看时长</div>
                    <div id="colockbox">
                        <span class="day">60</span>
                        <span class="hour">60</span>
                        <span class="minute">60</span>
                        <span class="second">60</span>
                    </div>
                </div>
                <script>  var time_now = new Date().getTime();
                    time_now = parseInt(time_now / 1000);
                    var viewtime = parseInt(RoomInfo.tiyantime * 60 + parseInt(getCookie("first_access_time")) - time_now);</script>
            <? } ?>
        </div>
        <div class="RegBagNumBox" title="主播收到的礼物" style="display:none;">
            <div class="RegBagNum"><p><em> </em><span><?= $cfg['config']['redbags'] ?></span></p></div>
            <span class="addnum">+1</span>
        </div>
        <div class="gift-container" style="display:none;height:0px">
            <div class="gift-info-panel-give">
                <input type="hidden" id="giftdata" data-id="" data-name="" data-credit="" data-path="">
                <div class="sendbtn-container">
                    <a href="javascript:;" class="gift-info-panel-form-send sendgift" id="sendgift">赠送</a>
                    <div id="myimage" class="hid ption_a hongbao" style="display: none;width: 150px;"
                         onmouseout="_toolTimeTea()" onmouseover="_toolCloseTimeTea()"></div>
                </div>
            </div>
            <div class="MR-gift" id="js-gift-panel">
                <div class="gift-panel">
                    <div class="gift-con">
                        <div class="gift-group">
                            <div id="js-gift-group-vip2" class="gift-wrap hidden">
                                <div class="gift-wrap" style="display:block;">
                                    <div class="M-arrow-scroll">
                                        <div class="con">
                                            <a href="javascript:;" class="roll_lt jQ_plstRoLt"
                                               style="display: none;"></a>
                                            <a href="javascript:;" class="roll_rt jQ_plstRoRt"
                                               style="display: none;"></a>
                                            <div class="scroll jQ_ptLst" style="">
                                                <ul class="clearfix gift-page active" data-page="0">
                                                    <li class="gift" data-locked="0" data-title="轮船" data-name="轮船"
                                                        data-id="3" data-credit="1800"
                                                        data-path="/room/images/gift/ship.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/ship.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="红酒" data-name="红酒"
                                                        data-id="4" data-credit="12"
                                                        data-path="/room/images/gift/redwine.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/redwine.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="金财神" data-name="金财神"
                                                        data-id="5" data-credit="36"
                                                        data-path="/room/images/gift/goldofwealth.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/goldofwealth.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="白酒" data-name="白酒"
                                                        data-id="6" data-credit="88"
                                                        data-path="/room/images/gift/whitewine.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/whitewine.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="金牛" data-name="金牛"
                                                        data-id="13" data-credit="66"
                                                        data-path="/room/images/gift/goldcow.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/goldcow.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="鼓掌" data-name="鼓掌"
                                                        data-id="16" data-credit="1"
                                                        data-path="/room/images/gift/applause.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/applause.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="豪车" data-name="豪车"
                                                        data-id="19" data-credit="1080"
                                                        data-path="/room/images/gift/car.gif">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/car.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="火焰" data-name="火焰"
                                                        data-id="26" data-credit="30"
                                                        data-path="/room/images/gift/firework.gif" data-level="2">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/firework.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="金算盘" data-name="金算盘"
                                                        data-id="32" data-credit="18"
                                                        data-path="/room/images/gift/goldcal.gif" data-level="2">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/goldcal.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="金话筒" data-name="金话筒"
                                                        data-id="36" data-credit="30"
                                                        data-path="/room/images/gift/goldmicrophone.gif" data-level="2">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/goldmicrophone.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift" data-locked="0" data-title="鲜花" data-name="鲜花"
                                                        data-id="37" data-credit="3"
                                                        data-path="/room/images/gift/flower.gif" data-level="2">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/flower.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>

                                                    <li class="gift cur" data-locked="0" data-title="赞" data-name="赞"
                                                        data-id="38" data-credit="1"
                                                        data-path="/room/images/gift/good.gif" data-level="2"
                                                        id="giftFirstZan">
                                                        <div class="gift-pic">
                                                            <img src="/room/images/gift/good.gif">
                                                            <i class="luck-mark gold-mark"></i>
                                                            <i class="is-event"></i>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <? if ($global['config']['goldegg'] == '1') { ?>
                <div class="egg"><img src="/images/egg.png" id="goldEgg"></div><? } ?>
        </div>
        <div class="NoticeList">
            <?php
            $txt = '';
            $query = $db->query("select * from {$tablepre}notice where type='1'  and  roomid ='$rid' order by ov desc,id desc");
            while ($row = $db->fetch_row($query)) {
                $tab .= "<a href='javascript:void(0)' id='notice_{$row[id]}' class='notice_tab'>{$row[title]}</a>";
                if ($row['content_type'] == 2) {
                    $lunboimg = unserialize(tohtml($row['lunboimg']));
                    $txt .= "<div id='notice_{$row[id]}_div' class='notice_div' style='display:none'>";
                    $txt .= '<script src="room/script/slide.js"></script><div class="ck-slide"><ul class="ck-slide-wrapper">';
                    $a = 1;
                    foreach ($lunboimg as $key => $value) {
                        if ($a == '1') {
                            $imglistyle = '';
                            $listyle = 'class="current"';
                        } else {
                            $imglistyle = 'style="display:none"';
                            $listyle = '';
                        }
                        $txt .= '<li ' . $imglistyle . '><a href="#"><img src="' . $value . '" alt="" /></a></li>';
                        $li .= '<li ' . $listyle . '><em>' . $a . '</em></li>';
                        $a++;
                    }
                    $txt .= '</ul><a href="javascript:;" class="ctrl-slide ck-prev">上一张</a> <a href="javascript:;" class="ctrl-slide ck-next">下一张</a><div class="ck-slidebox"><div class="slideWrap"><ul class="dot-wrap">';
                    $txt .= $li;
                    $txt .= '</ul></div></div></div><script>$(\'.ck-slide\').ckSlide({autoPlay: true});</script>';
                    $txt .= "</div>";

                } else {
                    $txt .= "<div id='notice_{$row[id]}_div' class='notice_div' style='display:none'>" . tohtml($row['txt']) . "</div>";
                }
            }

            ?>
            <div class="tab bg_png3">
                <?= $tab ?>
                <div style=" clear:both"></div>
            </div>
            <div id="NoticeList">
                <?= $txt ?>
            </div>
            <script>
                $('.tab a:first').addClass('active');
                $('.notice_div:first').css('display', 'block');
                $('.tab a').on('click', function () {
                    if ($(this).attr("id") == 'app_1') {
                        return;
                    }
                    $('.tab a').removeClass('active');
                    $(this).addClass('active');
                    $('.notice_div').css('display', 'none');
                    $('#' + $(this)[0].id + '_div').css('display', '');
                });
            </script>
        </div>
    </div>
    <div class="egg-code-container">
        <div class="inputInfo">
            <!-- <img class="colseLogin" src="/images/messageBtn.png" alt="关闭"> -->
            <div class="colseLogin"></div>
            <p class="inputInfoTitle">输入抽奖</p>
            <div class="codeDiv">
                <label for="codeInput">抽奖码:</label>
                <input class="codeInput" id="codeInput" type="text" placeholder="请输入抽奖码">
            </div>
            <input class="submitTijiao" type="button" value="提交" id="submitCode">
        </div>
    </div>
    <div id="UI_Central" class="bg_png" style="position: relative;">
        <div class="fans-rank-container" style="display:none;">
            <ul id="navTab" class="navTab clearfix">
                <li id="m1" class="nLi">
                    <h3 class="achover">
                        <a href="javascript:void(0)">贡献榜</a>
                    </h3>
                    <div class="sub mCustomScrollbar">
                        <div id="mCSB_1" class="mCustomScrollBox" tabindex="0">
                            <div id="mCSB_1_container" class="mCSB_container" dir="ltr">
                                <i class="arrow-icon"></i>
                                <ul class="contribution-head-container">
                                    <li class="contribution-head-item con-active">周榜</li>
                                    <li class="contribution-head-item">月榜</li>
                                    <li class="contribution-head-item">总榜</li>
                                </ul>
                                <ul class="rank-list clearfix " style="display:block" id="con-week">
                                </ul>
                                <ul class="rank-list clearfix" style="display:none" id="con-mon">
                                </ul>
                                <ul class="rank-list clearfix" style="display:none" id="con-all">
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <li id="m2" class="nLi">
                    <h3 class="">
                        <a href="javascript:void(0)">时长榜</a>
                    </h3>
                    <div class="sub mCustomScrollbar">
                        <div id="mCSB_2" class="mCustomScrollBox" tabindex="0">
                            <div id="mCSB_2_container" class="mCSB_container" dir="ltr">
                                <i class="arrow-icon"></i>
                                <ul class="rank-list rank-list-last clearfix">
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <li id="m3" class="nLi ">
                    <h3 class=""><a href="javascript:void(0)">分享榜</a>
                    </h3>
                    <div class="sub mCustomScrollbar">
                        <div id="mCSB_3" class="mCustomScrollBox" tabindex="0">
                            <div id="mCSB_3_container" class="mCSB_container" dir="ltr">
                                <i class="arrow-icon"></i>
                                <ul class="rank-list rank-list-share clearfix">
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="title_bar2">
            <span class="fl clearfix horn">公告：<img src="room/images/laba.png"></span>
            <div class="notice-scroll fl" id="notice-scrollbox">
                <marquee scrollamount="5" id="msg_tip_show">
                    <?php
                    $query = $db->query("select * from {$tablepre}chatlog where  rid='" . $rid . "' and type='2'  order by id desc limit 1");
                    while ($row = $db->fetch_row($query)) {
                        echo "<span style='color:red'>" . tohtml($row[msg]) . "</span>";
                    }
                    ?>
                </marquee>
            </div>
        </div>
        <div id="MsgBox" style="position:relative;">
            <div id='divnotice'></div>
<!--            --><?// if ($global['config']['rotate'] == '1') { ?>
<!--                <div class="dimensionCode" id="rotateMain">-->
<!--                    <img src="/images/choujiang.png"><br>-->
<!--                    抽奖-->
<!--                </div>-->
<!--            --><?// } ?>
            <div class="hongBao" title="红包">
                <img src="/images/hongbao.png"><br>
                <span class="bagmoney">发红包</span>
            </div>
            <div class="signin" title="签到" id="signin">
                <img src="/images/signin-icon.png"><br>
                <span class="bagmoney">签到</span>
            </div>
            <div id="Y_pub_Tools">
                <a href="javascript:void(0)" onclick="bt_MsgClear();"><span class="clear">清屏</span></a>
                <a href="javascript:void(0)" onclick="bt_toggleScroll();"><span class="scroll" id="bt_gundong">滚动</span></a>
            </div>
            <div id="Video_List"></div>
            <div id="MsgBox1"
                 style="overflow:auto; height:50px; padding:0px 6px 0px 6px;position:relative;height: 470px;">
                <div class="view-more-records"><a class="more-message" page="1">查看更多消息</a></div>
                <?= $omsg ?>
            </div>
            <div class="drag_skin" id="drag_bar" style=" display:none"></div>
            <div id="MsgBox2"
                 style="height:100px; overflow:auto;  padding:0px 10px 0px 10px;position:relative; display:none"></div>
        </div>
        <div id="UI_Control" class="tool_bar">
            <div class="adminTip">
    <span id="msg_tip_admin_show">
    <?php
    $query = $db->query("select * from {$tablepre}chatlog where  rid='" . $rid . "' and type='3'  order by id desc limit 1");
    while ($row = $db->fetch_row($query)) {
        echo "<span style='color:red;display:block;width:1000px;margin-left: 13px;margin-right: 13px;'>" . tohtml($row[msg]) . "</span>";
    }
    ?>
    </span>
            </div>
            <div id="qqbts"><p>
                    <?php
                    if ($cfg['config']['viewkefu'] == 1) {
                        $query = $db->query("select * from  {$tablepre}room_kefu where roomid={$rid} order by id desc limit 0,4 ");
                        while ($row = $db->fetch_row($query)) {
                            $kefulist .= "<a class='btn flash1' target='_blank' title='{$row['qqtitle']}' href='http://wpa.qq.com/msgrd?v=3&amp;uin={$row['qqnum']}&amp;site=qq&amp;menu=yes'><i class='fa fa-qq'></i>{$row['qqtitle']}</a>";
                        }
                    } else {
                        $query = $db->query("select realname from {$tablepre}members  where gid=3 and tuser='admin' and (roomid={$rid} or roomid='')  order by lastactivity DESC limit 0,4 ");
                        while ($row = $db->fetch_row($query)) {
                            $kefulist .= "<a class='btn flash1' target='_blank' title='点击咨询' href='http://wpa.qq.com/msgrd?v=3&amp;uin={$row['realname']}&amp;site=qq&amp;menu=yes'><i class='fa fa-qq'></i>财富助理</a>";
                        }

                    }
                    echo $kefulist;
                    ?>
                </p></div>


            <div id="bar_list">

                <div id="tanmu-select" class="layui-unselect layui-form-switch " style="float:right;"><i></i></div>
                <div style="float: left;height: 30px;">

                    <a href="javascript:void(0)" id="bt_face" class="bar_2 bar" onclick="showFacePanel(this,'#Msg');"
                       isface="2"></a>
                    <a href="javascript:void(0)" class="bar_3 bar" id="bt_caitiao" title="彩条"></a>
                    <? if (check_auth('send_img')) { ?><a href="javascript:void(0)" class="bar_1 bar" id="bt_myimage"
                                                          onclick="bt_insertImg('#Msg')"></a><? } ?>
                    <? if ($room_admin) { ?>  <a href="javascript:void(0)" class="bar_9 bar" id="bt_myimage"
                                                 onclick="bt_rollnotice()"></a><? } ?>

                    <!-- <a href="javascript:void(0)" class="bar_4 bar" id="bt_qingping"  onClick="bt_MsgClear();">清屏</a>-->
                    <!--<a href="javascript:void(0)" class="bar_5 bar" id="bt_gundong" select="true"  onClick="bt_toggleScroll()" >滚动</a>-->
                </div>
                <div style="margin-left:12px;float: left;"> 对
                    <div id="ToUser" class="lts_chat5">游客4cb6731a</div>
                    <span style="position: relative;"><div id="apDiv2"></div></span>&nbsp;说
                </div>
            </div>


            <input type="hidden" name="Personal" id="Personal" value="false"/>
        </div>
        <div class="inputmsg">
            <div id="Msg" contentEditable="true"
                 style="height:61px; overflow:auto;font-size:13px; outline: none;z-index: 3;top: 0;margin: 5px 115px 5px 5px; bottom: 0;left: 0;"
                 onClick="HideMenu();"></div>
            <div style="text-align:right;top:0px; right: 5px;position: absolute; width: 100px;height: 62px;margin: 6px 0px;">

                <button class="btn-input" name="Send_bt" id="Send_bt"></button>
                <input type="hidden" name="Send_key" id="Send_key" value="1"/>
                <!--
            <div style="text-align:right;top:-20px; left:100%;position:relative; margin-left:-77px; width:60px;"><input name="Send_bt" type="image" src="images/Send.gif" id="Send_bt" title="发送消息" onclick="SysSend.msg()"/><input name="Send_key" type="image" id="Send_key" src="images/Send_C.gif" / value="1" title="发送消息快捷键设置" onclick="bt_Send_key_option(this)">
            -->
            </div>

        </div>
        <div id="manage_div">
            <select id="chat_type" style="display:none">
                <option value="me" selected data-id='self'>发言人-自己</option>
            </select>
            &nbsp;&nbsp;
            <label>
                <input type="checkbox" id="msg_tip">置顶公告</label>&nbsp;&nbsp;
            <label><input type="checkbox" id="msg_tip_admin">管理提示</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <? if ($room_admin) { ?> <label><input type="checkbox"
                                                   id="ban_speak" <? if ($cfg['config']['banspeak'] == 1) {
                    echo 'checked="checked"';
                } ?>>全体禁言</label>&nbsp;&nbsp;&nbsp;&nbsp; <a href="javascript:void(0)" style="color:red;"
                                                             id="bt_myimage" onclick="bt_automsg()">自动发言</a><a
                    href="javascript:void(0)" style="color:red;" id="generateCode"
                    onclick="generateCode()">生成抽奖码</a><? } ?>
        </div>
    </div>
</div>
<div id='ColorTable' style="display:none; " onblur="BrdBlur('ColorTable');" tabIndex></div>
<div id="Smileys" style="display:none; height:180px;" onblur="BrdBlur('Smileys');" tabIndex></div>
<div id="Send_key_option" style="display:none" onblur="BrdBlur('Send_key_option');" tabIndex>
    <div onMouseOver="this.className='bt_true'" onMouseOut="this.className='bt_false'"
         style="padding-left:20px; height:20px; line-height:20px;" class="bt_false"
         onClick="$('Send_key').value='1';$('Send_key_option').style.display='none'">按 Enter 键发送消息
    </div>
    <div onMouseOver="this.className='bt_true'" onMouseOut="this.className='bt_false'"
         style="padding-left:20px; height:20px; line-height:20px;" class="bt_false"
         onClick="$('Send_key').value='2';$('Send_key_option').style.display='none'">按 Ctrl+Enter 键发送消息
    </div>
</div>

</div>
<div style="position:absolute; left: -300px;" id="MsgSound"></div>
<div id="face" style="position:absolute; display:none"></div>
<div id="caitiao" class="hid ption_a"></div>
<form id="imgUpload" name="imgUpload" action="" method="post" enctype="multipart/form-data" target="e">
    <input type="hidden" name="info" id="imgUptag" value="#Msg">
    <input id="filedata" contenteditable="false" type="file" style="display:none;"
           onchange="$('#imgUpload').attr('action','../upload/upload_frame.php?act=InsertImg&' + new Date().getTime() );document.imgUpload.submit();"
           name="filedata">
</form>
<iframe name="e" id="e" style="display:none"></iframe>
<div id="tip_login_win" style="display:none">
    <?php
    if ($cfg['config']['logintc'] == 1) {

        $tipopen = '<style>.kf_content {position: absolute;top: 50%;left: 50%;width: 800px;height: 285px;margin: -140px 0 0 -400px;color: #f00;z-index: 999;background: url(' . $cfg['config']['tipimg'] . ') no-repeat;}';
        $tipopen .= '.kf_content div {position: relative;}.kf_content div img#cls {position: absolute;width: 20px;height: 20px;top: 0px;right: 0px;overflow: hidden;text-indent: -99px;cursor: pointer;}#kfpn {margin-top: 215px;padding: 10px;}#kfs {text-align: center;display: block;width: 770px;}#kfpn li {float: left;height: 28px;line-height: 28px;width: 95px;list-style-type: none;display: inline-block;}#kfpn li a {margin-top: 0px;margin-right: 2px;padding-left: 12px;}#kfpn li img {height: height:22px;width: 77px;}</style> ';
        $tipopen .= '<div class="kf_content" id="kf_content"><div><img id="cls" onclick="layer.close(tipopen);" src="/images/close.gif" alt="" /></div><div id="kfpn"><div id="kfs">{kefulist}</div></div></div>';
        echo str_replace("{kefulist}", '', $tipopen);
    }
    $db->close();
    ?>
</div>
<script>
    if (RoomInfo.logintc == 1) {
        var tipopen = layer.open({
            type: 1,
            title: false,
            shadeClose: true,
            shade: false,
            closeBtn: false,
            bgcolor: '',
            area: ['800px', '230px'],
            content: $("#tip_login_win").html()
        });
    }
    OnInit();

</script>
<script src="room/script/jquery.danmu.min.js"></script>
<script src="room/script/nuoyun.js"></script>
<!--<script type="text/javascript" src="https://js.users.51.la/19529913.js"></script>-->
<div style="display:none">
    <?= tohtml($global['config']['tongji']) ?>
</div>
<input id="MyMoney" type="hidden" value="<?= $userinfo['moneyred'] ?>"/>

<!--[if IE 6]>
<script type='text/javascript' src='room/script/png.js'></script>
<script type='text/javascript'>
    DD_belatedPNG.fix('.bg_png,.bg_png1,.bg_png2');
</script>
<![endif]-->
</body>
</html>
