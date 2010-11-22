<?php
$this->load->view('admin/header');
?>
<h1><?php echo lang('add');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/actor/add');
?>
<div>
	<span><?php echo lang('name');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'name',
              'id'          => 'name',
              'value'       => set_value('name'),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:100px',
            ));echo form_error('name');?>
</div>
<div>
	<span><?php echo lang('gender');?>:<br /></span>
	<?php
	echo form_dropdown('gender', array('male'=>lang('male'),'female'=>lang('female'),'unknown'=>lang('unknown')), $this->input->post('gender') ? $this->input->post('gender') : null);
	echo form_error('gender');
	?>
</div>
<div>
	<span><?php echo lang('nationality');?>:<br /></span>
	<?php
	echo form_dropdown('nationality', array('japan'=>'Japan','china'=>'China','korea'=>'Korea'), $this->input->post('nationality') ? $this->input->post('nationality') : null);
	echo form_error('nationality');
	?>
</div>
<div>
	<span><?php echo lang('photo');?>:<br /></span>
	<?php
	echo form_upload('photo',set_value('photo'),'id=photo');
	echo $error;
	?>
</div>
<div>
<?php
echo form_submit('categorysubmit', lang('submit'), 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>