<?php

if (!function_exists("paginate_three")) {
function paginate_three($reload, $page, $tpages, $adjacents = 3) {
	
	$out = "<p> <span class=\"paginition2\">Pages:</span> ";
	
	// first
	if($page>($adjacents+1)) {
		$out.= "<a class=\"paginition\" href=\"$reload\" rel=\"1\">1</a>";
	}
	
	// interval
	if($page>($adjacents+2)) {
		$out.= "<span class=\"paginition\">...</span>";
	}
	
	// pages
	$pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
	$pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
	for($i=$pmin; $i<=$pmax; $i++) {
		if($i==$page) {
			$out.= "<span class=\"paginition\">$i</span>";
		}else {
			$out.= "<a class=\"paginition\" href=\"$reload&pnum=$i\" >$i</a>";
		}
	}
	
	// interval
	if($page<($tpages-$adjacents-1)) {
		$out.= "<span class=\"paginition\">...</span>";
	}
	
	// last
	if($page<($tpages-$adjacents)) {
		$out.= "<a class=\"paginition\" href=\"$reload&pnum=$tpages\" >$tpages</a>";
	}
	
	$out.= "</p>";
	
	return $out;
}
}


require 'careers_jobs.php';
require 'careers_help.php';
require 'careers_forms.php';
require 'careers_tags.php';
require 'careers_settings.php';


if (!function_exists("CareersPluginPages")) {
	function CareersPluginPages() {
		if (function_exists('add_menu_page')) {
			add_menu_page('Wp Careers', 'Wp Careers', 'edit_pages', 'careers_jobs.php', 'CareersListExec', plugins_url( '/reseller_account.png', __FILE__ ));
		}
		
		if (function_exists('add_submenu_page')) {
			add_submenu_page('careers_jobs.php', 'Wp Careers', 'Application Forms', 'edit_pages', 'careers_forms.php', 'CareersFormsExec' );
		}
		
		if (function_exists('add_submenu_page')) {
			add_submenu_page('careers_jobs.php', 'Wp Careers', 'Job Categories', 'edit_pages', 'careers_tags.php', 'CareersTagsExec' );
		}
		
		if (function_exists('add_submenu_page')) {
			add_submenu_page('careers_jobs.php', 'Wp Careers', 'Getting Started', 'edit_pages', 'careers_help.php', 'CareersHelpExec' );
		}
		
		if (function_exists('add_submenu_page')) {
			add_submenu_page('careers_jobs.php', 'Wp Careers', 'Settings', 'edit_pages', 'careers_settings.php', 'CareersSettingsExec' );
		}
	}	
}

add_action('admin_menu', 'CareersPluginPages');

function careers_admin_init(){
 	wp_register_style('careers_admin_style', plugins_url( '/careers_admin.css', __FILE__ ), array(), '1', 'screen' );
 	wp_enqueue_style( 'careers_admin_style' );
 	
 	wp_register_style('careers_zebra_style', plugins_url( '/zebra/css/zebra_datepicker_metallic.css', __FILE__ ), array(), '1', 'screen' );
 	wp_enqueue_style( 'careers_zebra_style' );
 	
 	wp_register_script('careers_zebra_script', plugins_url( '/zebra/javascript/zebra_datepicker.js', __FILE__ ), array( 'jquery'), '1', false );
 	wp_enqueue_script('careers_zebra_script');
}
add_action('admin_init', 'careers_admin_init');
