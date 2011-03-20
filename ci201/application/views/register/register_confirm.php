<?php $this->load->view('header');?>
<div id="bottomContainer">
    <h1 class="noBottom">
        Join <?php echo $this->config->item('site_title')?>...
        <span class="right">...in 3 easy steps</span>
    </h1>
    <div class="securityNote">Your personal details are always safe with us on our secure servers.</div>
    <?php echo validation_errors('<div class="errorNotice"><div class="icon error"></div>','</div>'); ?>
    <?php echo form_open()?>
    <?php echo form_hidden('step',3)?>
        <div class="registerSteps step3">
            Please review the information you entered, then confirm your registration.
        </div>
        <div class="formColumn left summary">
            <div class="formRow mandatory">
                <label>Login Name</label>
                <?php echo $this->session->userdata('username')?>
            </div>
            <div class="formRow">
                <label>Name</label>
                <?php echo $this->session->userdata('fname')?><?php echo $this->session->userdata('lname')?>
            </div>

            <div class="formRow">
                <label>E-mail</label>
                <?php echo $this->session->userdata('email')?>
            </div>
            <div class="formRow">
                <label>Phone Number</label>
                <?php echo $this->session->userdata('phone')?>
            </div>
            <div class="formRow">

                <label>Date of Birth</label>
                <?php echo $this->session->userdata('birth_month')?> <?php echo $this->session->userdata('birth_day')?> <?php echo $this->session->userdata('birth_year')?>
            </div>
            <div class="formRow auto">
                <label>Address</label>
                <span style="display: inline-block; vertical-align: top;"><?php echo $this->session->userdata('street_addr')?><br><?php echo $this->session->userdata('city')?>, <?php echo $this->session->userdata('province')?>, <?php echo $this->session->userdata('zip')?><br><?php echo $this->session->userdata('country')?></span>
            </div>

        </div>

        <div class="formColumn right">
            <div class="formText">
                Bonus money will be added to your player account
                immediatelly upon acceptance of your first deposit.
            </div>

            <div class="formRow">
                <label>Promotional Code</label>
                <input type="text" name="promo_code">

            </div>

            <div class="formNote">
                You will be contacted regarding your winnings, billing
                inquires, updates and promotions. A confirmation of each
                transaction made will also be emailed to you. Your account
                may be restricted if your personal information is found to
                not be correct or up to date.
            </div>
        </div>

        <div class="formFooter right">
            <div class="buttons">
                <a href="<?php echo site_url('register/step2')?>"><img src="<?php echo $this->config->item('asset_url')?>images/button_back.png" border="0" alt="Back"></a>

                <input type="image" src="<?php echo $this->config->item('asset_url')?>images/button_register.png" alt="REGISTER">
            </div>
        </div>
    </form>
    </div>
<?php $this->load->view('footer')?>