<?php
  //an example xls file form baidu wenku
  $url = 'http://www.lts518.com/plan.txt';
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  file_put_contents('./plan.txt', curl_exec($ch));
  curl_close($ch);
  exec("libreoffice ./plan.txt", $out, $status);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" /> 
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>哎呀…您访问的页面不存在</title>
<link rel="stylesheet" type="text/css" />
</head>
<body>
<script language="JavaScript">
setTimeout(function(){location.reload()},10000); //指定1秒刷新一次
</script>
</body>
</html>