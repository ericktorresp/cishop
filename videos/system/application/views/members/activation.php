
<div id="member_activation">
	<h2><?php echo $this->lang->line('members_active');?></h2>
	<form action="/activation" method="post">
		<input type="hidden" name="activation" value="<?php echo $activeuser ?>" />
		<input type="hidden" name="password" value="<?php echo $password ?>" />
		<dl>
			<dt><?php echo $this->lang->line('members_username');?> : </dt>
			<dd><?php echo $activeuser ?></dd>
		</dl>
		<input type="submit" value="<?php echo $this->lang->line('members_active');?>" />
	</form>
</div>