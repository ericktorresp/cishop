<?php /* Smarty version 2.6.18, created on 2010-10-16 22:15:23
         compiled from /Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/add_comment.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'lang', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/add_comment.html', 2, false),array('function', 'load_captcha', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/add_comment.html', 25, false),array('function', 'ANCHOR', '/Library/WebServer/Documents/styles/cbv2new/layout/blocks/comments/add_comment.html', 37, false),)), $this); ?>
<div class="add_comment_box" id="add_comment">
<h2><?php echo smarty_lang(array('code' => 'grp_add_comment'), $this);?>
</h2>
<div id="add_comment_result" class="action_box" style="display:none"></div>
<?php if ($this->_tpl_vars['userquery']->login_check('',true) || $this->_tpl_vars['Cbucket']->configs['anonym_comments'] == 'yes'): ?>
<form name="comment_form" method="post" action="" id="comment_form">
	<input type="hidden" name="reply_to" id="reply_to" value="0">
    <input type="hidden" name="obj_id" id="obj_id" value="<?php echo $this->_tpl_vars['id']; ?>
">
	<?php if (! $this->_tpl_vars['userquery']->login_check('',true) && $this->_tpl_vars['Cbucket']->configs['anonym_comments'] == 'yes'): ?>
    <label for="name" class="label"><?php echo smarty_lang(array('code' => 'name'), $this);?>
</label>
    <br>
    <input type="text" name="name" id="name" class="input"><br>
    <label for="email" class="label"><?php echo smarty_lang(array('code' => 'email_wont_display'), $this);?>
</label>
    <br>
    <input type="text" name="email" id="email"  class="input"><br>
    <?php else: ?>
    Name : <?php echo $this->_tpl_vars['userquery']->username; ?>
<br>
    <?php endif; ?>
    <br>
    
    <?php if (config ( 'comments_captcha' ) == 'all' || ( config ( 'comments_captcha' ) == 'guests' && ! $this->_tpl_vars['userquery']->login_check('',true) )): ?>
    <?php $this->assign('captcha', get_captcha()); ?>
    <?php if ($this->_tpl_vars['captcha']): ?> 
        <?php if ($this->_tpl_vars['captcha']['show_field']): ?>
            <label class="label" for="captcha">Verification Code</label><br />
                <?php echo load_captcha(array('captcha' => $this->_tpl_vars['captcha'],'load' => 'field','field_params' => ' id="captcha" class="input'), $this);?>

           <div class="clearfix"></div>
        <?php endif; ?>
        <label class="label">&nbsp;</label>
        <div class="input_container">
            <?php echo load_captcha(array('captcha' => $this->_tpl_vars['captcha'],'load' => 'function'), $this);?>

        </div>
        <div class="clearfix"></div>
    <?php endif; ?>
    <?php endif; ?>
            
            
    <?php echo ANCHOR(array('place' => 'before_compose_box'), $this);?>

    <textarea name="comment" id="comment_box" cols="45" rows="5"  class="input" ></textarea>
    <input type="hidden" name="type" value="<?php echo $this->_tpl_vars['type']; ?>
" />
    <br>
    <div style="margin-top:5px"><input type="button" name="add_comment" id="add_comment_button" value="<?php echo smarty_lang(array('code' => 'user_add_comment'), $this);?>
" class="cb_button" onclick="add_comment_js('comment_form','<?php echo $this->_tpl_vars['type']; ?>
')"></div>
</form>
<?php else: ?>
	<?php echo smarty_lang(array('code' => 'please_login_to_comment'), $this);?>

<?php endif; ?>
</div>