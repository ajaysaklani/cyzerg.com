<?php

?>
<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Create New Job','wp_careers_plugin'); ?> &nbsp;&nbsp;<a title="<?php echo __('List Jobs','wp_careers_plugin'); ?>" href="<?php echo $current_url;?>"><img style="vertical-align:middle" src="<?php echo plugins_url( '/application_view_list.png', __FILE__ );?>" /></a></h2>
	
	<br/>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<form method="POST" action="<?php echo $current_url;?>&action=add">
		
	<p><b><label for="title"><?php echo __('Title','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="title" name="title" value="<?php echo $job_title; ?>" size="40"></p>

	<p><b><label for="title"><?php echo __('Category','wp_careers_plugin'); ?></label></b>
			<br/>
			<select name="job_category" style="width: 340px;" >
			
			<option <?php echo ($job_category == 0 ? 'selected="selected"' : '');?> value="0"><?php echo __(' -- Select One -- ','wp_careers_plugin'); ?></option>
			<?php
			$tag_to_select = $wpdb->get_results("SELECT * FROM wp_careers_plugin_tags WHERE blog_id = '$blog_id' ");
			if($tag_to_select){
				foreach($tag_to_select as $fts){
					?>
					<option <?php echo ($job_category == $fts->id ? 'selected="selected"' : '');?> value="<?php echo $fts->id;?>"><?php echo $fts->name;?></option>
					<?php
				}
			}
			?>
			</select>
			</p>


<style>	
.wp-editor-wrap {
width:900px;
}
</style>
	<p><b><label for="description"><?php echo __('Description','wp_careers_plugin'); ?>:<span style="color: red;">*</span></label></b><br/><?php wp_editor( $job_description, "description", array("textarea_rows"=>15,'media_buttons' => true) ); ?></p>
	
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#datepicker').Zebra_DatePicker({format: 'F j, Y', show_icon: false});
	var datepicker = $('#datepicker').data('Zebra_DatePicker');
	datepicker.hide();
 });
</script>
	<p><b><label for="deadline"><?php echo __('Applications Deadline','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="datepicker" name="datepicker" value="<?php echo $job_end_date; ?>" size="40"></p>
	
	<p><b><label for="title"><?php echo __('Select a form to use','wp_careers_plugin'); ?></label></b>
			<br/>
			<select name="job_form" style="width: 340px;" >
			
			<option <?php echo ($job_form == 0 ? 'selected="selected"' : '');?> value="0"><?php echo __('Default','wp_careers_plugin'); ?></option>
			<?php
			$forms_to_select = $wpdb->get_results("SELECT * FROM wp_careers_plugin_forms WHERE blog_id = '$blog_id' ");
			if($forms_to_select){
				foreach($forms_to_select as $fts){
					?>
					<option <?php echo ($job_form == $fts->id ? 'selected="selected"' : '');?> value="<?php echo $fts->id;?>"><?php echo $fts->title;?></option>
					<?php
				}
			}
			?>
			</select>
			</p>
			
		<p><b><label for="city"><?php echo __('City','wp_careers_plugin'); ?></label></b><br/><input type="text" name="city" value="<?php echo $job_city; ?>" size="40"></p>
		<p><b><label for="state"><?php echo __('State','wp_careers_plugin'); ?></label></b><br/><input type="text" name="state" value="<?php echo $job_state; ?>" size="40"></p>
		<p><b><label for="country"><?php echo __('Country','wp_careers_plugin'); ?></label></b><br/><input type="text" name="country" value="<?php echo $job_country; ?>" size="40"></p>
	
	<div class="submit"><input class="button-primary" type="submit" name="save_publish" value="<?php echo __('Save & Publish','wp_careers_plugin') ?>" />  <input class="button" type="submit" name="save_only" value="<?php echo __('Save Only','wp_careers_plugin') ?>" /> </div>

		
		
	</form>
</div>
