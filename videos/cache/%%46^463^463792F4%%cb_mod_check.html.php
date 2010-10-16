<?php /* Smarty version 2.6.18, created on 2010-10-14 20:20:12
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/cb_mod_check.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'get_binaries', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/cb_mod_check.html', 18, false),array('function', 'check_module_path', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/cb_mod_check.html', 21, false),)), $this); ?>
<div align="center"><h2>MODULES AND PATHS</h2></div>
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="10" align="center" valign="middle" class="left_head">&nbsp;</td>
    <td width="200" class="head">Module</td>
    <td width="260" class="head"><div class="head_sep_left" style="width:100px">Server Path</div></td>
    <td width="260" class="head"><div class="head_sep_left" style="width:100px">User Path</div></td>
    <td width="60" class="head"><div class="head_sep_left" style="width:50px">Status</div></td>
    <td width="10" class="right_head">&nbsp;</td>
  </tr>
</table>

<table width="800" border="0" align="center" cellpadding="0" cellspacing="0" class="block">
  <tr>
    <td width="10" align="center" valign="middle">&nbsp;</td>
    <td width="200">FFMPEG</td>
    <td width="10">&nbsp;</td>
    <td width="250"><?php echo get_binaries(array('path' => 'ffmpeg','type' => 'server'), $this);?>
</td>
    <td width="10">&nbsp;</td>
    <td width="250"><?php echo get_binaries(array('path' => 'ffmpeg','type' => 'user'), $this);?>
</td>
    <?php echo check_module_path(array('path' => 'ffmpeg','get_path' => 'true','assign' => 'ffmpeg_status'), $this);?>

    <td width="10">&nbsp;</td>
    <td width="50"><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['ffmpeg_status']['status'] == 'ok'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td width="10">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle">&nbsp;</td>
    <td width="200">PHP</td>
    <td>&nbsp;</td>
    <td><?php echo get_binaries(array('path' => 'php','type' => 'server'), $this);?>
</td>
    <td>&nbsp;</td>
    <td width="250"><?php echo get_binaries(array('path' => 'php','type' => 'user'), $this);?>
</td>
    <?php echo check_module_path(array('path' => 'php','get_path' => 'true','assign' => 'php_status'), $this);?>

    <td width="10">&nbsp;</td>
    <td width="50"><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['php_status']['status'] == 'ok'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle">&nbsp;</td>
    <td width="200">FLVTool 2</td>
    <td>&nbsp;</td>
    <td><?php echo get_binaries(array('path' => 'flvtool2','type' => 'server'), $this);?>
</td>
    <td>&nbsp;</td>
    <td width="250"><?php echo get_binaries(array('path' => 'flvtool2','type' => 'user'), $this);?>
</td>
    <?php echo check_module_path(array('path' => 'flvtool2','get_path' => 'true','assign' => 'flvtool2_status'), $this);?>

    <td width="10">&nbsp;</td>
    <td width="50"><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['flvtool2_status']['status'] == 'ok'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle">&nbsp;</td>
    <td width="200">MP4Box</td>
    <td>&nbsp;</td>
    <td><?php echo get_binaries(array('path' => 'mp4box','type' => 'server'), $this);?>
</td>
    <td>&nbsp;</td>
    <td width="250"><?php echo get_binaries(array('path' => 'mp4box','type' => 'user'), $this);?>
</td>
    <?php echo check_module_path(array('path' => 'mp4box','get_path' => 'true','assign' => 'mp4box_status'), $this);?>

    <td width="10">&nbsp;</td>
    <td width="50"><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['mp4box_status']['status'] == 'ok'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td>&nbsp;</td>
  </tr>
</table>


<div align="center" style="margin-top:30px"><h2>MODULES VERSION</h2></div>
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="10" align="center" valign="middle" class="left_head">&nbsp;</td>
    <td width="195" align="left" class="head">Module</td>
    <td width="195" align="left" class="head"><div class="head_sep_left" style="width:120px">Installed Version</div></td>
    <td width="195" align="left" class="head"><div class="head_sep_left" style="width:100px">Required Version</div></td>
    <td width="195" align="left" class="head"><div class="head_sep_left" style="width:100px">Status</div></td>
    <td width="10" class="right_head">&nbsp;</td>
  </tr>
</table>

<table width="800" border="0" align="center" cellpadding="0" cellspacing="0"class="block">
  <tr>
    <td width="10" align="center" valign="middle" >&nbsp;</td>
    <td width="195" align="left" >FFMPEG</td>
    <td width="10" align="left" ></td>
    <td width="185" align="left" >r<?php echo $this->_tpl_vars['ffmpeg_status']['version']; ?>
</td>
    <td width="10" align="left" ></td>
    <td width="185" align="left" >r19000 or greater</td>
    <td width="10" align="left" ></td>
    <td width="185" align="left" ><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['ffmpeg_status']['version'] >= 19000): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td width="10" >&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle" >&nbsp;</td>
    <td align="left" >PHP</td>
    <td align="left" ></td>
    <td align="left" ><?php echo $this->_tpl_vars['php_status']['version']; ?>
</td>
    <td align="left" ></td>
    <td align="left" >5.2.1 or greater</td>
    <td align="left" ></td>
    <td align="left" ><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['php_status']['version'] >= '5.2.1'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td >&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle" >&nbsp;</td>
    <td align="left" >FLVTool2</td>
    <td align="left" ></td>
    <td align="left" ><?php echo $this->_tpl_vars['flvtool2_status']['version']; ?>
</td>
    <td align="left" ></td>
    <td align="left" >1.0.6 or greater</td>
    <td align="left" ></td>
    <td align="left" ><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['flvtool2_status']['version'] >= '1.0.6'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td >&nbsp;</td>
  </tr>
  <tr>
    <td align="center" valign="middle" >&nbsp;</td>
    <td align="left" >MP4Box</td>
    <td align="left" ></td>
    <td align="left" ><?php echo $this->_tpl_vars['mp4box_status']['version']; ?>
</td>
    <td align="left" ></td>
    <td align="left" >0.4.5 or greater</td>
    <td align="left" ></td>
    <td align="left" ><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['mp4box_status']['version'] >= '0.4.5'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td >&nbsp;</td>
  </tr>
</table>



<div align="center" style="margin-top:30px">
  <h2>CHECKING FFMPEG CODECS</h2></div>

<?php $this->assign('ffmpeg', get_ffmpeg_codecs()); ?>
<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="10" align="center" valign="middle" class="left_head">&nbsp;</td>
    <td width="150" class="head">Codec</td>
    <td width="150" class="head">Required</td>
    <td class="head">Description</td>
    <td width="100" class="head">Installed</td>
    <td width="10" class="right_head">&nbsp;</td>
  </tr>
</table>


<table width="800" border="0" align="center" cellpadding="0" cellspacing="0">
    <?php $this->assign('bgcolor', ""); ?>
    <?php $_from = $this->_tpl_vars['ffmpeg']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['key'] => $this->_tpl_vars['codec']):
?>
    <tr <?php if ($this->_tpl_vars['codec']['installed'] == 'yes'): ?> bgcolor="<?php echo $this->_tpl_vars['bgcolor']; ?>
" <?php else: ?>bgcolor="#fff7f7" <?php endif; ?>>
    <td width="10" align="center" valign="middle" >&nbsp;</td>
    <td width="150" height="20" valign="middle" <?php if ($this->_tpl_vars['codec']['installed'] != 'yes'): ?> style=" color:#ed0000;font-weight:bold"<?php endif; ?>><?php echo $this->_tpl_vars['key']; ?>
</td>
    <td width="150" valign="middle" ><?php if ($this->_tpl_vars['codec']['required'] == 'yes'): ?>Yes<?php else: ?>No<?php endif; ?></td>
    <td valign="middle"><?php echo $this->_tpl_vars['codec']['desc']; ?>
</td>
    <td width="100" valign="middle" ><img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/<?php if ($this->_tpl_vars['codec']['installed'] == 'yes'): ?>button_ok.png<?php else: ?>error.png<?php endif; ?>"></td>
    <td width="10">&nbsp;</td>
 	</tr>
    <?php if ($this->_tpl_vars['bgcolor'] == ""): ?>
    <?php $this->assign('bgcolor', "#EEEEEE"); ?>
    <?php else: ?>
    <?php $this->assign('bgcolor', ""); ?>
    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?>
</table>