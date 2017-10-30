<?php

$filter_q = '';	
switch($filter){
	case 'seen':
		$filter_q = "AND seen = 'y'";
		break;
	case 'unseen':
		$filter_q = "AND seen = 'n'";
		break;
	default:
		if($filter != '' AND (substr($filter,0,10) == "shortlist_")){
			$filter_q = "AND shortlist = " . intval(str_replace("shortlist_","",$filter));
		}
		break;
}

$job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $job_id ORDER BY id DESC LIMIT 0,1");
$application = $wpdb->get_row("SELECT * FROM $table_name2 WHERE id = $app_id ORDER BY id DESC LIMIT 0,1");	
	
	?>
	<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo $job->title;?> 
		<?php echo CareersJobsNavIcons($current_url, $job_id);?>
	</h2>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<br/>
	<p>
		<?php
		$apps_prev = count($wpdb->get_results("SELECT * FROM $table_name2 WHERE id > $app_id AND job_id = $job_id AND status = 'verified' $filter_q ORDER BY id ASC"));
		
		if($apps_prev > 0){
		
		$app_prev = $wpdb->get_row("SELECT * FROM $table_name2 WHERE id > $app_id AND job_id = $job_id AND status = 'verified' $filter_q ORDER BY id ASC LIMIT 0,1");
		
		?>
		<a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_prev->id;?>" title="<?php echo __('Previous','wp_careers_plugin'); ?>"><img style="vertical-align:middle" src="<?php echo plugins_url( '/document_page_previous.png', __FILE__ );?>" alt="<?php echo __('Previous','wp_careers_plugin'); ?>" /></a>
		
		<?php
		}
		
		echo $apps_prev + 1;
		
		?>
		
		/
		
		<?php
		
		echo count($wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $job_id AND status = 'verified' $filter_q"));
		
		?>
		
		<?php
		$apps_next = count($wpdb->get_results("SELECT * FROM $table_name2 WHERE id < $app_id AND job_id = $job_id AND status = 'verified' $filter_q ORDER BY id DESC"));
		
		if($apps_next > 0){
		
		$app_next = $wpdb->get_row("SELECT * FROM $table_name2 WHERE id < $app_id AND job_id = $job_id AND status = 'verified' $filter_q ORDER BY id DESC LIMIT 0,1");
		
		?>
		<a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_next->id;?>" title="<?php echo __('Next','wp_careers_plugin'); ?>"><img style="vertical-align:middle" src="<?php echo plugins_url( '/document_page_next.png', __FILE__ );?>" alt="<?php echo __('Next','wp_careers_plugin'); ?>" /></a>
		<?php
		}
		?>
	<span style="font-size: 1.5em;">&nbsp;&nbsp;
<?php
switch($filter){
	case 'seen':
		echo __('Seen','wp_careers_plugin');
		break;
	case 'unseen':
		echo __('Unseen','wp_careers_plugin');
		break;
	default:
		if($filter != '' AND (substr($filter,0,10) == "shortlist_")){
			echo __('Short List Level: ','wp_careers_plugin') . intval(str_replace("shortlist_","",$filter));
		}
		break;
}
?>
	</span>
	</p>
	<br/>
	<?php
	
	if($application){
	?>
	
	<div>
		<div style="width: 50%; float: left;">
	
	
	<p style="font-size: 1.2em;"><b><?php echo __('SHORT LIST','wp_careers_plugin'); ?>:</b> <?php echo __('Level','wp_careers_plugin'); ?> <?php echo $application->shortlist;?>
		<a class="button-primary" href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&shortlist=<?php echo $application->shortlist;?>"><?php echo __('Move Up &#8593;','wp_careers_plugin') ?></a>
	</p>
	
	<?php
			if(intval($job->form_id) > 0){
				$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($job->form_id)." ORDER BY order_by ASC, id ASC");
				if($forms_elements){
					foreach($forms_elements as $forms_e){
						switch($forms_e->type){
							case 'Checkbox':
								$some_results = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id));
								if(count($some_results) > 0){
									$some_results_str = array();
									foreach($some_results as $sr){
										$some_results_str[] = $sr->answer;
									}
									?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b> <?php echo implode(", ", $some_results_str);?></p><?php
								}else{
									?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b> </p><?php
								}
								break;
							case 'File':
								$a_file = $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id));
								if($a_file){
									?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b> <a title="<?php echo __('Download','wp_careers_plugin'); ?>" href="<?php echo plugins_url( '/resumes/' . $a_file, __FILE__ );?>"><?php echo __('Download','wp_careers_plugin'); ?></a></p><?php
								}else{
									?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b> </p><?php
								}
								break;
							case 'Textarea':
								?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b></p> <div><?php echo nl2br($wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id)));?></div><?php
								break;
							default:
								?><p style="font-size: 1.2em;"><b><?php echo $forms_e->title; ?>:</b> <?php echo $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id));?></p><?php
								break;
						}
					}
				}
			}else{
			?>
			<p style="font-size: 1.2em;"><b><?php echo __('NAME','wp_careers_plugin'); ?>:</b> <?php echo $application->name;?></p>
			<p style="font-size: 1.2em;"><b><?php echo __('EMAIL','wp_careers_plugin'); ?>:</b> <a href="mailto:<?php echo $application->email;?>"><?php echo $application->email;?></a></p>
			<p style="font-size: 1.2em;"><b><?php echo __('PHONE','wp_careers_plugin'); ?>:</b> <?php echo $application->phone;?></p>
			<p style="font-size: 1.2em;"><b><?php echo __('RESUME/CV','wp_careers_plugin'); ?>:</b> <a title="<?php echo __('Download','wp_careers_plugin'); ?>" href="<?php echo plugins_url( '/resumes/' . $application->cv, __FILE__ );?>"><?php echo __('Download','wp_careers_plugin'); ?></a></p>
			<div style="font-size: 1.2em;"><?php echo nl2br($application->cover_letter);?></div>
			<?php
			}
			?>
	
	
	
	
	<form action="<?php echo $current_url;?>&action=applicants&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>" method="post">
	<div class="submit"><input class="button delete_button" type="submit" name="delete_app" value="<?php echo __('Delete','wp_careers_plugin') ?>" onclick="return confirm('<?php echo __('Are you sure?','wp_careers_plugin') ?>');"/></div>
	</form>
	
	</div>
		
		<div style="width: 50%; float: right;">
			
			<br/>
			
			<style>
			.star-rating,
.star-rating a:hover,
.star-rating a:active,
.star-rating a:focus,
.star-rating .current-rating{
	background: url(<?php echo plugins_url( '/star_rating/star.gif', __FILE__ );?>) left -1000px repeat-x;
}
.star-rating{
	position:relative;
	width:125px;
	height:25px;
	overflow:hidden;
	list-style:none;
	margin:0;
	padding:0;
	background-position: left top;
}
.star-rating li{
	display: inline;
}
.star-rating a, 
.star-rating .current-rating{
	position:absolute;
	top:0;
	left:0;
	text-indent:-1000em;
	height:25px;
	line-height:25px;
	outline:none;
	overflow:hidden;
	border: none;
}
.star-rating a:hover,
.star-rating a:active,
.star-rating a:focus{
	background-position: left bottom;
}
.star-rating a.one-star{
	width:20%;
	z-index:6;
}
.star-rating a.two-stars{
	width:40%;
	z-index:5;
}
.star-rating a.three-stars{
	width:60%;
	z-index:4;
}
.star-rating a.four-stars{
	width:80%;
	z-index:3;
}
.star-rating a.five-stars{
	width:100%;
	z-index:2;
}
.star-rating .current-rating{
	z-index:1;
	background-position: left center;
}	

/* for an inline rater */
.inline-rating{
	display:-moz-inline-block;
	display:-moz-inline-box;
	display:inline-block;
	vertical-align: middle;
}

/* smaller star */
.small-star{
	width:50px;
	height:10px;
}
.small-star,
.small-star a:hover,
.small-star a:active,
.small-star a:focus,
.small-star .current-rating{
	background-image: url(<?php echo plugins_url( '/star_rating/star_small.gif', __FILE__ );?>);
	line-height: 10px;
	height: 10px;
}
			</style>
			<ul class="star-rating">
		<li class="current-rating" style="width:<?php echo $application->rating;?>%;">Currently <?php echo (intval($application->rating) / 20);?>/5 Stars.</li>
		<li><a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&rating=20" title="1 star out of 5" class="one-star">1</a></li>
		<li><a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&rating=40" title="2 stars out of 5" class="two-stars">2</a></li>
		<li><a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&rating=60" title="3 stars out of 5" class="three-stars">3</a></li>
		<li><a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&rating=80" title="4 stars out of 5" class="four-stars">4</a></li>
		<li><a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>&rating=100" title="5 stars out of 5" class="five-stars">5</a></li>
	</ul>
			
			
			<form action="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $app_id;?>" method="post">
	<p><b><label for="title"><?php echo __('Notes about the application','wp_careers_plugin'); ?></label></b><br/><textarea name="application_notes" rows="10" cols="70"><?php echo $application->notes;?></textarea></p>

	
	<div class="submit"><input class="button-primary" type="submit" name="save_notes" value="<?php echo __('Save Notes','wp_careers_plugin') ?>" /></div>
	</form>
		
		</div>
		<div style="clear: both;"></div>
	</div>
	
	<?php
		if($application->seen == 'n'){
			$wpdb->update("wp_careers_plugin_applications", array('seen'=>'y'), array('id'=>intval($application->id)), array("%s"));
		}
	
	}else{
		echo '<p>'.__('The application does not exist','wp_careers_plugin').'</p>';
	}
