<?php $this->load->view('header')?>
	<style type="text/css">
		#gameSelectorIcon_0 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameWitchsBrew.png'); }
		#gameSelectorIcon_1 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameLuckyDucts.png'); }
		#gameSelectorIcon_2 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameTreasureBox.png'); }
		#gameSelectorIcon_3 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameGolden8.png'); }
		#gameSelectorIcon_4 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameJacksOrBetter.png'); }
		#gameSelectorIcon_5 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameWildHeart.png'); }
		#gameSelectorIcon_6 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameReelRiot.png'); }
		#gameSelectorIcon_7 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameMegaSpin.png'); }
		#gameSelectorIcon_8 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameBooster.png'); }
		#gameSelectorIcon_9 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameHeavyMetal.png'); }
		#gameSelectorIcon_10 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameLuckyStars.png'); }
		#gameSelectorIcon_11 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameMagic.png'); }
		#gameSelectorIcon_12 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameStriking7s.png'); }
		#gameSelectorIcon_13 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameSpaceJack.png'); }
		#gameSelectorIcon_14 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameFourCast.png'); }
		#gameSelectorIcon_15 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameSilverKiss.png'); }
		#gameSelectorIcon_16 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameSlotris.png'); }
		#gameSelectorIcon_17 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameHalloweenMagic.png'); }
		#gameSelectorIcon_18 a { background-image: url('<?php echo $this->config->item('asset_url')?>images/games/gameSantaSleigh.png'); }
	</style>

	<script type="text/javascript">
		<!--
		// promotions
		var promoItemsCount = 0;


		// image cache {{{
		var imageCacheJackpot = new Array();
		imageCacheJackpot[","] = new Image();
		imageCacheJackpot[","].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_comma.png';
		imageCacheJackpot["."] = new Image();
		imageCacheJackpot["."].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_dot.png';
		imageCacheJackpot["0"] = new Image();
		imageCacheJackpot["0"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_0.png';
		imageCacheJackpot["1"] = new Image();
		imageCacheJackpot["1"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_1.png';
		imageCacheJackpot["2"] = new Image();
		imageCacheJackpot["2"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_2.png';
		imageCacheJackpot["3"] = new Image();
		imageCacheJackpot["3"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_3.png';
		imageCacheJackpot["4"] = new Image();
		imageCacheJackpot["4"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_4.png';
		imageCacheJackpot["5"] = new Image();
		imageCacheJackpot["5"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_5.png';
		imageCacheJackpot["6"] = new Image();
		imageCacheJackpot["6"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_6.png';
		imageCacheJackpot["7"] = new Image();
		imageCacheJackpot["7"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_7.png';
		imageCacheJackpot["8"] = new Image();
		imageCacheJackpot["8"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_8.png';
		imageCacheJackpot["9"] = new Image();
		imageCacheJackpot["9"].src = '<?php echo $this->config->item('asset_url')?>images/jackpot_9.png';

		// }}}

		// jackpot increase settings {{{
		var jackpotIncreaseTimeout      = 1.44;
		var currentJackpot              = 58886.50;
		// }}}

		// uri parts for jackpot letters {{{
		var jackpotLetterURIPart1       = "images/jackpot_";
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

		// base configuration variables {{{
		var gameSelectorIconWidth       = 145;
		var gameSelectorAnimationLength = 500;
		var gameSelectorGamesPerPage    = 6;

		var promotionIconWidth          = 300;
		var promotionAnimationLength    = 750;
		var promotionsPerPage           = 1;
		var promotionsRotationInterval  = 10000;
		var promotionChangeInterval     = undefined;
		// }}}

		// -->
	</script>

    <div id="jackpotContainer">
        <div id="jackpotLabel">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_5.png" alt="5">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_8.png" alt="8">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_comma.png" alt=",">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_8.png" alt="8">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_8.png" alt="8">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_6.png" alt="6">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_dot.png" alt=".">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_5.png" alt="5">
            <img src="<?php echo $this->config->item('asset_url')?>images/jackpot_0.png" alt="0">
        </div>
    </div>
	<script type="text/javascript">
		<!--
		var so = new SWFObject("<?php echo $this->config->item('asset_url')?>flash/jackpot.swf", "jackpotContainerFlash", "356px", "126px", "9", "#000000");
				so.addParam("menu", "false");
				so.addParam("wmode", "transparent");
				so.addParam("allowScriptAccess", "always");
				so.addVariable("jackpotIncreaseTimeout", jackpotIncreaseTimeout);
				so.addVariable("currentJackpot", currentJackpot);
		if (so.installedVer.versionIsValid(so.getAttribute('version'))) {
			so.write("jackpotContainer");
			var e = getHTMLObject("jackpotContainer");
			e.style.backgroundImage = 'none';
		} else {
			setTimeout('increaseJackpot();', 1000 * jackpotIncreaseTimeout);
		}
		// -->
	</script>
    <div id="livechat">
		<!-- Begin LiveChat button tag. See also www.livechatinc.com -->
		<div style="text-align:right">
			<a href=""><img src="http://chat.livechatinc.net/licence/1032156/button.cgi?lang=en&amp;groups=2&amp;d=1299407761000" border="0" alt="Live Support"></a>
		</div>
		<!-- End LiveChatbutton tag. See also www.livechatinc.com -->
	</div>


    <div id="promo1" style="display: none;">
        <div id="promo1decoration"></div>
        <div id="promo1Mask">
        
        </div>
    </div>
	<script type='text/javascript'>
		if (promoItemsCount > 1)
			showPromotions("promo1Icon", promoItemsCount);
	</script>

    <div id="promo2">
        <a href="/user/register"></a>
    </div>
	<script type="text/javascript">
		<!--
		var so = new SWFObject("<?php echo $this->config->item('asset_url')?>flash/register.swf", "promo2Flash", "391px", "209px", "9", "#000000");
				so.addParam("menu", "false");
				so.addParam("wmode", "transparent");
				so.addParam("allowScriptAccess", "always");
				so.addVariable("registrationUrl", "<?php echo $this->config->item('base_url')?>register");
				so.addVariable("handle", "UsRyI6lltoIrXyK25q");
		if (so.installedVer.versionIsValid(so.getAttribute('version'))) {
			so.write("promo2");
			var e = getHTMLObject("promo2");
			e.style.backgroundImage = 'none';
		}
		// -->
	</script>
    <div id="mainImageHomepage"></div>

    <div id="gameSelector">
        <a onclick="return gameSelectorScrollLeft('gamesList');" href="/games" id="gameSelectorLeft"></a>
        <div id="gameSelectorMiddle">
            <div id="gamesList">
				<div id="gameSelectorIcon_0" style="left: 0px;" class="gameSelectorIcon">
					<a href="/witchsbrew/"><span class="new"></span></a>
				</div>
				<div id="gameSelectorIcon_1" style="left: 145px;" class="gameSelectorIcon">
					<a href="/luckyducts/"></a>
				</div>
				<div id="gameSelectorIcon_2" style="left: 290px;" class="gameSelectorIcon">
					<a href="/treasurebox/"></a>
				</div>
				<div id="gameSelectorIcon_3" style="left: 435px;" class="gameSelectorIcon">
					<a href="/golden8/"></a>
				</div>
				<div id="gameSelectorIcon_4" style="left: 580px;" class="gameSelectorIcon">
					<a href="/jacksorbetter/"></a>
				</div>
				<div id="gameSelectorIcon_5" style="left: 725px;" class="gameSelectorIcon">
					<a href="/wildheart/"></a>
				</div>
				<div id="gameSelectorIcon_6" style="left: 870px;" class="gameSelectorIcon">
					<a href="/reelriot/"></a>
				</div>
				<div id="gameSelectorIcon_7" style="left: 1015px;" class="gameSelectorIcon">
					<a href="/megaspin/"></a>
				</div>
				<div id="gameSelectorIcon_8" style="left: 1160px;" class="gameSelectorIcon">
					<a href="/booster/"></a>
				</div>
				<div id="gameSelectorIcon_9" style="left: 1305px;" class="gameSelectorIcon">
					<a href="/heavymetal/"></a>
				</div>
				<div id="gameSelectorIcon_10" style="left: 1450px;" class="gameSelectorIcon">
					<a href="/luckystars/"></a>
				</div>
				<div id="gameSelectorIcon_11" style="left: 1595px;" class="gameSelectorIcon">
					<a href="/magic/"></a>
				</div>
				<div id="gameSelectorIcon_12" style="left: 1740px;" class="gameSelectorIcon">
					<a href="/striking7s/"></a>
				</div>
				<div id="gameSelectorIcon_13" style="left: 1885px;" class="gameSelectorIcon">
					<a href="/spacejack/"></a>
				</div>
				<div id="gameSelectorIcon_14" style="left: 2030px;" class="gameSelectorIcon">
					<a href="/fourcast/"></a>
				</div>
				<div id="gameSelectorIcon_15" style="left: 2175px;" class="gameSelectorIcon">
					<a href="/silverkiss/warning.html"></a>
				</div>
				<div id="gameSelectorIcon_16" style="left: 2320px;" class="gameSelectorIcon">
					<a href="/slotris/"></a>
				</div>
				<div id="gameSelectorIcon_17" style="left: 2465px;" class="gameSelectorIcon">
					<a href="/halloweenmagic/"></a>
				</div>
				<div id="gameSelectorIcon_18" style="left: 2610px;" class="gameSelectorIcon">
					<a href="/santasleigh/"></a>
				</div>
            </div>
        </div>
        <a onclick="return gameSelectorScrollRight('gamesList');" href="/games" id="gameSelectorRight"></a>
    </div>

    <script type='text/javascript'>
        showGameSelector("gameSelectorIcon", 9);
    </script>

    <a href="/games" id="allGames"></a>

    <div style="margin-top: 184px;"></div>

    <div id="bottomContainer">
        <div class="leftColumn">
            <div class="bottomContainerBlockContent logos">
                <p>
                    <img src="images/logow_visa.png" alt="VISA"><img src="images/logow_mc.png" alt="MasterCard"><img src="images/logow_neteller.png" alt="Neteller"><img src="images/logow_mb.png" alt="MoneyBookers"><img src="images/logow_instadebit.png" alt="InstaDebit"><img src="images/logow_wt.png" alt="Wire Transfer">
                </p>
            </div>
        </div>
        <div class="rightColumn">
            <div class="bottomContainerBlockContent logos">
                <p>
                    <img src="images/logow_iphone.png" alt="iPhone"><img src="images/logow_ipad.png" alt="iPad"><img src="images/logow_android.png" alt="Android"><img src="images/logow_wii.png" alt="Wii"><img src="images/logow_wtv.png" alt="WebTV"><img src="images/logow_msntv.png" alt="msnTV">
                </p>
            </div>
        </div>
        <div class="clear"></div>
    </div>
<?php $this->load->view('footer')?>
