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
	
$PageRows = 15;
$offset = ($PageNum - 1) * $PageRows;
 $applications = $wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $job_id AND status = 'verified' $filter_q ORDER BY seen ASC, rating DESC, id DESC LIMIT $offset,$PageRows");
$total_applications = count($wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $job_id AND status = 'verified' $filter_q"));

?>
 <?php
$total_pages = ceil($total_applications / $PageRows);
	
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
	<select style="width: 200px;" id="careers_filter">
		<option value="<?php echo $current_url . '&action=applicants&job_id=' . $job_id . '&filter=null';?>"><?php echo __('No filter','wp_careers_plugin'); ?></option>
		<option <?php echo ($filter == 'seen' ? ' selected="selected"' : '');?> value="<?php echo $current_url . '&action=applicants&job_id=' . $job_id . '&filter=seen';?>"><?php echo __('Seen','wp_careers_plugin'); ?></option>
		<option <?php echo ($filter == 'unseen' ? ' selected="selected"' : '');?> value="<?php echo $current_url . '&action=applicants&job_id=' . $job_id . '&filter=unseen';?>"><?php echo __('Unseen','wp_careers_plugin'); ?></option>
<?php
$apps_shortlist = $wpdb->get_row("SELECT * FROM $table_name2  WHERE job_id = $job_id AND status = 'verified' ORDER BY shortlist DESC LIMIT 0,1");

if($apps_shortlist->shortlist > 0){
	$shortlist_count = 0;
	while($shortlist_count < ($apps_shortlist->shortlist + 1)){
		?>
		<option <?php echo ($filter == 'shortlist_' .$shortlist_count ? ' selected="selected"' : '');?> value="<?php echo $current_url . '&action=applicants&job_id=' . $job_id . '&filter=shortlist_' .$shortlist_count;?>"><?php echo __('Short List Level ','wp_careers_plugin') .$shortlist_count; ?></option>
		<?php
		$shortlist_count = $shortlist_count + 1;
	}
}
?>
	
	</select>
	<br/>
	<br/>
	
	<?php
	if(count($applications) > 0){
	?>
	<table class="table table-hover table-bordered">
	<thead>
		<tr>
			
			<?php
			if(intval($job->form_id) > 0){
				$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($job->form_id)." AND is_on_list = 'y' ORDER BY order_by ASC, id ASC");
				if($forms_elements){
					foreach($forms_elements as $forms_e){
						echo '<th>'.$forms_e->title.'</th>';
					}
				}
			}else{
			?>
			<th><?php echo __('Name','wp_careers_plugin'); ?></th>
			<th><?php echo __('Email','wp_careers_plugin'); ?></th>
			<th><?php echo __('Phone','wp_careers_plugin'); ?></th>
			<th width="10%"><?php echo __('Resume','wp_careers_plugin'); ?></th>
			<?php
			}
			?>
			<!-- <th width="10%"><?php echo __('Rating','wp_careers_plugin'); ?></th> -->
			<th width="10%"><?php echo __('Short List Name','wp_careers_plugin'); ?></th>
			<th width="10%"></th> 
			<th style="text-align: center;"><?php echo __('Actions','wp_careers_plugin'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php	
		
		foreach($applications as $a){
			?>
	<tr <?php echo ($a->seen == 'y' ? 'class="seen"' : '');?>>
		
		
		
			<?php
			if(intval($job->form_id) > 0){
				if($forms_elements){
					foreach($forms_elements as $forms_e){
						switch($forms_e->type){
							case 'Checkbox':
								$some_results = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($a->id));
								if(count($some_results) > 0){
									$some_results_str = array();
									foreach($some_results as $sr){
										$some_results_str[] = $sr->answer;
									}
									echo '<td>'.implode(", ", $some_results_str).'</td>';
								}else{
									echo '<td></td>';
								}
								break;
							case 'File':
								$a_file = $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($a->id));
								if($a_file){
									echo '<td><a title="'. __('Download','wp_careers_plugin').'" href="'. plugins_url( '/resumes/' . $a_file, __FILE__ ).'"><img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/download.png', __FILE__ ).'" alt="'. __('download','wp_careers_plugin').'" /></a></td>';
								}else{
									echo '<td></td>';
								}
								break;
							case 'Textarea':
								echo '<td>'.CareersTrimText($wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($a->id)),30).'</td>';
								break;
							default:
								echo '<td>'.$wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($a->id)).'</td>';

								break;
						}
					}
				}
			}else{
			
			?>
			<td><?php echo $a->name;?></td>
			<td><?php echo $a->email;?></td>
			<td><?php echo $a->phone;?></td>
			<td><a title="<?php echo __('Download','wp_careers_plugin'); ?>" href="<?php echo plugins_url( '/resumes/' . $a->cv, __FILE__ );?>"><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/download.png', __FILE__ );?>" alt="<?php echo __('download','wp_careers_plugin'); ?>" /></a></td>
			<?php
			}
			$tes1=3;
 $forms_elements1 = $wpdb->get_results("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$tes1." AND app_id = " . intval($a->id));
			//print_r($forms_elements1);
			foreach($forms_elements1 as $forms_e){
				//echo $forms_e->answer;
			}
?> 

		
		<!-- <td><?php echo (intval($a->rating) / 20);?>/5 <?php echo __('Stars','wp_careers_plugin'); ?></td> -->
		<td><?php echo __('','wp_careers_plugin'); ?> <?php echo $forms_e->answer;?></td>

		<td><?php echo $a->name;?></td>
		<td style="text-align: center;">
			<a href="<?php echo $current_url;?>&action=applicant&job_id=<?php echo $job_id;?>&filter=<?php echo $filter;?>&app_id=<?php echo $a->id;?>" title="<?php echo __('View','wp_careers_plugin'); ?>"><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/pencil.png', __FILE__ );?>" /></a>&nbsp;&nbsp;
		</td>
	</tr>
			<?php
		}
	?>
	</tbody>
	</table>
	<?php echo paginate_three($current_url . '&action=applicants&job_id=' . $job_id . '&filter=' . $filter, $PageNum, $total_pages); ?>
	
	<?php
	
	}else{
		?>
		<p><?php echo __('No applications to display for this job or filter','wp_careers_plugin'); ?></p>
		<?php
	}
	
	?>
	
	</div>


	

						
	
	<script>
	jQuery('#careers_filter').change(function() {
		window.location = jQuery(this).val();
	});
	</script>
