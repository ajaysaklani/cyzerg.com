<?php

function CareersHelpExec(){
?>
<div class="wrap" style="padding: 20px;">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /><?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Gettings Started','wp_careers_plugin'); ?>
	</h2>
	
	<br/>
<p>This plugin is ment to help you solicit and collect job applications through your wordpress site.</p>

<h3>Features</h3>

<ol>
	<li>Creates a page to list jobs and receive applications</li>
	<li>Forms protected by captcha</li>
	<li>Sidebar widget showing latest jobs</li>
	<li>Send mass emails to applicants</li>
	<li>Download shortlisted resumes as ZIP</li>
	<li>Export applicants list as CSV</li>
	<li>Successively filter applications using shortlist levels</li>
	<li>Specify date to stop receiving applications</li>
</ol>
<br/>
<h3>Step 0: On activation of the plugin</h3>
<p>When this plugin is activated a page is created on the frontend titled "Careers". The page's content is a shortcode: [careers_page]</p>
<p>The purpose of this page is to list the jobs, display the individual jobs and receive the applications</p>
<p>You might want to add the page to your navigation menu.</p>
<?php
		if(get_option('careers_page_id')){
			$permalink_o = get_permalink(get_option('careers_page_id'));
		}else{
			$permalink_o = '';
		}
?>
<p>In your case, this is the  page: <a href="<?php echo $permalink_o;?>" target="_blank">Click Here</a><p>

<br/>
<h3>Step 1: Create a job</h3>
<p><a href="admin.php?page=careers_jobs.php&action=add">Click here</a> to create a new job.</p>
<p>To create other jobs you will need to click on the "Wp Careers" in the wordpress admin dasboard and then click on the plus icon in the heading that looks like this <img style="vertical-align:middle" src="<?php echo plugins_url( '/add.png', __FILE__ );?>" /></p>

<br/>
<h3>Step 2: Add the widget to the sidebar</h4>
<p>This plugin has a sidebar widget that you can use to display the 5 latest jobs. The title of the widget is "Careers Widget".</p>
<p>You can add it now and the job you have created will be listed.</p>

<br/>
<h3>Step 3: Receiving applications</h3>
<p>When a user submits an application they will need to confirm their email address. An email is sent to their account and they will then need to click on a link.</p>
<p>After the user applicant has confirmed their email address, you will be able to view the application in the admin area. You will need to click on the "Wp Careers" in the wordpress admin dasboard for you to be take to the page that lists the jobs and then, on the row of the job you want, click on the icon that looks like this <img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_programm.png', __FILE__ );?>" /></p>
<p>There are two numbers next to this icon. The first is a count of applications you have not seen and the next one is the total count for applications submitted for the job.</p>


<br/>
<h3>Step 4: Processing applicants</h3>
<h4>Short listing</h4>
<p>This plugin in allows you to created ordered short lists. You can use them to determine who close the candidates are to being hired.</p> 
<p>The short list are label from Level 0 to as high as you will need. Level 0 is the level that all the applications start at by default.</p>
<p>If you follow the instructions in Step 3 you will be able to get to the page for an individual applications. To move an application to a higher short list simply click on the button <a class="button-primary" href="#">Move Up &#8593;</a></p>
<h4>Mass Email, Mass Resume Download and Export to CSV</h4>
<p>These three functions will be explained together since the procedure is essentially the same for all three.</p>
<p>After you have moved the applications to short lists, you might want to send rejection emails, send acceptance emails, download resumes for offline process and Export the names and contacts of the applications in the various short list.</p>
<p>Performing these tasks is very simple. Click on the "Wp Careers" menu in the dashboard and you will be taken to a page that lists the jobs. On the row for each job click:</p>
<p><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/mail_green.png', __FILE__ ) ?>" /> for sending mass emails</p>
<p><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/file_extension_zip.png', __FILE__ ) ?>" /> for downloading a ZIP of resumes</p>
<p><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/file_extension_xls.png', __FILE__ ) ?>" /> for exporting applicants details</p>
<p>On the page that you will be directed to, select the short list that you want to work with and the click on the submit button.</p>
<br/>
<p>Thank you for the purchase and incase you have any question, feel free to email me at robertnduati.karanja@gmail.com</p>
</div>
<?php
}
