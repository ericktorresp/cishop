<?php $this->load->view('admin/header');?>
<h1><?php echo $this->lang->line('video');?></h1>
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
	<?php echo anchor('admin/video/add', $this->lang->line('add')); ?>
</p>
<?php echo $pagination;?>
<table cellpadding="4" cellspacing="1" border="0" bgcolor="#cccccc" width="100%">
	<tr>
		<td bgcolor="#cccccc"><strong><?php echo lang('video_title')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo lang('category')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo lang('server')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo lang('key')?></strong></td>
		<td bgcolor="#cccccc"><strong><?php echo lang('video_mime')?></strong></td>
		<td bgcolor="#cccccc" colspan="3"><strong><?php echo lang('operation')?></strong></td>
	</tr>
<?php
foreach($videos as $video)
{
?>
	<tr onmouseover="this.bgColor='#dddddd'" onmouseout="this.bgColor='#ffffff'" onclick="parent.location='<?php echo site_url("admin/video/edit/".$video->vid)?>'" bgcolor="#ffffff">
		<td><?php echo $video->title;?></td>
		<td><?php echo anchor('admin/video/category/'.$video->cid, $video->ctitle);?></td>
		<td><?php echo $video->server;?></td>
		<td><?php echo $video->key;?></td>
		<td><?php echo $video->mime;?></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_view.png" alt="<?php echo lang('view')?>" /><a href="<?php echo site_url("/watch?v=".$video->key)?>"><?php echo lang('view')?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_update.png" alt="<?php echo lang('edit')?>" /><a href="<?php echo site_url("admin/video/edit/".$video->vid)?>"><?php echo lang('edit')?></a></td>
		<td width="100"><img src="<?php echo base_url();?>images/icon_delete.png" alt="<?php echo lang('delete')?>" /><a href="<?php echo site_url("admin/video/delete/".$video->vid)?>"><?php echo lang('delete')?></a></td>
	</tr>
<?php
}
?>
</table>
<?php echo $pagination;?>
<?php $this->load->view('admin/footer');?>