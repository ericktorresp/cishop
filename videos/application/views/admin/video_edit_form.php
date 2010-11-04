<?php
$this->load->view('admin/header');
?>
<h1><?php echo $this->lang->line('edit');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/video/edit/', array('name'=>'insert_work'));
echo form_hidden('vid', $video->vid);
?>
<div>
	<span><?php echo $this->lang->line('category');?>:<br /></span>
	<?php
	echo form_dropdown('cid', $cats, $video->cid);
	echo form_error('cid');
	?>
</div>
<div>
	<span><?php echo $this->lang->line('video_mime');?>:<br /></span>
	<?php
	echo form_dropdown('mime', array('flv'=>'flv','mp4'=>'mp4','f4v'=>'f4v'), $video->mime);
	echo form_error('mime');
	?>
</div>
<div>
	<span><?php echo $this->lang->line('video_server');?>:<br /></span>
	<?php echo form_dropdown('server', $servers, $video->server);echo form_error('server');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_title');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'title',
              'id'          => 'title',
              'value'       => set_value('title', $video->title),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:50%',
            ));echo form_error('title');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_description');?>:<br /></span>
    <?php echo form_textarea('description',set_value('title', $video->description));?>
</div>
<div>
	<span><?php echo $this->lang->line('video_width');?>x<?php echo $this->lang->line('video_height');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'width',
              'id'          => 'width',
              'value'       => set_value('title', $video->width),
              'maxlength'   => '4',
              'size'        => '3',
              'style'       => 'width:24px',
            ));?> x <?php echo form_input(array(
              'name'        => 'height',
              'id'          => 'height',
              'value'       => set_value('title', $video->height),
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
              'value'       => set_value('title', $video->duration),
              'maxlength'   => '10',
              'size'        => '5',
              'style'       => 'width:60px',
            ));echo form_error('duration');?>
</div>
<div>
	<span><?php echo $this->lang->line('video_futured');?>:<br /></span>
    <?php echo form_checkbox('is_fetured', '1', $video->is_fetured);?>
</div>
<div>
	<span><?php echo $this->lang->line('video_views');?>:<br /></span>
    <?php echo form_input(array(
              'name'        => 'views',
              'id'          => 'views',
              'value'       => set_value('title', $video->views),
              'maxlength'   => '6',
              'size'        => '6',
              'style'       => 'width:30px',
            ));?>
</div>
<div>
	<span><?php echo $this->lang->line('video_published');?>:<br /></span>
    <?php echo form_checkbox('published', '1', $video->published);?>
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
echo form_submit('videosubmit', 'Submit Video', 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>