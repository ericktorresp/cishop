<?php /* Smarty version 2.6.18, created on 2010-10-14 20:16:52
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/subscriptions.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/subscriptions.html', 3, false),array('function', 'AD', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/subscriptions.html', 21, false),)), $this); ?>
<?php if (userid ( )): ?>
<div class="main_vids clearfix" style="border-top:1px solid #CCC">
	<span class="subsription"><a href="<?php echo $this->_tpl_vars['baseurl']; ?>
/edit_account.php?mode=subscriptions"><?php echo smarty_lang(array('code' => 'subscriptions'), $this);?>
</a></span>
  
   <div>
   <!-- Listing Subscriptions-->
   <?php $this->assign('subs_vids', $this->_tpl_vars['userquery']->get_subscribed_videos($this->_tpl_vars['userquery']->userid,10)); ?>
   <?php if ($this->_tpl_vars['subs_vids']): ?>
   	<?php $_from = $this->_tpl_vars['subs_vids']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['video']):
?>
      <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/video.html", 'smarty_include_vars' => array('video' => $this->_tpl_vars['video'],'video_view' => 'grid_view')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endforeach; endif; unset($_from); ?>
   <?php else: ?>
   <em><?php echo smarty_lang(array('code' => 'no_new_subs_video'), $this);?>
</em>
   <?php endif; ?>
   <!-- End Listing Subscriptions -->
   </div>
    

</div>
<div class="feature_shadow" ></div>
  <div class="ad"><?php echo getAd(array('place' => 'ad_468x60'), $this);?>
</div>
<?php endif; ?>