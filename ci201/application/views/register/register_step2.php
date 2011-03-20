<?php $this->load->view('header')?>
<div id="bottomContainer">
    <h1 class="noBottom">
        Join <?php echo $this->config->item('site_title')?>...
        <span class="right">...in 3 easy steps</span>
    </h1>
    <div class="securityNote">Your personal details are always safe with us on our secure servers.</div>
		<?php echo validation_errors('<div class="errorNotice"><div class="icon error"></div>','</div>'); ?>
	    <?php echo form_open()?>
        <?php echo form_hidden('step',2)?>
        <div class="registerSteps step2"></div>
        <div class="formColumn left">
            <div class="formRow mandatory<?php if(form_error('gender')){?> error<?php }?>">
                <label><?php echo lang('Gender')?></label>
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender1','value'=>'M','checked'=>set_radio('gender', 'M', $this->session->userdata('gender')=='M')))?><label for="r_gender1" class="plain"><?php echo lang('Male')?></label>
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender2','value'=>'F','checked'=>set_radio('gender', 'F', $this->session->userdata('gender')=='F')))?><label for="r_gender2" class="plain"><?php echo lang('Female')?></label>
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender3','value'=>'U','checked'=>set_radio('gender', 'U', $this->session->userdata('gender')=='U')))?><label for="r_gender3" class="plain"><?php echo lang('Unknown')?></label>
            </div>

            <div class="formRow mandatory<?php if(form_error('fname')){?> error<?php }?>">
                <label>First name</label>
                <?php echo form_input('fname', set_value('fname',$this->session->userdata('fname')))?>
            </div>
            <div class="formRow mandatory<?php if(form_error('lname')){?> error<?php }?>">
                <label>Last name</label>
                <?php echo form_input('lname', set_value('lname',$this->session->userdata('lname')))?>
            </div>

            <div class="formRow mandatory<?php if(form_error('phone')){?> error<?php }?>">
                <label>Phone number</label>
                <?php echo form_input('phone', set_value('phone',$this->session->userdata('phone')))?>
            </div>
            <div class="formRow mandatory<?php if(form_error('birth_month')||form_error('birth_day')||form_error('birth_year')){?> error<?php }?>">
                <label>Date of Birth</label>
                <?php
                $months = array(''=>lang('Month'));
                for($i=1;$i<=12;$i++)
                {
                	$months[$i] = date("M", mktime(0, 0, 0, $i+1, 0, 0));
                }
                echo form_dropdown('birth_month', $months, set_value('birth_month', $this->session->userdata('birth_month')), 'style="width: 65px;"');
                echo '&nbsp;';
                $days = array(''=>lang('Day'));
                for($i=1;$i<=31;$i++)
                {
                	$days[$i] = $i;
                }
                echo form_dropdown('birth_day', $days, set_value('birth_day', $this->session->userdata('birth_day')), 'style="width: 55px;"');
                echo '&nbsp;';
                $years = array(''=>lang('Year'));
                for($i=1950;$i<=date('Y');$i++)
                {
                	$years[$i] = $i;
                }
                echo form_dropdown('birth_year', $years, set_value('birth_year', $this->session->userdata('birth_year')), 'style="width: 76px;"');
                ?>
            </div>
        </div>

        <div class="formColumn right">
            <div class="formRow mandatory<?php if(form_error('street_addr')){?> error<?php }?>">
                <label>Street address</label>
                <?php echo form_input('street_addr', set_value('street_addr',$this->session->userdata('street_addr')))?>
            </div>
            <div class="formRow">
                <label>Apt/Suite number</label>
                <?php echo form_input('suite', set_value('suite',$this->session->userdata('suite')))?>
            </div>
            <div class="formRow mandatory<?php if(form_error('city')){?> error<?php }?>">
                <label>City</label>
                <?php echo form_input('city', set_value('city',$this->session->userdata('city')))?>
            </div>
            <div class="formRow mandatory<?php if(form_error('zip')){?> error<?php }?>">
                <label>Postal code</label>
                <?php echo form_input('zip', set_value('zip',$this->session->userdata('zip')))?>
            </div>
            <div class="formRow">
                <label>State/Province</label>
				<SELECT NAME="state">
				<OPTION VALUE="">Please choose a state
				<OPTION VALUE="">-- AMERICAN STATES --
				<OPTION VALUE="AL" >Alabama
				<OPTION VALUE="AK" >Alaska
				<OPTION VALUE="AZ" >Arizona
				<OPTION VALUE="AR" >Arkansas
				<OPTION VALUE="CA" >California
				<OPTION VALUE="CO" >Colorado
				<OPTION VALUE="CT" >Connecticut
				<OPTION VALUE="DC" >District of Columbia
				<OPTION VALUE="DE" >Delaware
				<OPTION VALUE="FL" >Florida
				<OPTION VALUE="GA" >Georgia
				<OPTION VALUE="HI" >Hawaii
				<OPTION VALUE="ID" >Idaho
				<OPTION VALUE="IL" >Illinois
				<OPTION VALUE="IN" >Indiana
				
				<OPTION VALUE="IA" >Iowa
				<OPTION VALUE="KS" >Kansas
				<OPTION VALUE="KY" >Kentucky
				<OPTION VALUE="LA" >Louisiana
				<OPTION VALUE="ME" >Maine
				<OPTION VALUE="MD" >Maryland
				<OPTION VALUE="MA" >Massachusetts
				<OPTION VALUE="MI" >Michigan
				<OPTION VALUE="MN" >Minnesota
				<OPTION VALUE="MS" >Mississippi
				<OPTION VALUE="MO" >Missouri
				<OPTION VALUE="MT" >Montana
				<OPTION VALUE="NE" >Nebraska
				<OPTION VALUE="NV" >Nevada
				<OPTION VALUE="NH" >New Hampshire
				<OPTION VALUE="NJ" >New Jersey
				<OPTION VALUE="NM" >New Mexico
				
				<OPTION VALUE="NY" >New York
				<OPTION VALUE="NC" >North Carolina
				<OPTION VALUE="ND" >North Dakota
				<OPTION VALUE="OH" >Ohio
				<OPTION VALUE="OK" >Oklahoma
				<OPTION VALUE="OR" >Oregon
				<OPTION VALUE="PA" >Pennsylvania
				<OPTION VALUE="RI" >Rhode Island
				<OPTION VALUE="SC" >South Carolina
				<OPTION VALUE="SD" >South Dakota
				<OPTION VALUE="TN" >Tennessee
				<OPTION VALUE="TX" >Texas
				<OPTION VALUE="UT" >Utah
				<OPTION VALUE="VT" >Vermont
				<OPTION VALUE="VA" >Virginia
				<OPTION VALUE="WA" >Washington
				<OPTION VALUE="WV" >West Virginia
				
				<OPTION VALUE="WI" >Wisconsin
				<OPTION VALUE="WY" >Wyoming
				<OPTION VALUE="">-- CANADIAN PROVINCES --
				<OPTION VALUE="AB" >Alberta
				<OPTION VALUE="BC" >British Columbia
				<OPTION VALUE="MB" >Manitoba
				<OPTION VALUE="NB" >New Brunswick
				<OPTION VALUE="NF" >Newfoundland
				<OPTION VALUE="NT" >Northwest Territories
				<OPTION VALUE="NS" >Nova Scotia
				<OPTION VALUE="ON" >Ontario
				<OPTION VALUE="PE" >Prince Edward Island
				<OPTION VALUE="QC" >Quebec
				<OPTION VALUE="SK" >Saskatchewan
				<OPTION VALUE="YT" >Yukon
				<OPTION VALUE="">-- AUSTRALIAN STATES --
				<OPTION VALUE="AAC" >Australian Capital Territory
				
				<OPTION VALUE="ANS" >New South Wales
				<OPTION VALUE="ANT" >Northern Territory
				<OPTION VALUE="AQL" >Queensland
				<OPTION VALUE="ASA" >South Australia
				<OPTION VALUE="ATS" >Tasmania
				<OPTION VALUE="AVI" >Victoria
				<OPTION VALUE="AWA" >Western Australia
				</SELECT>
            </div>
            <div class="formRow mandatory<?php if(form_error('country')){?> error<?php }?>">
                <label>Country</label>
                <?php echo form_dropdown('country', $country, set_value('country', $this->session->userdata('country')))?>
            </div>

        </div>

        <div class="formFooter">
            <div class="importantNote">All <span class="mandatory">gold fields</span> are mandatory</div>
            <div class="buttons">
                <input type="image" src="<?php echo $this->config->item('asset_url')?>images/button_next.png" alt="NEXT">
            </div>

        </div>
    </form>
    </div>
<?php $this->load->view('footer')?>