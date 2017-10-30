<?php

function CareersFormsExec(){
	global $wpdb;
	$table_name = "wp_careers_plugin_forms";
	$table_name2 = "wp_careers_plugin_form_elements";
	$table_name3 = "wp_careers_plugin_form_element_choices";
	$table_name4 = "wp_careers_plugin_form_results";
	$current_url = $_SERVER["REQUEST_URI"];
	
	global $blog_id;
	if(!$blog_id){
		$blog_id = 'no_id';
	}
	
	$form_title = '';
	$form_heading = '';
	$form_element_title = '';
	$form_element_is_required = 'n';
	$form_element_is_on_list = 'n';
	$form_element_is_email = 'n';
	$form_element_type = '';
	$form_element_choices = array();
	
	
	$job_end_date = '';
	
	$error_msg = '';
	$success_msg = '';
	
	$action = 'list';
	
	$id = null;
	$form_id = null;
	$element_id = null;
	
	$PageNum = 1;
	
	if(isset($_GET['action']) AND $_GET['action'] != ''){
		$action = $_GET['action'];
		$current_url = str_replace('&action=' . $_GET['action'] , '', $current_url);
	}
	
	if(isset($_GET['form_id']) AND $_GET['form_id'] != ''){
		$form_id = $_GET['form_id'];
		$current_url = str_replace('&form_id=' . $_GET['form_id'] , '', $current_url);
	}
	
	if(isset($_GET['element_id']) AND $_GET['element_id'] != ''){
		$element_id = $_GET['element_id'];
		$current_url = str_replace('&element_id=' . $_GET['element_id'] , '', $current_url);
	}
	
	if(isset($_GET['pnum']) AND $_GET['pnum'] != ''){
		$PageNum = intval($_GET['pnum']);
		$current_url = str_replace('&pnum=' . $_GET['pnum'] , '', $current_url);
	}
	
	
	
	if(isset($_POST['save'])){
		$values = array();
		$format = array();
		
		if(!isset($_POST['title']) OR $_POST['title'] == ''){
			$error_msg .= __(' Form title is required.','wp_careers_plugin');
		}else{
			$form_title = stripslashes($_POST['title']);
			$values['title'] = stripslashes($_POST['title']);
			$format[] = "%s";
		}
		
		if($error_msg == ''){
			if($action == 'edit' AND $form_id != null){
				$wpdb->update($table_name, $values, array('id'=>$form_id), $format);
				$success_msg = __('The form has been saved','wp_careers_plugin');
			}else{
			
			
				$values['blog_id'] = $blog_id;
				$format[] = "%s";
			
				if($wpdb->insert($table_name, $values, $format)){
					$form_id = $wpdb->insert_id;
					$action = 'edit';
					$success_msg = __('The form has been saved','wp_careers_plugin');
				}else{
					$error_msg .= __(' An unknown error occured. Please try again.','wp_careers_plugin');
				}
			
			}
		}
	}
	
	if(isset($_POST['save_heading'])){
		$values = array();
		$format = array();
		
		if(isset($_POST['form_heading']) ){
			$form_heading = stripslashes($_POST['form_heading']);
			$values['heading'] = stripslashes($_POST['form_heading']);
			$format[] = "%s";
		}else{
			$values['heading'] = "";
			$format[] = "%s";
		}
		
		
		
		if($error_msg == ''){
			if($form_id != null){
				$wpdb->update($table_name, $values, array('id'=>$form_id), $format);
				$success_msg = __('The form has been saved','wp_careers_plugin');
			}
		}
	}
	
	if(isset($_POST['update_order'])){
		$orderQuery = $wpdb->get_results("SELECT * FROM $table_name2 WHERE form_id = $form_id ");
		foreach($orderQuery as $o){
			if(isset($_POST['order_by'][$o->id]) AND (intval($_POST['order_by'][$o->id]) > 0)){
				$wpdb->update($table_name2, array('order_by'=>intval($_POST['order_by'][$o->id])), array('id'=>$o->id), array("%d"));
			}else{
				$wpdb->update($table_name2, array('order_by'=>9999), array('id'=>$o->id), array("%d"));
			}
		}
		
		$success_msg = __('The order has been updated.','wp_careers_plugin');
	}
	
	if(isset($_POST['delete_element'])){
		
		$wpdb->query("DELETE FROM $table_name2 WHERE id = $element_id");
		$wpdb->query("DELETE FROM $table_name3 WHERE form_element_id = $element_id");
		$wpdb->query("DELETE FROM $table_name4 WHERE form_element_id = $element_id");
		$action = 'edit';
		$success_msg = __('The form element has been deleted.','wp_careers_plugin');
	}
	
	if(isset($_POST['delete_form'])){
		$wpdb->query("DELETE FROM $table_name WHERE id = $form_id");
		$wpdb->query("DELETE FROM $table_name2 WHERE form_id = $form_id");
		$wpdb->query("DELETE FROM $table_name3 WHERE form_id = $form_id");
		$wpdb->query("DELETE FROM $table_name4 WHERE form_id = $form_id");
		$action = 'list';
		$success_msg = __('The form has been deleted.','wp_careers_plugin');
	}
	
	if(isset($_POST['save_element'])){
		$values = array();
		$format = array();
		
		if(!isset($_POST['title']) OR $_POST['title'] == ''){
			$error_msg .= __(' Form title is required.','wp_careers_plugin');
		}else{
			$form_element_title = stripslashes($_POST['title']);
			$values['title'] = stripslashes($_POST['title']);
			$format[] = "%s";
		}
		
		
		if(isset($_POST['is_required'])){
			$values['is_required'] = 'y';
			$form_element_is_required = 'y';
		}else{
			$values['is_required'] = 'n';
			$form_element_is_required = 'n';
		}
		$format[] = "%s";
		
		if(isset($_POST['is_on_list'])){
			$values['is_on_list'] = 'y';
			$form_element_is_on_list = 'y';
		}else{
			$values['is_on_list'] = 'n';
			$form_element_is_on_list = 'n';
		}
		$format[] = "%s";
		
		if(isset($_POST['is_email'])){
			$values['is_email'] = 'y';
			$form_element_is_email = 'y';
		}else{
			$values['is_email'] = 'n';
			$form_element_is_email = 'n';
		}
		$format[] = "%s";
		
		if(!isset($_POST['type']) OR $_POST['type'] == ''){
			$error_msg .= __(' The input type is required.','wp_careers_plugin');
		}else{
			$form_element_type = stripslashes($_POST['type']);
			$values['type'] = stripslashes($_POST['type']);
			$format[] = "%s";
			if($form_element_type == 'Checkbox' OR $form_element_type == 'Radio Button' OR $form_element_type == 'Dropdown'){
				if(!isset($_POST['choice']) OR count($_POST['choice']) == 0){
					$error_msg .= __(' The choices are required.','wp_careers_plugin');
				}else{
					$form_element_choices = $_POST['choice'];
				}
			}
		}
		
		if($error_msg == ''){
			if($action == 'element'){
				$wpdb->update($table_name2, $values, array('id'=>$element_id), $format);
				if($form_element_type == 'Checkbox' OR $form_element_type == 'Radio Button' OR $form_element_type == 'Dropdown'){
					foreach($_POST['choice'] as $k=>$ch){
						if(substr($k,0,7) == "update_"){
							$choice_id = str_replace("update_","",$k);
							if(trim($ch) == ''){
								$wpdb->query("DELETE FROM $table_name3 WHERE id = $choice_id");
							}else{
								$wpdb->update($table_name3, array('choice'=>$ch), array('id'=>$choice_id), array("%s"));
							}
						}else{
							$wpdb->insert($table_name3, array('form_id'=>$form_id,'form_element_id'=>$element_id,'choice'=>$ch), array("%d","%d","%s"));
						}
					}
				}
			}else{
				$values['form_id'] = $form_id;
				$format[] = "%d";
				
				$wpdb->insert($table_name2, $values, $format);
				$new_element_id = $wpdb->insert_id;
			
				if($form_element_type == 'Checkbox' OR $form_element_type == 'Radio Button' OR $form_element_type == 'Dropdown'){
					foreach($_POST['choice'] as $ch){
						$wpdb->insert($table_name3, array('form_id'=>$form_id,'form_element_id'=>$new_element_id,'choice'=>$ch), array("%d","%d","%s"));
					}
				}
			}
			
			$success_msg = __('The form element has been saved','wp_careers_plugin');
		}
	}elseif($action == 'element'){
		$el = $wpdb->get_row("SELECT * FROM $table_name2 WHERE id = $element_id ORDER BY id DESC LIMIT 0,1");
		if($el){
			$form_element_title = $el->title;
			$form_element_is_required = $el->is_required;
			$form_element_is_on_list = $el->is_on_list;
			$form_element_is_email = $el->is_email;
			$form_element_type = $el->type;
			$form_element_choices = array();
			
			$el_choices = $wpdb->get_results("SELECT * FROM $table_name3 WHERE form_id = $form_id AND form_element_id = $element_id");
			
			if($el_choices){
				foreach($el_choices as $el_c){
					$form_element_choices[$el_c->id] = $el_c->choice;
				}
			}
			
			
		}
	}
	
	require 'careers_forms_'.$action.'.php';
}
