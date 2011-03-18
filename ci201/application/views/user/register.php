<?php $this->load->view('header')?>
	<div id="bottomContainer">
	    <h1 class="noBottom">
	        Join <?php echo $this->config->item('site_title')?>...
	        <span class="right">...in 3 easy steps</span>
	    </h1>
	    <div class="securityNote">Your personal details are always safe with us on our secure servers.</div>
		<?php echo validation_errors('<div class="errorNotice"><div class="icon error"></div>','</div>'); ?>
	    <?php echo form_open()?>
	    <?php echo form_hidden('step',1)?>
	        <div class="registerSteps step1 right"></div>
	        <div class="registerPromo">
	        	<p>"I just wanted to say Thank you. I have accounts at 7 or 8 online casinos, but I haven't played at any other site in weeks as Slotland is the most responsive, courtious, friendly & professional casino on the internet I have visited."</p>
	            <cite>-- TRIP42&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</cite>
	        </div>
	        <div class="formColumn right">
	            <div class="formRow mandatory<?php if(form_error('username')){?> error<?php }?>">
	                <label>Login Name</label>
	                <?php echo form_input('username', set_value('username',$this->session->userdata('username')))?>
	            </div>
	            <div class="formRow mandatory<?php if(form_error('password')){?> error<?php }?>">
	                <label>Choose Password</label>
	                <?php echo form_password('password', set_value('password',$this->session->userdata('password')))?>
	            </div>
	            <div class="formRow mandatory<?php if(form_error('passwordconfirm')){?> error<?php }?>">
	                <label>Confirm Password</label>
	                <?php echo form_password('passwordconfirm', set_value('passwordconfirm',$this->session->userdata('passwordconfirm')))?>
	            </div>
	            <div class="formRow mandatory<?php if(form_error('email')){?> error<?php }?>">
	                <label>E-mail address</label>
	                <?php echo form_input('email', set_value('email',$this->session->userdata('email')))?>
	            </div>
	            <div class="formRow taller mandatory checkbox<?php if(form_error('agree')){?> error<?php }?>">
	                <span class="mandatory"><?php echo form_checkbox(array('name'=>'agree','id'=>'ch_agree','value'=>1,'checked'=> set_checkbox('agree', '1')));?></span>
	                <label for="ch_agree" class="block">
	                    I'm at least 18 years old. I have read and agree to these
	                    <a href="<?php echo $this->config->item('base_url')?>terms-and-conditions.html" target="_new">Terms &amp; Conditions</a>
	                    and this
	                    <a href="<?php echo $this->config->item('base_url')?>privacy.html" target="_new">Privacy Policy</a>.
	                </label>
	            </div>
	        </div>
	        <div class="formFooter">
	            <div class="importantNote">All <span class="mandatory">gold fields</span> are mandatory</div>
	            <div class="buttons">
	                <input type="image" src="<?php echo $this->config->item('asset_url')?>images/button_next.png" alt="NEXT">
	            </div>
	        </div>
	    <?php echo form_close()?>
	</div>
<?php $this->load->view('footer')?>