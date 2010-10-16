<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_top.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_top.html', 6, false),array('modifier', 'sprintf', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_top.html', 6, false),)), $this); ?>
<!-- Channel Top -->
<div class="channel_box">
    <div class="channel_inner_box channel_top">
        <div class="channel_top_user_box">
            <div class="usr_small_thumb"><img src="<?php echo $this->_tpl_vars['userquery']->getUserThumb($this->_tpl_vars['u'],'small'); ?>
" class="user_small_thumb" alt="<?php echo $this->_tpl_vars['u']['username']; ?>
" style="max-height:30px; max-width:50px"></div>
            <div class="usr_channel"><?php echo smarty_lang(array('code' => 'user_s_channel','assign' => 'usr_s'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['usr_s'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
 </div>
        </div>
        <?php $this->assign('channel_links', $this->_tpl_vars['userquery']->get_inner_channel_top_links($this->_tpl_vars['u'])); ?>
        <ul class="top_channel_links">
        <?php $_from = $this->_tpl_vars['channel_links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['link_title'] => $this->_tpl_vars['link']):
?>
            <li><a href="<?php echo $this->_tpl_vars['link']['link']; ?>
" <?php if ($this->_tpl_vars['link']['onclick']): ?>onClick="<?php echo $this->_tpl_vars['link']['onclick']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['link_title']; ?>
</a></li>
        <?php endforeach; endif; unset($_from); ?>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<!-- Channel Top End-->