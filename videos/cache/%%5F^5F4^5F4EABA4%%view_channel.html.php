<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'FlashPlayer', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 22, false),array('function', 'AD', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 26, false),array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 46, false),array('function', 'get_videos', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 48, false),array('function', 'ANCHOR', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 76, false),array('function', 'show_flag_form', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 105, false),array('modifier', 'sprintf', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 46, false),array('modifier', 'capitalize', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 80, false),array('modifier', 'date_format', '/Library/WebServer/Documents/styles/cbv2new/layout/view_channel.html', 109, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/global_header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<body>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/view_channel/channel_global.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<div id="container" class="clearfix" style="background-color:none">

    <!-- Including Commong header -->
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/header.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <div style="height:10px; background-attachment:"></div>
    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/message.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <div id="content_container" >
    <div id="content" style="padding-bottom:15px">
    	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/view_channel/channel_top.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
        
        <?php $this->assign('user_vdo', $this->_tpl_vars['userquery']->get_user_profile_video($this->_tpl_vars['p'])); ?>
            <?php if ($this->_tpl_vars['user_vdo']): ?>
              <!-- User Video Block -->
            <div class="channel_box clearfix">
             <div class="channel_inner_box">
             <div style="width:610px; float:left">

              <?php echo flashPlayer(array('vdetails' => $this->_tpl_vars['user_vdo'],'height' => $this->_tpl_vars['Cbucket']->configs['channel_player_height'],'width' => $this->_tpl_vars['Cbucket']->configs['channel_player_width']), $this);?>

			 
              </div>
              <div class="clear"></div>
              <div align="center" style="width:300px; float:left"><?php echo getAd(array('place' => '336x280'), $this);?>
</div>

             </div>
             
            </div>
             <!-- User Video Blcok Ends-->
        <?php endif; ?>
        
        <div class="channel_box">
        	<div class="channel_inner_box" id="result_cont" style="display:none"></div>
        </div>
        
        <!-- Starting Bottom Channel Box -->
        <div class="channel_box clearfix" style="margin-bottom:0px">
   	  
       <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/view_channel/channel_left.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
       
       <!-- Right Colum -->
      <div class="right_column">
       	<div class="channel_inner_box clearfix">
        	<span class="channel_heading"><?php echo smarty_lang(array('code' => 'users_videos','assign' => 'users_videos'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['users_videos'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
</span>
 				<?php $this->assign('videos_item_channel_page', config(videos_item_channel_page)); ?>
            	<?php echo get_videos(array('assign' => 'usr_vids','limit' => $this->_tpl_vars['videos_item_channel_page'],'order' => 'date_added DESC','user' => $this->_tpl_vars['u']['userid']), $this);?>


                <?php unset($this->_sections['v_list']);
$this->_sections['v_list']['name'] = 'v_list';
$this->_sections['v_list']['loop'] = is_array($_loop=$this->_tpl_vars['usr_vids']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['v_list']['show'] = true;
$this->_sections['v_list']['max'] = $this->_sections['v_list']['loop'];
$this->_sections['v_list']['step'] = 1;
$this->_sections['v_list']['start'] = $this->_sections['v_list']['step'] > 0 ? 0 : $this->_sections['v_list']['loop']-1;
if ($this->_sections['v_list']['show']) {
    $this->_sections['v_list']['total'] = $this->_sections['v_list']['loop'];
    if ($this->_sections['v_list']['total'] == 0)
        $this->_sections['v_list']['show'] = false;
} else
    $this->_sections['v_list']['total'] = 0;
if ($this->_sections['v_list']['show']):

            for ($this->_sections['v_list']['index'] = $this->_sections['v_list']['start'], $this->_sections['v_list']['iteration'] = 1;
                 $this->_sections['v_list']['iteration'] <= $this->_sections['v_list']['total'];
                 $this->_sections['v_list']['index'] += $this->_sections['v_list']['step'], $this->_sections['v_list']['iteration']++):
$this->_sections['v_list']['rownum'] = $this->_sections['v_list']['iteration'];
$this->_sections['v_list']['index_prev'] = $this->_sections['v_list']['index'] - $this->_sections['v_list']['step'];
$this->_sections['v_list']['index_next'] = $this->_sections['v_list']['index'] + $this->_sections['v_list']['step'];
$this->_sections['v_list']['first']      = ($this->_sections['v_list']['iteration'] == 1);
$this->_sections['v_list']['last']       = ($this->_sections['v_list']['iteration'] == $this->_sections['v_list']['total']);
?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/video.html", 'smarty_include_vars' => array('video' => $this->_tpl_vars['usr_vids'][$this->_sections['v_list']['index']],'video_view' => 'grid_view')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endfor; else: ?>
                	<div align="center"><em><?php echo smarty_lang(array('code' => 'user_have_no_vide'), $this);?>
</em></div>
                <?php endif; ?>
              <div class="clear"></div> 
            <hr width="100%" size="1" noshade>
            <div align="right"><a href="<?php echo $this->_tpl_vars['userquery']->get_user_videos_link($this->_tpl_vars['u']); ?>
"><?php echo smarty_lang(array('code' => 'more'), $this);?>
</a></div>
        </div>
        
        
        <div class="channel_inner_box clearfix">
        	<span class="channel_heading"><?php echo smarty_lang(array('code' => 'users_subscribers','assign' => 'users_videos'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['users_videos'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
</span>
            	<?php $this->assign('users_items_subscibers', config(users_items_subscibers)); ?>
            	<?php $this->assign('usr_subs', $this->_tpl_vars['userquery']->get_user_subscribers_detail($this->_tpl_vars['u']['userid'],$this->_tpl_vars['users_items_subscibers'])); ?>
                <?php unset($this->_sections['u_list']);
$this->_sections['u_list']['name'] = 'u_list';
$this->_sections['u_list']['loop'] = is_array($_loop=$this->_tpl_vars['usr_subs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['u_list']['show'] = true;
$this->_sections['u_list']['max'] = $this->_sections['u_list']['loop'];
$this->_sections['u_list']['step'] = 1;
$this->_sections['u_list']['start'] = $this->_sections['u_list']['step'] > 0 ? 0 : $this->_sections['u_list']['loop']-1;
if ($this->_sections['u_list']['show']) {
    $this->_sections['u_list']['total'] = $this->_sections['u_list']['loop'];
    if ($this->_sections['u_list']['total'] == 0)
        $this->_sections['u_list']['show'] = false;
} else
    $this->_sections['u_list']['total'] = 0;
if ($this->_sections['u_list']['show']):

            for ($this->_sections['u_list']['index'] = $this->_sections['u_list']['start'], $this->_sections['u_list']['iteration'] = 1;
                 $this->_sections['u_list']['iteration'] <= $this->_sections['u_list']['total'];
                 $this->_sections['u_list']['index'] += $this->_sections['u_list']['step'], $this->_sections['u_list']['iteration']++):
$this->_sections['u_list']['rownum'] = $this->_sections['u_list']['iteration'];
$this->_sections['u_list']['index_prev'] = $this->_sections['u_list']['index'] - $this->_sections['u_list']['step'];
$this->_sections['u_list']['index_next'] = $this->_sections['u_list']['index'] + $this->_sections['u_list']['step'];
$this->_sections['u_list']['first']      = ($this->_sections['u_list']['iteration'] == 1);
$this->_sections['u_list']['last']       = ($this->_sections['u_list']['iteration'] == $this->_sections['u_list']['total']);
?>
                    <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/user.html", 'smarty_include_vars' => array('user' => $this->_tpl_vars['usr_subs'][$this->_sections['u_list']['index']],'block_type' => 'small')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
                <?php endfor; else: ?>
                	<div align="center"><em><strong><?php echo smarty_lang(array('code' => 'user_no_subscribers','assign' => 'users_videos'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['users_videos'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
</strong></em></div>
                <?php endif; ?>
                <div class="clear"></div>
            <hr width="100%" size="1" noshade>
            <div align="right"><a href="#"><?php echo smarty_lang(array('code' => 'more'), $this);?>
</a></div>
        </div>
	

	<?php echo ANCHOR(array('place' => 'channel_box'), $this);?>

       
       <!-- Comments -->
        <div class="channel_inner_box" style="font-weight:normal" >
        	<span class="channel_heading"><?php echo smarty_lang(array('code' => ((is_array($_tmp='comments')) ? $this->_run_mod_handler('capitalize', true, $_tmp, true) : smarty_modifier_capitalize($_tmp, true))), $this);?>
</span>
            <hr width="100%" size="1" noshade><!-- Displaying Comments -->
    		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/comments/comments.html", 'smarty_include_vars' => array('id' => $this->_tpl_vars['u']['userid'],'type' => 'c')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?><hr width="100%" size="1" noshade>
            
             <?php if ($this->_tpl_vars['myquery']->is_commentable($this->_tpl_vars['p'],'u')): ?>
                <?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => ($this->_tpl_vars['style_dir'])."/blocks/comments/add_comment.html", 'smarty_include_vars' => array('id' => $this->_tpl_vars['u']['userid'],'type' => 'c')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php else: ?>
    			<div class="disable_msg" align="center"><?php echo smarty_lang(array('code' => 'coments_disabled_profile'), $this);?>
</div>
    		<?php endif; ?>
        </div>
        
        
        </div>
        
        <!-- Ending Bottomg Channel Box-->
     </div>   
     
     
     
    </div>
    
    
    </div>
    
    <div align="left"><a href="javascript:void(0)" onClick="$('#flag_item').slideToggle()"><?php echo smarty_lang(array('code' => 'report_this_user'), $this);?>
</a></div>
    <?php echo show_flag_form(array('id' => $this->_tpl_vars['u']['userid'],'type' => 'User'), $this);?>

    <div style="height:10px"></div>
  <div id="footer" style="margin-top:0px;">
    	<div class="footer">
        	&copy; <?php echo $this->_tpl_vars['title']; ?>
 <?php echo ((is_array($_tmp=time())) ? $this->_run_mod_handler('date_format', true, $_tmp, "%Y") : smarty_modifier_date_format($_tmp, "%Y")); ?>

        </div>
    </div>
</div>

</div>
</body>
</html>