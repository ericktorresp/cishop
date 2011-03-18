<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php echo $this->config->item('site_title')?></title>
    <link rel="shortcut icon" href="<?php echo $this->config->item('asset_url')?>favicon.ico">
    <meta name="keywords" content="beyondsoft, beyondsoft, online beyondsoft, beyondsoft online, online beyondsoft, casinos online, casino slot, online casino, play beyondsoft, play beyondsoft online, real money beyondsoft">
    <meta name="description" content="Play beyondsoft online for real money. Choose from a range of online beyondsoft. Play beyondsoft &amp; win huge cash payouts. Get 100% registration beyondsoft bonus to play our online beyondsoft. Pick your favourite beyondsoft machines from the online casino beyondsoft games at Beyondsoft">
    <meta http-equiv="Content-type" content="text/html;charset=utf-8">
    <meta http-equiv="content-language" content="zh_CN">

    <base href="">

    <script type='text/javascript' src='<?php echo $this->config->item('asset_url')?>js/sprintf.js'></script>
    <script type='text/javascript' src='<?php echo $this->config->item('asset_url')?>js/main.js'></script>
	<script type="text/javascript" src="<?php echo $this->config->item('asset_url')?>js/swfobject.js"></script>

    <link href="<?php echo $this->config->item('asset_url')?>css/style.css" rel="stylesheet" type="text/css" media="screen,print">
    <!--[if lte IE 6]>
    <style type="text/css">@import "<?php echo $this->config->item('asset_url')?>css/style-ie.css";</style>
    <![endif]-->
	<!--
    <link href="<?php echo $this->config->item('asset_url')?>css/winter/style.css" rel="stylesheet" type="text/css" media="screen,print">
    -->
    <!--[if lte IE 6]>
    <style type="text/css">@import "<?php echo $this->config->item('asset_url')?>css/winter/style-ie.css";</style>
    <![endif]-->

    <script type="text/javascript">
    <!--
        var clearPassword = 1;
        var loginLabel = '用户名';
    // -->
    </script>
</head>

<body>
<div id="container">
    <a href="/" target="_top" id="logo"></a>
    <div id="login">
        <form name="login" action="/" method="POST" onSubmit="if(clearPassword) this.elements['slot_login_password'].value='';return true;">
            <div id="loginLock"></div>
            <input type="text" name="slot_login_nickname" size="10" value="用户名" class="loginField" onFocus="if (this.value == loginLabel) this.value='';" onBlur="if (this.value == '') this.value=loginLabel;">
            <input type="password" name="slot_login_password" size="10" value="********" class="loginField" onFocus="if(clearPassword) {clearPassword=0;this.value='';}">
            <input type="submit" value="" id="buttonLogin">
        </form>
        <a href="user.pw_forgotten" id="buttonForgottenPassword"></a>
    </div>
	<!-- <div class="registered" id="login">
        <a href="https://www.slotland.com/.user.profile?u=1PsqmtW7srIeqLTAZl&amp;sm_id=4">DARKMOON1111</a>
        <a href="https://www.slotland.com/.user.transfer?u=1PsqmtW7srIeqLTAZl&amp;sm_id=4">$0.00</a>
        <a id="buttonAccount" href="https://www.slotland.com/.user.profile?u=1PsqmtW7srIeqLTAZl&amp;sm_id=4">&nbsp;</a>
        <a id="buttonLogout" href="http://www.slotland.com/.user.logout?u=1PsqmtW7srIeqLTAZl">&nbsp;</a>
    </div> -->
    <div id="topMenu">
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonHomeActive" href="/" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonGames" href="/games" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonPromotions" href="/bonuses" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonNewsletters" href="/promotions" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonVIP" href="/vip" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonBanking" href="/banking" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonContact" href="/contact" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonFaq" href="/faq" target="_top"></a>
        <div class="topMenuDelimiter"></div>
        <a class="topMenuButtonLanguage" href="/?l=es" target="_top"></a>
        <div class="topMenuDelimiter"></div>
    </div>
<?php if(uri_string()):?>
<script type="text/javascript">
<!--
// jackpot increase settings {{{
var jackpotIncreaseTimeout      = 1.44;
var currentJackpot              = 69568.00;
// }}}

// uri parts for jackpot letters {{{
var jackpotLetterURIPart1       = "<?php echo $this->config->item('asset_url')?>images/jackpot_";
var jackpotLetterURIPart2       = ".png";
// }}}

// available letter mappings {{{
var letters = new Array();
letters[","] = "comma";
letters["."] = "dot";
letters["0"] = "0";
letters["1"] = "1";
letters["2"] = "2";
letters["3"] = "3";
letters["4"] = "4";
letters["5"] = "5";
letters["6"] = "6";
letters["7"] = "7";
letters["8"] = "8";
letters["9"] = "9";

// }}}
// -->
</script>
    <div id="jackpotContainer">
        <div id="jackpotLabel">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_6.png" alt="6">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_9.png" alt="9">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_comma.png" alt=",">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_5.png" alt="5">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_6.png" alt="6">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_8.png" alt="8">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_dot.png" alt=".">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_0.png" alt="0">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_0.png" alt="0">
        </div>
    </div>
<script type="text/javascript">
<!--
    var so = new SWFObject("<?php echo $this->config->item('asset_url')?>flash/jackpot.swf", "jackpotContainerFlash", "311px", "62px", "9", "#000000");
            so.addParam("menu", "false");
            so.addParam("wmode", "transparent");
            so.addParam("allowScriptAccess", "always");
            so.addVariable("jackpotIncreaseTimeout", jackpotIncreaseTimeout);
            so.addVariable("currentJackpot", currentJackpot);
    if (so.installedVer.versionIsValid(so.getAttribute('version'))) {
        so.write("jackpotContainer");
        var e = getHTMLObject("jackpotContainer");
        e.style.backgroundImage = 'none';
    } else {
        setTimeout('increaseJackpot();', 1000 * jackpotIncreaseTimeout);
    }
// -->
</script>
    <div id="livechat">
    <!-- Begin LiveChat button tag. See also www.livechatinc.com -->
    	<div style="text-align:right">
    		<a href=""><img src="http://chat.livechatinc.net/licence/1032156/button.cgi?lang=en&amp;groups=2&amp;d=1299407761000" border="0" alt="Live Support"></a>
    	</div>
    <!-- End LiveChatbutton tag. See also www.livechatinc.com --></div>
    <div id="mainImage"></div>
 <?php endif?>