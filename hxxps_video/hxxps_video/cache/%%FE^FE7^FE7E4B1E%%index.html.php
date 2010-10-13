<?php /* Smarty version 2.6.18, created on 2010-10-13 21:43:09
         compiled from /Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'get_videos', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 15, false),array('function', 'get_groups', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 20, false),array('function', 'get_users', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 25, false),array('modifier', 'count', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 31, false),array('modifier', 'date_format', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 66, false),array('modifier', 'nl2br', '/Library/WebServer/Documents/admin_area/styles/cbv2/layout/index.html', 132, false),)), $this); ?>

	
<table width="100%" border="0" class="index_table">
  <tr>
    <td valign="top" style="padding-right:13px">
    
<div class="widgets-wrap" id="column1">
	 
     
     <div class="dragbox" id="cbstats" >
        <h2><?php echo $this->_tpl_vars['title']; ?>
 Quick Stats</h2>
        <div class="dragbox-content" >
            <div class="item clearfix">
            	<div class="stats_subitem">Videos</div>
                <div class="stats_subitem_d">Total : <strong><?php echo get_videos(array('count_only' => true), $this);?>
</strong> | Active : <strong><?php echo get_videos(array('count_only' => true,'active' => 'yes'), $this);?>
</strong> | Flagged : <strong><?php echo $this->_tpl_vars['cbvid']->action->count_flagged_objects(); ?>
</strong> | Processing : <strong><?php echo get_videos(array('count_only' => true,'status' => 'Processing'), $this);?>
</strong></div>
            </div>
            
             <div class="item clearfix">
            	<div class="stats_subitem">Groups</div>
                <div class="stats_subitem_d">Total : <strong><?php echo get_groups(array('count_only' => true), $this);?>
</strong> | Active : <strong><?php echo get_groups(array('count_only' => true,'active' => 'yes'), $this);?>
</strong> | Flagged : <strong><?php echo $this->_tpl_vars['cbgroup']->action->count_flagged_objects(); ?>
</strong></div>
            </div>
            
             <div class="item clearfix">
            	<div class="stats_subitem">Members</div>
                <div class="stats_subitem_d">Total : <strong><?php echo get_users(array('count_only' => true), $this);?>
</strong> | Active : <strong><?php echo get_users(array('count_only' => true,'status' => 'Ok'), $this);?>
</strong> | Flagged : <strong><?php echo $this->_tpl_vars['userquery']->action->count_flagged_objects(); ?>
</strong> | Banned : <strong><?php echo get_users(array('count_only' => true,'ban' => 'yes'), $this);?>
</strong></div>
            </div>
            
            
            <div class="item">
            <?php $this->assign('users', $this->_tpl_vars['userquery']->get_online_users()); ?>
            <strong style="text-decoration:underline">Online Users(<?php echo count($this->_tpl_vars['users']); ?>
)</strong><br />
            
            <?php if ($this->_tpl_vars['users']): ?>
            	<?php $_from = $this->_tpl_vars['users']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['onlines'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['onlines']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['user']):
        $this->_foreach['onlines']['iteration']++;
?>
                	<strong><a href="<?php echo $this->_tpl_vars['userquery']->profile_link($this->_tpl_vars['user']); ?>
" target="_blank"><?php echo $this->_tpl_vars['user']['username']; ?>
</a><?php if (! ($this->_foreach['onlines']['iteration'] == $this->_foreach['onlines']['total'])): ?>, <?php endif; ?></strong>
                <?php endforeach; endif; unset($_from); ?>
            <?php else: ?>
            	No User is Online
            <?php endif; ?>    
            
            
            </div>
            
            <div class="item subitem">
            	<?php if ($this->_tpl_vars['Cbucket']->cbinfo['new_available']): ?>
                	<div class="stats_subitem" style="width:60%; color:#060">Currently you are running <strong><?php echo $this->_tpl_vars['ClipBucket']->cbinfo['version']; ?>
 <?php echo $this->_tpl_vars['ClipBucket']->cbinfo['state']; ?>
</strong><br />
Latest Version <strong><?php echo $this->_tpl_vars['Cbucket']->cbinfo['latest']['version']; ?>
 <?php echo $this->_tpl_vars['Cbucket']->cbinfo['latest']['state']; ?>
 </strong></div>
               		<div class="stats_subitem" style="width:39%"><span class="simple_button"><a href="<?php echo $this->_tpl_vars['Cbucket']->cbinfo['latest']['link']; ?>
">Update Now</a></span></div>
                	<div class="clearfix"></div>
                <?php else: ?>
            		<div>
                    Currently you are running <strong><?php echo $this->_tpl_vars['Cbucket']->cbinfo['version']; ?>
 <?php echo $this->_tpl_vars['Cbucket']->cbinfo['state']; ?>
</strong> - No New Version Available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
       
    <div class="dragbox" id="cbnews" >
        <h2>ClipBucket News</h2>
        <div class="dragbox-content" >
            <?php $this->assign('cbnews', $this->_tpl_vars['Cbucket']->get_cb_news()); ?>
            <?php if ($this->_tpl_vars['cbnews']): ?>
                <?php $_from = $this->_tpl_vars['cbnews']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['news']):
?>
                <div class="item news">
                    <div class="news_title"><span class="title"><a href="<?php echo $this->_tpl_vars['news']['link']; ?>
"><?php echo $this->_tpl_vars['news']['title']; ?>
</a></span><span class="date"><?php echo ((is_array($_tmp=$this->_tpl_vars['news']['pubDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp) : smarty_modifier_date_format($_tmp)); ?>
</span></div><span class="clearfix"></span>
                    <div>
                        <?php echo $this->_tpl_vars['news']['description']; ?>

                    </div>
                </div>
                <?php endforeach; endif; unset($_from); ?>
            <?php else: ?>
                <div align="center"><em><strong>No News Found</strong></em></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="height:20px;"></div>
    <h2>ClipBucket Team and Development</h2><br /><br />

    ClipBucket is developed by <strong>Arslan</strong>, <strong>Fawaz</strong> and <strong>Frank White</strong>.<br />
We say special thanks to <strong>Frank</strong> and <strong>Christian</strong> (oUTSKIRTs) for their great support and time.
<br />
<br />
We need to grow our team but so far, very few people are thinking of doing any kind of contribution, so please go ahead and contribute your code so we can develop more features.
    
</div>    
    
    </td>
    <td width="210" valign="top">


   
<div class="widgets-wrap" style="width:210px" id="column2">

    <!-- Admin Todo List -->  
    <div class="dragbox" id="todo_list" >
        <h2>Todo List</h2>
        <div class="dragbox-content" >
        	<div class="item"><a href="video_manager.php?search=search&active=no">Approve Videos (<?php echo get_videos(array('active' => 'no','count_only' => true), $this);?>
)</a></div>
            <div class="item"><a href="members.php?search=yes&amp;status=ToActivate">Approve Members (<?php echo get_users(array('status' => 'ToActivate','count_only' => true), $this);?>
)</a></div>
            <div class="item"><a href="groups_manager.php?active=no&amp;search=yes">Approve Groups (<?php echo get_groups(array('active' => 'no','count_only' => true), $this);?>
)</a></div>
	    </div>
    </div>
    <!-- Admin Todo List -->
    
    <!-- Admin Todo List -->  
    <div class="dragbox" id="quick_actions" >
        <h2>Quick Action</h2>
        <div class="dragbox-content" >
        	<div class="item"><a href="main.php">Website Settings</a></div>
            <div class="item"><a href="add_member.php">Add Members</a></div>
            <div class="item"><a href="add_group.php">Add Group</a></div>
            <div class="item"><a href="cb_mod_check.php">Check Video Modules</a></div>          
	    </div>
    </div>
    <!-- Admin Todo List -->  


    <!-- Admin personal Note Widget -->
	<div class="dragbox" id="private_notes" >
        <h2>Personal Notes</h2>
        <div class="dragbox-content" >
        <?php $this->assign('notes', $this->_tpl_vars['myquery']->get_notes()); ?>
        
        <div id="the_notes">
        <?php if ($this->_tpl_vars['notes']): ?>
       		<div id="no_note"></div>
            <?php $_from = $this->_tpl_vars['notes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['note']):
?>
            	<div class="item" id="note-<?php echo $this->_tpl_vars['note']['note_id']; ?>
">
                	<img src="<?php echo $this->_tpl_vars['imageurl']; ?>
/cross.png" class="delete_note" onclick="delete_note('<?php echo $this->_tpl_vars['note']['note_id']; ?>
');" />
                    <?php echo ((is_array($_tmp=$this->_tpl_vars['note']['note'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                </div>
            <?php endforeach; endif; unset($_from); ?>
        <?php else: ?>
        	<div id="no_note" align="center"><strong><em>No notes</em></strong></div>
        <?php endif; ?>
        </div>
        <form method="post">
        	<textarea name="personal_note" id="personal_note" style="width:90%; height:50px; margin:5px; border:1px solid #999"></textarea>
            <div align="right" style="padding-right:10px"><a href="javascript:void(0)" 
            onclick="add_note('#personal_note')">Add Note</a></div>
        </form>
	    </div>
    </div>
    <!-- Admin personal Note Widget -->
    
    
</div>
    
    
    </td>
  </tr>
</table>