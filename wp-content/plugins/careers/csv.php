<?php 
require('../../../wp-load.php');

if(!function_exists("array_to_csv")) {
function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"')
{
	$output = "";
	if (!is_array($array) or !is_array($array[0])) return false;
	
	//Header row.
	if ($header_row)
	{
		foreach ($array[0] as $key => $val)
		{
			//Escaping quotes.
			$key = str_replace($qut, "$qut$qut", $key);
			$output .= "$col_sep$qut$key$qut";
		}
		$output = substr($output, 1)."\n";
	}
	//Data rows.
	foreach ($array as $key => $val)
	{
		$tmp = '';
		foreach ($val as $cell_key => $cell_val)
		{
			//Escaping quotes.
			$cell_val = str_replace($qut, "$qut$qut", $cell_val);
			$tmp .= "$col_sep$qut$cell_val$qut";
		}
		$output .= substr($tmp, 1).$row_sep;
	}
	
	return $output;
}
}

$id = intval($_GET['id']);

if(!isset($_GET['csv_shortlist'])){
	echo 'Error';
	exit();
}

$job_zip_q = $wpdb->get_row("SELECT * FROM wp_careers_plugin_jobs WHERE id = $id ORDER BY id DESC LIMIT 0,1");

$email_apps = $wpdb->get_results("SELECT * FROM wp_careers_plugin_applications WHERE job_id = $id AND status = 'verified' AND shortlist = " . intval($_GET['csv_shortlist']));
$csv_fields = array();
				
$csv_fields[0] = array();


if(intval($job_zip_q->form_id) > 0){
	$forms_elements = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_elements WHERE form_id = ".intval($job_zip_q->form_id)." ORDER BY order_by ASC, id ASC");
	if($forms_elements){
		foreach($forms_elements as $forms_e){
			if($forms_e->type != 'File'){
				$csv_fields[0][] = str_replace(array(',','"'), " ",$forms_e->title);
			}
		}
	}
}else{
	$csv_fields[0][] = __('Name','wp_careers_plugin');
	$csv_fields[0][] = __('Email','wp_careers_plugin');
	$csv_fields[0][] = __('Phone','wp_careers_plugin');
}

$csv_fields[0][] = __('Rating','wp_careers_plugin');
$csv_fields[0][] = __('Notes','wp_careers_plugin');

if(count($email_apps) > 0){
	foreach($email_apps as $e){
		if(intval($job_zip_q->form_id) > 0){
			$row = array();
			
			if($forms_elements){
				foreach($forms_elements as $forms_e){
					switch($forms_e->type){
						case 'Checkbox':
							$some_results = $wpdb->get_results("SELECT * FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($e->id));
							if(count($some_results) > 0){
								$some_results_str = array();
								foreach($some_results as $sr){
									$some_results_str[] = $sr->answer;
								}
								$row[] = str_replace(array(',','"'), " ",implode("; ", $some_results_str));
							}else{
								$row[] = "";
							}
							break;
						case 'File':
							break;
						default:
							$row[] = str_replace(array(',','"'), " ",$wpdb->get_var("SELECT answer FROM wp_careers_plugin_form_results WHERE form_element_id = ".$forms_e->id." AND app_id = " . intval($e->id)));
							break;
					}
				}
			}
			
			$row[] = (intval($e->rating) / 20) . '/5 ' . __('Stars','wp_careers_plugin');
			
			$row[] = str_replace(array(',','"'), " ",$e->notes);
			
			$csv_fields[] = $row;
		}else{
			$csv_fields[] = array(0=>str_replace(array(',','"'), " ",$e->name),1=>str_replace(array(',','"'), " ",$e->email),2=>str_replace(array(',','"'), " ",$e->phone),3=>str_replace(array(',','"'), " ",$e->notes));
		}
	}
	
	$csv_name = careers_slugify($job_zip_q->title) . '-shortlist-level-'.intval($_GET['csv_shortlist']).'.csv';
				
				
    	$csv_data = array_to_csv($csv_fields, false);
    	header("Content-type: application/ofx");
	header("Content-Disposition: attachment; filename=$csv_name");
	echo $csv_data;
	exit;			
}else{
	echo 'Error';
	exit();
}
