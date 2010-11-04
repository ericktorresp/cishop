<?php
$this->load->view('admin/header');
?>
<h1><?php echo $this->lang->line('edit');?></h1>
<div id="work-insert" class="form-page">
<?php
echo form_open_multipart('admin/server/edit');
echo form_hidden('sid', $server->sid);
?>
<div>
	<span><?php echo $this->lang->line('domain');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'domain',
              'id'          => 'domain',
              'value'       => set_value('domain', $server->domain),
              'maxlength'   => '100',
              'size'        => '50',
              'style'       => 'width:100px',
            ));echo form_error('domain');?>
</div>
<div>
	<span><?php echo $this->lang->line('ip');?>:<br /></span>
	<?php echo form_input(array(
              'name'        => 'ip',
              'id'          => 'ip',
              'value'       => set_value('ip', $server->ip),
              'maxlength'   => '16',
              'size'        => '16',
              'style'       => 'width:60px',
            ));echo form_error('ip');?>
</div>
<div>
	<span><?php echo $this->lang->line('actived');?>:<br /></span>
    <?php echo form_checkbox('actived', '1', ((boolean)$this->input->post('actived')||$server->actived));?>
</div>
<div>
<?php
echo form_submit('serversubmit', $this->lang->line('submit'), 'id="submit"');
?>
</div>
<?php
echo form_close();
?>
</div>
<?php
$this->load->view('admin/footer');
?>