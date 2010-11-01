<?php
$this->load->view('admin/header');
echo $error;
echo form_open_multipart('admin/video/add');
?>

<input type="file" name="userfile" size="20" />

<br /><br />

<input type="submit" value="upload" name="submit" />
</form>
<?php
$this->load->view('admin/footer');
?>