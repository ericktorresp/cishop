<?php $this->load->view('header');?>
				<div id="login-box">
					<form action="/login" method="post" name="login">
						<h1><?php echo lang('login');?></h1>
						<div>
							<span><?php echo lang('username')?>:<br /></span>
							<input type="text" name="username" id="username" value="<?php echo set_value('username');?>" style="width:200px;" />
							<?php echo form_error('username');?>
						</div>
						<div>
							<span><?php echo lang('password')?>:<br /></span>
							<input type="password" name="password" id="password" value="" style="width:200px;" />
							<?php echo form_error('password');?>
						</div>
						<div>
							<input type="submit" value="<?php echo lang('login');?>" id="submit" name="loginsubmit" />
						</div>
						<div></div>
					</form>
				</div>
<?php $this->load->view('footer');?>