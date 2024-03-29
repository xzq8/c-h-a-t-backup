<?php
require_once '../../include/common.inc.php';
require_once '../function.php';
if(stripos(auth_group($_SESSION['login_gid']),'apps_jyts')===false)exit("没有权限！");
if($act=="app_jyts_add"){
	
	 app_jyts_add($title,$txt,$user);
	$id=$db->insert_id();
	$type='edit';
}else if($act=="app_jyts_edit"){
	app_jyts_edit($id,$title,$txt,$user);
}

$query=$db->query("select * from {$tablepre}apps_jyts where id='$id'");
if($db->num_rows($query)>0)$row=$db->fetch_row($query);
else {$row[user]=$_SESSION[admincp];}

?>
<!DOCTYPE HTML>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../assets/css/dpl-min.css" rel="stylesheet" type="text/css" />
<link href="../assets/css/bui-min.css" rel="stylesheet" type="text/css" />
<link href="../assets/css/page-min.css" rel="stylesheet" type="text/css" />
<!-- 下面的样式，仅是为了显示代码，而不应该在项目中使用-->
<link href="../assets/css/prettify.css" rel="stylesheet" type="text/css" />
<link href="../assets/css/base.css" rel="stylesheet" type="text/css" />
<style type="text/css">
code { padding: 0px 4px; color: #d14; background-color: #f7f7f9; border: 1px solid #e1e1e8; }
input,select{vertical-align:middle;}
.liw { width:160px; height:25px; line-height:25px;}
.lb-required:before {
  content: '*';
  color: red;
}
</style>
</head>
<body>
<div class="container" style="margin-bottom:50px;">

<form action="?id=<?=$id?>&type=<?=$type?>" method="post" enctype="application/x-www-form-urlencoded" onsubmit="return verify()">

  
<table class="">
          <tr>
            <td width="30" class="tableleft_layer lb-required" style="width:80px;">标题：</td>
            <td><input name="title" type="text" id="title" value="<?=$row[title]?>"  /></td>
      </tr>
          <tr>
            <td class="tableleft_layer lb-required">内容：</td>
            <td class="editor-con"><textarea name="txt" id="txt" style="width:100%" class="xheditor {cleanPaste:0,height:'300',internalScript:true,inlineScript:true,linkTag:true,upLinkUrl:'../../upload/upload.php',upImgUrl:'../../upload/upload.php',upFlashUrl:'../../upload/upload.php',upMediaUrl:'../../upload/upload.php'}"><?=$row[txt]?></textarea></td>
          </tr>
          <tr>
            <td class="tableleft_layer lb-required">发布人：</td>
            <td><input name="user" type="text" id="user" value="<?=$row[user]?>"  /></td>
          </tr>
        <tr>
          <td class="tableleft_layer">&nbsp;</td>
          <td>
            <button type="submit"  class="button button-success">确定</button>
            <button type="button"  class="button" onclick="layer_close()">关闭</button>
            <input type="hidden" name="act" value="app_jyts_<?=$type?>">
            <input type="hidden" name="id" value="<?=$id?>">
            <input type="hidden" name="type" value="<?=$type?>">
          </td>
        </tr>
    </table>
  </form>

</div>
<script type="text/javascript" src="../assets/js/jquery-1.8.1.min.js"></script>
<script type="text/javascript" src="../assets/layer/layer.js"></script>
<script type="text/javascript" src="../assets/js/bui.js"></script> 
<script type="text/javascript" src="../assets/js/config.js"></script> 
<script type="text/javascript" src="../../xheditor/xheditor.js"></script>
<script type="text/javascript" src="../../xheditor/xheditor_lang/zh-cn.js"></script>

    <script type="text/javascript">
        BUI.use('bui/calendar',function(Calendar){
            var datepicker = new Calendar.DatePicker({
              trigger:'.calendar-time',
              showTime:true,
              autoRender : true
            });
        });
        function verify() {
          $('#title').css({ border: "1px solid #D7D7D7",boxShadow: "none"});
          $('.editor-con table.xheLayout').css({ border: "1px solid #D7D7D7 !important",boxShadow: "none"});
          $('#user').css({ border: "1px solid #D7D7D7",boxShadow: "none"});

          if ($.trim($('#title').val()) == '') {
            $('#title').focus().css({
              border: "1px solid red",
              boxShadow: "0 0 2px red"
            });
            layer.msg('必须填写标题！');
            return false;
          }
          if ($.trim($('#txt').val()) == '') {
            $('.editor-con table.xheLayout').css({
              border: "1px solid red !important",
              boxShadow: "0 0 2px red"
            });
            layer.msg('必须填写内容！');
            return false;
          }
          if ($.trim($('#user').val()) == '') {
            $('#user').focus().css({
              border: "1px solid red",
              boxShadow: "0 0 2px red"
            });
            layer.msg('必须填写发布人！');
            return false;
          }
        }
        function layer_close(){ 
          window.parent.location.reload();
          var index = parent.layer.getFrameIndex(window.name);
          parent.layer.close(index);
        }
      </script>
</body>
</html>
