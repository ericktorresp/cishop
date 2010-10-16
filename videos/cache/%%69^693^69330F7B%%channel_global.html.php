<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_global.html */ ?>
<div <?php if ($this->_tpl_vars['userquery']->getUserBg($this->_tpl_vars['u'],true)): ?>style="background-position:center;background:<?php if ($this->_tpl_vars['userquery']->getUserBg($this->_tpl_vars['u'])): ?>url('<?php echo $this->_tpl_vars['userquery']->getUserBg($this->_tpl_vars['u']); ?>
');background-repeat: <?php echo $this->_tpl_vars['u']['background_repeat']; ?>
;<?php elseif ($this->_tpl_vars['u']['background_color'] != ''): ?><?php echo $this->_tpl_vars['u']['background_color']; ?>
<?php endif; ?>; <?php if ($this->_tpl_vars['u']['background_attachement'] == 'yes'): ?>background-attachment:fixed<?php endif; ?>"<?php endif; ?>>