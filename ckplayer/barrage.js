var time=0;
var _CK_=null;
var bOpen=ckstyle()['cpt_barrageopen'];
var bObj=null;

function loadedHandler(){
	_CK_=CKobject.getObjectById('ckplayer_a1');
	_CK_.addListener('time','timeHandler');//监听当前的播放时间
	_CK_.addListener('sendBarrage','sendBarrageHandler');//监听发送弹幕的内容
	_CK_.addListener('barrageShow','barrageShowHandler');//监听弹幕开关
	//初始化一个弹幕元件
	bObj = {
		background: {
			backgroundColor: 0x000000, //背景颜色，16进制作0x开头
			borderColor: null, //边框颜色
			radius: 25, //圆角弧度
			alpha: 50, //背景透明度
			height: 25 //高度
		},
		list: [
			{
				type:'png',
				url:'http://www.ckplayer.com/static/images/face_temp_003.jpg',
				radius: 30,//圆角弧度
				width:30,//定义宽，必需要定义
				height:30,//定义高，必需要定义
				alpha:90,//透明度
				top:-5,//离元件顶部距离
				left:0,//左边距离
				right:0//右边距离
			},
			{
				type: "text",//说明是文本
				text: "演示弹幕内容，弹幕只支持普通文本，不支持HTML",//文本内容
				color: "#FFFFFF",
				size: 16,
				face: "Microsoft YaHei,微软雅黑",
				alpha: 90,//透明度
				left: 10,//左边距离
				right: 10,//右边距离
				top: -2//离元件顶部距离
			}
			
		],
		y: "50%",//这里定义了y属性，则表示是水平方向移动，如果不定义y而定义x，则在垂直方向移动，坐标支持数字和百分比
		time: 20,//移动频率，单位：毫秒，例20指每20毫秒移动一次
		step: 1,//移动距离，单位：像素，正的表示向左或向上，小于0则表示向右或向下
		marginX: 20,//x轴修正，因为元件里第一个元素的坐标如果是小于0，则可能开始出现在界面中，此时需要修正一下。
		marginY: 20//同上
	}
	
}
function timeHandler(n){//监听当前时间
	time=n;
}
function sendBarrageHandler(s){//当用户提交了弹幕内容则调用该函数

	writeBarrage(s);
}
function barrageShowHandler(b){//监听点击播放器上弹幕开关
	bOpen=b?1:0;
}
function addBarrage(){
	if(bOpen==1 && time>0){
		_CK_.addBarrage(bObj);
	}
	else{
		alert('目前不能发送弹幕，可能的原因：1、关闭了弹幕，2、视频没有开始播放');
	}
}
function getBarrage(){
	var arr=_CK_.getBarrage();
	alert('获取的弹幕信息是一组对象集合成的数组：'+arr);
}

//下面是用来做一个简单的将播放器里发送弹幕显示在播放器里的测试
function writeBarrage(s){
    var y=Math.floor(Math.random()*30)+'%';
   var listinfo={
				type: "text",//说明是文本
				text: s,//文本内容
				color: "#FFFFFF",
				size: 25,
				face: "Microsoft YaHei,微软雅黑",
				alpha: 100,//透明度
				left: 10,//左边距离
				right: 10,//右边距离
				top: -2//离元件顶部距离
			}
var imgReg = /<img.*?(?:>|\/>)/gi;
//匹配src属性
var srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
var arr = s.match(imgReg);
if(arr!=null){
for (var i = 0; i < arr.length; i++) {
 var src = arr[i].match(srcReg);

 if(src[1]){
  listinfo={
				type: "png",//说明是文本
				url: src[1],//文本内容
				radius:0,//圆角弧度
				width:30,//定义宽，必需要定义
				height:30,//定义高，必需要定义
				alpha:90,//透明度
				top:-5,//离元件顶部距离
				left:0,//左边距离
				right:0//右边距离
			}
 }
}
}
		
		
	var o = {
		background: {
			backgroundColor: null, //背景颜色，16进制作0x开头
			borderColor: null, //边框颜色
			radius: 25, //圆角弧度
			alpha: 50, //背景透明度
			height: 0 //高度
		},
		list: [listinfo],
		y: y,//这里定义了y属性，则表示是水平方向移动，如果不定义y而定义x，则在垂直方向移动，坐标支持数字和百分比
		time: 5,//移动频率，单位：毫秒，例20指每20毫秒移动一次
		step: 3,//移动距离，单位：像素，正的表示向左或向上，小于0则表示向右或向下
		marginX: 20,//x轴修正，因为元件里第一个元素的坐标如果是小于0，则可能开始出现在界面中，此时需要修正一下。
		marginY: 20//同上
	}
	_CK_.addBarrage(o);
}
