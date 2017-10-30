<?php


function careers_page_function($atts){
	$layout = '';
	global $wpdb;
	$table_name = "wp_careers_plugin_jobs";
	
	global $blog_id;
	if(!$blog_id){
		$blog_id = 'no_id';
	}
	
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
	
	$category = null;
	$category_pager = '';
	
	
	$tags = $wpdb->get_results("SELECT * FROM wp_careers_plugin_tags WHERE blog_id = '$blog_id' ORDER BY name ASC");
		
	$captcha_instance = new CareersCaptcha();
	
	$captcha_instance->cleanup();
	
	
	
	if(!isset($_GET['job_id']) AND !isset($_GET['verify'])){
	
	$PageNum = 1;
	
	if(isset($_GET['pnum']) AND $_GET['pnum'] != ''){
		$PageNum = intval($_GET['pnum']);
	}
	
	$PageRows = 15;
	$offset = ($PageNum - 1) * $PageRows;
	if(intval($category) > 0){
		$jobs = $wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status='published' AND tag_id = $category ORDER BY status DESC, id DESC LIMIT $offset,$PageRows");
		$total_jobs = count($wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status='published' AND tag_id = $category"));
	}else{
		$jobs = $wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status='published' ORDER BY status DESC, id DESC LIMIT $offset,$PageRows");
		$total_jobs = count($wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status='published'"));
	}
	
	$total_pages = ceil($total_jobs / $PageRows);
	
	
	$careers_page_intro = get_option('careers_page_intro');
		if($careers_page_intro){
			$layout .= '<div>'.nl2br(do_shortcode($careers_page_intro)).'</div><br/>';
		}
	
    
    	if(count($jobs) > 0){
		$layout .= '<div class="careers-list">';
		
		$layout .= '<div class="first-row">&nbsp;</div>';
		foreach($jobs as $j){
    		$location = array();
    		if($j->city){
    			$location[] = $j->city;
    		}
    		if($j->state){
    			$location[] = $j->state;
    		}
    		if($j->country){
    			$location[] = $j->country;
    		}
    $layout .= '<div class="other-row">
    <div class="left-side">
    	<h3><a href="'.$permalink . 'job_id=' . $j->id .'">'.$j->title.'</a>';
    	if(count($location) > 0){
    		$layout .= '<br/><span style="font-weight: normal;">'.implode(", ", $location).'</span>';
    	}
    $layout .= '</h3></div>
    <div class="right-side">';
    	if(strtotime($j->end_date) > 1){
    	$layout .= '<p><i>Deadline</i><br/> <span class="careers-date">'.date(get_option('date_format'), strtotime($j->end_date)).'</span><p>';
   	}
    $layout .= '</div>
    <div style="clear: both;"></div>
    </div>';
    		
    		}
    		  $layout .= '</div><br/>';
    	}else{
		if(intval($category) > 0){
			$layout .= '<p>'. __('No job openings in that category','wp_careers_plugin').'</p>';
		}else{
			$layout .= '<p>'. __('No job openings at the moment','wp_careers_plugin').'</p>';
		}
		
		
	}
    
  
		
		if($total_jobs > $PageRows){
			if($PageNum > 1){
    				$layout .= '<div class="nav-previous"><a href="'.$permalink . 'pnum=' . ($PageNum - 1) . $category_pager . '"><span class="meta-nav">&larr;</span> '.__('Previous','wp_careers_plugin').'</a></div>';
    			}
    			
    			if($total_pages > $PageNum){
    				$layout .= '<div class="nav-next"><a href="'.$permalink . 'pnum=' . ($PageNum + 1) . $category_pager . '">'.__('Next','wp_careers_plugin').' <span class="meta-nav">&rarr;</span></a></div>';
    			}
		}
		
	}elseif(isset($_GET['job_id'])){
		if(isset($_GET['job_id']) AND $_GET['job_id'] != ''){
			$id = $_GET['job_id'];
		}
		
		$errors = false;
		$values = array();
		$format = array();
		$error_msg = '';
		$mail_this = false;
		$mail_this_email = "";
		
		
		if(isset($_POST['careers_submit'])){
			
			if(isset($_POST['captcha']) AND $_POST['captcha'] != ''){
				if(!$captcha_instance->check($_POST['code'], $_POST['captcha'])){
					$errors = true;
					$error_msg .= __('The code is not correct','wp_careers_plugin') . '. ';
				}
				$captcha_instance->remove($_POST['code']);
			}else{
				$errors = true;
				$error_msg .= __('The code is required','wp_careers_plugin') . '. ';
				$captcha_instance->remove($_POST['code']);
			}
			
			$sub_job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id AND blog_id = '$blog_id' AND status = 'published' ORDER BY id DESC LIMIT 0,1");
			if(intval($sub_job->form_id) > 0){
				$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($sub_job->form_id)." ORDER BY order_by ASC, id ASC");
				if($forms_elements){
					foreach($forms_elements as $forms_e){
							if($forms_e->is_email == 'y'){
								$mail_this = true;
								$mail_this_email = $_POST['app_form_field_'.$forms_e->id];
								$values['email'] = $_POST['app_form_field_'.$forms_e->id];
								$format[] = "%s";
							}
						switch($forms_e->type){
							
							
							case 'Checkbox':
								if(!isset($_POST['app_form_field_'.$forms_e->id]) OR count($_POST['app_form_field_'.$forms_e->id]) == 0){
									if($forms_e->is_required == 'y'){
										$errors = true;
										$error_msg .= $forms_e->title . ' ' . __('is required','wp_careers_plugin') . '. ';
									}
								}
								break;
							case 'File':
								if(!$_FILES["app_form_field_".$forms_e->id]["name"]){
									if($forms_e->is_required == 'y'){
										$errors = true;
										$error_msg .= $forms_e->title . ' ' . __('is required','wp_careers_plugin') . '. ';
									}
								}else{
									if($_FILES["app_form_field_".$forms_e->id]["error"] > 0){
										$errors = true;
										$error_msg .= ' ' . CareersUploadError($_FILES["app_form_field_".$forms_e->id]["error"]) . '.';
									}
								}	
								break;
							default:
								if(!isset($_POST['app_form_field_'.$forms_e->id]) OR $_POST['app_form_field_'.$forms_e->id] == ''){
									if($forms_e->is_required == 'y'){
										$errors = true;
										$error_msg .= $forms_e->title . ' ' . __('is required','wp_careers_plugin') . '. ';
									}
								}	
								break;
						}
					}
				}
			
			
			}else{
				if(isset($_POST['email']) AND $_POST['email'] != ''){
					$check_email = $wpdb->get_row("SELECT * FROM wp_careers_plugin_applications WHERE job_id = ".$id." AND email = '" . $_POST['email'] . "' ORDER BY id DESC LIMIT 0,1");
					if($check_email){
						$errors = true;
						$error_msg .=  __('The email has already been used to apply for this job','wp_careers_plugin') . '. ';
					}elseif(!CareersValidateEmail($_POST['email'])){
						$errors = true;
						$error_msg .=  __('The email is invalid','wp_careers_plugin') . '. ';
					}else{
						$values['email'] = $_POST['email'];
						$format[] = "%s";
						
						$mail_this = true;
						$mail_this_email = $_POST['email'];
					}
				}else{
					$errors = true;
					$error_msg .= __('Your email is required','wp_careers_plugin') . '. ';
				}
			
				if(isset($_POST['ur_name']) AND $_POST['ur_name'] != ''){
					$values['name'] = $_POST['ur_name'];
					$format[] = "%s";
				}else{
					$errors = true;
					$error_msg .= __('Your name is required','wp_careers_plugin') . '. ';
				}
				
				if(isset($_POST['phone']) AND $_POST['phone'] != ''){
					$values['phone'] = $_POST['phone'];
					$format[] = "%s";
				}else{
					$errors = true;
					$error_msg .= __('Your phone is required','wp_careers_plugin') . '. ';
				}
			
			
				if(isset($_POST['cover_letter']) AND $_POST['cover_letter'] != ''){
					$values['cover_letter'] = $_POST['cover_letter'];
					$format[] = "%s";
				}else{
					$errors = true;
					$error_msg .= __('Your cover letter is required','wp_careers_plugin') . '. ';
				}
			
				if($_FILES["cv"]["name"]){
					if($_FILES["cv"]["error"] > 0){
						$errors = true;
						$error_msg .= ' ' . CareersUploadError($_FILES["cv"]["error"]) . '.';
					}else{
						if(!CareersValidFileExtension($_FILES["cv"]["name"])){
							$errors = true;
							$error_msg .= __('The resume/CV has an invalid extension','wp_careers_plugin') . '. ';
						}else{
							$filename = CareersSetFilename(plugin_dir_path(__FILE__) . 'resumes/', $_FILES["cv"]["name"]);
							$values['cv'] = $filename;
							$format[] = "%s";
						}
					}
				}else{
					$errors = true;
					$error_msg .= __('The resume/CV is required','wp_careers_plugin') . '. ';
				}
			}
			
			$check_job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id AND blog_id = '$blog_id' ORDER BY id DESC LIMIT 0,1");
			if($check_job->status != 'published' OR $check_job->end_date < date('Y-m-d H:i:s')){
					$error_msg .= __('The job is not available','wp_careers_plugin') . '. ';
			}
			
			if(!$errors){
				$values['status'] = 'pending';
				$format[] = "%s";
				$values['added_on'] = date('Y-m-d H:i:s');
				$format[] = "%s";
				
				$values['job_id'] = $id;
				$format[] = "%d";
		
				$key = $mail_this_email . time();  
				$values['hash'] = str_replace(' ','',md5($key));
				$format[] = "%s";
		
				if($wpdb->insert("wp_careers_plugin_applications", $values, $format)){
					$new_record = $wpdb->insert_id;
					
					if(intval($sub_job->form_id) > 0){
						
						$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($sub_job->form_id)." ORDER BY order_by ASC, id ASC");
						if($forms_elements){
							foreach($forms_elements as $forms_e){
								$answers = array();
								
								$answers['form_id'] = intval($sub_job->form_id);
								$answers_format[] = "%d";
								
								$answers['form_element_id'] = $forms_e->id;
								$answers_format[] = "%d";
								
								$answers['job_id'] = $id;
								$answers_format[] = "%d";
								
								$answers['app_id'] = $new_record;
								$answers_format[] = "%d";
								
								switch($forms_e->type){
									case 'Checkbox':
										$answers_format[] = "%s";
										if(is_array($_POST['app_form_field_'.$forms_e->id])){
											foreach($_POST['app_form_field_'.$forms_e->id] as $p){
												$answers['answer'] = $p;
												$wpdb->insert("wp_careers_plugin_form_results", $answers, $answers_format);
											}
										}
										if(!isset($_POST['app_form_field_'.$forms_e->id]) OR count($_POST['app_form_field_'.$forms_e->id]) == 0){
											if($forms_e->is_required == 'y'){
												$errors = true;
												$error_msg .= $forms_e->title . ' ' . __('is required','wp_careers_plugin') . '. ';
											}
										}
										break;
									case 'File':
										$filename = CareersSetFilename(plugin_dir_path(__FILE__) . 'resumes/', $_FILES["app_form_field_".$forms_e->id]["name"]);
										$answers['answer'] = $filename;
										$answers_format[] = "%s";
										$wpdb->insert("wp_careers_plugin_form_results", $answers, $answers_format);
										move_uploaded_file($_FILES["app_form_field_".$forms_e->id]["tmp_name"], plugin_dir_path(__FILE__) . 'resumes/' . $filename);	
										break;
									default:
										$answers['answer'] = $_POST['app_form_field_'.$forms_e->id];
										$answers_format[] = "%s";
										$wpdb->insert("wp_careers_plugin_form_results", $answers, $answers_format);
										break;
								}
							}
						}
					}else{
						move_uploaded_file($_FILES["cv"]["tmp_name"], plugin_dir_path(__FILE__) . 'resumes/' . $filename);
					}
					
					if($mail_this){
								
						$to      = $mail_this_email; 
						$subject = "[" . get_option('blogname') . "] " . __('Job application confirmation','wp_careers_plugin');
						$message = ' 
 
'.__('Thank you for your application. Please click on the following link to complete the process.','wp_careers_plugin').'
 
'.$permalink.'verify=1&email='.$mail_this_email.'&hash='.$values['hash'].' 
 
';
                      
					
					
						$careers_from_email_address = get_option('careers_from_email_address');
					
						if($careers_from_email_address){
							$headers = 'From: do-not-reply <'.$careers_from_email_address.'>' . "\r\n";
						}else{
							$headers = null;
						}
					
						@wp_mail($to, $subject, $message,$headers);
					
					}
				}else{
					$errors = true;
					$error_msg .= __('An unknow error occurred. Please try again','wp_careers_plugin') . '. ';
				}
			}
		}
		
		$layout .= '<div>';
		
		$job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id AND blog_id = '$blog_id' AND status = 'published' ORDER BY id DESC LIMIT 0,1");
		if($job){
			
			
			$layout .= '<div>';
			
					
			
			$location = array();
    			if($job->city){
    				$location[] = $job->city;
    			}
    			if($job->state){
    				$location[] = $job->state;
    			}
    			if($job->country){
    				$location[] = $job->country;
    			}
    			if(count($location) > 0){
    				//$layout .= '<p style="float: right;"><i><b>'.__('Location','wp_careers_plugin') .':</b>'.implode(", ", $location).'</p>';
    			}
    			
    			$layout .= '<div style="clear: both;"></div></div>';
		
			$layout .= '<div>'.nl2br($job->description).'</div>';
			
			$layout .= '<br/>';
			
			if(isset($_POST['careers_submit'])){
				if($errors == true){
					$layout .= '<div id="careers_form_msg" class="careers-alert careers-alert-error">
              <strong>'.__('Error','wp_careers_plugin') .'!</strong> '. $error_msg .'
            </div>';
				}else{
					$layout .= '<div id="careers_form_msg" class="careers-alert careers-alert-success">
              <strong>'.__('Success','wp_careers_plugin') .'!</strong> '.__('Your application has been received. A message has been sent to your email. Please click on the link in it to complete the application process. If you fail to do this, you application will not be processed.','wp_careers_plugin') .'
            </div>';
				}
			}
			
			$prefix = mt_rand();
			
			
		
			if(intval($job->form_id) > 0){
				$form = $wpdb->get_row("SELECT * FROM wp_careers_plugin_forms WHERE id = ".$job->form_id." AND blog_id = '$blog_id' ORDER BY id ASC LIMIT 0,1");
				if($form AND $form->heading){
					$layout .= '<div>'.nl2br($form->heading).'</div><br/>';
				}
				
				
				$layout .= '<form id="careers_form" action="'.$permalink . 'job_id=' . $job->id .'#careers_form_msg" method="post" enctype="multipart/form-data">';
				$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($job->form_id)." ORDER BY order_by ASC, id ASC");
				if($forms_elements){
					foreach($forms_elements as $forms_e){
						switch($forms_e->type){
							case 'Textarea':
								$layout .= '<p>'.$forms_e->title .'<br/><textarea '. (($forms_e->is_required != 'y') ? 'required' : '').' name="app_form_field_'.$forms_e->id.'" id="app_form_field_'.$forms_e->id.'" rows="10" cols="75">'. ((isset($_POST['app_form_field_'.$forms_e->id]) AND $_POST['app_form_field_'.$forms_e->id] != '' AND $errors == true) ? $_POST['app_form_field_'.$forms_e->id] : '').'</textarea></p>';
								break;
							case 'Checkbox':
								$layout .= '<p>'.$forms_e->title .'<br/>';
								$el_choices = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_element_choices WHERE form_element_id = ".intval($forms_e->id));
								if($el_choices){
									foreach($el_choices as $el_c){
										$layout .= '<input name="app_form_field_'.$forms_e->id.'['.$el_c->id .']" value="'.$el_c->choice .'" type="checkbox" '. ((isset($_POST['app_form_field_'.$forms_e->id][$el_c->id]) AND $errors == true) ? 'checked="checked"' : '').'> '.$el_c->choice .'<br/>';
									}
								}
								$layout .= '</p>';
								break;
							case 'Radio Button':
								$layout .= '<p>'.$forms_e->title .'<br/>';
								$el_choices = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_element_choices WHERE form_element_id = ".intval($forms_e->id));
								if($el_choices){
									foreach($el_choices as $el_c){
										$layout .= '<input name="app_form_field_'.$forms_e->id.'" value="'.$el_c->choice .'" type="radio" '. ((isset($_POST['app_form_field_'.$forms_e->id]) AND $_POST['app_form_field_'.$forms_e->id] == $el_c->choice AND $errors == true) ? 'checked="checked"' : '').'> '.$el_c->choice .'<br/>';
									}
								}
								$layout .= '</p>';
								break;
							case 'Dropdown':
								$layout .= '<p>'.$forms_e->title .'<br/>';
								$layout .= '<select name="app_form_field_'.$forms_e->id.'">';
								$el_choices = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_element_choices WHERE form_element_id = ".intval($forms_e->id));
								if($el_choices){
									foreach($el_choices as $el_c){
										$layout .= '<option value="'.$el_c->choice .'" '. ((isset($_POST['app_form_field_'.$forms_e->id]) AND $_POST['app_form_field_'.$forms_e->id] == $el_c->choice AND $errors == true) ? 'selected="selected"' : '').'> '.$el_c->choice .'</option>';
									}
								}
								$layout .= '</select>';
								$layout .= '</p>';
								break;
							case 'File':
								$layout .= '<p class="upload-file">'.$forms_e->title .'<br/><input '. (($forms_e->is_required != 'y') ? 'required' : '').' id="app_form_field_'.$forms_e->id.'" name="app_form_field_'.$forms_e->id.'" type="file" ></p>';
								break;
							default:
								$layout .= '<p>'.$forms_e->title .'<br/><input '. (($forms_e->is_required != 'y') ? 'required' : '').' title="'.$forms_e->title.'" size="40" id="app_form_field_'.$forms_e->id.'" name="app_form_field_'.$forms_e->id.'" type="text" value="'. ((isset($_POST['app_form_field_'.$forms_e->id]) AND $_POST['app_form_field_'.$forms_e->id] != '' AND $errors == true) ? $_POST['app_form_field_'.$forms_e->id] : '').'"></p>';
								break;
						}
					}
				}
			}else{
				$layout .= '
		<p>'.__('Your Name','wp_careers_plugin') .'<br/><input required title="Your Name" size="40" id="ur_name" name="ur_name" type="text" value="'. ((isset($_POST['ur_name']) AND $_POST['ur_name'] != '' AND $errors == true) ? $_POST['ur_name'] : '').'"></p>
		
		<p class="email-address">'.__('Your Email Address','wp_careers_plugin') .'<br/><input required title="Your Email Address" size="40" id="email" name="email" type="email" value="'. ((isset($_POST['email']) AND $_POST['email'] != '' AND $errors == true) ? $_POST['email'] : '').'"></p><p>'.__('Your Phone Number','wp_careers_plugin') .'<br/><input required title="Your Phone Number" size="40" id="phone" name="phone" type="tel" value="'. ((isset($_POST['phone']) AND $_POST['phone'] != '' AND $errors == true) ? $_POST['phone'] : '').'"></p>
		<p>'.__('You Resume/CV <i>(.pdf or .doc only)</i>','wp_careers_plugin') .'<br/><input required id="cv" name="cv" type="file" ></p>
		<p>'.__('Tell us about yourself','wp_careers_plugin') .'<br/><textarea required name="cover_letter" id="cover_letter" rows="10" cols="75">'. ((isset($_POST['cover_letter']) AND $_POST['cover_letter'] != '' AND $errors == true) ? $_POST['cover_letter'] : '').'</textarea></p>';
		
			}
			
		$layout .= '<input type="hidden" name="code" value="'.$prefix.'">';
		$word = $captcha_instance->generate_random_word();
		
		$captcha = $captcha_instance->generate_image( $prefix, $word );
		
		
		
		
		$layout .= '<p class="wp_careers">'.__('Enter the code','wp_careers_plugin') .'<br/><img style="vertical-align:middle" src="'.plugins_url( '/tmp/'.$captcha, __FILE__ ).'"> <input required title="Captcha" size="15" id="captcha" name="captcha" type="text"></p>';
		
		$layout .= '<p class="submit-form"><input type="submit" name="careers_submit" value="'.__('Submit','wp_careers_plugin') .'"></p>
</form>';
			
			
		}else{
			$layout .= '<div id="careers_form_msg" class="careers-alert careers-alert-error">
              <strong>'.__('Error','wp_careers_plugin') .'!</strong> '. __('The job is no longer available','wp_careers_plugin') .'
            </div>';
		}
		
		$layout .= '</div>';
		
	}elseif(isset($_GET['verify'])){
		if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){  
			$email = $_GET['email'];
			$hash = $_GET['hash']; 
	
			$user = $wpdb->get_row("SELECT * FROM wp_careers_plugin_applications WHERE email = '" . str_replace(' ','',$email) . "'  AND hash = '" . str_replace(' ','',$hash) . "' ORDER BY id DESC LIMIT 0,1");
			if($user){
				$wpdb->update("wp_careers_plugin_applications", array('status'=>'verified'), array('id'=>intval($user->id)), array("%s"));
				$layout .= '<div class="careers-alert careers-alert-success">
              <strong>'.__('Success','wp_careers_plugin') .'!</strong> '.__('Your application has been confirmed. Thank you.','wp_careers_plugin') .'
            </div>';
				$app_id = $user->id;
				$careers_notice_email_address = get_option('careers_notice_email_address');
				if($careers_notice_email_address){
					$to      = $careers_notice_email_address; 
					$subject = "[" . get_option('blogname') . "] " . __('New Job Application','wp_careers_plugin');
					$message = __('A new job application has been received.','wp_careers_plugin') . '

';
					$attachments = array();
					$app_job = $wpdb->get_row("SELECT * FROM $table_name WHERE id = ".intval($user->job_id)." ORDER BY id DESC LIMIT 0,1");
                      
					if($app_job){
						if(intval($app_job->form_id) > 0){
							$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($app_job->form_id)." ORDER BY order_by ASC, id ASC");
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
												$message .=  $forms_e->title . ': ' . implode(", ", $some_results_str) . '

';
											}else{
												$message .=  $forms_e->title . ':
';
											}
											break;
										case 'File':
											$a_file = $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id));
											if($a_file){
												$attachments[] = plugin_dir_path(__FILE__) . 'resumes/' . $a_file;
											}
											break;
										case 'Textarea':
											$message .=  $forms_e->title . ': 

' .  $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id)) . '

';
											break;
										default:
											$message .=  $forms_e->title . ': ' . $wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($app_id)) . '

';
											break;
									}
								}
							}
						}else{
						
						$message .=  __('NAME','wp_careers_plugin') . ': ' . $user->name .'

';
						$message .=  __('EMAIL','wp_careers_plugin') . ': ' . $user->email .'

';
						$message .=  __('PHONE','wp_careers_plugin') . ': ' . $user->phone .'

';
						$attachments[] = plugin_dir_path(__FILE__) . 'resumes/' . $user->cv;
						
						$message .=   $user->cover_letter;
						
						}
						
					}
					
					$careers_from_email_address = get_option('careers_from_email_address');
					
					if($careers_from_email_address){
						$headers = 'From: do-not-reply <'.$careers_from_email_address.'>' . "\r\n";
					}else{
						$headers = null;
					}
					
					@wp_mail($to, $subject, $message, $headers, $attachments );
				}
			
			
			}else{
				$layout .= '<div class="careers-alert careers-alert-error">
              <strong>'.__('Error','wp_careers_plugin') .'!</strong> '. __('Invalid confirmation link','wp_careers_plugin') .'
            </div>';
			}
		}else{  
			$layout .= '<div class="careers-alert careers-alert-error">
              <strong>'.__('Error','wp_careers_plugin') .'!</strong> '. __('Invalid confirmation link','wp_careers_plugin') .'
            </div>';
		}
	}	
	
	return $layout;
}
