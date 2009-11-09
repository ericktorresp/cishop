<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php print $this->bep_site->get_metatags(); ?>
	<title><?php print $header.' | '.$this->preference->item('site_name')?></title>
	<?php print $this->bep_site->get_variables()?>
	<?php print $this->bep_assets->get_header_assets();?>
	<?php print $this->bep_site->get_js_blocks()?>
</head>

<body>
<div id="wrapper">
<!--    <div id="header">
        <h1><?php print $this->preference->item('site_name')?></h1>
    </div>
-->
<!--top-->
<div id="header" class="top">
	<div class="kj">
		<div class="logo">
			<a href="<?php print config_item('base_url')?>"><img src="assets/images/logo.gif" /></a>
		</div>
        <div class="mypanli">
            <ul>
                <li><a href="<?php print config_item('base_url')?>my/cart" class="gouwu" target="_blank">购物车(0)</a>|</li>
                <li><a href="<?php print config_item('base_url')?>my" target="_blank">我的 <?php print $this->preference->item('site_name')?></a>|</li>
                <li><a href="<?php print config_item('base_url')?>my/order" target="_blank">我的送货车</a>|</li>
                <li><a href="<?php print config_item('base_url')?>help">帮助中心</a></li>
            </ul>
            <p>
			<?php
			    if(is_user())
			        print "您好！darkmoon[" . anchor('auth/logout','退出') . "]";
			    else
			        print "您好！游客 请 " . anchor('auth/login','[登录]') . " 或 " . anchor('auth/register','[免费注册]');
			?>
            </p>
        </div>
        <dl>
            <dt><a id="A1" onclick="AddItemShow()" title="一键填单"></a></dt>
            <dd>
                <a href="javascript:;" onclick="window.open('/CustomerService/kefu.html','在线客服','height=315,width=615,status=no,toolbar=no,resizable=no,menubar=no,location=no');"
                    title="在线客服"></a>
            </dd>
        </dl>
    </div>
    <div class="nav">

        <ul id="allPages">
            <li id="default" class="xt"><a href="<?php print config_item('base_url')?>" onclick="this.blur();">首页</a></li>
            <li id="explore"><a href="<?php print config_item('base_url')?>explore" onclick="this.blur();">随便逛逛</a></li>
            <li id="recommend"><a href="<?php print config_item('base_url')?>recommend" onclick="this.blur();">推荐</a></li>
            
            
            <li id="special"><a href="<?php print config_item('base_url')?>special" onclick="this.blur();">专题活动</a></li>
            <li id="free_shipping"><a href="<?php print config_item('base_url')?>free_shipping" onclick="this.blur();">免邮商家</a></li>

            <li id="discount"><a href="<?php print config_item('base_url')?>discount" onclick="this.blur();">折扣信息</a></li>
            <li id="forum"><a href="<?php print config_item('base_url')?>forum" onclick="this.blur();">论坛</a></li>
        </ul>
    </div>
</div>
<!--top-->
<div class="addpanel_dialog" style="display: none;">
    <div class="addpanel_windowname">
        <h2>

            一键填单</h2>
        <a id="closeBtn" title="关闭"></a>
    </div>
    <div class="addpanel_inlay">
        <div id="p0">
            <img src="assets/images/loading.gif" alt="加载中。。。" />
            <p>
                加&nbsp;载&nbsp;中……
            </p>

        </div>
        <div id="p1">
        </div>
        <div id="p2" style="display: none;">
        </div>
        <div id="p3" style="display: none;">
        </div>
    </div>
</div>

<div class="addpanel_overlay">
</div>
