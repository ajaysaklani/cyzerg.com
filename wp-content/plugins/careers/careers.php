<?php
/*
Plugin Name: Wp Careers
Plugin URI: http://codecanyon.net/user/robertnduati
Description: Plugin to manange a jobs board and the applications that are received
Version: 1.0
Author: Robert Nduati
Author URI: http://codecanyon.net/user/robertnduati
License: http://codecanyon.net/licenses
*/

if(!function_exists("CareersTrimText")) {
	function CareersTrimText($input, $length) {
		$input = strip_tags($input);
		if (strlen($input) <= $length) {
			return $input;
		}
		$last_space = strrpos(substr($input, 0, $length), ' ');
		$trimmed_text = substr($input, 0, $last_space);
  
		$trimmed_text .= '&hellip;';
  
		return $trimmed_text;
	}
}

if(!function_exists("CareersValidateEmail")) {
	function CareersValidateEmail($email){
   		if (preg_match("/[\\000-\\037]/",$email)) {
      			return false;
   		}
   		$pattern = "/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD";
   		if(!preg_match($pattern, $email)){
      			return false;
   		}
   		return true;
	}
}


if(!function_exists("CareersValidFileExtension")) {
	function CareersValidFileExtension($name){
		$allowed_extensions = explode(',', 'doc,pdf');
		$extension = strtolower(CareersGetExtension($name));
		if (in_array($extension, $allowed_extensions, TRUE)){
			return true;
		} else {
			return false;
		}
	}
}

if(!function_exists("CareersGetExtension")) {
	function CareersGetExtension($filename){
		$x = explode('.', $filename);
		return end($x);
	}
}
	
if(!function_exists("CareersSetFilename")) {
	function CareersSetFilename($path, $filename){
		$filename = CareersCleanFileName($filename);
		$file_ext = CareersGetExtension($filename);
		if ( ! file_exists($path.$filename)){
			return $filename;
		}
		$new_filename = str_replace('.'.$file_ext, '', $filename);
		for ($i = 1; $i < 300; $i++){			
			if ( ! file_exists($path.$new_filename.'_'.$i.'.'.$file_ext)){
				$new_filename .= '_'.$i.'.'.$file_ext;
				break;
			}
		}
		return $new_filename;
	}
}
	
if(!function_exists("CareersCleanFileName")) {
	function CareersCleanFileName($filename){
		$invalid = array("<!--","-->","'","<",">",'"','&','$','=',';','?','/',"%20","%22","%3c","%253c","%3e","%0e","%28","%29","%2528","%26","%24","%3f","%3b", "%3d");		
		$filename = str_replace($invalid, '', $filename);
		$filename = preg_replace("/\s+/", "_", $filename);
		return stripslashes($filename);
	}
}
	
if(!function_exists("CareersUploadError")) {
	function CareersUploadError($code){
		$response = '';	
		switch ($code) {
       			case UPLOAD_ERR_INI_SIZE:
            			$response = __('The uploaded file exceeds maximum filesize.','wp_careers_plugin');
            			break;
       			case UPLOAD_ERR_FORM_SIZE:
            			$response = __('The uploaded file exceeds maximum filesize.','wp_careers_plugin');
            			break;
        		case UPLOAD_ERR_PARTIAL:
            			$response = __('The uploaded file was only partially uploaded.','wp_careers_plugin');
            			break;
        		case UPLOAD_ERR_NO_FILE:
            			$response = __('No file was uploaded.','wp_careers_plugin');
           			break;
        		case UPLOAD_ERR_NO_TMP_DIR:
           			$response = __('Missing a tmp folder.','wp_careers_plugin');
            			break;
        		case UPLOAD_ERR_CANT_WRITE:
            			$response = __('Failed to write file to disk.','wp_careers_plugin');
            			break;
        		case UPLOAD_ERR_EXTENSION:
            			$response = __('File upload stopped by extension.','wp_careers_plugin');
            			break;
        		default:
            			$response = __('Unknown error.','wp_careers_plugin');
           			break;
   		}
 
    		return $response;
	}
}

if(!function_exists("CareersJobsNavIcons")) {
	function CareersJobsNavIcons($url, $id){
		global $wpdb;
		$table_name2 = "wp_careers_plugin_applications";
		$return = '<a href="'.$url.'&action=edit&id='.$id.'" title="'. __('Edit','wp_careers_plugin').'"><img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/pencil.png', __FILE__ ). '" /></a>&nbsp;&nbsp;
			<a href="'.$url.'&action=applicants&job_id='.$id.'" title="'. __('View Applications','wp_careers_plugin').'">
		<img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/reseller_programm.png', __FILE__ ).'" /></a>['. count($wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = ".$id." AND status = 'verified' AND seen ='n'")).'/'. count($wpdb->get_results("SELECT * FROM $table_name2 WHERE job_id = ".$id." AND status = 'verified'")).']
			&nbsp;&nbsp;<a title="'. __('Mass Email','wp_careers_plugin').'" href="'.$url.'&action=email&id='.$id.'"><img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/mail_green.png', __FILE__ ).'" /></a>
			&nbsp;&nbsp;<a title="'. __('Mass Download CVs','wp_careers_plugin').'" href="'.$url.'&action=zip&id='.$id.'"><img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/file_extension_zip.png', __FILE__ ).'" /></a>
			&nbsp;&nbsp;<a title="'. __('Export Applicants Details as CSV','wp_careers_plugin').'" href="'.$url.'&action=csv&id='.$id.'"><img style="vertical-align:middle" width="24px" height="24px" src="'. plugins_url( '/file_extension_xls.png', __FILE__ ).'" /></a>';
		
		return $return;
	}
}

if(!function_exists("careers_slugify")) {
function careers_slugify($text)
{ 
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
}
}

add_action('init', 'CareersStartSession', 1);
function CareersStartSession() {
    if(!session_id()) {
        session_start();
    }
}

global $careers_db_version;
$careers_db_version = "1.4";

require 'careers_page.php';
require 'careers_db.php';
require 'captcha.php';
require 'careers_zip_function.php';

register_activation_hook(__FILE__,'careers_install');
register_activation_hook(__FILE__,'careers_create_the_page');

function careers_do_this_hourly() {
	global $wpdb;
	$table_name = "wp_careers_plugin_jobs";
	
	global $blog_id;
	if(!$blog_id){
		$blog_id = 'no_id';
	}
	
	$jobs = $wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND end_date < '" . date('Y-m-d H:i:s') . "'");
	foreach($jobs as $j){
		if(strtotime($j->end_date) > 1){
			$wpdb->update($table_name, array('status'=>'inactive'), array('id'=>intval($j->id)), array("%s"));
		}
	}
}

careers_do_this_hourly();

require 'careers_widget.php';
add_action('widgets_init', 'careers_widget');

require 'careers_admin.php';

require 'careers_shortcode.php';
add_shortcode('careers_page', 'careers_page_function');

function careers_styles(){
	wp_register_style('careers-custom-style', plugins_url( '/careers_frontend.css', __FILE__ ), array(), '1', 'all' );
	wp_enqueue_style('careers-custom-style');
}

add_action('wp_enqueue_scripts', 'careers_styles');

