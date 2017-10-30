<?php

?>
<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Edit Job','wp_careers_plugin'); ?> 
	<?php echo CareersJobsNavIcons($current_url, $id);?>
	</h2>
	
	<br/>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<form method="POST" action="<?php echo $current_url;?>&action=edit&id=<?php echo $id;?>">
	
	<div id="edit-slug-box">
	<strong><?php echo __('Job Link','wp_careers_plugin'); ?>:</strong>
<span id="sample-permalink" tabindex="-1">
<?php
if(get_option('careers_page_id')){
			$permalink_o = get_permalink(get_option('careers_page_id'));
			if(parse_url($permalink_o, PHP_URL_QUERY)){
				$permalink = $permalink_o . '&';
			}else{
				$permalink = $permalink_o . '?';
			}
		}else{
			$permalink_o = '';
		}
		
		echo $permalink . 'job_id=' . $id;
?>
</span>
<span id="view-post-btn"><a href="<?php echo $permalink . 'job_id=' . $id;?>" target="_blank" class="button button-small"><?php echo __('View','wp_careers_plugin'); ?></a></span>
	</div>
		
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
	
	<p><input <?php echo ($job_status == 'published' ? '' : 'checked="checked"');?> type="checkbox" name="deactivate" value="1"/> <?php echo __('Inactive','wp_careers_plugin') ?></p>
	
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
	
	
	<div class="submit"><input <?php echo ($job_status == 'published' ? 'style="display:none;"' : 'checked="checked"');?> class="button-primary" type="submit" name="save_publish" value="<?php echo __('Save & Publish','wp_careers_plugin') ?>" />  <input class="button" type="submit" name="save_only" value="<?php echo __('Save Only','wp_careers_plugin') ?>" /> &nbsp;&nbsp;&nbsp; <input class="button delete_button" type="submit" name="delete" value="<?php echo __('Delete','wp_careers_plugin') ?>" onclick="return confirm('<?php echo __('Are you sure? NOTE: applications will be deleted too','wp_careers_plugin') ?>');"/></div>

		
		
	</form>
</div>
