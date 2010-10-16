<?php /* Smarty version 2.6.18, created on 2010-10-14 20:16:52
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/body.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/body.html', 10, false),array('function', 'ANCHOR', '/Library/WebServer/Documents/styles/cbv2new/layout/body.html', 28, false),array('function', 'link', '/Library/WebServer/Documents/styles/cbv2new/layout/body.html', 44, false),array('modifier', 'get_form_val', '/Library/WebServer/Documents/styles/cbv2new/layout/body.html', 45, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/global_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<body>

<!-- ADMIN BAR -->
<?php if (has_access ( 'admin_access' ) || $this->_tpl_vars['userquery']->is_admin_logged_as_user()): ?>
<div class="admin_bar">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
          <td align="left"><a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/admin_area"><?php echo smarty_lang(array('code' => 'admin_panel'), $this);?>
</a> | <a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/admin_area/video_manager.php"><?php echo smarty_lang(array('code' => 'moderate_videos'), $this);?>
</a> | <a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/admin_area/members.php"><?php echo smarty_lang(array('code' => 'moderate_users'), $this);?>
</a></td>
          <td align="right">
          <?php if ($this->_tpl_vars['userquery']->is_admin_logged_as_user()): ?>
          <a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/admin_area/login_as_user.php?revert=yes"><?php echo smarty_lang(array('code' => 'revert_back_to_admin'), $this);?>
</a>
          <?php endif; ?></td>
    </tr>
  </table>
</div>
<?php endif; ?>
<!-- ADMIN BAR -->

<!-- Including Commong header -->
<div class="container_container">
    <div id="container" class="clearfix">
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        <div class="nav_shadow"></div>
        <div id="content" style="">
        
        <?php echo ANCHOR(array('place' => 'global'), $this);?>

        
        <!-- Message -->
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/message.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
            <?php $_from = $this->_tpl_vars['template_files']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
            <?php if (template_file_exists ( $this->_tpl_vars['file'] , $this->_tpl_vars['style_dir'] )): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/".($this->_tpl_vars['file']), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                    <?php else: ?>
                        <div class="error" align="center">Unable to load template file "<?php echo $this->_tpl_vars['file']; ?>
"</div>
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
        </div> <!--CONTENT END-->
        <div class="clear"></div>
        <div class="search_footer clearfix">
            <div class="foot_s_con">
                <div class="advance" title="Advanced Search"></div>
                <form name="search-form" method="get" action="<?php echo cblink(array('name' => 'search_result'), $this);?>
">
                    <input type="text" name="query" value="<?php echo ((is_array($_tmp='query')) ? $this->_run_mod_handler('get_form_val', true, $_tmp, true) : get_form_val($_tmp, true)); ?>
"  />
                    <input type="submit" value="<?php echo smarty_lang(array('code' => 'search'), $this);?>
" class="foot_s_btn" name="cbsearch" />
                </form>            
            </div> <!--FOOT_S_CON END-->
        </div> <!--SEARCH_FOOTER END-->
        <div id="footer">
            <div class="changer">
            <div class="copyright"></div> 
			    <?php if (config ( 'allow_language_change' )): ?>
                <form action="" method="post" name="change_lang">
                    <?php echo $this->_tpl_vars['cbobjects']->display_languages(); ?>
              
                </form>
                <?php endif; ?>       
                <div class="ch_left"></div>        	
                <div class="ch_right"></div>
            </div> <!--CHANGER END-->
            <div class="ch_shadow"></div>
            <!--FOOTER CLASS END-->
            <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        </div> <!--FOOTER ID END-->
    </div>
</div>
</body>
</html>