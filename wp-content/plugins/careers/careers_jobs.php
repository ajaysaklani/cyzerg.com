<?php

function CareersListExec(){
	global $wpdb;
	$table_name = "wp_careers_plugin_jobs";
	$table_name2 = "wp_careers_plugin_applications";
	$current_url = $_SERVER["REQUEST_URI"];
	
	global $blog_id;
	if(!$blog_id){
		$blog_id = 'no_id';
	}
	
	$job_title = '';
	$job_description = '';
	$job_status = '';
	$job_end_date = '';
	$job_form = 0;
	$job_city = '';
	$job_state = '';
	$job_country = '';
	$job_category = 0;
	
	$error_msg = '';
	$success_msg = '';
	
	$action = 'list';
	
	$id = null;
	$job_id = null;
	$filter = 'null';
	$app_id = null;
	
	$PageNum = 1;
	
	if(isset($_GET['action']) AND $_GET['action'] != ''){
		$action = $_GET['action'];
		$current_url = str_replace('&action=' . $_GET['action'] , '', $current_url);
	}
	
	if(isset($_GET['id']) AND $_GET['id'] != ''){
		$id = $_GET['id'];
		$current_url = str_replace('&id=' . $_GET['id'] , '', $current_url);
	}
	
	if(isset($_GET['job_id']) AND $_GET['job_id'] != ''){
		$job_id = $_GET['job_id'];
		$current_url = str_replace('&job_id=' . $_GET['job_id'] , '', $current_url);
	}
	
	if(isset($_GET['app_id']) AND $_GET['app_id'] != ''){
		$app_id = $_GET['app_id'];
		$current_url = str_replace('&app_id=' . $_GET['app_id'] , '', $current_url);
	}
	
	if(isset($_GET['pnum']) AND $_GET['pnum'] != ''){
		$PageNum = intval($_GET['pnum']);
		$current_url = str_replace('&pnum=' . $_GET['pnum'] , '', $current_url);
	}
	
	if(isset($_GET['filter'])){
		$filter = $_GET['filter'];
		$current_url = str_replace('&filter=' . $_GET['filter'] , '', $current_url);
	}
	
	if(isset($_POST['delete']) AND $id != null){
		$wpdb->query("DELETE FROM $table_name WHERE id = $id");
		$del_apps = $wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $job_id");
		if($del_apps){
			foreach($del_apps as $del_app){
				if($del_app){
					$exists = file_exists(plugin_dir_path(__FILE__) . 'resumes/' . $del_app->cv);
					if($exists){
						unlink(plugin_dir_path(__FILE__) . 'resumes/' . $del_app->cv);
					}	
				}
			}
		}
		$wpdb->query("DELETE FROM wp_careers_plugin_applications WHERE job_id = $id");
		$wpdb->query("DELETE FROM wp_careers_plugin_form_results WHERE job_id = $id");
		$success_msg = __('The job and the attached applications have been deleted.','wp_careers_plugin');
		$action = 'list';
	}
	
	if(isset($_GET['shortlist'])){
		$wpdb->update("wp_careers_plugin_applications", array('shortlist'=>(intval($_GET['shortlist']) + 1)), array('id'=>intval($app_id)), array("%d"));
		$success_msg = __('The application has been moved to a higher short list.','wp_careers_plugin');
		$current_url = str_replace('&shortlist=' . $_GET['shortlist'] , '', $current_url);
	}
	
	if(isset($_GET['rating'])){
		$wpdb->update("wp_careers_plugin_applications", array('rating'=>intval($_GET['rating'])), array('id'=>intval($app_id)), array("%d"));
		$success_msg = __('The application has been rated.','wp_careers_plugin');
		$current_url = str_replace('&rating=' . $_GET['rating'] , '', $current_url);
	}
	
	if(isset($_POST['delete_app']) AND $app_id != null){
		
		$del_app = $wpdb->get_row("SELECT * FROM $table_name2 WHERE id = $app_id ORDER BY id DESC LIMIT 0,1");
		if($del_app->cv){
			$exists = file_exists(plugin_dir_path(__FILE__) . 'resumes/' . $del_app->cv);
			if($exists){
				unlink(plugin_dir_path(__FILE__) . 'resumes/' . $del_app->cv);
			}	
		}
		
		$wpdb->query("DELETE FROM $table_name2 WHERE id = $app_id");
		$wpdb->query("DELETE FROM wp_careers_plugin_form_results WHERE app_id = $app_id");
		$success_msg = __('The application has been deleted.','wp_careers_plugin');
	}
	
	if(isset($_POST['save_notes']) AND $app_id != null){
		
		$wpdb->update("wp_careers_plugin_applications", array('notes'=>$_POST['application_notes']), array('id'=>intval($app_id)), array("%s"));
		
		$success_msg = __('The note has been saved.','wp_careers_plugin');
	}
	
	if(isset($_POST['send_email'])){
		if(!isset($_POST['email_shortlist'])){
			$error_msg .= __(' The short list to send to is required.','wp_careers_plugin');
		}
		
		if(!isset($_POST['subject']) OR $_POST['subject'] == ''){
			$error_msg .= __(' Subject is required.','wp_careers_plugin');
		}
		
		if(!isset($_POST['message']) OR $_POST['message'] == ''){
			$error_msg .= __(' Message is required.','wp_careers_plugin');
		}
		
		if(isset($_POST['from_reply']) AND $_POST['from_reply'] != '' AND !CareersValidateEmail($_POST['from_reply'])){
			$error_msg .= __(' The email to reply to is invalid.','wp_careers_plugin');
		}
		
		if($error_msg == ''){
			$email_apps = $wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' AND shortlist = " . intval($_POST['email_shortlist']));
			if(count($email_apps) > 0){
				foreach($email_apps as $e){
					if($e->name){
					$email_body = preg_replace('/\{name}/',$e->name,$_POST['message']);
					}else{
					$email_body = preg_replace('/\{name}/','',$_POST['message']);
					}
					
                      			$headers = 'From: ' . get_option('admin_email') . "\r\n";
                      			if(isset($_POST['from_reply']) AND $_POST['from_reply'] != ''){
						$headers .= 'Reply-To: ' . trim($_POST['from_reply']) . "\r\n";
					}
					$headers .= 'X-Mailer: PHP/' . phpversion();
					@wp_mail(trim($e->email), $_POST['subject'], $email_body, $headers);
				}
				
				$success_msg = __('The emails have been sent.','wp_careers_plugin');
				$action = 'applicants';
				$job_id = $id;
			}else{
				$error_msg .= __(' There are no applications in the selected shortlist.','wp_careers_plugin');
			}
			
			
		}
	}
	
	if(isset($_POST['create_zip'])){
		if(!isset($_POST['zip_shortlist'])){
			$error_msg .= __(' The short list to download to is required.','wp_careers_plugin');
		}
		
		if($error_msg == ''){
			$email_apps = $wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' AND shortlist = " . intval($_POST['zip_shortlist']));
			$files_to_download = array();
			if(count($email_apps) > 0){
				foreach($email_apps as $e){
					if($e->cv){
						$files_to_download[] = array('old'=>$e->cv, 'new'=> careers_slugify($e->name) . '-' . mt_rand() . '.' . CareersGetExtension($e->cv));
					}
				}
				$job_zip_q = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id ORDER BY id DESC LIMIT 0,1");
				$zip_name = careers_slugify($job_zip_q->title) . '-shortlist-level-'.intval($_POST['zip_shortlist']).'.zip';
				
				
				if(count($files_to_download) > 0){
					if(create_zip($files_to_download, $zip_name,true,plugin_dir_path(__FILE__) . 'resumes/')){
						$success_msg = __('Click on the following link to download the zip file of the resumes/CVs. ','wp_careers_plugin') . '<a href="'.plugins_url( '/resumes/' . $zip_name, __FILE__ ).'">'.__('Download ','wp_careers_plugin').'</a>';
					}else{
						$error_msg .= __(' Could not create the zip. Please try again.','wp_careers_plugin');
					}
				}else{
					$error_msg .= __(' There are no CVs to download.','wp_careers_plugin');
				}
			}else{
				$error_msg .= __(' There are no applications in the selected shortlist.','wp_careers_plugin');
			}
			
			
		}
	}
	
	if(isset($_POST['export_csv'])){
		if(!isset($_POST['csv_shortlist'])){
			$error_msg .= __(' The short list to export to is required.','wp_careers_plugin');
		}
		
		if($error_msg == ''){
			$email_apps = $wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = $id AND status = 'verified' AND shortlist = " . intval($_POST['csv_shortlist']));
			$csv_fields = array();
				
			$csv_fields[0] = array();
			$csv_fields[0][] = __('Name','wp_careers_plugin');
			$csv_fields[0][] = __('Email','wp_careers_plugin');
			$csv_fields[0][] = __('Phone','wp_careers_plugin');
			if(count($email_apps) > 0){
				$success_msg = __('Click on the following link to download the csv file of the applicants. ','wp_careers_plugin') . '<a target="_blank" href="'.plugins_url( '/csv.php?id='.$id.'&csv_shortlist=' . intval($_POST['csv_shortlist']), __FILE__ ).'">'.__('Download ','wp_careers_plugin').'</a>';
				
			}else{
				$error_msg .= __(' There are no applications in the selected shortlist.','wp_careers_plugin');
			}
			
			
		}
	}
	
	if(isset($_POST['save_publish']) OR isset($_POST['save_only'])){
		$values = array();
		$format = array();
		
		if(!isset($_POST['title']) OR $_POST['title'] == ''){
			$error_msg .= __(' Title is required.','wp_careers_plugin');
		}else{
			$job_title = stripslashes($_POST['title']);
			$values['title'] = stripslashes($_POST['title']);
			$format[] = "%s";
		}
		
		if(!isset($_POST['description']) OR $_POST['description'] == ''){
			$error_msg .= __(' Description is required.','wp_careers_plugin');
		}else{
			$job_description = stripslashes($_POST['description']);
			$values['description'] = stripslashes($_POST['description']);
			$format[] = "%s";
		}
		
		if(!isset($_POST['datepicker']) OR $_POST['datepicker'] == '' OR strtotime(stripslashes($_POST['datepicker'])) == 0){
			$values['end_date'] = null;
			$format[] = "%s";
		}else{
			$job_end_date = stripslashes($_POST['datepicker']);
			$values['end_date'] = date('Y-m-d H:i:s', strtotime(stripslashes($_POST['datepicker'])));
			$format[] = "%s";
		}
		
		if(!isset($_POST['job_form']) OR $_POST['job_form'] == ''){
			$job_form = 0;
			$values['form_id'] = 0;
			$format[] = "%s";
		}else{
			$job_form = intval($_POST['job_form']);
			$values['form_id'] = intval($_POST['job_form']);
			$format[] = "%d";
		}
		
		if(!isset($_POST['job_category']) OR $_POST['job_category'] == ''){
			$job_category = 0;
			$values['tag_id'] = 0;
			$format[] = "%s";
		}else{
			$job_category = intval($_POST['job_category']);
			$values['tag_id'] = intval($_POST['job_category']);
			$format[] = "%d";
		}
		
		if(!isset($_POST['city']) OR $_POST['city'] == ''){
			$values['city'] = "";
			$format[] = "%s";
		}else{
			$job_city = stripslashes($_POST['city']);
			$values['city'] = stripslashes($_POST['city']);
			$format[] = "%s";
		}
		
		if(!isset($_POST['state']) OR $_POST['state'] == ''){
			$values['state'] = "";
			$format[] = "%s";
		}else{
			$job_state = stripslashes($_POST['state']);
			$values['state'] = stripslashes($_POST['state']);
			$format[] = "%s";
		}
		
		if(!isset($_POST['country']) OR $_POST['country'] == ''){
			$values['country'] = "";
			$format[] = "%s";
		}else{
			$job_country = stripslashes($_POST['country']);
			$values['country'] = stripslashes($_POST['country']);
			$format[] = "%s";
		}
		
		
		if($error_msg == ''){
			if($action == 'edit' AND $id != null){
				if(isset($_POST['save_publish'])){
					$values['status'] = 'published';
					$format[] = "%s";
					$popup_status = 'published';
				}elseif(isset($_POST['deactivate'])){
					$values['status'] = 'inactive';
					$format[] = "%s";
					$popup_status = 'inactive';
				}else{
					$values['status'] = 'published';
					$format[] = "%s";
					$popup_status = 'published';
				}
				
				
				$wpdb->update($table_name, $values, array('id'=>$id), $format);
				$success_msg = __('The job has been saved','wp_careers_plugin');
			}else{
			
			
				$values['created_on'] = date('Y-m-d H:i:s');
				$format[] = "%s";
				
				$values['blog_id'] = $blog_id;
				$format[] = "%s";
			
				if(isset($_POST['save_publish'])){
					$values['status'] = 'published';
					$popup_status = 'published';
				}else{
					$values['status'] = 'inactive';
					$popup_status = 'inactive';
				}
				$format[] = "%s";
			
				if($wpdb->insert($table_name, $values, $format)){
					$id = $wpdb->insert_id;
					$action = 'edit';
					$success_msg = __('The job has been saved','wp_careers_plugin');
				}else{
					$error_msg .= __(' An unknown error occured. Please try again.','wp_careers_plugin');
				}
			
			}
		}
	}
	
	if($action == 'edit' AND $id != null){
		$job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id AND blog_id = '$blog_id' ORDER BY id DESC LIMIT 0,1");
		if($job){
			$job_title = $job->title;
			$job_description = $job->description;
			$job_status = $job->status;
			if($job->end_date AND $job->end_date != '' AND strtotime($job->end_date) > 1){
				$job_end_date = date('F j, Y', strtotime($job->end_date));
			}
			$job_form = $job->form_id;
			
			$job_city = $job->city;
			$job_state = $job->state;
			$job_country = $job->country;
			$job_category = $job->tag_id;
		}
	}else{
		$action == 'list';
	}
	
	require 'careers_admin_'.$action.'.php';
}
