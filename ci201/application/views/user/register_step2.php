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
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender1','value'=>'M','checked'=>set_radio('gender', 'M')))?><label for="r_gender1" class="plain"><?php echo lang('Male')?></label>
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender2','value'=>'F','checked'=>set_radio('gender', 'F')))?><label for="r_gender2" class="plain"><?php echo lang('Female')?></label>
                <?php echo form_radio(array('name'=>'gender','id'=>'r_gender3','value'=>'U','checked'=>set_radio('gender', 'U')))?><label for="r_gender3" class="plain"><?php echo lang('Unknown')?></label>
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
			<SELECT NAME=country SIZE=1>
			<OPTION VALUE="">Please choose a country
			<OPTION VALUE="US" >United States
			<OPTION VALUE="CA" >Canada
			<OPTION VALUE="AF" >Afghanistan
			<OPTION VALUE="AL" >Albania
			<OPTION VALUE="DZ" >Algeria
			<OPTION VALUE="AS" >American Samoa
			<OPTION VALUE="AD" >Andorra
			<OPTION VALUE="AO" >Angola
			<OPTION VALUE="AI" >Anguilla
			<OPTION VALUE="AQ" >Antarctica
			<OPTION VALUE="AG" >Antigua And Barbuda
			<OPTION VALUE="AR" >Argentina
			<OPTION VALUE="AM" >Armenia
			<OPTION VALUE="AW" >Aruba
			<OPTION VALUE="AU" >Australia
			<OPTION VALUE="AT" >Austria
			<OPTION VALUE="AZ" >Azerbaijan
			
			<OPTION VALUE="BS" >Bahamas
			<OPTION VALUE="BH" >Bahrain
			<OPTION VALUE="BD" >Bangladesh
			<OPTION VALUE="BB" >Barbados
			<OPTION VALUE="BY" >Belarus
			<OPTION VALUE="BE" >Belgium
			<OPTION VALUE="BZ" >Belize
			<OPTION VALUE="BJ" >Benin
			<OPTION VALUE="BM" >Bermuda
			<OPTION VALUE="BT" >Bhutan
			<OPTION VALUE="BO" >Bolivia
			<OPTION VALUE="BA" >Bosnia And Herzegowina
			<OPTION VALUE="BW" >Botswana
			<OPTION VALUE="BV" >Bouvet Island
			<OPTION VALUE="BR" >Brazil
			<OPTION VALUE="IO" >British Indian Ocean Territory
			<OPTION VALUE="BN" >Brunei Darussalam
			
			<OPTION VALUE="BG" >Bulgaria
			<OPTION VALUE="BF" >Burkina Faso
			<OPTION VALUE="BI" >Burundi
			<OPTION VALUE="KH" >Cambodia
			<OPTION VALUE="CM" >Cameroon
			<OPTION VALUE="CA" >Canada
			<OPTION VALUE="CV" >Cape Verde
			<OPTION VALUE="KY" >Cayman Islands
			<OPTION VALUE="CF" >Central African Republic
			<OPTION VALUE="TD" >Chad
			<OPTION VALUE="CL" >Chile
			<OPTION VALUE="CN" >China
			<OPTION VALUE="CX" >Christmas Island
			<OPTION VALUE="CC" >Cocos (Keeling) Islands
			<OPTION VALUE="CO" >Colombia
			<OPTION VALUE="KM" >Comoros
			<OPTION VALUE="CG" >Congo
			
			<OPTION VALUE="CD" >Congo, The Dem. Rep. Of The
			<OPTION VALUE="CK" >Cook Islands
			<OPTION VALUE="CR" >Costa Rica
			<OPTION VALUE="CI" >Cote D'Ivoire
			<OPTION VALUE="HR" >Croatia (Local Name: Hrvatska)
			<OPTION VALUE="CU" >Cuba
			<OPTION VALUE="CY" >Cyprus
			<OPTION VALUE="DK" >Denmark
			<OPTION VALUE="DJ" >Djibouti
			<OPTION VALUE="DM" >Dominica
			<OPTION VALUE="DO" >Dominican Republic
			<OPTION VALUE="TP" >East Timor
			<OPTION VALUE="EC" >Ecuador
			<OPTION VALUE="EG" >Egypt
			<OPTION VALUE="SV" >El Salvador
			<OPTION VALUE="GQ" >Equatorial Guinea
			<OPTION VALUE="ER" >Eritrea
			
			<OPTION VALUE="EE" >Estonia
			<OPTION VALUE="ET" >Ethiopia
			<OPTION VALUE="FK" >Falkland Islands (Malvinas)
			<OPTION VALUE="FO" >Faroe Islands
			<OPTION VALUE="FJ" >Fiji
			<OPTION VALUE="FI" >Finland
			<OPTION VALUE="GA" >Gabon
			<OPTION VALUE="GM" >Gambia
			<OPTION VALUE="GE" >Georgia
			<OPTION VALUE="DE" >Germany
			<OPTION VALUE="GH" >Ghana
			<OPTION VALUE="GI" >Gibraltar
			<OPTION VALUE="GR" >Greece
			<OPTION VALUE="GL" >Greenland
			<OPTION VALUE="GD" >Grenada
			<OPTION VALUE="GP" >Guadeloupe
			<OPTION VALUE="GU" >Guam
			
			<OPTION VALUE="GT" >Guatemala
			<OPTION VALUE="GN" >Guinea
			<OPTION VALUE="GW" >Guinea-Bissau
			<OPTION VALUE="GY" >Guyana
			<OPTION VALUE="HT" >Haiti
			<OPTION VALUE="HM" >Heard And Mc Donald Islands
			<OPTION VALUE="VA" >Holy See (Vatican City State)
			<OPTION VALUE="HN" >Honduras
			<OPTION VALUE="HK" >Hong Kong
			<OPTION VALUE="HU" >Hungary
			<OPTION VALUE="IS" >Iceland
			<OPTION VALUE="IN" >India
			<OPTION VALUE="ID" >Indonesia
			<OPTION VALUE="IR" >Iran (Islamic Republic Of)
			<OPTION VALUE="IQ" >Iraq
			<OPTION VALUE="IE" >Ireland
			<OPTION VALUE="IL" >Israel
			
			<OPTION VALUE="IT" >Italy
			<OPTION VALUE="JM" >Jamaica
			<OPTION VALUE="JP" >Japan
			<OPTION VALUE="JO" >Jordan
			<OPTION VALUE="KZ" >Kazakhstan
			<OPTION VALUE="KE" >Kenya
			<OPTION VALUE="KI" >Kiribati
			<OPTION VALUE="KP" >Korea, Dem. People's Rep. Of
			<OPTION VALUE="KR" >Korea, Republic Of
			<OPTION VALUE="KW" >Kuwait
			<OPTION VALUE="KG" >Kyrgyzstan
			<OPTION VALUE="LA" >Lao People'S Democratic Rep.
			<OPTION VALUE="LV" >Latvia
			<OPTION VALUE="LB" >Lebanon
			<OPTION VALUE="LS" >Lesotho
			<OPTION VALUE="LR" >Liberia
			<OPTION VALUE="LY" >Libyan Arab Jamahiriya
			
			<OPTION VALUE="LI" >Liechtenstein
			<OPTION VALUE="LT" >Lithuania
			<OPTION VALUE="LU" >Luxembourg
			<OPTION VALUE="MO" >Macau
			<OPTION VALUE="MK" >Macedonia
			<OPTION VALUE="MG" >Madagascar
			<OPTION VALUE="MW" >Malawi
			<OPTION VALUE="MY" >Malaysia
			<OPTION VALUE="MV" >Maldives
			<OPTION VALUE="ML" >Mali
			<OPTION VALUE="MT" >Malta
			<OPTION VALUE="MH" >Marshall Islands
			<OPTION VALUE="MQ" >Martinique
			<OPTION VALUE="MR" >Mauritania
			<OPTION VALUE="MU" >Mauritius
			<OPTION VALUE="YT" >Mayotte
			<OPTION VALUE="MX" >Mexico
			
			<OPTION VALUE="FM" >Micronesia, Federated States Of
			<OPTION VALUE="MD" >Moldova, Republic Of
			<OPTION VALUE="MC" >Monaco
			<OPTION VALUE="MN" >Mongolia
			<OPTION VALUE="MS" >Montserrat
			<OPTION VALUE="MA" >Morocco
			<OPTION VALUE="MZ" >Mozambique
			<OPTION VALUE="MM" >Myanmar
			<OPTION VALUE="NA" >Namibia
			<OPTION VALUE="NR" >Nauru
			<OPTION VALUE="NP" >Nepal
			<OPTION VALUE="NL" >Netherlands
			<OPTION VALUE="AN" >Netherlands Antilles
			<OPTION VALUE="NC" >New Caledonia
			<OPTION VALUE="NZ" >New Zealand
			<OPTION VALUE="NI" >Nicaragua
			<OPTION VALUE="NE" >Niger
			
			<OPTION VALUE="NG" >Nigeria
			<OPTION VALUE="NU" >Niue
			<OPTION VALUE="NF" >Norfolk Island
			<OPTION VALUE="MP" >Northern Mariana Islands
			<OPTION VALUE="NO" >Norway
			<OPTION VALUE="OM" >Oman
			<OPTION VALUE="PK" >Pakistan
			<OPTION VALUE="PW" >Palau
			<OPTION VALUE="PA" >Panama
			<OPTION VALUE="PG" >Papua New Guinea
			<OPTION VALUE="PY" >Paraguay
			<OPTION VALUE="PE" >Peru
			<OPTION VALUE="PH" >Philippines
			<OPTION VALUE="PN" >Pitcairn
			<OPTION VALUE="PL" >Poland
			<OPTION VALUE="PT" >Portugal
			<OPTION VALUE="PR" >Puerto Rico
			
			<OPTION VALUE="QA" >Qatar
			<OPTION VALUE="RE" >Reunion
			<OPTION VALUE="RO" >Romania
			<OPTION VALUE="RU" >Russian Federation
			<OPTION VALUE="RW" >Rwanda
			<OPTION VALUE="KN" >Saint Kitts And Nevis
			<OPTION VALUE="LC" >Saint Lucia
			<OPTION VALUE="VC" >Saint Vincent & The Grenadines
			<OPTION VALUE="WS" >Samoa
			<OPTION VALUE="SM" >San Marino
			<OPTION VALUE="ST" >Sao Tome And Principe
			<OPTION VALUE="SA" >Saudi Arabia
			<OPTION VALUE="SN" >Senegal
			<OPTION VALUE="SC" >Seychelles
			<OPTION VALUE="SL" >Sierra Leone
			<OPTION VALUE="SG" >Singapore
			
			<OPTION VALUE="SI" >Slovenia
			<OPTION VALUE="SB" >Solomon Islands
			<OPTION VALUE="SO" >Somalia
			<OPTION VALUE="ZA" >South Africa
			<OPTION VALUE="GS" >South Georgia And Sandwich Isl.
			<OPTION VALUE="ES" >Spain
			<OPTION VALUE="LK" >Sri Lanka
			<OPTION VALUE="SH" >St. Helena
			<OPTION VALUE="PM" >St. Pierre And Miquelon
			<OPTION VALUE="SD" >Sudan
			<OPTION VALUE="SR" >Suriname
			<OPTION VALUE="SJ" >Svalbard And Jan Mayen Islands
			<OPTION VALUE="SZ" >Swaziland
			<OPTION VALUE="SE" >Sweden
			<OPTION VALUE="CH" >Switzerland
			<OPTION VALUE="SY" >Syrian Arab Republic
			<OPTION VALUE="TW" >Taiwan, Province Of China
			
			<OPTION VALUE="TJ" >Tajikistan
			<OPTION VALUE="TZ" >Tanzania, United Republic Of
			<OPTION VALUE="TH" >Thailand
			<OPTION VALUE="TG" >Togo
			<OPTION VALUE="TK" >Tokelau
			<OPTION VALUE="TO" >Tonga
			<OPTION VALUE="TT" >Trinidad And Tobago
			<OPTION VALUE="TN" >Tunisia
			<OPTION VALUE="TR" >Turkey
			<OPTION VALUE="TM" >Turkmenistan
			<OPTION VALUE="TC" >Turks And Caicos Islands
			<OPTION VALUE="TV" >Tuvalu
			<OPTION VALUE="UG" >Uganda
			<OPTION VALUE="UA" >Ukraine
			<OPTION VALUE="AE" >United Arab Emirates
			<OPTION VALUE="GB" >United Kingdom
			<OPTION VALUE="US" >United States
			
			<OPTION VALUE="UM" >U.S. Minor Outlying Islands
			<OPTION VALUE="UY" >Uruguay
			<OPTION VALUE="UZ" >Uzbekistan
			<OPTION VALUE="VU" >Vanuatu
			<OPTION VALUE="VE" >Venezuela
			<OPTION VALUE="VN" >Viet Nam
			<OPTION VALUE="VG" >Virgin Islands (British)
			<OPTION VALUE="VI" >Virgin Islands (U.S.)
			<OPTION VALUE="WF" >Wallis And Futuna Islands
			<OPTION VALUE="EH" >Western Sahara
			<OPTION VALUE="YE" >Yemen
			<OPTION VALUE="YU" >Yugoslavia
			<OPTION VALUE="ZM" >Zambia
			<OPTION VALUE="ZW" >Zimbabwe
			
			</SELECT>
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