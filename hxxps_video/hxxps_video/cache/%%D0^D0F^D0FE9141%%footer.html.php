<?php /* Smarty version 2.6.18, created on 2010-10-13 23:02:31
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/footer.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/Library/WebServer/Documents/styles/cbv2new/layout/footer.html', 3, false),array('function', 'foot_menu', '/Library/WebServer/Documents/styles/cbv2new/layout/footer.html', 7, false),)), $this); ?>
<div class="footer">
	<div class="footer_copyr">
    	&copy; <?php echo $this->_tpl_vars['title']; ?>
 <?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

   	</div>
    <ul class="clearfix">
    
    	<?php echo foot_menu(array('assign' => 'foot_menu'), $this);?>

        
        <?php $_from = $this->_tpl_vars['foot_menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['fm']):
?>
        	<?php if ($this->_tpl_vars['fm']['name'] != ''): ?>
            	<li><a href="<?php echo $this->_tpl_vars['fm']['link']; ?>
" <?php if ($this->_tpl_vars['fm']['target']): ?> target="<?php echo $this->_tpl_vars['fm']['target']; ?>
"<?php endif; ?> <?php if ($this->_tpl_vars['fm']['onclick']): ?> onclick="<?php echo $this->_tpl_vars['fm']['onclick']; ?>
" <?php endif; ?>><?php echo $this->_tpl_vars['fm']['name']; ?>
</a></li>
            <?php endif; ?>
        <?php endforeach; endif; unset($_from); ?>
    </ul>
    <div class="clearfix"></div>
    <div class="footer_sp"></div>
    <?php echo $this->_tpl_vars['Cbucket']->footer(); ?>

</div>