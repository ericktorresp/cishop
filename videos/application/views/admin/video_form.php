<?php
$this->load->view('admin/header');
?>
<h1>添加视频</h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/video/add', array('name'=>'insert_work'));
?>
<div>
	<span>类别:<br /></span>
	<?php
	echo form_dropdown('cid', $cats);
	echo form_error('cid');
	?>
</div>
<div>
	<span>服务器:<br /></span>
	<?php echo form_dropdown('server', $servers);echo form_error('server');?>
</div>
<div>
	<span>标题:<br /></span>
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
	<span>简介:<br /></span>
    <?php echo form_textarea('description','');?>
</div>
<div>
	<span>宽x高:<br /></span>
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
	<span>是否推荐:<br /></span>
    <?php echo form_checkbox('is_fetured', '1', FALSE);?>
</div>
<div>
	<span>观看次数:<br /></span>
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
	<span>已完成上传:<br /></span>
    <?php echo form_checkbox('published', '1', FALSE);?>
</div>
<div>
	<span>缩略图:<br /></span>
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