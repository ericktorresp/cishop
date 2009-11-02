<h1>
<span class="action-span"><a href="<?php print current_url()?>/add"><?php print $this->lang->line('ui_style_add')?></a></span>
<span class="action-span1"><a href="<?php print config_item('base_url')?>admin/"><?php print $this->lang->line('ui_manage')?></a>  - <?php print $this->lang->line('ui_style_manage')?></span>
<div style="clear:both"></div>
</h1>
<div class="list-div" id="listDiv">
	<table cellpadding="3" cellspacing="1">
		<tr>
		  <th><?php print $this->lang->line('ui_style_code')?></th>
		  <th><?php print $this->lang->line('ui_style_name')?></th>
		  <th><?php print $this->lang->line('ui_style_time')?></th>
		  <th><?php print $this->lang->line('ui_oprate')?></th>
		</tr>

<?php
foreach($style_list as $style)
{
?>
	    <tr>
	      <td align="center" class="first-cell"><?php print $style->code?></td>
	      <td align="center"><?php print $style->name?></td>
		  <td align="center"><?php print $style->time?></td>
	      <td align="center"><?php print $this->lang->line('ui_edit')?> | <?php print $this->lang->line('ui_remove')?></td>
	    </tr>
<?php
}
?>
		<tr>
	      <td nowrap="true" align="right" colspan="4" style="background-color: rgb(255, 255, 255);"><?php print $paginate?></td>
	    </tr>
	</table>
</div>