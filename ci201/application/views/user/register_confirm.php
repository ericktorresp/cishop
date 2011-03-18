<?php $this->load->view('header');?>
<div id="bottomContainer">
    <h1 class="noBottom">
        Join <?php echo $this->config->item('site_title')?>...
        <span class="right">...in 3 easy steps</span>
    </h1>
    <div class="securityNote">Your personal details are always safe with us on our secure servers.</div>
    <form method="POST" action="https://www.slotland.com/.user.new?step=3&u=1PsqmtW7srIeqLTAZl">
        <input type="hidden" name="step" value="3">

        <div class="registerSteps step3">
            Please review the information you entered, then confirm your registration.
        </div>

        <div class="formColumn left summary">
            <div class="formRow mandatory">
                <label>Login Name</label>
                DARKMOON1111
            </div>
            <div class="formRow">
                <label>Name</label>
                Mr. dark moon
            </div>

            <div class="formRow">
                <label>E-mail</label>
                cmtv@163.com
            </div>
            <div class="formRow">
                <label>Phone Number</label>
                025550234
            </div>
            <div class="formRow">

                <label>Date of Birth</label>
                January 1, 1911
            </div>
            <div class="formRow auto">
                <label>Address</label>
                <span style="display: inline-block; vertical-align: top;">na.a<br>Portland, AL, 78649<br>United States</span>
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
                <a href="https://www.slotland.com/.user.new?step=2&u=1PsqmtW7srIeqLTAZl"><img src="<?php echo $this->config->item('asset_url')?>images/button_back.png" border="0" alt="Back"></a>

                <input type="image" src="<?php echo $this->config->item('asset_url')?>images/button_register.png" alt="REGISTER">
            </div>
        </div>
    </form>
    </div>
<?php $this->load->view('footer')?>