<?php /* Smarty version 2.6.18, created on 2010-10-13 21:43:08
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/body.html */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/global_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<body>
<!-- Including Commong header -->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/msg.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div style="min-height:600px; min-width:1000px">

<?php if ($_COOKIE['admin_menu'] == 'hide'): ?>
	<?php $this->assign('left_menu_class', 'left_menu_0'); ?>
    <?php $this->assign('contentcolumn_class', 'contentcolumn0'); ?>
<?php else: ?>
	<?php $this->assign('left_menu_class', 'left_menu'); ?>
    <?php $this->assign('contentcolumn_class', 'contentcolumn'); ?>
<?php endif; ?>

<div class="clearfix"></div>

<!-- Setting Body File -->
<?php if ($this->_tpl_vars['Cbucket']->show_page): ?>
    <div id="contentwrapper">
        <div id="contentcolumn" class="<?php echo $this->_tpl_vars['contentcolumn_class']; ?>
">
            <div class="innertube" style="padding-right:10px">
            <?php $_from = $this->_tpl_vars['template_files']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file']):
?>
            	<?php if (! is_array ( $this->_tpl_vars['file'] )): ?>
                	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/".($this->_tpl_vars['file']), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php else: ?>
                	<?php $this->assign('folder', $this->_tpl_vars['file']['folder']); ?>
                    <?php $this->assign('inc_file', $this->_tpl_vars['file']['file']); ?>
                	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['folder'])."/".($this->_tpl_vars['inc_file']), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<!-- Setting Body File -->


<!-- Changing Left Menu -->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/left_menu.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

</div>
<div class="clearfix"></div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/footer.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</body>
</html>