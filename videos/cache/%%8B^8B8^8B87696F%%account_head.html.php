<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:39
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html', 4, false),array('modifier', 'date_format', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html', 7, false),array('modifier', 'nicetime', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html', 8, false),array('modifier', 'number_format', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html', 10, false),array('modifier', 'country', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/manage/account_head.html', 15, false),)), $this); ?>
<div class="account_head_container clearfix">
  <div class="account_thumb_container" align="center">
	<img src="<?php echo $this->_tpl_vars['userquery']->getUserThumb($this->_tpl_vars['user']); ?>
" alt="<?php echo $this->_tpl_vars['user']['username']; ?>
" class="account_thumb"><br />
    <a href="<?php echo $this->_tpl_vars['userquery']->profile_link($this->_tpl_vars['user']); ?>
"><?php echo smarty_lang(array('code' => 'com_view_channel'), $this);?>
</a></div>
    <div class="stats">
    	<span class="account_stat"><?php echo smarty_lang(array('code' => 'username'), $this);?>
 : <strong><?php echo $this->_tpl_vars['user']['username']; ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'joined'), $this);?>
 : <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['doj'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'last_logged_in'), $this);?>
 : <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['last_logged'])) ? $this->_run_mod_handler('nicetime', true, $_tmp) : nicetime($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'last_active'), $this);?>
 : <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['last_active'])) ? $this->_run_mod_handler('nicetime', true, $_tmp) : nicetime($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'total_logins'), $this);?>
 : <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['num_visits'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'profile_views'), $this);?>
: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['profile_hits'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'total_videos'), $this);?>
: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['total_videos'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'total_videos_watched'), $this);?>
 <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['total_watched'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</strong></span>
        <span class="account_stat"><?php echo smarty_lang(array('code' => 'subscribers'), $this);?>
 : <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['subscribers'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</strong></span>
        <span class="account_stat">country: <strong><?php echo ((is_array($_tmp=$this->_tpl_vars['user']['country'])) ? $this->_run_mod_handler('country', true, $_tmp) : get_country($_tmp)); ?>
</strong></span>
</div>
    <div class="account_head_right">
    	<div class="pm_box">
         <span class="messages"><?php echo smarty_lang(array('code' => 'messages'), $this);?>
</span>
         <?php echo $this->_tpl_vars['cbpm']->get_new_messages(); ?>

        </div>
    </div>

</div>