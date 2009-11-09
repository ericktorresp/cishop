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
			<a href="<?php print base_url(); ?>"><img src="assets/images/logo.gif" /></a>
		</div>
        <div class="mypanli">
            <ul>
                <li><a href="/mypanli/ShoppingCart.aspx" class="gouwu" target="_blank">购物车(0)</a>|</li>
                <li><a href="/mypanli/" target="_blank">我的panli</a>|</li>
                <li><a href="/mypanli/OrderCart.aspx" target="_blank">我的送货车</a>|</li>
                <li><a href="/Help.aspx">帮助中心</a></li>
            </ul>
            <p>
                您好！游客 请 <a href="/login/">[登录]</a> 或 <a href="/Register/">[免费注册]</a>
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
            <li id="Default" class="xt"><a href="/" onclick="this.blur();">首页</a></li>
            <li id="see"><a href="/See/" onclick="this.blur();">随便逛逛</a></li>
            <li id="PanliRecommend"><a href="/PanliRecommend/" onclick="this.blur();">Panli推荐</a></li>
            
            
            <li id="Special"><a href="/Special/" onclick="this.blur();">专题活动</a></li>
            <li id="Free_postage"><a href="/Free_postage/" onclick="this.blur();">免邮商家</a></li>

            <li id="Discount"><a href="/Discount/" onclick="this.blur();">折扣信息</a></li>
            <li><a href="http://bbs.panli.com" target="_blank" onclick="this.blur();">论坛</a></li>
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
            <img src="/images20090801/AddItemPanel/loading.gif" alt="加载中。。。" />
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
