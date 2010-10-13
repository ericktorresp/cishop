<?php /* Smarty version 2.6.18, created on 2010-10-13 23:02:31
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/global_header.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'date_format', '/Library/WebServer/Documents/styles/cbv2new/layout/global_header.html', 6, false),array('function', 'rss_feeds', '/Library/WebServer/Documents/styles/cbv2new/layout/global_header.html', 12, false),array('function', 'cbtitle', '/Library/WebServer/Documents/styles/cbv2new/layout/global_header.html', 19, false),array('function', 'include_js', '/Library/WebServer/Documents/styles/cbv2new/layout/global_header.html', 50, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- ClipBucket v2 -->
<meta name="copyright" content="ClipBucket - PHPBucket ClipBucket 2007 - <?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>
" />
<meta name="author" content="Arslan Hassan - http://clip-bucket.com/arslan-hassan" />
<link rel="shortcut icon" href="<?php echo $this->_tpl_vars['baseurl']; ?>
/favicon.ico">
<link rel="icon" type="image/ico" href="<?php echo $this->_tpl_vars['baseurl']; ?>
/favicon.ico" />

<!-- RSS FEEDS -->
<?php echo rss_feeds(array('link_tag' => true), $this);?>


<meta name="keywords" content="<?php echo $this->_tpl_vars['Cbucket']->configs['keywords']; ?>
" />
<meta name="description" content="<?php echo $this->_tpl_vars['Cbucket']->configs['description']; ?>
" />
<meta name="distribution" content="global" />


<title><?php echo cbtitle(array(), $this);?>
</title>

<link href="<?php echo $this->_tpl_vars['theme']; ?>
/main.css" rel="stylesheet" type="text/css" />

<!-- Setting Template Variables -->
<?php 
	if(!$_COOKIE['current_style'])
    	$_COOKIE['current_style'] = 'grid_view';
 ?>
<!-- Setting Template Variables -->


<script type="text/javascript">
var baseurl = '<?php echo $this->_tpl_vars['baseurl']; ?>
';
var imageurl = '<?php echo $this->_tpl_vars['imageurl']; ?>
';
<?php if ($this->_tpl_vars['upload_form_name'] != ''): ?>
	var upload_form_name = '<?php echo $this->_tpl_vars['upload_form_name']; ?>
';
	function submit_upload_form()
	<?php echo '
	{
	'; ?>

		document.<?php echo $this->_tpl_vars['upload_form_name']; ?>
.submit();
	<?php echo '
	}
	'; ?>

<?php endif; ?>
</script>

<!-- Including JS Files-->
<?php $_from = $this->_tpl_vars['Cbucket']->JSArray; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['type']):
?>
 <?php $_from = $this->_tpl_vars['type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file'] => $this->_tpl_vars['scope']):
?>
  <?php echo include_js(array('type' => $this->_tpl_vars['scope'],'file' => $this->_tpl_vars['file']), $this);?>

 <?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?>
<!-- Including JS Files-->


<!-- Including Headers -->
<?php $_from = $this->_tpl_vars['Cbucket']->header_files; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file'] => $this->_tpl_vars['type']):
?>
    <?php if ($this->_tpl_vars['curActive'] == $this->_tpl_vars['type'] || $this->_tpl_vars['type'] == 'global'): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['file']), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<!-- Ending Headers -->



<?php if (@THIS_PAGE == 'upload' && $this->_tpl_vars['step'] == '2'): ?>

<script src="<?php echo $this->_tpl_vars['js']; ?>
/swfupload/swfupload.js" type="text/javascript"></script>
<script src="<?php echo $this->_tpl_vars['js']; ?>
/swfupload/plugins/all_in_one.js" type="text/javascript"></script>

<script type="text/javascript">
		var swfu;
		var file_name = '<?php echo $this->_tpl_vars['file_name']; ?>
';
<?php echo '
		
		window.onload = function() {
			var settings = {
				'; ?>

				
				flash_url : "<?php echo $this->_tpl_vars['js']; ?>
/swfupload/swfupload.swf",
				upload_url: "<?php echo $this->_tpl_vars['baseurl']; ?>
/actions/file_uploader.php",
				button_image_url: "<?php echo $this->_tpl_vars['imageurl']; ?>
/cb_button.png",
				post_params: 
				<?php echo '
				{
				'; ?>

					"file_name" : file_name,
				<?php echo '
				}
				'; ?>
,
				
				
				file_size_limit : "<?php echo $this->_tpl_vars['Cbucket']->configs['max_upload_size']; ?>
 MB",				
				file_types : "<?php echo $this->_tpl_vars['Cbucket']->list_extensions(); ?>
",
				file_types_description : "Video Files",
				file_upload_limit : 1,
				file_queue_limit : 0,
				
				<?php echo '
				custom_settings : {
					progressTarget : "fsUploadProgress",
					cancelButtonId : "btnCancel"
				},
				debug: false,

				// Button settings
				
				button_placeholder_id: "spanButtonPlaceHolder",
				button_width: 77,
				button_height: 22,
				button_text_style: ".cb_button_font { font-weight:bold; font-family:Arial, Helvetica, sans-serif;font-size:12;color:#333}",
				button_text_left_padding: 18,
				button_text_top_padding: 2,

				button_text: \'<span class="cb_button_font">Upload</span>\',
				
				// The event handler functions are defined in handlers.js
				file_queued_handler 			: fileQueued,
				file_queue_error_handler 		: fileQueueError,
				file_dialog_complete_handler 	: fileDialogComplete,
				upload_start_handler 			: uploadStart,
				upload_progress_handler 		: uploadProgress,
				upload_error_handler 			: uploadError,
				upload_success_handler 			: uploadSuccess,
				upload_complete_handler 		: uploadComplete,
				queue_complete_handler 			: queueComplete	// Queue plugin event
				

			}

			swfu = new SWFUpload(settings);
			
			load_quicklist_box();
			ini_cookies();
	     };
'; ?>

</script>

<?php else: ?>
<script type="text/javascript">
<?php echo '
window.onload = function() {
	load_quicklist_box();
	ini_cookies();
}
'; ?>

</script>
<?php endif; ?>

<!-- Including Plugin Headers -->
<?php $_from = $this->_tpl_vars['Cbucket']->header_files; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['file'] => $this->_tpl_vars['pages']):
?>
	<?php if (is_includeable ( $this->_tpl_vars['pages'] )): ?>
    	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['file'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <?php endif; ?>
<?php endforeach; endif; unset($_from); ?>
<!-- Including Plugin Headers -->

<?php if (@THIS_PAGE == 'private_message' && $_GET['mid']): ?>
<script type="text/javascript">
var mid  = <?php echo $_GET['mid']; ?>
;
<?php echo '
		window.onload = function() {
			$(\'#messages_container\').scrollTo( \'#message-\'+mid, 800 );
		}
'; ?>

</script>
<?php endif; ?>

<?php echo '
<script type="text/javascript">
	
	function ToggleView(obj) {
		var obj = $(obj),
			obj_id = obj.attr(\'id\'),
			parent = obj.parent().attr(\'id\'),
			target = $("#"+parent).next().attr(\'id\');
			//alert(\'#\'+parent+\' #\'+target+\' .grid_view\');
			if(obj_id == "grid") {
				$(\'#\'+parent+\' + #\'+target+\' .grid_view\').removeClass(\'list_view\');
				$.cookie("current_style","grid_view")
				$(\'.vid_sp\').hide();				
			} else {
				$(\'#\'+parent+\' + #\'+target+\' .grid_view\').addClass(\'list_view\');
				$.cookie("current_style","list_view")
				$(\'.vid_sp\').show();				
			}
	}
	
$(document).ready(function() {					   
	$(\'.user_login\').hide();					   
	
	$(\'#user_login\').toggle(
		function() {
			$(\'.user_login\').slideDown(\'normal\');
		},
		function() {
			$(\'.user_login\').slideUp(\'normal\');
		}
								
	);
	
//	$(\'#grid\').click(
//		function() {
//			$(\'.grid_view\').removeClass(\'list_view\');
//			$.cookie("current_style","grid_view")
//			$(\'.vid_sp\').hide();
//		}
//	);
//	
//	$(\'#list\').click(
//		function() {
//			$(\'.grid_view\').addClass(\'list_view\');
//			$.cookie("current_style","list_view")
//			$(\'.vid_sp\').show();
//		}
//	);	
	
	$(\'.tabs li\').click(
		function() {
			$(\'.tabs li\').removeClass(\'selected\')
			$(this).addClass(\'selected\');
		}
	);
	$(\'#lang_selector\').click(function(){
  //do redirection
});

	$(\'#lang_selector\').change(function(){
	  var optionSelectedValue = $(\'#lang_selector option:selected\').val();
	  if(optionSelectedValue)
	  window.location = "?set_site_lang="+optionSelectedValue;
	});
	get_video(\'recent_viewed_vids\',\'#index_vid_container\');
	
});


</script>
'; ?>

<?php echo '
<!--[if lte IE 6]>
<style type="text/css">
.clearfix { height: 1%; }
</style>
<![endif]-->
'; ?>

<!--[if IE 7]>
<link href="<?php echo $this->_tpl_vars['theme']; ?>
/ie7.css" rel="stylesheet" type="text/css" />
<![endif]-->

</head>

<!-- Global Header Ends Here -->