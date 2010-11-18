<?php
$this->load->view('admin/header');
?>
<h1><?php echo lang('edit');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/publisher/edit');
echo form_hidden('id', set_value('id',$publisher->id));
?>
<div>
	<span><?php echo lang('name');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'name',
              'id'          => 'name',
              'value'       => set_value('name',$publisher->name),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:100px',
            ));echo form_error('name');?>
</div>
<div>
	<span><?php echo lang('nationality');?>:<br /></span>
	<?php
	echo form_dropdown('nationality', array('japan'=>lang('japan'),'china'=>lang('china'),'korea'=>lang('korea')), $this->input->post('nationality') ? $this->input->post('nationality') : $publisher->nationality);
	echo form_error('nationality');
	?>
</div>
<div>
<?php
echo form_submit('submit', lang('submit'), 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>