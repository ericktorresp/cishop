<?php
$this->load->view('admin/header');
?>
<h1><?php echo $this->lang->line('add');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/video/add', array('name'=>'insert_work'));
?>
<div>
	<span><?php echo $this->lang->line('category');?>:<br /></span>
	<?php
	echo form_dropdown('cid', $cats, $this->input->post('cid') ? $this->input->post('cid') : null);
	echo form_error('cid');
	?>
</div>
<div>
	<span><?php echo $this->lang->line('video_mime');?>:<br /></span>
	<?php
	echo form_dropdown('mime', array('flv'=>'flv','mp4'=>'mp4','f4v'=>'f4v'), $this->input->post('mime') ? $this->input->post('mime') : NULL);
	echo form_error('mime');
	?>
</div>
<div>
	<span><?php echo $this->lang->line('server');?>:<br /></span>
	<?php echo form_dropdown('server', $servers, $this->input->post('server') ? $this->input->post('server') : NULL);echo form_error('server');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_title');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'title',
              'id'          => 'title',
              'value'       => set_value('title'),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:50%',
            ));echo form_error('title');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_description');?>:<br /></span>
    <?php echo form_textarea('description',set_value('description'));?>
</div>
<div>
	<span><?php echo $this->lang->line('actor');?>:<br /></span>
	<?php echo form_dropdown('aid', $actors, $this->input->post('aid') ? $this->input->post('aid') : NULL);echo form_error('aid');?>
</div>
<div>
	<span><?php echo $this->lang->line('publisher');?>:<br /></span>
	<?php echo form_dropdown('pid', $publishers, $this->input->post('pid') ? $this->input->post('pid') : NULL);echo form_error('pid');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_width');?>x<?php echo $this->lang->line('video_height');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'width',
              'id'          => 'width',
              'value'       => set_value('width'),
              'maxlength'   => '4',
              'size'        => '3',
              'style'       => 'width:24px',
            ));?> x <?php echo form_input(array(
              'name'        => 'height',
              'id'          => 'height',
              'value'       => set_value('height'),
              'maxlength'   => '4',
              'size'        => '3',
              'style'       => 'width:24px',
            ));echo form_error('width');echo form_error('height');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_duration');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'duration',
              'id'          => 'duration',
              'value'       => set_value('duration'),
              'maxlength'   => '10',
              'size'        => '5',
              'style'       => 'width:60px',
            ));echo form_error('duration');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_futured');?>:<br /></span>
    <?php echo form_checkbox('is_fetured', '1', FALSE, set_checkbox('is_fetured','1'));?>
</div>
<div>
	<span><?php echo $this->lang->line('video_views');?>:<br /></span>
    <?php echo form_input(array(
              'name'        => 'views',
              'id'          => 'views',
              'value'       => set_value('views'),
              'maxlength'   => '6',
              'size'        => '6',
              'style'       => 'width:30px',
            ));?>
</div>
<div>
	<span><?php echo $this->lang->line('video_published');?>:<br /></span>
    <?php echo form_checkbox('published', '1', FALSE, set_checkbox('published','1'));?>
</div>
<div>
	<span><?php echo $this->lang->line('video_thumbnail');?>:<br /></span>
	<?php
	echo form_upload('userfile',set_value('userfile'),'id=userfile');
	echo $error;
	?>
</div>
<div>
<?php
echo form_submit('videosubmit', lang('submit'), 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>