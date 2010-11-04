<?php
$this->load->view('admin/header');
?>
<h1><?php echo $this->lang->line('add');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/category/add');
?>
<div>
	<span><?php echo $this->lang->line('video_title');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'ctitle',
              'id'          => 'ctitle',
              'value'       => set_value('ctitle'),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:200px',
            ));echo form_error('ctitle');?>
</div>
<div>
	<span><?php echo $this->lang->line('category_order');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'order',
              'id'          => 'order',
              'value'       => set_value('order'),
              'maxlength'   => '3',
              'size'        => '3',
              'style'       => 'width:20px',
            ));echo form_error('title');?>
</div>
<div>
<?php
echo form_submit('categorysubmit', 'Submit Category', 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>