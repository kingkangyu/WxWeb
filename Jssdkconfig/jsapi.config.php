<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
    wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onRecordEnd',
        'playVoice',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
    ]
});
wx.ready(function() {
    <?php if (isset($hideOptionMenu)&&$hideOptionMenu == 1) echo "wx.hideOptionMenu();"; ?>
    <?php if (isset($hideProtect)&&$hideProtect == 1) {
        $hideProtect = '
        wx.hideMenuItems({
            menuList: [
                "menuItem:share:email",
                "menuItem:openWithSafari",
                "menuItem:originPage",
                "menuItem:copyUrl",
                "menuItem:editTag",
                "menuItem:share:qq",
                "menuItem:share:QZone"
            ]
        });
        ';  
        echo $hideProtect;
    }?>
});
</script>