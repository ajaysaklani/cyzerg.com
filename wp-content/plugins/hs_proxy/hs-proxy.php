<?php
/**
 * Plugin Name: Cyzerg HS Proxy
 * Plugin URI: http://cyzerg.com
 * Description: This plugin integrates Contact forms with HubSpot API
 * Version: 1.0.0
 * Author: Muhammad Umair
 * Author URI: http://www.cyzerg.com
 */
define('HS_PORTAL', '2199347');
define('SUBSCRIPTION_FORM_HS_GUID', '7cf45675-2fc3-49d4-94f4-d4bd9ed6913b');
define('SUBSCRIPTION_FORM_ID', '5903');

define('QUESTION_FORM_HS_GUID', 'bcf7c5d1-1602-49a5-88dc-d5a7aca4c094');
define('QUESTION_FORM_ID', '5920');

define('CONTACT_FORM_HS_GUID', 'ddb73555-6acd-4103-8a57-5bb118c5a564');
define('CONTACT_FORM_ID', '6893');

define('DOWNLOAD_EBOOK_FORM_HS_GUID', 'c81fdb47-66eb-4789-bc18-aae3cd60a06a');
define('DOWNLOAD_EBOOK_FORM_ID', '7060');

define('DOWNLOAD_EBOOK_FORM_HS_GUID_1', '1d2529be-b263-4140-8fb6-75751c6dacf7');
define('DOWNLOAD_EBOOK_FORM_ID_1', '7195');

define('DOWNLOAD_EBOOK_FORM_HS_GUID_2', '3bc29ab4-92d7-4bde-9a9f-fa2fd8d98844');
define('DOWNLOAD_EBOOK_FORM_ID_2', '7188');

define('DOWNLOAD_EBOOK_FORM_HS_GUID_3', '4c7f73f8-f5bf-4d7f-a051-ba912fad9793');
define('DOWNLOAD_EBOOK_FORM_ID_3', '7201');

define('DOWNLOAD_EBOOK_FORM_HS_GUID_4', '09f37484-68eb-4a3f-9e1a-34ef451a0d66');
define('DOWNLOAD_EBOOK_FORM_ID_4', '7205'); 

define('DOWNLOAD_EBOOK_FORM_HS_GUID_5', 'db78c117-ea46-42b1-a580-067539894302');
define('DOWNLOAD_EBOOK_FORM_ID_5', '7209'); 

define('FREE_TRIAL_FORM_HS_GUID', '0419605f-c386-48cb-bccf-1a90c72a0eaf');
define('FREE_TRIAL_FORM_ID', '7013');

define('FREE_TRIAL_FORM_HS_GUID_1', '5149bd5b-6f0d-4220-a94c-38938b4b930f');
define('FREE_TRIAL_FORM_ID_1', '7187');

define('FREE_TRIAL_FORM_HS_GUID_2', '39e4e6fe-c418-4cb1-a8b6-336657a3e92c');
define('FREE_TRIAL_FORM_ID_2', '7194'); 

define('FREE_TRIAL_FORM_HS_GUID_3', '77d51e23-4403-4945-bd97-98e60bbe3338');
define('FREE_TRIAL_FORM_ID_3', '7200'); 

define('FREE_TRIAL_FORM_HS_GUID_4', '908b490c-3fd7-4561-9c96-4ab9eaab9d45');
define('FREE_TRIAL_FORM_ID_4', '7204'); 

define('FREE_TRIAL_FORM_HS_GUID_5', 'fad791f7-be4c-4862-ae91-9000c980a874');
define('FREE_TRIAL_FORM_ID_5', '7208'); 

function _get_hs_context() {
    if(isset($_COOKIE['hubspotutk'])) {
        $hubspotutk = $_COOKIE['hubspotutk'];
    } else {
        $hubspotutk = "";
    }


    $ip_addr = $_SERVER['REMOTE_ADDR'];
    $hs_context = array(
        'hutk' => $hubspotutk,
        'ipAddress' => $ip_addr,
        'pageUrl' => 'http://www.cyzerg.com/',
        'pageName' => 'Logistics Technology Solutions & Services | Cyzerg'
    );

    return $hs_context_json = json_encode($hs_context);

}

function _post_data_to_hs($data, $endpoint) {
    $ch = @curl_init();
    @curl_setopt($ch, CURLOPT_POST, true);
    @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    @curl_setopt($ch, CURLOPT_URL, $endpoint);
    @curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded'
    ));
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch); 
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    @curl_close($ch);
}


function cyzerg_synchronize_hs ($wpcf7_data) {
    $submission = WPCF7_Submission::get_instance();
    

    if ( $submission ) {
        $input = $submission->get_posted_data();
        if($input['_wpcf7'] == SUBSCRIPTION_FORM_ID) {

        
        
            $firstname = ($input['firstname'])?$input['firstname']:"";
            if (!ctype_alpha($firstname)) {//avoid spams
                return;         
            }

            $lastname = ($input['lastname'])?$input['lastname']:"";
            $email = ($input['emailaddress'])?$input['emailaddress']:"";

            $hs_context_json = _get_hs_context();

        

            $str_post = "firstname=" . urlencode($firstname)
            . "&lastname=" . urlencode($lastname)
            . "&email=" . urlencode($email)
            . "&hs_context=" . urlencode($hs_context_json); ; 

            $endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . SUBSCRIPTION_FORM_HS_GUID;
            _post_data_to_hs($str_post, $endpoint);


        
        } else if(QUESTION_FORM_ID == $input['_wpcf7']  ) {
            $firstname = ($input['firstname'])?$input['firstname']:"";
            $lastname = ($input['lastname'])?$input['lastname']:"";
            $email = ($input['email'])?$input['email']:"";
            $company = ($input['company'])?$input['company']:"";
            $message = ($input['message'])?$input['message']:"";
            $mobilephone = ($input['mobilephone'])?$input['mobilephone']:"";
            $subscribe_to_our_mailing_list_to_get_the_updates_to_your_email_inbox_ = ($input['subscribe_to_our_mailing_list_to_get_the_updates_to_your_email_inbox_'])?$input['subscribe_to_our_mailing_list_to_get_the_updates_to_your_email_inbox_']:"";


            $hs_context_json = _get_hs_context();

            $str_post = "firstname=" . urlencode($firstname)
            . "&lastname=" . urlencode($lastname)
            . "&email=" . urlencode($email)
            . "&company=" . urlencode($company)
            . "&message=" . urlencode($message)
            . "&mobilephone=" . urlencode($mobilephone)
            . "&subscribe_to_our_mailing_list_to_get_the_updates_to_your_email_inbox_=" . urlencode($subscribe_to_our_mailing_list_to_get_the_updates_to_your_email_inbox_)
            . "&hs_context=" . urlencode($hs_context_json);

            $endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . QUESTION_FORM_HS_GUID;

            _post_data_to_hs($str_post, $endpoint);

        } else if(CONTACT_FORM_ID == $input['_wpcf7']  ) {
            $firstname = ($input['firstname'])?$input['firstname']:"";
                    $lastname = ($input['lastname'])?$input['lastname']:"";
                $email = ($input['email'])?$input['email']:"";
                    $mobilephone = ($input['mobilephone'])?$input['mobilephone']:"";
                    $message = ($input['message'])?$input['message']:"";
		    $select_department = ($input['select_department'])?$input['select_department']:"";
            


                    $hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                    . "&lastname=" . urlencode($lastname)
                    . "&email=" . urlencode($email)
                    . "&message=" . urlencode($message)
                    . "&mobilephone=" . urlencode($mobilephone)
	      	    . "&select_department=" . urlencode($select_department)
                    . "&hs_context=" . urlencode($hs_context_json);

                    $endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . CONTACT_FORM_HS_GUID;

                    _post_data_to_hs($str_post, $endpoint);
        } else if(DOWNLOAD_EBOOK_FORM_ID == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID;

            	_post_data_to_hs($str_post, $endpoint);
        } else if(DOWNLOAD_EBOOK_FORM_ID_1 == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID_1; 

            	_post_data_to_hs($str_post, $endpoint);
        }else if(DOWNLOAD_EBOOK_FORM_ID_2 == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID_2; 

            	_post_data_to_hs($str_post, $endpoint);
        }
		
		else if(DOWNLOAD_EBOOK_FORM_ID_3 == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID_3; 

            	_post_data_to_hs($str_post, $endpoint);
        }
		
		else if(DOWNLOAD_EBOOK_FORM_ID_4 == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID_4; 

            	_post_data_to_hs($str_post, $endpoint);
        }		
			else if(DOWNLOAD_EBOOK_FORM_ID_5 == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
                $email = ($input['email'])?$input['email']:"";  
       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . DOWNLOAD_EBOOK_FORM_HS_GUID_5; 

            	_post_data_to_hs($str_post, $endpoint);
        }
		
		
		else if(FREE_TRIAL_FORM_ID == $input['_wpcf7']  ) {
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID;

            	_post_data_to_hs($str_post, $endpoint);
        } else if(FREE_TRIAL_FORM_ID_1 == $input['_wpcf7']  ) {
			
			
			
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID_1;

            	_post_data_to_hs($str_post, $endpoint);
        } else if(FREE_TRIAL_FORM_ID_2 == $input['_wpcf7']  ) {
		
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID_2;

            	_post_data_to_hs($str_post, $endpoint);
        } 
		
		else if(FREE_TRIAL_FORM_ID_3 == $input['_wpcf7']  ) {
		
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID_3;

            	_post_data_to_hs($str_post, $endpoint);
        } 
		
		else if(FREE_TRIAL_FORM_ID_4 == $input['_wpcf7']  ) {
		
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID_4;

            	_post_data_to_hs($str_post, $endpoint);
        } 
		
		else if(FREE_TRIAL_FORM_ID_5 == $input['_wpcf7']  ) {
		
       		$firstname = ($input['firstname'])?$input['firstname']:"";
              	$mobilephone = ($input['phone'])?$input['phone']:"";
                $email = ($input['email'])?$input['email']:"";

       		$hs_context_json = _get_hs_context();

                $str_post = "firstname=" . urlencode($firstname)
                          . "&email=" . urlencode($email)
			  . "&mobilephone=" . urlencode($mobilephone)
                          . "&hs_context=" . urlencode($hs_context_json);

            	$endpoint = 'https://forms.hubspot.com/uploads/form/v2/' . HS_PORTAL . '/' . FREE_TRIAL_FORM_HS_GUID_5;

            	_post_data_to_hs($str_post, $endpoint);
        }
		
    }

    $wpcf7_data->skip_mail = true;
}

add_action("wpcf7_mail_sent", "cyzerg_synchronize_hs"); 
