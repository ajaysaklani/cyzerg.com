<?php
function more_button( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="more_button">' . do_shortcode($content) . '</div>';
}
add_shortcode('more_button', 'more_button');

register_sidebar( array(
		'name' => __( 'Footer Bottom', 'twentyeleven' ),
		'id' => 'footer_bottom',
		'description' => __( 'Footer Bottom', 'twentyeleven' ),		
	) );
	
register_sidebar( array(
		'name' => __( 'Footer Social', 'twentyeleven' ),
		'id' => 'footer_social',
		'before_widget' => '<div id="%1$s" class="widget  %2$s">',
    'after_widget' => '</div>',
		'description' => __( 'Footer Social', 'twentyeleven' ),		
	) );	
	
function one_thirds( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="et_pb_column et_pb_column_1_3 one_thirds">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_thirds', 'one_thirds');

function plans( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="plans-block">' . do_shortcode($content) . '</div>';
}
add_shortcode('plans', 'plans');

function request_btn( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="request_btn">' . do_shortcode($content) . '</div>';
}
add_shortcode('request_btn', 'request_btn');

function one_third( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="et_pb_column et_pb_column_1_3 one_third">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_third', 'one_third');

function address( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="address">' . do_shortcode($content) . '</div>';
}
add_shortcode('address', 'address');

function one_thirdlast( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="one_third one_thirdlast">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_thirdlast', 'one_thirdlast');

function clear( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="clear">' . do_shortcode($content) . '</div>';
}
add_shortcode('clear', 'clear');

function hourly_btns( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="hourly_btns">' . do_shortcode($content) . '</div>';
}
add_shortcode('hourly_btns', 'hourly_btns');

function blue_btn1( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="blue_btn1">' . do_shortcode($content) . '</div>';
}
add_shortcode('blue_btn1', 'blue_btn1');

function blue_btn( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'link'      => '#',
	), $atts));
	$content = "<a class=\"blue_btn\" href=\"" .$link. "\"><span>" .do_shortcode($content). "</span></a>";
	return $content;
}
add_shortcode('blue_btn', 'blue_btn');

function learn_more_button( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'link'      => '#',
	), $atts));
	$content = "<a class=\"learn_more_button\" href=\"" .$link. "\"><span>" .do_shortcode($content). "</span></a>";
	return $content;
}
add_shortcode('learn_more_button', 'learn_more_button');
function lightblue_btn( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'link'      => '#',
	), $atts));
	$content = "<a class=\"lightblue_btn\" href=\"" .$link. "\"><span>" .do_shortcode($content). "</span></a>";
	return $content;
}
add_shortcode('lightblue_btn', 'lightblue_btn');
function yellow_btn( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'link'      => '#',
	), $atts));
	$content = "<a class=\"yellow_btn\" href=\"" .$link. "\"><span>" .do_shortcode($content). "</span></a>";
	return $content;
}
add_shortcode('yellow_btn', 'yellow_btn');

function blue_cost( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="blue_cost">' . do_shortcode($content) . '</div>';
}
add_shortcode('blue_cost', 'blue_cost');

function quote( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="quote">' . do_shortcode($content) . '</div>';
}
add_shortcode('quote', 'quote');

function open_quote( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<span class="open-quote">' . do_shortcode($content) . '</span>';
}
add_shortcode('open_quote', 'open_quote');

function close_quote( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<span class="close-quote">' . do_shortcode($content) . '</span>';
}
add_shortcode('close_quote', 'close_quote');

register_sidebar( array(
		'name' => __( 'Blog Header', 'twentyeleven' ),
		'id' => 'blog-header',
		'description' => __( 'Blog Header', 'twentyeleven' ),		
	) );
	
register_sidebar( array(
		'name' => __( 'Search Header', 'twentyeleven' ),
		'id' => 'search-resultspage',
		'description' => __( 'Search Header', 'twentyeleven' ),		
	) );

register_sidebar( array(
		'name' => __( 'Newsletter Blog', 'twentyeleven' ),
		'id' => 'newsletter',
		'description' => __( 'Newsletter Blog', 'twentyeleven' ),		
	) );
	
register_sidebar( array(
		'name' => __( 'Search Footer Bottom', 'twentyeleven' ),
		'id' => 'search-footer-bottom',
		'description' => __( 'Search Footer Bottom', 'twentyeleven' ),		
	) );

register_sidebar( array(
		'name' => __( 'Search Page Top', 'twentyeleven' ),
		'id' => 'search-top-page',
		'description' => __( 'Search Page Top', 'twentyeleven' ),		
	) );
	
register_sidebar( array(
		'name' => __( 'Phone Page Top', 'twentyeleven' ),
		'id' => 'call-top-page',
		'description' => __( 'call Page Top', 'twentyeleven' ),	
 'before_widget' => '<div id="%1$s" class="widget  %2$s">',
    'after_widget' => '</div>',		
	) );
	
register_sidebar( array(
	'name' => __( 'Have a Question Top', 'twentyeleven' ),
	'id' => 'question-top',
	'description' => __( 'Have a Question CTA Button at top', 'twentyeleven' ),	
	'before_widget' => '<div id="%1$s" class="widget  %2$s">',
    'after_widget' => '</div>',		
) );
	
register_sidebar( array(
		'name' => __( 'Home Testimonials Slider', 'cyzerg' ),
		'id' => 'testimonial-home-page',
		'description' => __( 'Testimonial For Homepage', 'cyzerg' ),	
 'before_widget' => '<div id="%1$s" class="widget  %2$s">',
    'after_widget' => '</div>',		
	) );
	
register_sidebar( array(
		'name' => __( 'Home Testimonials Slider Mobile', 'cyzerg' ),
		'id' => 'testimonial-home-page-mobile',
		'description' => __( 'Testimonial For Homepage on Mobile', 'cyzerg' ),	
		'before_widget' => '<div id="%1$s" class="widget  %2$s">',
		'after_widget' => '</div>',		
	) );
	
register_sidebar( array(
		'name' => __( 'Home Recent Post Slider', 'cyzerg' ),
		'id' => 'recent-post-slider',
		'description' => __( 'Recent Post Slider On Homepage', 'cyzerg' ),	
 'before_widget' => '<div id="%1$s" class="widget  %2$s">',
    'after_widget' => '</div>',		
	) );
	
function blue_button( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="blue_button">' . do_shortcode($content) . '</div>';
}
add_shortcode('blue_button', 'blue_button');

function gry_button( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="blue_button gry_button">' . do_shortcode($content) . '</div>';
}
add_shortcode('gry_button', 'gry_button');

function et_pb_column_1_3( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="col-left et_pb_column et_pb_column_1_3">' . do_shortcode($content) . '</div>';
}
add_shortcode('et_pb_column_1_3', 'et_pb_column_1_3');

function et_pb_column_2_3( $atts, $content = null ) {
	$content = preg_replace('#^<\/p>|<p>$#', '', $content);
	return '<div class="col-right et_pb_column et_pb_column_2_3">' . do_shortcode($content) . '</div>';
}
add_shortcode('et_pb_column_2_3', 'et_pb_column_2_3');
function book_it( $atts, $content = null ) {
	$content = preg_replace('#^$#', '', $content);
	return '<div class="book_it">' . do_shortcode($content) . '</div>';
}
add_shortcode('book_it', 'book_it');

function sharpspring( $atts, $content = null ) {
	$content = preg_replace('#^$#', '', $content);
	return '<div class="blog-sharpspring">' . do_shortcode($content) . '</div>';
}
add_shortcode('sharpspring', 'sharpspring');

/* function my_wpcf7_form_elements($html) {
	$text = 'Please select...';
	$html = str_replace('<option value="">---</option>', '<option value="">' . $text . '</option>', $html);
	return $html;
}
add_filter('wpcf7_form_elements', 'my_wpcf7_form_elements'); */
function ov3rfly_replace_include_blank($name, $text, &$html) {
		$matches = false;
		preg_match('/<select name="' . $name . '"[^>]*>(.*)<\/select>/iU', $html, $matches);
		if ($matches) {
			$select = str_replace('<option value="">---</option>', '<option value="">' . $text . '</option>', $matches[0]);
			$html = preg_replace('/<select name="' . $name . '"[^>]*>(.*)<\/select>/iU', $select, $html);
		}
	}
function my_wpcf7_form_elements($html) {
	
	ov3rfly_replace_include_blank('company-size', 'Company Size', $html);
	ov3rfly_replace_include_blank('requesttype', 'Request Type', $html);
	ov3rfly_replace_include_blank('priority', 'Priority ', $html);
	return $html;
}
add_filter('wpcf7_form_elements', 'my_wpcf7_form_elements');


/* ActiveCampaign Forms Include */
/* steve@brilliantmetrics.com */

function include_form($atts) {
	//check the input and override the default filepath NULL
	//if filepath was specified
    extract(shortcode_atts(array(
        'filepath' => 'NULL', // the relative path to the form file
        'h3' => '' // the text to appear in the h3 heading at the top of the form
    ), $atts));

	//turn on output buffering to capture script output
	ob_start();
	//check if the filepath was specified and if the file exists
    if ($filepath!='NULL' && file_exists(get_stylesheet_directory().'/'.$filepath)){
    
        //include the specified file
        include(get_stylesheet_directory().'/'.$filepath);
    } else {
        echo "<p>file not found: " . get_stylesheet_directory().'/'.$filepath . "</p>";
    }

	//assign the file output to $content variable and clean buffer
	$content = ob_get_clean();
    
    //return the $content
	//return is important for the output to appear at the correct position
	//in the content
	return $content;
}
//register the Shortcode handler
add_shortcode('include_form', 'include_form');
// Add Shortcode


/* Add any needed scripts for the theme */
function cyzerg_scripts() {
    wp_enqueue_script( 'lockfixed-jquery', get_stylesheet_directory_uri() . '/js/jquery.hc-sticky.min.js', array('jquery'), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'cyzerg_scripts' );
/* function modify_jquery() {
if (!is_admin()) {
	wp_deregister_script('jquery');
	wp_register_script('jquery', 'https://code.jquery.com/jquery-1.11.3.min.js');
	wp_enqueue_script('jquery');
}
}
add_action('init', 'modify_jquery'); */

function modify_jquery() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', false, '1.10.2');
        wp_enqueue_script('jquery');
    }
}
add_action('init', 'modify_jquery');

function getTheHub() {
	return '<script type="text/javascript">
	 hbspt.forms.create({ 
		portalId: "2199347",
		formId: "bcf7c5d1-1602-49a5-88dc-d5a7aca4c094"
	  });
	</script>';
}
add_shortcode('get-the-hub-form', 'getTheHub');

function _remove_script_version( $src ){
$parts = explode( '?ver', $src );
return $parts[0];
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );

// Async load
function ikreativ_async_scripts($url)
{
    if ( strpos( $url, '#asyncload') === false )
        return $url;
    else if ( is_admin() )
        return str_replace( '#asyncload', '', $url );
    else
	return str_replace( '#asyncload', '', $url )."' async='async"; 
    }
add_filter( 'clean_url', 'ikreativ_async_scripts', 11, 1 );
?>
