<?php
session_start();
//����������ʾ��ͼ��XXX���䱾���д��޷���ʾ�����ɾ���ȥ�����пո�
//�ȳ����������ٰ����ɵ���֤�����ȥ
$img_height=50;//�ȶ���ͼƬ�ĳ�����
$img_width=23;
$authnum='';
//������֤���ַ�
$ychar="0,1,2,3,4,5,6,7,8,9";
$list=explode(",",$ychar);
for($i=0;$i<4;$i++){
    $randnum=rand(0,9);
    $authnum.=$list[$randnum];
}
//����֤���ַ����浽session
$_SESSION["vcode"] = $authnum;


$aimg = imagecreate($img_height,$img_width);    //����ͼƬ
imagecolorallocate($aimg, 255,255,255);            //ͼƬ��ɫ��ImageColorAllocate��1�ζ�����ɫPHP����Ϊ�ǵ�ɫ��
$black = imagecolorallocate($aimg, 0,0,0);        //������Ҫ�ĺ�ɫ




//Ϊ�������ڱ������������ɫ������200������Ĳ�С��200
for ($i=0;$i<strlen($authnum);$i++){
    imagestring($aimg, 5,($i+1)*8,4, $authnum[$i],imagecolorallocate($aimg,mt_rand(0,100),mt_rand(0,150),mt_rand(0,200)));
}
imagerectangle($aimg,0,0,$img_height-1,$img_width-1,$black);//��һ������
header("Content-type: image/PNG");
imagepng($aimg);                    //����png��ʽ
imagedestroy($aimg);
?> 