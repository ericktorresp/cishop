<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 9, false),array('function', 'input_value', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 69, false),array('modifier', 'capitalize', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 25, false),array('modifier', 'number_format', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 29, false),array('modifier', 'get_age', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 35, false),array('modifier', 'date_format', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 35, false),array('modifier', 'nicetime', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 46, false),array('modifier', 'country', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 56, false),array('modifier', 'nl2br', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 73, false),array('modifier', 'sprintf', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/view_channel/channel_left.html', 97, false),)), $this); ?>
<div style="width:270px; float:left">
            	<!-- Thumb and Links-->
        		<div class="channel_inner_box" align="center">
                	<div class="usr_thumb_container" align="center">
                    	<img src="<?php echo $this->_tpl_vars['userquery']->getUserThumb($this->_tpl_vars['u']); ?>
" alt="<?php echo $this->_tpl_vars['u']['username']; ?>
" class="user_thumb">
                    </div>
                    
                    <?php if (! $this->_tpl_vars['userquery']->is_subscribed($this->_tpl_vars['u']['userid'])): ?>
                    <span class="cb_button" onClick="subscriber('<?php echo $this->_tpl_vars['u']['userid']; ?>
','subscribe_user','result_cont')"><?php echo smarty_lang(array('code' => 'subscribe'), $this);?>
</span><br>
                    <?php else: ?>
                    <span class="cb_button" onClick="subscriber('<?php echo $this->_tpl_vars['u']['userid']; ?>
','unsubscribe_user','result_cont')"><?php echo smarty_lang(array('code' => 'unsubscribe'), $this);?>
</span><br>
                    <?php endif; ?>
                    
					<?php $this->assign('channel_action_links', $this->_tpl_vars['userquery']->get_channel_action_links($this->_tpl_vars['u'])); ?>
                    <ul class="channel_action_links">
                    <?php $_from = $this->_tpl_vars['channel_action_links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['link_title'] => $this->_tpl_vars['link']):
?>
                        <li><a href="<?php echo $this->_tpl_vars['link']['link']; ?>
" <?php if ($this->_tpl_vars['link']['onclick']): ?>onClick="<?php echo $this->_tpl_vars['link']['onclick']; ?>
"<?php endif; ?>><?php echo $this->_tpl_vars['link_title']; ?>
</a></li>
                    <?php endforeach; endif; unset($_from); ?>
                    </ul>
   	 			 </div>
                <!-- Thumb and Links Ends-->
                <div style="height:5px"></div>
           		<!-- Profile Details -->
                <div class="channel_inner_box">
                	<span class="channel_heading"><?php echo smarty_lang(array('code' => ((is_array($_tmp='profile')) ? $this->_run_mod_handler('capitalize', true, $_tmp, true) : smarty_modifier_capitalize($_tmp, true))), $this);?>
</span>
                    
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'user_channel_views'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['profile_hits'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</div>
                    </div>
                    
                    <?php if ($this->_tpl_vars['p']['show_dob'] == 'yes'): ?>
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'age'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['dob'])) ? $this->_run_mod_handler('get_age', true, $_tmp) : get_age($_tmp)); ?>
 - <?php echo ((is_array($_tmp=$this->_tpl_vars['u']['dob'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'joined'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['doj'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
</div>
                    </div>
                    
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'user_last_login'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['last_logged'])) ? $this->_run_mod_handler('nicetime', true, $_tmp) : nicetime($_tmp)); ?>
</div>
                    </div>
                    
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'subscribers'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['subscribers'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
</div>
                    </div>
                    
                    <div class="show_info">
                   	 <div class="item_list float_left" align="left"><?php echo smarty_lang(array('code' => 'country'), $this);?>
</div>
                     <div class="item_list float_right" align="right"><?php echo ((is_array($_tmp=$this->_tpl_vars['u']['country'])) ? $this->_run_mod_handler('country', true, $_tmp) : get_country($_tmp)); ?>
</div>
                    </div>
                    
                    
                 <div class="clearfix"></div>  
            <span class="channel_heading"><?php echo smarty_lang(array('code' => ((is_array($_tmp='Personal Details')) ? $this->_run_mod_handler('capitalize', true, $_tmp, true) : smarty_modifier_capitalize($_tmp, true))), $this);?>
</span>
                    <?php $this->assign('personal_details', $this->_tpl_vars['userquery']->load_personal_details($this->_tpl_vars['p'])); ?>
                    <?php $_from = $this->_tpl_vars['personal_details']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field']):
?>
                    <?php $this->assign('db_field', $this->_tpl_vars['field']['db_field']); ?>
                    <?php if ($this->_tpl_vars['p'][$this->_tpl_vars['db_field']] && $this->_tpl_vars['field']['auto_view'] == 'yes'): ?>
                        <div class="show_info">
                        <?php if ($this->_tpl_vars['field']['type'] != 'textarea' && $this->_tpl_vars['field']['type'] != 'text' && $this->_tpl_vars['field']['type'] != 'textfield'): ?>
                         <div class="item_list float_left" align="left"><?php echo $this->_tpl_vars['field']['title']; ?>
</div>
                         <div class="item_list float_right" align="right"><?php echo input_value(array('input' => $this->_tpl_vars['field']), $this);?>
</div>
                         <div class="clearfix"></div>
                        <?php elseif ($this->_tpl_vars['field']['type'] == 'textarea'): ?>
                        	<div class="item_list" align="left" style="margin-top:5px"><strong><?php echo $this->_tpl_vars['field']['title']; ?>
</strong></div>
                            <div style=" display:block; padding:5px; font-size:10px; background-color:#eaeaea; margin:3px 0px"><?php echo ((is_array($_tmp=$this->_tpl_vars['field']['value'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
                        <?php else: ?>
                        	<div class="item_list" align="left" style="margin-top:5px"><?php echo $this->_tpl_vars['field']['title']; ?>
</div>
                            <div class="clearfix"></div>
                         	<div class="item_list"><?php echo input_value(array('input' => $this->_tpl_vars['field']), $this);?>
</div>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php endforeach; endif; unset($_from); ?>
                    
                    
                    <?php if ($this->_tpl_vars['p']['about_me']): ?>
                    <div class="show_info">
                    	<div class="item_list" align="left" style="margin-top:5px"><strong><?php echo smarty_lang(array('code' => 'user_about_me'), $this);?>
</strong></div>
              			<div style=" display:block; padding:5px; font-size:10px; background-color:#eaeaea; margin:3px 0px"><?php echo ((is_array($_tmp=$this->_tpl_vars['p']['about_me'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>
</div>
                    </div>
                    <?php endif; ?>
                
                <div class="clearfix"></div>
           		</div>
              	<!-- Profile Details Ends -->
                
                <!-- User Subscriotions -->
                <div class="channel_inner_box" style="margin-top:5px">
                <span class="channel_heading"><?php echo smarty_lang(array('code' => 'user_subscriptions','assign' => 'users_videos'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['users_videos'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
</span>
                <hr width="100%" size="1" noshade>
                <?php $this->assign('users_items_subscriptions', config(users_items_subscriptions)); ?>
                <?php $this->assign('usr_subs', $this->_tpl_vars['userquery']->get_user_subscriptions($this->_tpl_vars['u']['userid'],$this->_tpl_vars['users_items_subscriptions'])); ?>
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
                <div align="center"><em><strong><?php echo smarty_lang(array('code' => 'user_no_subscriptions','assign' => 'user_subs'), $this);?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['user_subs'])) ? $this->_run_mod_handler('sprintf', true, $_tmp, $this->_tpl_vars['u']['username']) : sprintf($_tmp, $this->_tpl_vars['u']['username'])); ?>
</strong></em></div>
                <?php endif; ?>
                <div class="clearfix"></div>
                <hr width="100%" size="1" noshade>
                <div align="right"><a href="#"><?php echo smarty_lang(array('code' => 'more'), $this);?>
</a></div>
                </div>
        
        <!-- User Subscriptions end -->
            </div>