<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/comments.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/comments.html', 9, false),)), $this); ?>
<?php $this->assign('comments', $this->_tpl_vars['myquery']->get_comments($this->_tpl_vars['id'],$this->_tpl_vars['type'])); ?>
<?php if ($this->_tpl_vars['comments']): ?>
    <?php $_from = $this->_tpl_vars['comments']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['comment']):
?>
        <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/comments/comment.html", 'smarty_include_vars' => array('comment' => $this->_tpl_vars['comment'],'type' => $this->_tpl_vars['type'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endforeach; endif; unset($_from); ?>
<?php else: ?>
	<div id="latest_comment_container">
	<div align="center" class="no_comments">
    	<em><?php echo smarty_lang(array('code' => 'no_comments'), $this);?>
</em>
    </div>
    </div>
<?php endif; ?>
<?php if ($this->_tpl_vars['comments']): ?>
<div id="latest_comment_container"></div>
<?php endif; ?>