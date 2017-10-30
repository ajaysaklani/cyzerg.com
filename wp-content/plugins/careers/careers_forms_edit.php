<?php
$form = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $form_id AND blog_id = '$blog_id' ORDER BY id ASC LIMIT 0,1");
if($form){
	$form_title = $form->title;
	$form_heading = $form->heading;
}
	
$PageRows = 15;
$offset = ($PageNum - 1) * $PageRows;
$forms_elements = $wpdb->get_results("SELECT * FROM $table_name2 WHERE form_id = $form_id ORDER BY order_by ASC, id ASC LIMIT $offset,$PageRows");
$total_forms_elements = count($wpdb->get_results("SELECT * FROM $table_name2 WHERE form_id = $form_id "));
$total_pages = ceil($total_forms_elements / $PageRows);

?>
<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Application Forms','wp_careers_plugin'); ?></h2>

	
	<br/>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	
	<br/>
	<form method="post" action="<?php echo $current_url;?>&action=edit&form_id=<?php echo $form_id;?>&pnum=<?php echo $PageNum;?>">
	<p><input type="text" id="title" name="title" placeholder="<?php echo __('Title of the form','wp_careers_plugin'); ?>" value="<?php echo $form_title; ?>" size="40"> <input class="button-primary" type="submit" name="save" value="<?php echo __('Edit Form Title','wp_careers_plugin') ?>" />&nbsp;&nbsp;&nbsp; <input class="button delete_button" type="submit" name="delete_form" value="<?php echo __('Delete','wp_careers_plugin') ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete? NOTE: Data submitted with this form will also be deleted','wp_careers_plugin') ?>');"/></p>
	</form>
	<br/>
	<div>
		<div style="width: 40%; float: left;">
			<h3><?php echo __('Create New Elements/Fields','wp_careers_plugin'); ?></h3>
			<form method="post" action="<?php echo $current_url;?>&action=edit&form_id=<?php echo $form_id;?>&pnum=<?php echo $PageNum;?>">
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
			if(count($form_element_choices) > 0 AND is_array($form_element_choices) AND $error_msg != ''){
				foreach($form_element_choices as $c){
					echo '<input type="text" size="40" name="choice[]" value="'.$c.'" placeholder="Choice"><br>';
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
			<div class="submit"><input class="button" type="submit" name="save_element" value="<?php echo __('Save','wp_careers_plugin') ?>" /></div>

			</form>
		</div>
		
		<div style="width: 60%; float: right;">
		
		<form method="post" action="<?php echo $current_url;?>&action=edit&form_id=<?php echo $form_id;?>&pnum=<?php echo $PageNum;?>">
		
		<style>	
.email_message {
width: 100%;
}
</style>
	<p><b><label for="form_heading"><?php echo __('Form Heading','wp_careers_plugin'); ?>:</label></b> <br/><textarea rows="5" name="form_heading" class="email_message"><?php echo $form_heading; ?></textarea></p>
	
		<div class="submit"><input class="button" type="submit" name="save_heading" value="<?php echo __('Save Heading','wp_careers_plugin') ?>" /></div>

		</form>
		
			
		<h3><?php echo __('Form Elements/Fields','wp_careers_plugin'); ?></h3>
		<form method="post" action="<?php echo $current_url;?>&action=edit&form_id=<?php echo $form_id;?>&pnum=<?php echo $PageNum;?>">
		<input class="button" type="submit" name="update_order" value="<?php echo __('Update Order','wp_careers_plugin') ?>" />
		<br/><br/>
<table class="table table-hover table-bordered table-striped">
	<thead>
		<tr>
			<th width="10%"><?php echo __('Order','wp_careers_plugin'); ?></th>
			<th><?php echo __('Name','wp_careers_plugin'); ?></th>
			<th width="20%"><?php echo __('Type','wp_careers_plugin'); ?></th>
			<th width="10%" style="text-align: center;"><?php echo __('Actions','wp_careers_plugin'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if(count($forms_elements) > 0){
		foreach($forms_elements as $j){
			?>
	<tr>
		<td><input type="text" name="order_by[<?php echo $j->id;?>]" value="<?php echo ($j->order_by == 9999 ? '' : $j->order_by);?>" size="3"/></td>
		<td><span style="font-weight: bold; font-size: 14px;"><?php echo $j->title;?></span></td>
		<td><span style="font-weight: bold; font-size: 14px;"><?php echo $j->type;?></span></td>
		<td style="text-align: center;">
			<a href="<?php echo $current_url;?>&action=element&form_id=<?php echo $form_id;?>&element_id=<?php echo $j->id;?>" title="<?php echo __('Edit','wp_careers_plugin');?>"><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/pencil.png', __FILE__ );?>" /></a>
		</td>
	</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="3"><?php echo __('No forms elements to display','wp_careers_plugin'); ?></td></tr>
		<?php
	}
	
	?>
	</tbody>
	</table>
	</form>
	<?php echo paginate_three($current_url . '&action=edit&form_id=' . $form_id , $PageNum, $total_pages); ?>	
			
			
		</div>
		<div style="clear: both;"></div>
	</div>
	
</div>
