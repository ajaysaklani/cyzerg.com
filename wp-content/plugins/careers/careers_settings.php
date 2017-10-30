<?php

function CareersSettingsExec(){
	$current_url = $_SERVER["REQUEST_URI"];
	
	$error_msg = '';
	$success_msg = '';
	$careers_page_id = '';
	$careers_page_intro = '';
	$careers_notice_email_address = '';
	
	
	if(isset($_POST['save'])){
		if(isset($_POST['careers_page_id']) AND $_POST['careers_page_id'] != ""){
			update_option('careers_page_id', $_POST['careers_page_id']);
			$careers_page_id = $_POST['careers_page_id'];
		}else{
			$error_msg = 'The Careers Page ID is required. ';
		}
		
		if(isset($_POST['careers_page_intro']) AND $_POST['careers_page_intro'] != ""){
			update_option('careers_page_intro', stripslashes($_POST['careers_page_intro']));
			$careers_page_intro = stripslashes($_POST['careers_page_intro']);
		}else{
			delete_option('careers_page_intro');
		}
		
		if(isset($_POST['careers_from_email_address']) AND $_POST['careers_from_email_address'] != ""){
			update_option('careers_from_email_address', $_POST['careers_from_email_address']);
			$careers_from_email_address = $_POST['careers_from_email_address'];
		}else{
			delete_option('careers_from_email_address');
		}
		
		if(isset($_POST['careers_notice_email_address']) AND $_POST['careers_notice_email_address'] != ""){
			update_option('careers_notice_email_address', $_POST['careers_notice_email_address']);
			$careers_notice_email_address = $_POST['careers_notice_email_address'];
		}else{
			delete_option('careers_notice_email_address');
		}
		
		$success_msg = __('Settings saved.','wp_careers_plugin');;
	}else{
		$careers_page_id = get_option('careers_page_id');
		$careers_page_intro = get_option('careers_page_intro');
		$careers_from_email_address = get_option('careers_from_email_address');
		$careers_notice_email_address = get_option('careers_notice_email_address');
	}

?>
<div class="wrap" style="padding: 20px;">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Settings','wp_careers_plugin'); ?>
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

<form method="POST" action="<?php echo $current_url;?>">
		
	<p><b><label for="careers_page_id"><?php echo __('Careers Page ID','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="careers_page_id" name="careers_page_id" value="<?php echo $careers_page_id; ?>" size="40"></p>
	
	<p><b><label for="careers_from_email_address"><?php echo __('Email address to use in FROM part of emailss','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="careers_from_email_address" name="careers_from_email_address" value="<?php echo $careers_from_email_address; ?>" size="40"></p>

	<p><b><label for="careers_notice_email_address"><?php echo __('Email address to send notice when an application is made','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="careers_notice_email_address" name="careers_notice_email_address" value="<?php echo $careers_notice_email_address; ?>" size="40"></p>

	
	<style>	
.email_message {
width:900px;
}
</style>
	<p><b><label for="careers_page_intro"><?php echo __('Jobs Listing Introduction Text','wp_careers_plugin'); ?>:</label></b> <br/><textarea rows="10" name="careers_page_intro" class="email_message"><?php echo $careers_page_intro; ?></textarea></p>
	
<div class="submit"><input class="button-primary" type="submit" name="save" value="<?php echo __('Save Settings','wp_careers_plugin') ?>" /></div>

		
		
	</form>

</div>
<?php
}
