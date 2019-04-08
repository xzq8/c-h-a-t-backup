function IsPC() {
    var userAgentInfo = navigator.userAgent;
    var Agents = ["Android", "iPhone",
                "SymbianOS", "Windows Phone",
                "iPad", "iPod"];
    var flag = true;
    for (var v = 0; v < Agents.length; v++) {
        if (userAgentInfo.indexOf(Agents[v]) > 0) {
            flag = false;
            break;
        }
    }
    return flag;
}
 
var flag = IsPC();
// 电脑页面

function kaijiang ( api, apiname)
{
	$.getJSON('api.php',{api:api,apiname:apiname},function( json )
	{
		$("#cqsscdt h2").html('第'+json['qishu']+'期开奖号码');
		var arr = json['haoma'].split(' ');
		var haoma = '';
		for (var i = 0,len = arr.length - 1; i <= len; i++) {
			haoma += "<span>" + arr[i] + "</span>";
		}
		$(".cqssc-nums").html( haoma );
	}).error(function()
	{
		$("#cqsscdt h2").html('正在获取开奖信息···');
		$(".cqssc-nums").html('');
	});
}

function kaijiang2 ( api, apiname)
{
	$.getJSON('api.php',{api:api,apiname:apiname},function( json )
	{
		$("#cqsscdt h2").html('第'+json['qishu']+'期开奖号码');
		var arr = json['haoma'].split(' ');
		var haoma = '';
		for (var i = 0,len = arr.length - 1; i <= len; i++) {
			haoma += "<span class='car-"+ arr[i] +"'>" + arr[i] + "</span>";
		}
		$(".cqssc-nums2").html( haoma );
	}).error(function()
	{
		$("#cqsscdt h2").html('正在获取开奖信息···');
		$(".cqssc-nums").html('');
	});
}

function jilu ( api, apiname)
{
	$.getJSON('api.php',{api:api,apiname:apiname},function( json )
	{
		$("#plandt").html(json['content']);
		$("#dengkai").html(json['dengkai']);
	}).error(function()
	{
		$("#plandt").html('正在获取开奖信息···');
	});
}


$( document ).ready(function() {
    $( '.sidebar' ).simpleSidebar({
        settings: {
            opener: '#open-sb',
            wrapper: '.wrapper',
        },
        sidebar: {
            align: 'left',
            width: 200,
            closingLinks: 'a',
        }
    });
});