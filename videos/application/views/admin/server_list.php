<?php
$this->load->view('admin/header');
?>
<h1><?php echo $this->lang->line('server');?></h1>
<?php
if($this->session->flashdata('error'))
{
	echo '<div id="error">'.$this->session->flashdata('error').'</div>';
}
if($this->session->flashdata('infomation'))
{
	echo '<div id="infomation">'.$this->session->flashdata('infomation').'</div>';
}
?>
<p>
	<img src="<?php echo base_url();?>images/icon_add.png" alt="Add new" />
	<?php echo anchor('admin/server/add', $this->lang->line('add')); ?>
</p>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#cccccc" width="100%">
	<tr>
		<td bgcolor="#cccccc"><strong><?php echo $this->lang->line('domain')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo $this->lang->line('ip')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo $this->lang->line('status')?></strong></td>
		<td bgcolor="#cccccc" colspan="2"><strong><?php echo $this->lang->line('operation')?></strong></td>
	</tr>
<?php
foreach($servers as $server)
{
?>
	<tr onmouseover="this.bgColor='#dddddd'" onmouseout="this.bgColor='#ffffff'" onclick="parent.location='<?php echo site_url("admin/server/edit/".$server->sid)?>'" bgcolor="#ffffff">
		<td><?php echo $server->domain;?></td>
		<td><?php echo $server->ip;?></td>
		<td><?php echo ($server->actived ? $this->lang->line('actived') : $this->lang->line('inactive'));?></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_update.png" alt="<?php echo $this->lang->line('edit')?>" /> <a href="<?php echo site_url("admin/server/edit/".$server->sid)?>"><?php echo $this->lang->line('edit')?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_delete.png" alt="<?php echo $this->lang->line('delete')?>" /><a href="<?php echo site_url("admin/server/delete/".$server->sid)?>"><?php echo $this->lang->line('delete')?></a></td>
	</tr>
<?php
}
?>
</table>
<?php
$this->load->view('admin/footer');
?>