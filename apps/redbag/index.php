<?php
require_once '../../include/common.inc.php';
if(!isset($_SESSION['login_uid']) && $_SESSION['login_uid']=='0' )exit("Access Denied");
switch ($act) {
    case "SendMoneygift";


        if (isset($_SESSION['login_uid']) && $_SESSION['login_uid']!='0' && $amount>0 ) {
            $time = time();
            $senduid=$_SESSION['login_uid'];
            $db->query("insert into {$tablepre}sendhongbao(senduid,realmoney,realnumber,beizhu,addtime,roomid)values('$senduid','$amount','$num','$memos','$time','$rid')");
            $id=$db->insert_id();
            $db->query("update {$tablepre}memberfields set moneyred=moneyred-$amount where uid='$senduid'");
            $data['status']='ok';
            $data['beizhu']=$memos;
            $data['id']=$id;
            echo json_encode($data);
        } 
        break;

    case "GetMoneygift":
     if (isset($_SESSION['login_uid']) && $_SESSION['login_uid']!='0' ) {
            $time = time();
            $uid=$_SESSION['login_uid'];
            $data=array();
           $rows=array();
            $hongbao=$db->fetch_row($db->query("select s.*,m.nickname from {$tablepre}sendhongbao s,{$tablepre}memberfields m where s.senduid=m.uid and  s.id='$moneyGiftId' "));
        
            $query=$db->query("select * from {$tablepre}gethongbao where hongbaoid='$moneyGiftId' and getuid='$uid' ");
            $myhongbao=$db->num_rows($query);
            if($myhongbao<=0){
                 $query=$db->query("select * from {$tablepre}gethongbao where hongbaoid='$moneyGiftId'");
                 $hongbao_num=$db->num_rows($query);
                
                if($hongbao_num<$hongbao['realnumber']){
                   //可以抢红包 
                    $get_num=0;
                    $get_money=0;
                    while($row=$db->fetch_row($query)){
                        
                        $get_money=$get_money+$row['getmoney'];
                         $get_num++;
                      }
                    $total=$hongbao['realmoney']-$get_money;//红包总余金额  
                    
                     $num=$hongbao['realnumber'];// 红包个数
                    $min=0.01;//每个人最少能收到0.01元  
                    $my_num=$get_num+1;
                   $safe_total=($total-($num-$my_num)*$min)/($num-$my_num);//随机安全上限  
                   $my_money=mt_rand($min*100,$safe_total*100)/100;  
                    if($my_num==$num){$my_money=$total-$my_money;}
                 
        $db->query("insert into {$tablepre}gethongbao(getuid,hongbaoid,getmoney,addtime)values('$uid','$moneyGiftId','$my_money','$time')");
         $db->query("update {$tablepre}memberfields set moneyred=moneyred+$my_money where uid='$uid'");     
             $data['senderName']=$hongbao['nickname'];
             $data['moneygiftTitle']=$hongbao['beizhu'];
             $data['robcount']=$my_num;  //第几个红包
             $data['allcount']=$hongbao['realnumber'];  //红包总个数
             $data['realityMoney']=$hongbao['realmoney'];  //红包总额
             $data['amount']=$my_money;
             $data['senderAvatar']='/face/p1/null.jpg';
              $data['status']=1;    
             
             $query=$db->query("select g.*,m.nickname from {$tablepre}gethongbao g,{$tablepre}memberfields m where g.getuid=m.uid and g.hongbaoid='$moneyGiftId' order by g.id desc");
              while($row=$db->fetch_row($query)){
                    $row['senderAvatar']='/face/p1/null.jpg';  
                  $row['addtime']=date('Y-m-d H:i:s',$row['addtime']);
                  $data['rows'][]=$row;
                  
                      }
             
                }else{
                    
                //红包抢完了   
                     $data['senderName']=$hongbao['nickname'];
               $data['senderAvatar']='/face/p1/null.jpg';
                 $data['status']=0;    
                }
                
            }else{
                //已经抢过红包
                $data['senderName']=$hongbao['nickname'];
               $data['senderAvatar']='/face/p1/null.jpg';
                $data['status']=-1; 
                
            }
            
            
            
           // $db->query("insert into {$tablepre}sendhongbao(senduid,realmoney,realnumber,beizhu,addtime)values('$senduid','$amount','$num','$memos','$time')");
           // $id=$db->insert_id();
          //  $db->query("update {$tablepre}memberfields set moneyred=moneyred-$amount where uid='$senduid'");
          
            echo json_encode($data);
        } 

        break;
        
         case "GetMoneygiftList";

             $num=0;
        if (isset($_SESSION['login_uid']) && $_SESSION['login_uid']!='0' ) {
            $time = time();
            $senduid=$_SESSION['login_uid'];
        $hongbao=$db->fetch_row($db->query("select s.*,m.nickname from {$tablepre}sendhongbao s,{$tablepre}memberfields m where s.senduid=m.uid and  s.id='$moneyGiftId' "));
          $data['senderName']=$hongbao['nickname'];
             $data['moneygiftTitle']=$hongbao['beizhu'];
             $data['senderAvatar']='/face/p1/null.jpg';
           $data['allcount']=$hongbao['realnumber'];  //红包总个数
             $data['realityMoney']=$hongbao['realmoney'];  //红包总额
            $query=$db->query("select g.*,m.nickname from {$tablepre}gethongbao g,{$tablepre}memberfields m where g.getuid=m.uid and g.hongbaoid='$moneyGiftId' order by g.id desc");
              while($row=$db->fetch_row($query)){
                    $row['senderAvatar']='/face/p1/null.jpg';  
                  $row['addtime']=date('Y-m-d H:i:s',$row['addtime']);
                  $data['rows'][]=$row;
                  $num++;
                      }  
             
               $data['robcount']=$num; 
            echo json_encode($data);
        } 
        break;
        case "GetUsertMoneygift";
            $num=0;
            $allmoney=0;
        if (isset($_SESSION['login_uid']) && $_SESSION['login_uid']!='0' ) {
            $time = time();
            $uid=$_SESSION['login_uid'];
            $data['nickName']=userinfo($uid,'{nickname}');
             $data['avatar']='/face/p1/null.jpg';
            
            $query=$db->query("select g.*,s.senduid  from {$tablepre}gethongbao g,{$tablepre}sendhongbao s where g.hongbaoid=s.id and g.getuid='$uid' order by g.id desc");
              while($row=$db->fetch_row($query)){
                    $row['avatar']='/face/p1/null.jpg';  
                  $row['addtime']=date('Y-m-d H:i:s',$row['addtime']);
                  $row['nickname']=userinfo($row['senduid'],'{nickname}');
                  $data['rows'][]=$row;
                  $num++;
                  $allmoney=$allmoney+$row['getmoney'];
                      }  
             $data['allmoney']=$allmoney; 
               $data['allcount']=$num; 
            echo json_encode($data);
        } 
        break;
}
?>