<?php /* Smarty version 2.6.18, created on 2010-10-13 21:42:57
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/global_header.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'cbtitle', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/global_header.html', 5, false),array('function', 'include_js', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/global_header.html', 42, false),array('function', 'include_header', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/global_header.html', 51, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo cbtitle(array('sub_sep' => '&#8250;'), $this);?>
</title>
<link href="<?php echo $this->_tpl_vars['theme']; ?>
/main.css" rel="stylesheet" type="text/css" />
<link href="<?php echo $this->_tpl_vars['theme']; ?>
/slidemenu.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" charset="utf-8">
var baseurl = "<?php echo $this->_tpl_vars['baseurl']; ?>
";
var imageurl = "<?php echo $this->_tpl_vars['imageurl']; ?>
";
</script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/swfobject.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/swfobject.obj.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/flashobject.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/functions.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/admin_functions.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/checkall.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/jquery.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/ui/jquery-ui-1.7.2.custom.min.js"></script>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/hover_intent.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/cb.tabs.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/cookie.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/timer.js"></script>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/css/jquery.tooltip.css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/css/screen.css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/css/tipsy.css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['js']; ?>
/ui/css/ui-lightness/jquery-ui-1.7.2.custom.css" />

<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/jquery.bgiframe.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/jquery.dimensions.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/jquery.tooltip.pack.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/chili-1.7.pack.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/compressed/jeditable.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/jquery_plugs/jquery.tipsy.js" type="text/javascript"></script>

<?php if ($this->_tpl_vars['Cbucket']->AdminJSArray): ?>
<!-- Including JS Files-->
<?php $_from = $this->_tpl_vars['Cbucket']->AdminJSArray; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file'] => $this->_tpl_vars['type']):
?>
    <?php if ($this->_tpl_vars['curActive'] == $this->_tpl_vars['type'] || $this->_tpl_vars['type'] == 'global'): ?>
        <?php echo include_js(array('type' => $this->_tpl_vars['type'],'file' => $this->_tpl_vars['file']), $this);?>

    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<!-- Including JS Files-->
<?php endif; ?>

<?php if ($this->_tpl_vars['Cbucket']->admin_header_files): ?>
    <!-- Including Headers -->
    <?php $_from = $this->_tpl_vars['Cbucket']->admin_header_files; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file'] => $this->_tpl_vars['type']):
?>
        <?php echo include_header(array('type' => $this->_tpl_vars['type'],'file' => $this->_tpl_vars['file']), $this);?>

    <?php endforeach; endif; unset($_from); ?>
    <!-- Ending Headers -->
<?php endif; ?>



<!-- Including Nice Edit -->
<script type="text/javascript" src="<?php echo $this->_tpl_vars['js']; ?>
/nicedit/nicEdit.js"></script>
<?php echo '

	<script type="text/javascript" charset="utf-8">
	
	
		function makeTall(divid){  $(\'#\'+divid).animate({"height":209},200);}
		function makeShort(divid){ $(\'#\'+divid).animate({"height":0},200);}
		var on = false;
		function switch_func(divid)
		{
			if(on)
			{
				makeShort(divid);
				on = false;
			}else{
				makeTall(divid);
				on = true;
			}
				
		}
		
		$(document).ready(function() {
			 $(\'.edit_lang\').editable(baseurl+\'/actions/update_phrase.php\', { 
			// cancel    : \'Cancel\',
			// submit    : \'OK\',
			 indicator : \'<img src="\'+baseurl+\'/images/icons/progIndicator.gif">\',
			 tooltip   : \'Click to edit...\',
     		});
		});
		
		$(document).ready(function() {
			 $(\'.edit_comment\').editable(baseurl+\'/actions/edit_comment.php\', { 
			// cancel    : \'Cancel\',
			// submit    : \'OK\',
			 indicator : \'<img src="\'+baseurl+\'/images/icons/progIndicator.gif">\',
			 tooltip   : \'Click to edit...\',
     		});
		
		
		$(\'.widgets-wrap\').sortable({
			connectWith: \'.widgets-wrap\',
			handle: \'h2\',
			cursor: \'move\',
			placeholder: \'placeholder\',
			forcePlaceholderSize: true,
			opacity: 0.4,
			stop: function(event, ui){
				$(ui.item).find(\'h2\').click();
			}
		})

		
		$(\'.dragbox\').each(function(){
			$(this).hover(function(){
				$(this).find(\'h2\').addClass(\'collapse\');
			}, function(){
				$(this).find(\'h2\').removeClass(\'collapse\');
			})
			.find(\'h2\').hover(function(){
				$(this).find(\'.configure\').css(\'visibility\', \'visible\');
			}, function(){
				$(this).find(\'.configure\').css(\'visibility\', \'hidden\');
			})
			.click(function(){
				$(this).siblings(\'.dragbox-content\').toggle();
			})
			.end()
			.find(\'.configure\').css(\'visibility\', \'hidden\');
		});
		
		$(\'.tipsy_tip\').tipsy({gravity: \'w\'});
		
	});

	
	
	</script>
'; ?>

</head>