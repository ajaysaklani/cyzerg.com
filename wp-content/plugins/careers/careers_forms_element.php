<?php

?>
<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Application Forms','wp_careers_plugin'); ?> > <?php echo __('Edit Element','wp_careers_plugin'); ?></h2>

	<br/>
	<p><a href="<?php echo $current_url;?>&action=edit&form_id=<?php echo $form_id;?>"><< <?php echo __('Back to form','wp_careers_plugin'); ?></a></p>
	<br/>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<form method="post" action="<?php echo $current_url;?>&action=element&form_id=<?php echo $form_id;?>&element_id=<?php echo $element_id;?>">
			<p><b><label for="title"><?php echo __('Title','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="title" name="title" value="<?php echo $form_element_title; ?>" size="40"></p>

			<p><input <?php echo ($form_element_is_required == 'y' ? 'checked="checked"' : '');?> type="checkbox" name="is_required" value="1"/> <?php echo __('Is required','wp_careers_plugin') ?></p>
	
			<p><input <?php echo ($form_element_is_on_list == 'y' ? 'checked="checked"' : '');?> type="checkbox" name="is_on_list" value="1"/> <?php echo __('Is one of the colums on the page that lists applications','wp_careers_plugin') ?></p>
			<p><input <?php echo ($form_element_is_email == 'y' ? 'checked="checked"' : '');?> type="checkbox" name="is_email" value="1"/> <?php echo __('Is this field and email address','wp_careers_plugin') ?></p>
	
			<p><b><label for="title"><?php echo __('Input Type','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b>
			<br/>
			<select name="type" style="width: 340px;" onChange="toggle_question_type();" id="question_type">
			<option <?php echo ($form_element_type == 'Text Input' ? 'selected="selected"' : '');?> value="Text Input"><?php echo __('Text Input','wp_careers_plugin'); ?></option>
			<option <?php echo ($form_element_type == 'Textarea' ? 'selected="selected"' : '');?> value="Textarea"><?php echo __('Textarea','wp_careers_plugin'); ?></option>
			<option <?php echo ($form_element_type == 'Checkbox' ? 'selected="selected"' : '');?> value="Checkbox"><?php echo __('Checkbox','wp_careers_plugin'); ?></option>
			<option <?php echo ($form_element_type == 'Radio Button' ? 'selected="selected"' : '');?> value="Radio Button"><?php echo __('Radio Button','wp_careers_plugin'); ?></option>
			<option <?php echo ($form_element_type == 'Dropdown' ? 'selected="selected"' : '');?> value="Dropdown"><?php echo __('Dropdown','wp_careers_plugin'); ?></option>
			<option <?php echo ($form_element_type == 'File' ? 'selected="selected"' : '');?> value="File"><?php echo __('File','wp_careers_plugin'); ?></option>
			</select>
			</p>

			<div id="multiple_choice" <?php echo (($form_element_type == 'Text Input' OR $form_element_type == 'Textarea' OR $form_element_type == 'File' OR $form_element_type == '') ? 'style="display:none;"' : '');?>>
			<div id="choices">
			<?php
			if(count($form_element_choices) > 0 AND is_array($form_element_choices)){
				foreach($form_element_choices as $k=>$c){
					if(trim($c) != ''){
						echo '<input type="text" size="40" name="choice[update_'.$k.']" value="'.$c.'" placeholder="Choice"><br>';
					}
				}
			}
			?>
			</div>
			<a href="#" onclick="return add_choice_field();"><?php echo __('Add Choice','wp_careers_plugin'); ?></a>
			</div>
<script>
function toggle_question_type(){
	if(jQuery('#question_type').val() == 'Checkbox' || jQuery('#question_type').val() == 'Radio Button' || jQuery('#question_type').val() == 'Dropdown'){
		jQuery('#multiple_choice').show();
	}else{
		jQuery("#choices").empty();
		jQuery('#multiple_choice').hide();
	}
}
function add_choice_field(){
	jQuery("#choices").append('<input type="text" size="40" name="choice[]" value="" placeholder="Choice"><br>');
	return false;
}
</script>	
			<div class="submit"><input class="button-primary" type="submit" name="save_element" value="<?php echo __('Save','wp_careers_plugin') ?>" />&nbsp;&nbsp;&nbsp; <input class="button delete_button" type="submit" name="delete_element" value="<?php echo __('Delete','wp_careers_plugin') ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete? NOTE: Data submitted under this field will also be deleted','wp_careers_plugin') ?>');"/></div>

			</form>
</div>
