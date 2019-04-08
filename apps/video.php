<?php
	require_once '../include/common.inc.php';
	$rid = $_SESSION['roomid'];
	if (empty($rid)) {
		$rid = $_COOKIE['roomid'];
	}
?>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
		<style type="text/css">
			* {
				margin: 0;
				padding: 0;
			}
			body {
				font-family: "Microsoft YaHei",SimHei,"Helvetica Neue",Helvetica,Arial,sans-serif;
				background-color: #edf2f6;
			}
			ul {
				list-style-type: none;
			}
			.main {
			    text-align: center;
			    border-radius: 4px;
			}
			.main .player {
				height: 500px;
			    background-color: black;
			}
			.main .player video {
				width: 100%;
				height: 100%;
			}
			.video-list {
				padding: 32px 0;
			}
			.video-list ul {
				overflow: hidden;
			    width: 95%;
			    margin: 0 auto;
			}
			.video-list li {
			    float: left;
			    margin: 12px 5px;
			}
			.video-list li span {
			    display: inline-block;
			    padding: 7px 23px;
			    background-color: #fafafa;
			    color: black;
			    cursor: pointer;
			}
			.video-list li.cur span {
			    color: #fff;
			    background-color: #42a5f6;
			}
			@media screen and (max-width: 450px) {
				.main .player {
					height: 230px;
				}
			}
		</style>
	</head>
	<body>
		<div class="main">
			<div class="player">
				<video controls="" autoplay="" playsinline="" webkit-playsinline="" x5-playsinline="" x-webkit-airplay="allow" id="nuoyunvideo" src="" type="application/x-mpegURL"></video>
			</div>
			<div class="video-list">
				<ul>
				</ul>
			</div>
		</div>
		<script src="/room/script/jquery.min.js"></script>
		<script type="text/javascript">
		$(function() {
			$.ajax({
				url: '/ajax.php?act=get_video_list&rid=<?=$rid?>',
				type: 'GET',
				dataType:'JSON',
				success: function(res) {
					if (res.code) {
						$.each(res.data, function(idx, item) {
							var cur = '';
							if (idx == 0) {
								$('#nuoyunvideo')[0].src = item.weburl;
								cur = 'cur';
							}
							var li = '<li data-url="' + item.weburl + '" class="' + cur + '"><span>' + item.videoname + '</span></li>';
							$('.video-list ul').append(li);
						});
					}
				}
			});
			$('.video-list ul').delegate('li', 'click', function() {
				var url = $(this).data('url');
				$('#nuoyunvideo')[0].src = url;
				$(this).addClass('cur').siblings().removeClass('cur');
			});
		});
		</script> 
	</body>
</html>