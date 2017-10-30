<?php

function careers_create_the_page(){
	
	$_p = array();
        $_p['post_title'] = 'Careers';
        $_p['post_content'] = "[careers_page]";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1);

        $the_page_id = wp_insert_post($_p);
	
	add_option("careers_page_id", $the_page_id);
}

