<script type="text/javascript">
//wx.ready start
wx.ready(function () {
	wx.onMenuShareTimeline({
		title:  '<?php if (!empty($wxtitle)) echo $wxtitle;   ?>', // 分享标题
		link:   '<?php if (!empty($gamelink)) echo $gamelink; ?>', // 分享链接
		imgUrl: '<?php if (!empty($imgurl)) echo $imgurl;     ?>', // 分享图标
		success: function () { 
			$.post("share.php");
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	}); 
	//分享给朋友
	wx.onMenuShareAppMessage({
		title:  '<?php if (!empty($wxtitle)) echo $wxtitle;   ?>',
		desc:   '<?php if (!empty($wxdesc)) echo $wxdesc;     ?>',
		link:   '<?php if (!empty($gamelink)) echo $gamelink; ?>',
		imgUrl: '<?php if (!empty($imgurl)) echo $imgurl;     ?>'
	});
});
//wx.ready end
</script>