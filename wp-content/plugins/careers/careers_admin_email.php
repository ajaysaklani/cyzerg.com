<?php
	$job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id ORDER BY id DESC LIMIT 0,1");
	
	if(isset($_POST['from_reply'])){
		$email_reply = $_POST['from_reply'];
	}else{
		$email_reply = '';
	}
	
	if(isset($_POST['subject'])){
		$email_subject = $_POST['subject'];
	}else{
		$email_subject =  '['.get_option('blogname').'] ' . __('Job','wp_careers_plugin') . ': ' . $job->title;
	}
	
	if(isset($_POST['message'])){
		$email_message = $_POST['message'];
	}else{
		$email_message = __('Hi','wp_careers_plugin') . ' {name},';
	}
?>
<div class="wrap" style="padding: 20px;">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Email Applicants','wp_careers_plugin'); ?>
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
	<form method="POST" action="<?php echo $current_url;?>&action=email&id=<?php echo $id;?>">
	<p><b><label for="email_shortlist"><?php echo __('Select short list level to send to','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/>
		<select style="width: 335px;" name="email_shortlist">
		<option value=""></option>
<?php
$apps_shortlist = $wpdb->get_row("SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' ORDER BY shortlist DESC LIMIT 0,1");

if($apps_shortlist->shortlist > 0){
	$shortlist_count = 0;
	while($shortlist_count < ($apps_shortlist->shortlist + 1)){
		?>
		<option <?php echo (isset($_POST['email_shortlist']) AND $_POST['email_shortlist'] == $shortlist_count ? ' selected="selected"' : '');?> value="<?php echo $shortlist_count; ?>"><?php echo __('Short List Level ','wp_careers_plugin') .$shortlist_count; ?></option>
		<?php
		$shortlist_count = $shortlist_count + 1;
	}
}
?>
		</select>
	</p>
	
	<p><b><label for="subject"><?php echo __('Subject','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="subject" name="subject" value="<?php echo $email_subject; ?>" size="40"></p>

		
	<p><b><label for="from_reply"><?php echo __('Reply To Email','wp_careers_plugin'); ?></label></b>: <?php echo __('An email that the recipients can send their replies to','wp_careers_plugin'); ?><br/><input type="text" id="from_reply" name="from_reply" value="<?php echo $email_reply; ?>" size="40"></p>
	
	
<style>	
.email_message {
width:900px;
}
</style>
	<p><b><label for="message"><?php echo __('Message','wp_careers_plugin'); ?>:<span style="color: red;">*</span></label></b> <?php echo __('Use {name} as a placeholder for the name','wp_careers_plugin') ?> <br/><textarea rows="15" name="message" class="email_message"><?php echo $email_message; ?></textarea></p>
	

	
	<div class="submit"><input class="button-primary" type="submit" name="send_email" value="<?php echo __('Send the email','wp_careers_plugin') ?>" />  </div>

		
		
	</form>
</div>
