<?php
	$job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id ORDER BY id DESC LIMIT 0,1");
?>
<div class="wrap" style="padding: 20px;">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Mass Download CVs','wp_careers_plugin'); ?>
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
//echo "tessssssssssssssssssssss".  $tr="SELECT * FROM wp_careers_plugin_form_results WHERE job_id = $id ";
?>
	<form method="POST" action="<?php echo $current_url;?>&action=zip&id=<?php echo $id;?>">
	<p><b><label for="email_shortlist"><?php echo __('Select short list level to download','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/>
		<select style="width: 335px;" name="zip_shortlist">
		<option value=""></option>
<?php
//echo "tessssssssssssssssssssss".  $tr="SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' ORDER BY shortlist DESC LIMIT 0,1";
 $apps_shortlist = $wpdb->get_row("SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' ORDER BY shortlist DESC LIMIT 0,1");
//print_r($apps_shortlist); 

if($apps_shortlist->shortlist){
	$shortlist_count = 0;
	while($shortlist_count < ($apps_shortlist->shortlist + 1)){
		?>
		<option <?php echo (isset($_POST['zip_shortlist']) AND $_POST['zip_shortlist'] == $shortlist_count ? ' selected="selected"' : '');?> value="<?php echo $shortlist_count; ?>"><?php echo __('Short List Level ','wp_careers_plugin') .$shortlist_count; ?></option>
		<?php
		$shortlist_count = $shortlist_count + 1;
	}
}
?>
		</select>
	</p>
	
	
<style>	
.email_message {
width:900px;
}
</style>
	<div class="submit"><input class="button-primary" type="submit" name="create_zip" value="<?php echo __('Create zip to download','wp_careers_plugin') ?>" />  </div>

		
		
	</form>
</div>
