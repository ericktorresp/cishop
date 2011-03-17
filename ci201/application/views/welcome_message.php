<?php $this->load->view('header')?>
<h1>Welcome to CodeIgniter!</h1>
<?php echo form_open('welcome/login');?>
<p>Username: <?php echo form_input('username', '');?></p>
<p>Password: <?php echo form_password('loginpwd', '');?></p>
<p><?php echo $cap['image']?></p>
<p>Captcha: <?php echo form_input('captcha', '');?></p>
<p><?php echo form_submit('login','LOGIN')?></p>
<?php echo form_close();?>
<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

<p>If you would like to edit this page you'll find it located at:</p>
<code>application/views/welcome_message.php</code>

<p>The corresponding controller for this page is found at:</p>
<code>application/controllers/welcome.php</code>

<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>


<p><br />Page rendered in {elapsed_time} seconds</p>

<?php $this->load->view('footer')?>
