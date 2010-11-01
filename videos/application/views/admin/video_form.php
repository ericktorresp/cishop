<?php
$this->load->view('admin/header');
echo $error;
echo form_open_multipart('admin/video/add');
?>
<dl>
	<dt>标题</dt>
	<dd><?php echo form_input(array(
              'name'        => 'title',
              'id'          => 'title',
              'value'       => '',
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:50%',
            ));?></dd>
	<dt>简介</dt>
	<dd><?php echo form_textarea('description','');?></dd>
	<dt>宽x高</dt>
	<dd><?php echo form_input(array(
              'name'        => 'width',
              'id'          => 'width',
              'value'       => '',
              'maxlength'   => '3',
              'size'        => '2',
              'style'       => 'width:20px',
            ));?> x <?php echo form_input(array(
              'name'        => 'height',
              'id'          => 'height',
              'value'       => '',
              'maxlength'   => '3',
              'size'        => '2',
              'style'       => 'width:20px',
            ));?></dd>
     <dt>是否推荐</dt>
     <dd><?php echo form_checkbox('is_fetured', '1', FALSE);?></dd>
     <dt>观看次数</dt>
     <dd><?php echo form_input(array(
              'name'        => 'views',
              'id'          => 'views',
              'value'       => '',
              'maxlength'   => '6',
              'size'        => '6',
              'style'       => 'width:30px',
            ));?></dd>
     
</dl>
<?php
//echo form_label('缩略图','userfile');
echo form_upload('userfile','','id=userfile');
echo form_submit('videosubmit', 'Submit Video');
echo form_close();
$this->load->view('admin/footer');
?>