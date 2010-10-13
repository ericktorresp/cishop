<?php /* Smarty version 2.6.18, created on 2010-10-13 23:02:31
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/header.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cbtitle', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 6, false),array('function', 'link', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 12, false),array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 16, false),array('function', 'head_menu', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 44, false),array('function', 'current_page', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 47, false),array('modifier', 'sprintf', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 24, false),array('modifier', 'get_form_val', '/Library/WebServer/Documents/styles/cbv2new/layout/header.html', 73, false),)), $this); ?>
<!-- Loading Quicklist Box -->
<div id="quicklist_box"></div>

<div id="header">
	<div>
    <a href="<?php echo $this->_tpl_vars['baseurl']; ?>
"><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/dot.gif" alt="<?php echo cbtitle(array(), $this);?>
" name="logo_icon" width="348" height="53" border="0" id="logo_icon" title="<?php echo cbtitle(array(), $this);?>
"  class="logo"></a>
</div> <!--LOGO END-->
    <div class="login_con clearfix">
        <div class="user_login clearfix">
        <?php if (! $this->_tpl_vars['userquery']->login_check('',true)): ?>
        	<a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/forgot.php" class="forget" title="Forget Username/Password, Click Here"></a>
            <form action="<?php echo cblink(array('name' => 'login'), $this);?>
" method="post" name="login" style="margin:0px; padding:0px;">
                <input type="text" value="Username" id="username" name="username" onfocus="if(this.defaultValue == this.value) this.value = ''" onblur="if(this.value == '') this.value = this.defaultValue"  />
                <input type="password" value="Password" id="password" name="password" onfocus="if(this.defaultValue == this.value) this.value = ''" onblur="if(this.value == '') this.value = this.defaultValue"  />                
                <input type="submit" value="Login" id="login" name="login"  />
                <input type="checkbox" name="rememberme" id="rememberme"  /> <span class="remember"><?php echo smarty_lang(array('code' => 'remember_me'), $this);?>
</span>
			</form> 
        <?php else: ?>
        	<div class="avatar">
            	<div style="width:40px; height:40px; background:#EEE; margin:0px; padding:0px;"><img src="<?php echo $this->_tpl_vars['userquery']->getUserThumb($this->_tpl_vars['userquery']->udetails,'small'); ?>
" class="mid_user_thumb" style="max-height:40px; max-width:40px" /></div>
            </div>
            <div class="txt">
            <?php echo smarty_lang(array('code' => 'howdy_user','assign' => 'howdyuser'), $this);?>

            <?php echo ((is_array($_tmp=$this->_tpl_vars['howdyuser'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['userquery']->username) : sprintf($_tmp, $this->_tpl_vars['userquery']->username)); ?>

      		| <a href="<?php echo cblink(array('name' => 'logout'), $this);?>
"><?php echo smarty_lang(array('code' => 'logout'), $this);?>
</a></div>
            <div class="links"><a href="<?php echo cblink(array('name' => 'inbox'), $this);?>
"><?php echo smarty_lang(array('code' => 'messages'), $this);?>
 (<?php echo $this->_tpl_vars['cbpm']->get_new_messages(); ?>
)</a> | <a href="<?php echo cblink(array('name' => 'notifications'), $this);?>
"><?php echo smarty_lang(array('code' => 'notifications'), $this);?>
 (<?php echo $this->_tpl_vars['cbpm']->get_new_messages('','notification'); ?>
)</a> | <a href="<?php echo cblink(array('name' => 'my_account'), $this);?>
"><?php echo smarty_lang(array('code' => 'account'), $this);?>
</a></div>
        <?php endif; ?> 
        </div> <!--USER_LOGIN CLASS END-->
      
         <div id="user_login">
            <a href="javascript:void(0)">
            <?php if (! $this->_tpl_vars['userquery']->login_check('',true)): ?>
            	Login
            <?php else: ?>
            	My Info
            <?php endif; ?>    
            </a>
        </div> <!--USER_LOGIN ID END-->
    </div> <!--LOGIN_CON END-->    
</div> <!--HEADER ID END-->

<div class="top_tabs clearfix">
    <ul>
    	<?php echo head_menu(array('assign' => 'headmenu'), $this);?>
        
        <?php $_from = $this->_tpl_vars['headmenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['hm']):
?>
        	<?php if ($this->_tpl_vars['hm']['name'] != ''): ?>
            	<li><a href="<?php echo $this->_tpl_vars['hm']['link']; ?>
" <?php echo current_page(array('page' => $this->_tpl_vars['hm']['this']), $this);?>
 <?php if ($this->_tpl_vars['hm']['target']): ?> target="<?php echo $this->_tpl_vars['hm']['target']; ?>
"<?php endif; ?> <?php if ($this->_tpl_vars['hm']['onclick']): ?> onclick="<?php echo $this->_tpl_vars['hm']['onclick']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['hm']['name']; ?>
</a></li>
            <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
    </ul>
	
</div> <!--TOP_TABS END-->

<div id="navi_con">
    <div class="navi">
        <ul>
        <?php if (! $this->_tpl_vars['userquery']->login_check('',true)): ?>
        <?php echo smarty_lang(array('code' => 'after_meny_guest_msg','assign' => 'guestmsg'), $this);?>

        <?php echo cblink(array('name' => 'login','assign' => 'loginlink'), $this);?>

        	<li><?php echo ((is_array($_tmp=$this->_tpl_vars['guestmsg'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['loginlink'], $this->_tpl_vars['loginlink']) : sprintf($_tmp, $this->_tpl_vars['loginlink'], $this->_tpl_vars['loginlink'])); ?>
</li>
        <?php else: ?>
        	<li><a href="<?php echo cblink(array('name' => 'my_account'), $this);?>
"><?php echo smarty_lang(array('code' => 'com_my_account'), $this);?>
</a></li>          	
        	<li><a href="<?php echo $this->_tpl_vars['userquery']->profile_link($this->_tpl_vars['userquery']->udetails); ?>
"><?php echo smarty_lang(array('code' => 'com_my_channel'), $this);?>
</a></li>
        	<li><a href="<?php echo cblink(array('name' => 'my_videos'), $this);?>
"><?php echo smarty_lang(array('code' => 'com_my_videos'), $this);?>
</a></li>
        	<li><a href="<?php echo cblink(array('name' => 'my_favorites'), $this);?>
"><?php echo smarty_lang(array('code' => 'Favorites'), $this);?>
</a></li>
        	<li><a href="<?php echo cblink(array('name' => 'my_playlists'), $this);?>
"><?php echo smarty_lang(array('code' => 'playlists'), $this);?>
</a></li>
        	<li><a href="<?php echo cblink(array('name' => 'my_contacts'), $this);?>
"><?php echo smarty_lang(array('code' => 'friend_requests'), $this);?>
 (<?php echo $this->_tpl_vars['userquery']->get_pending_contacts($this->_tpl_vars['userquery']->userid,0,true); ?>
)</a></li>  
        <?php endif; ?>     
         </ul>
<div class="search_con">
    <div class="s_bg">	
        <form action="<?php echo cblink(array('name' => 'search_result'), $this);?>
" method="get" name="search" id="search" style="margin:0px; padding:0px;">               
            <input name="query" id="query" type="text" class="searchbar" value="<?php echo ((is_array($_tmp='query')) ? $this->_run_mod_handler('get_form_val', true, $_tmp, true) : get_form_val($_tmp, true)); ?>
" />
            <select name="type" class="s_type">
            <?php $_from = $this->_tpl_vars['Cbucket']->search_types; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['stypes'] => $this->_tpl_vars['t']):
?>
                <option value="<?php echo $this->_tpl_vars['stypes']; ?>
" <?php if ($_GET['type'] == $this->_tpl_vars['stypes']): ?> selected="selected"<?php endif; ?>><?php echo $this->_tpl_vars['stypes']; ?>
</option>
            <?php endforeach; endif; unset($_from); ?>
            </select>                                 
            <input name="submit" type="submit" class="searchbutton" value="Search" />
        </form>
    </div> <!--S_BG END-->    
</div> <!--SEARCH_CON END-->
    </div> <!--NAVI END-->
</div> <!--NAVI_CON END-->