<h1>
<span class="action-span"><a href="<?php print config_item('base_url')?>admin/style"><?php print $this->lang->line('ui_style_manage')?></a></span>
<span class="action-span1"><a href="<?php print config_item('base_url')?>admin/"><?php print $this->lang->line('ui_manage')?></a>  - <?php print $this->lang->line('ui_style_manage')?></span>
<div style="clear:both"></div>
</h1>
<div class="main-div">
<?php
$attributes = array('name'=>'theForm','onSubmit'=>'return validate();');
print form_open(current_url(),$attributes);
?>
<table cellspacing="1" cellpadding="3" width="100%">
  <tr>
    <td class="label"><?php print $this->lang->line('ui_style_code')?></td>

    <td><input type="text" name="style_code" maxlength="15" value=""  /><span class="require-field">*</span></td>
  </tr>
  <tr>
    <td class="label"><?php print $this->lang->line('ui_style_name')?></td>
    <td><input type="text" name="style_name" maxlength="60" size="40" value=""  /><span class="require-field">*</span></td>
  </tr>
  <tr>

    <td colspan="2" align="center"><br />
      <input type="submit" class="button" value="<?php print $this->lang->line('ui_submit')?>"  />
      <input type="reset" class="button" value="<?php print $this->lang->line('ui_reset')?>"  />
      <input type="hidden" name="id" value="" />
     </td>
  </tr>
</table>
<?php print form_close();?>
</div>
