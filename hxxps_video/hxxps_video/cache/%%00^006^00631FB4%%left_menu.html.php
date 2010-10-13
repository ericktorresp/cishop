<?php /* Smarty version 2.6.18, created on 2010-10-13 21:43:09
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/left_menu.html */ ?>

<div class="<?php echo $this->_tpl_vars['left_menu_class']; ?>
" id="left_column">

<img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/dot.gif" class="menu_toggle_arrow" alt="Toggle Menu" title="Toggle Menu" onClick="toggle_menu()"/>
<img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/dot.gif" class="menu_toggle" alt="Toggle Menu" title="Toggle Menu" onClick="toggle_menu()"/>


<?php $this->assign('menu', $this->_tpl_vars['Cbucket']->AdminMenu); ?>
<?php $_from = $this->_tpl_vars['menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['name'] => $this->_tpl_vars['menu']):
?>
  <!-- *********************************Start <?php echo $this->_tpl_vars['name']; ?>
 Menu****************************** -->
 <div class="mainDiv" >
    <div class="topItem"  ><?php echo $this->_tpl_vars['name']; ?>
</div>
    <div class="dropMenu" >          
      <div class="subMenu">
        <?php $_from = $this->_tpl_vars['menu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['sub_menu'] => $this->_tpl_vars['sub_link']):
?>
        	<div class="subItem"><a href="<?php echo $this->_tpl_vars['sub_link']; ?>
"><?php echo $this->_tpl_vars['sub_menu']; ?>
</a></div>
        <?php endforeach; endif; unset($_from); ?>
      </div>
    </div>
  </div>
  <!-- *********************************End <?php echo $this->_tpl_vars['name']; ?>
 Menu****************************** -->
<?php endforeach; endif; unset($_from); ?>
 <!-- *********************************End Menu****************************** -->
</div>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/slidemenu.js"></script>