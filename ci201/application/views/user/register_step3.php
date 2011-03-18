<?php
$this->load->view('header');
?>
<script type="text/javascript">
<!--
// jackpot increase settings {{{
var jackpotIncreaseTimeout      = 1.44;
var currentJackpot              = 69809.50;
// }}}

// uri parts for jackpot letters {{{
var jackpotLetterURIPart1       = "https://www.slotland.com/images/jackpot_";
var jackpotLetterURIPart2       = ".png";
// }}}

// available letter mappings {{{
var letters = new Array();
letters[","] = "comma";
letters["."] = "dot";
letters["0"] = "0";
letters["1"] = "1";
letters["2"] = "2";
letters["3"] = "3";
letters["4"] = "4";
letters["5"] = "5";
letters["6"] = "6";
letters["7"] = "7";
letters["8"] = "8";
letters["9"] = "9";

// }}}
// -->
</script>
    <div id="jackpotContainer">
        <div id="jackpotLabel">

            <img src="https://www.slotland.com/images/jackpot_6.png" alt="6"><img src="https://www.slotland.com/images/jackpot_9.png" alt="9"><img src="https://www.slotland.com/images/jackpot_comma.png" alt=","><img src="https://www.slotland.com/images/jackpot_8.png" alt="8"><img src="https://www.slotland.com/images/jackpot_0.png" alt="0"><img src="https://www.slotland.com/images/jackpot_9.png" alt="9"><img src="https://www.slotland.com/images/jackpot_dot.png" alt="."><img src="https://www.slotland.com/images/jackpot_5.png" alt="5"><img src="https://www.slotland.com/images/jackpot_0.png" alt="0">
        </div>
    </div>
<script type="text/javascript">
<!--
    var so = new SWFObject("https://www.slotland.com/flash/jackpot.swf", "jackpotContainerFlash", "311px", "62px", "9", "#000000");
            so.addParam("menu", "false");
            so.addParam("wmode", "transparent");
            so.addParam("allowScriptAccess", "always");
            so.addVariable("jackpotIncreaseTimeout", jackpotIncreaseTimeout);
            so.addVariable("currentJackpot", currentJackpot);
            // so.addVariable("url", "http://www.slotland.com/promotions.html?u=1PsqmtW7srIeqLTAZl");
    if (so.installedVer.versionIsValid(so.getAttribute('version'))) {
        so.write("jackpotContainer");
        var e = getHTMLObject("jackpotContainer");
        e.style.backgroundImage = 'none';
    } else {
        setTimeout('increaseJackpot();', 1000 * jackpotIncreaseTimeout);
    }
// -->
</script>
    <div id="livechat"><!-- Begin LiveChat button tag. See also www.livechatinc.com --><div style="text-align:right"><a href="https://chat.livechatinc.net/licence/1032156/open_chat.cgi?lang=en&amp;groups=2&amp;name=dark%20moon%20%28DARKMOON1111%29&amp;autologin=1&amp;params=origin%3dSLOTLAND" target="chat_1032156_chat.livechatinc.net" onclick="window.open('https://chat.livechatinc.net/licence/1032156/open_chat.cgi?lang=en&amp;groups=2&amp;name=dark%20moon%20%28DARKMOON1111%29&amp;autologin=1&amp;params=origin%3dSLOTLAND&amp;'+'dc='+escape(document.cookie+';l='+document.location+';r='+document.referer+';s='+typeof lc_session),'Czat_1032156','width=530,height=520,resizable=yes,scrollbars=no,status=1');return false;"><img src="https://chat.livechatinc.net/licence/1032156/button.cgi?lang=en&amp;groups=2&amp;d=1300427857000" border="0" alt="Live Support"></a></div><!-- End LiveChatbutton tag. See also www.livechatinc.com --></div>


    <div id="mainImage"></div>

<div id="bottomContainer">
    <h1 class="noBottom">

        Join Slotland.com...
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
                <a href="https://www.slotland.com/.user.new?step=2&u=1PsqmtW7srIeqLTAZl"><img src="/images/button_back.png" border="0" alt="Back"></a>

                <input type="image" src="/images/button_register.png" alt="REGISTER">
            </div>
        </div>
    </form>
    </div>
<?php $this->load->view('footer')?>