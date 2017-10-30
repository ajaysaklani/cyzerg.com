<?php

function careers_install() {
	global $wpdb;
	global $careers_db_version;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$sql = "CREATE TABLE wp_careers_plugin_jobs (
id int(11) NOT NULL AUTO_INCREMENT,
title text NOT NULL,
description text,
created_on timestamp NULL DEFAULT NULL,
status varchar(20) NOT NULL,
end_date timestamp NULL DEFAULT NULL,
blog_id VARCHAR(20) NOT NULL DEFAULT 'no_id',
quiz text,
city text,
state text,
country text,
form_id int(11) NOT NULL DEFAULT '0',
tag_id int(11) NOT NULL DEFAULT '0',
PRIMARY KEY  (id)
);";

	dbDelta($sql);
	
$sql = "CREATE TABLE wp_careers_plugin_applications (
id int(11) NOT NULL AUTO_INCREMENT,
job_id int(11) NOT NULL,
name text,
email text,
phone text,
status text,
shortlist int(11) NOT NULL DEFAULT '0',
cv text,
cover_letter text,
hash text,
added_on timestamp NULL DEFAULT NULL,
seen VARCHAR(1) NOT NULL DEFAULT 'n',
notes text,
rating int(11) NOT NULL DEFAULT '0',
PRIMARY KEY  (id)
);";

	dbDelta($sql);
$sql = "CREATE TABLE wp_careers_plugin_forms (
id int(11) NOT NULL AUTO_INCREMENT,
title text NOT NULL,
heading text,
blog_id VARCHAR(20) NOT NULL DEFAULT 'no_id',
PRIMARY KEY  (id)
);";

	dbDelta($sql);

$sql = "CREATE TABLE wp_careers_plugin_form_elements (
id int(11) NOT NULL AUTO_INCREMENT,
title text NOT NULL,
form_id int(11) NOT NULL,
type text,
is_required varchar(1) NOT NULL DEFAULT 'n',
is_on_list varchar(1) NOT NULL DEFAULT 'n',
is_email varchar(1) NOT NULL DEFAULT 'n',
order_by int(11) NOT NULL DEFAULT '9999',
PRIMARY KEY  (id)
);";

	dbDelta($sql);
	
$sql = "CREATE TABLE wp_careers_plugin_form_element_choices (
id int(11) NOT NULL AUTO_INCREMENT,
form_id int(11) NOT NULL,
form_element_id int(11) NOT NULL,
choice text,
PRIMARY KEY  (id)
);";

	dbDelta($sql);
	
$sql = "CREATE TABLE wp_careers_plugin_form_results (
id int(11) NOT NULL AUTO_INCREMENT,
form_id int(11) NOT NULL,
form_element_id int(11) NOT NULL,
job_id int(11) NOT NULL,
app_id int(11) NOT NULL,
answer text,
PRIMARY KEY  (id)
);";

	dbDelta($sql);
	
$sql = "CREATE TABLE wp_careers_plugin_tags (
id int(11) NOT NULL AUTO_INCREMENT,
name text,
blog_id VARCHAR(20) NOT NULL DEFAULT 'no_id',
PRIMARY KEY  (id)
);";

	dbDelta($sql);

	
	add_option("careers_db_version", $careers_db_version);
}

if(get_option('careers_db_version') AND get_option('careers_db_version') != $careers_db_version){
	careers_install();
}
