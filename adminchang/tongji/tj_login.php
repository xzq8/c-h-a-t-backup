<?php
require_once '../../include/common.inc.php';
require_once '../function.php';
if(stripos(auth_group($_SESSION['login_gid']),'tongji_t')===false)exit(denied_pape());
function count_guest($tuid,$ym){
	global $db,$tablepre;
	$sql="select count(*) as t1 from {$tablepre}msgs where type='3' and tuid='$tuid' and uid like 'x%'";
	if($ym!=""){$sql.=" and FROM_UNIXTIME(mtime,'%Y-%m')='$ym'";}
	$row=$db->fetch_row($query=$db->query($sql));
	return $row[t1];
}
function count_guest_ip($tuid,$ym){
	global $db,$tablepre;
	$sql="select count(DISTINCT ip) as t1 from {$tablepre}msgs where type='3' and tuid='$tuid' and uid like 'x%'";
	if($ym!=""){$sql.=" and FROM_UNIXTIME(mtime,'%Y-%m')='$ym'";}
	$row=$db->fetch_row($query=$db->query($sql));
	return $row[t1];
}
switch($type){
	case 'loginroom':
		$sql="select count(*) as t1,COUNT( DISTINCT ip )as ips,tname,tuid from {$tablepre}msgs where type='3'";
		if($ym!=""){$sql.=" and FROM_UNIXTIME(mtime,'%Y-%m')='$ym'";}
		$query=$db->query($sql." group by tname order by t1 desc");
		//exit($sql." group by tname order by t1 desc");
		$c1['tag']=array();
		$c1['data1']=array();
		$c1['data2']=array();
		$c1['data3']=array();
		$c1['data4']=array();
		$c1['data1_tag']="访问数";
		$c1['data2_tag']="游客访问数";
		$c1['data3_tag']="访客独立IP数";
		$c1['data4_tag']="游客独立IP数";
		$c1['sn']="";
		$c1['title']="访客数统计";
		while($row=$db->fetch_row($query)){ 
                   if( $row['tname']=='') continue;
			array_push($c1['tag'],"'{$row[tname]}'");
			array_push($c1['data1'],$row[t1]);
			array_push($c1['data2'],count_guest($row[tuid],$ym));
			array_push($c1['data3'],$row[ips]);
			array_push($c1['data4'],count_guest_ip($row[tuid],$ym));
		}
	break;
}

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
<style type="text/css">
code { padding: 0px 4px; color: #d14; background-color: #f7f7f9; border: 1px solid #e1e1e8; }
</style>
<script>
Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
function ftime(time){
	return new Date(time*1000).Format("yyyy-MM-dd hh:mm"); ; 
}
</script>
</head>
<body>
<div class="container"  style=" min-width:1300px;">
<form  class="form-horizontal" action="" method="get"> 
  <ul class="breadcrumb">
    <li class="active">
    <input type="hidden" name="type" value="<?=$type?>">
    按统计月份：
      <input type="text" name="ym" id="ym"  class="calendar" value="<?=$ym?>"> 
      &nbsp;&nbsp;
      <button type="submit"  class="button ">查询</button> 为空统计所有
    &nbsp;&nbsp;</li>
   
  </ul>
  </form>
  <table  class="table table-bordered table-hover definewidth m10">
	<tr><td>
	
	<div class="row">
        <div class="span24" id="canvas"></div>
      </div>
	</td></tr>



  </table>
</div>
<script type="text/javascript" src="../assets/js/jquery-1.8.1.min.js"></script> 
<script type="text/javascript" src="../assets/js/bui.js"></script> 
<script type="text/javascript" src="../assets/js/config.js"></script> 
  <script type="text/javascript">
    BUI.use('bui/calendar',function(Calendar){
          var datepicker = new Calendar.DatePicker({
            trigger:'.calendar',
			dateMask : 'yyyy-mm',
            autoRender : true
          });
        });
    BUI.use('common/page');

    BUI.use('bui/chart',function (Chart) {
      
        var chart = new Chart.Chart({
          render : '#canvas',
          width : 950,
          height : 400,
          title : {
            text : '<?=$c1['title']?>',
            'font-size' : '16px'
          },
          subTitle : {
            text : '<?=$c1['sn']?>'
          },
          xAxis : {
            categories: [
                      <?=implode(',',$c1[tag])?>
                  ]
          },
          yAxis : {
            title : {
              text : ''
            },
            min : 0
          },  
          tooltip : {
            shared : true
          },
          seriesOptions : {
              columnCfg : {
                  
              }
          },
          series: [ {
                  name: '<?=$c1['data1_tag']?>',
                  data: [<?=implode(',',$c1['data1'])?>]
 
              },
			  {
                  name: '<?=$c1['data2_tag']?>',
                  data: [<?=implode(',',$c1['data2'])?>]
 
              },
			  {
                  name: '<?=$c1['data3_tag']?>',
                  data: [<?=implode(',',$c1['data3'])?>]
 
              }
			  ,
			  {
                  name: '<?=$c1['data4_tag']?>',
                  data: [<?=implode(',',$c1['data4'])?>]
 
              }]
              
        });
 
        chart.render();
    });

  </script>

</body>
</html>
