<?php /* Smarty version 2.6.18, created on 2010-10-14 20:20:23
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/language_settings.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'form_val', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/language_settings.html', 74, false),)), $this); ?>
<?php if ($this->_tpl_vars['edit_lang'] != 'yes'): ?>
<h2>Language Settings</h2>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" style="margin-top:15px">
  <tr>
    <td width="20" class="left_head" style="padding-left:5px">ID</td>
    <td width="100" align="left" class="head"><div class="head_sep_left">Default</div></td>
    <td class="head"><div class="head_sep_left" style="width:100px">Language</div></td>
    <td width="100" align="left" class="head"><div class="head_sep_left">Language Code</div></td>
    <td width="150" align="left" class="head"><div  class="head_sep_left">Regular Expression</div></td>
    <td width="300" class="right_head"><div  class="head_sep_left">Actions</div></td>
  </tr>
</table>

<form name="default_lang" method="post" action="?default">
<?php unset($this->_sections['l_list']);
$this->_sections['l_list']['name'] = 'l_list';
$this->_sections['l_list']['loop'] = is_array($_loop=$this->_tpl_vars['language_list']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['l_list']['show'] = true;
$this->_sections['l_list']['max'] = $this->_sections['l_list']['loop'];
$this->_sections['l_list']['step'] = 1;
$this->_sections['l_list']['start'] = $this->_sections['l_list']['step'] > 0 ? 0 : $this->_sections['l_list']['loop']-1;
if ($this->_sections['l_list']['show']) {
    $this->_sections['l_list']['total'] = $this->_sections['l_list']['loop'];
    if ($this->_sections['l_list']['total'] == 0)
        $this->_sections['l_list']['show'] = false;
} else
    $this->_sections['l_list']['total'] = 0;
if ($this->_sections['l_list']['show']):

            for ($this->_sections['l_list']['index'] = $this->_sections['l_list']['start'], $this->_sections['l_list']['iteration'] = 1;
                 $this->_sections['l_list']['iteration'] <= $this->_sections['l_list']['total'];
                 $this->_sections['l_list']['index'] += $this->_sections['l_list']['step'], $this->_sections['l_list']['iteration']++):
$this->_sections['l_list']['rownum'] = $this->_sections['l_list']['iteration'];
$this->_sections['l_list']['index_prev'] = $this->_sections['l_list']['index'] - $this->_sections['l_list']['step'];
$this->_sections['l_list']['index_next'] = $this->_sections['l_list']['index'] + $this->_sections['l_list']['step'];
$this->_sections['l_list']['first']      = ($this->_sections['l_list']['iteration'] == 1);
$this->_sections['l_list']['last']       = ($this->_sections['l_list']['iteration'] == $this->_sections['l_list']['total']);
?>
<div class="row_white">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="20" height="25" valign="middle" style="padding-left:5px"><?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
</td>
    <td width="100" align="left" valign="middle"><label>
      <input type="radio" name="make_default" id="radio" value="<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
" <?php if ($this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_default'] == 'yes'): ?>checked="checked"<?php endif; ?> onclick="document.default_lang.submit()"/>
    </label></td>
    <td valign="middle" style="padding-left:10px"><?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_name']; ?>
</td>
    <td width="100" align="left" valign="middle"><span style="margin-left:10px;"><?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_code']; ?>
</span></td>
    <td width="150" align="left" valign="middle"><span style="margin-left:10px;"><?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_regex']; ?>
</span></td>
    <td width="300" align="left" valign="middle">
    <a href="?edit_language=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
" style="margin-left:10px">Edit Phrases</a> | <a href="?edit_language=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
">Edit</a> | <a href="javascript:void(0)" onclick="if(confirm_it('Are you sure you want to delete \'<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_name']; ?>
\' pack')) window.location = '?delete=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
'">Delete</a> | <a href="?download=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
" target="_blank">Export</a><?php if ($this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_active'] != 'yes'): ?> | <a href="?action=activate&id=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
">Activate</a><?php endif; ?><?php if ($this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_active'] == 'yes'): ?> | <a href="?action=deactivate&id=<?php echo $this->_tpl_vars['language_list'][$this->_sections['l_list']['index']]['language_id']; ?>
">Dectivate</a><?php endif; ?></td>
  </tr>
</table>
</div>
<?php endfor; endif; ?>  
</form>

<div style="height:50px"></div>
<h2> Add New Language </h2>
<form action="?import" method="post" enctype="multipart/form-data" name="import_language" id="import_language">
	<fieldset>
    	<legend>Upload file</legend>
        	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="block">
            <tr>
              <td>Browse ClipBucket Language File ( must be .xml format ) 
              <input type="file" name="lang_file" id="lang_file" />
              <input type="submit" name="add_language" id="add_language" value="Add Language" class="button" /></td>
            </tr>
            </table>
    </fieldset>
</form>
<?php else: ?>
<span class="page_title">Edit <?php echo $this->_tpl_vars['lang_details']['language_name']; ?>
</span>

<div class="search_box">  <form id="form1" name="form1" method="post" action="">
    <label>Language Name 
      <input name="name" type="text" id="name" value="<?php echo $this->_tpl_vars['lang_details']['language_name']; ?>
" />
Language Code   </label>
    <input name="code" type="text" id="code" value="<?php echo $this->_tpl_vars['lang_details']['language_code']; ?>
" />
Language Regex 
<input name="regex" type="text" id="regex" value="<?php echo $this->_tpl_vars['lang_details']['language_regex']; ?>
" /> 
<input type="submit" name="update_language" class="button" value="Update"  style="margin-top:10px" id="update_language"/>
<br />
</form></div>
  
<div style="height:10px">

</div>
<span class="page_title">Editing <?php echo $this->_tpl_vars['lang_details']['language_name']; ?>
 Phrases</span>

<div style="height:10px">

</div>


<div class="search_box">  <form id="form1" name="form1" method="get" action="?">
    <label>Phrase Code - Name<br />
<input name="varname" type="text" id="varname" value="<?php echo ((is_array($_tmp=$_GET['varname'])) ? $this->_run_mod_handler('form_val', true, $_tmp) : form_val($_tmp)); ?>
" />
<input name="edit_language" type="hidden" value="<?php echo $_GET['edit_language']; ?>
" />
    </label>
    <br />
    <label>Phrase Text<br />
<input name="text" type="text" id="text" value="<?php echo ((is_array($_tmp=$_GET['text'])) ? $this->_run_mod_handler('form_val', true, $_tmp) : form_val($_tmp)); ?>
" />
    </label>
    <br />
    <label>
      <input type="submit" name="search_phrase" class="button" value="Submit"  style="margin-top:10px" id="search_phrase"/>
    </label>
  </form></div>

<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="5" class="left_head" ></td>
    <td width="200" class="head">Phrase Code</td>
    <td align="left" class="head">Phrase</td>
    <td width="200"  class="right_head"></td>
  </tr>
</table>

<?php unset($this->_sections['p_list']);
$this->_sections['p_list']['name'] = 'p_list';
$this->_sections['p_list']['loop'] = is_array($_loop=$this->_tpl_vars['lang_phrases']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['p_list']['show'] = true;
$this->_sections['p_list']['max'] = $this->_sections['p_list']['loop'];
$this->_sections['p_list']['step'] = 1;
$this->_sections['p_list']['start'] = $this->_sections['p_list']['step'] > 0 ? 0 : $this->_sections['p_list']['loop']-1;
if ($this->_sections['p_list']['show']) {
    $this->_sections['p_list']['total'] = $this->_sections['p_list']['loop'];
    if ($this->_sections['p_list']['total'] == 0)
        $this->_sections['p_list']['show'] = false;
} else
    $this->_sections['p_list']['total'] = 0;
if ($this->_sections['p_list']['show']):

            for ($this->_sections['p_list']['index'] = $this->_sections['p_list']['start'], $this->_sections['p_list']['iteration'] = 1;
                 $this->_sections['p_list']['iteration'] <= $this->_sections['p_list']['total'];
                 $this->_sections['p_list']['index'] += $this->_sections['p_list']['step'], $this->_sections['p_list']['iteration']++):
$this->_sections['p_list']['rownum'] = $this->_sections['p_list']['iteration'];
$this->_sections['p_list']['index_prev'] = $this->_sections['p_list']['index'] - $this->_sections['p_list']['step'];
$this->_sections['p_list']['index_next'] = $this->_sections['p_list']['index'] + $this->_sections['p_list']['step'];
$this->_sections['p_list']['first']      = ($this->_sections['p_list']['iteration'] == 1);
$this->_sections['p_list']['last']       = ($this->_sections['p_list']['iteration'] == $this->_sections['p_list']['total']);
?>
<div class="row_white">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="5"  ></td>
    <td width="200" ><?php echo $this->_tpl_vars['lang_phrases'][$this->_sections['p_list']['index']]['varname']; ?>
</td>
    <td align="left"><div class="edit_lang" id="<?php echo $this->_tpl_vars['lang_phrases'][$this->_sections['p_list']['index']]['id']; ?>
"><?php echo $this->_tpl_vars['lang_phrases'][$this->_sections['p_list']['index']]['text']; ?>
</div></td>
    <td width="200" ></td>
  </tr>
</table>
</div>
<?php endfor; endif; ?>

<!-- DIsplaying Videos Ends-->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/pagination.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php endif; ?>