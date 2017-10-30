<?php
/*
Plugin Name: HS Blog Feed
Plugin URI: http://www.cyzerg..com/
Description: This plugin displays HS Blog feed.
Version: 1.0
Author: Muhammad Umair
Author URI: http://www.tezrosolutions.com
License: GPL
*/
define("HS_API_KEY", "7601f1fa-0377-42d3-846e-bfd74bfa24c1");

add_shortcode('hsblogitem', 'hsblogitem_func');

function hsblogitem_func( $atts ) {
	$args = shortcode_atts( array(
        	'id' => 0,
    	), $atts );

    $id = $args['id'];

	$url = 'https://api.hubapi.com/content/api/v2/blog-posts?hapikey=7601f1fa-0377-42d3-846e-bfd74bfa24c1&order_by=-publish_date&content_group_id=4026147924&limit=1&offset=0&state=published';

    if($id > 0) {
	$url =  'https://api.hubapi.com/content/api/v2/blog-posts/'. $id .'?hapikey=7601f1fa-0377-42d3-846e-bfd74bfa24c1';
    }



   $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    
    $posts = json_decode($resp);
    $markup = '';
    


if(isset($posts->objects)) {
    foreach($posts->objects as $post) {
        $body = substr(strip_tags($post->post_body), 0, 100);
        $time = gmdate("M d, Y", ($post->publish_date/1000));
        $markup .= '<div class="latest-blogs-sidebar"><div class="image"><img class="lazy" data-original="'.$post->featured_image.'" alt="Warehouse Mobile" width="300" height="169" class="alignnone size-medium wp-image-7036" /></div><div class="title"><a href="'.$post->url.'" target="_blank"> '.$post->name.'</a></div><!--//title --><div class="timse-date-general"><span class="author">BY '.$post->blog_author->full_name.' | '. $time .' </span></div><!--//timse-date-general --><div class="desc"><div class="excerpt">'.$body.'.....<a href="'.$post->url.'" target="_blank"> Read More</a></div></div><!--//desc --></div><!--// latest-blogs-sidebar--></div><!--// Right Sidebar -->';
    }
} else {
	$post = $posts;
 $body = substr(strip_tags($post->post_body), 0, 100);
        $time = gmdate("M d, Y", ($post->publish_date/1000));
	$markup .= '<div class="latest-blogs-sidebar"><div class="image"><img class="lazy" data-original="'.$post->featured_image.'" alt="Warehouse Mobile" width="300" height="169" class="alignnone size-medium wp-image-7036" /></div><div class="title"><a href="'.$post->url.'" target="_blank"> '.$post->name.'</a></div><!--//title --><div class="timse-date-general"><span class="author">BY '.$post->blog_author->full_name.' | '. $time .' </span></div><!--//timse-date-general --><div class="desc"><div class="excerpt">'.$body.'.....<a href="'.$post->url.'" target="_blank"> Read More</a></div></div><!--//desc --></div><!--// latest-blogs-sidebar--></div><!--// Right Sidebar -->';
}
    
    $markup .= '</div>';
     
    return $markup;
}

         
add_shortcode( 'hsfeed', 'hsfeed_func' );

function hsfeed_func( $atts ) {
	// Get cURL resource
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.hubapi.com/content/api/v2/blog-posts?hapikey=7601f1fa-0377-42d3-846e-bfd74bfa24c1&order_by=-publish_date&content_group_id=4026147924&limit=8&offset=0&state=published',
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    
    $posts = json_decode($resp);
    $markup = '<h1 style="text-align: center; display:none;">Logistics Technology & Business Insights</h1><div id="blog-listing" class="latest-blog-listing">';
    

    
    foreach($posts->objects as $post) {
        $body = substr(strip_tags($post->post_body), 0, 100);
        $time = gmdate("M d, Y", ($post->publish_date/1000));
        $markup .= '<div class="left-f"><div class="image"><a href="'.$post->url.'" target="_blank"> <img class="lazy" data-original="'.$post->featured_image.'" alt="" height="195" /> </a></div><!--// image --><div class="title"><a href="'.$post->url.'"> '.$post->name.' </a></div><!--// title--><div class="timse-date-general"><span class="author">BY '.$post->blog_author->full_name.' | '. $time .' </span></div><!--// image --><div class="desc"><div class="excerpt">'.$body.'.....<a href="'.$post->url.'" target="_blank"> Read More</a></div></div><!-- //desc --></div><!--//span4 left-f-->';
    }
    
    $markup .= '</div>';
     
    return $markup;
}


/** 
* Adding scripts and styles 
**/
function hbf_adding_styles() {
    wp_register_script('owl-css-2', plugins_url('includes/owl-carousel/owl.carousel.css', __FILE__));
    wp_enqueue_script('owl-css-2');
    
    wp_register_script('owl-css-1', plugins_url('includes/owl-carousel/owl.theme.css', __FILE__));
    wp_enqueue_script('owl-css-1');
}
//add_action( 'wp_enqueue_scripts', 'hbf_adding_styles' );  


function hbf_adding_scripts() {
    //wp_register_script('owl-js', plugins_url('includes/owl-carousel/owl.carousel.min.js', __FILE__), array('jquery'),'1.1', true);
    //wp_enqueue_script('owl-js');
    
    wp_register_script('lazyload-js', plugins_url('includes/lazyload/jquery.lazyload.min.js', __FILE__), array('jquery'),'1.1', true);
    wp_enqueue_script('lazyload-js');
}
add_action( 'wp_enqueue_scripts', 'hbf_adding_scripts' );  


function hbf_inline_script() {
  //if ( wp_script_is( 'owl-carousel-init', 'done' ) ) {
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("img.lazy").lazyload();

        setTimeout(function() {
        $("#blog-listing").owlCarousel({
                            loop: false,
                            nav: false,
                            navSpeed: 800,
                            dots: true,
                            dotsSpeed: 800,
                            lazyLoad: false,
                            autoplay: false,
                            autoplayHoverPause: true,
                            autoplayTimeout: 1200,
                            autoplaySpeed:  1000,
                            margin: 30,
                            stagePadding: 0,
                            freeDrag: false,
                            mouseDrag: false,
                            touchDrag: true,
                            slideBy: 3,
                            fallbackEasing: "swing",
                            responsiveClass: true,
                            navText: [ "previous", "next" ],
                            responsive:{
                                0:{items: 1},
                                600:{items: 2},
                                1000:{items: 3}
                                
                            },
                            autoHeight: true
                        });
    }, 2000);
    });
</script>
<?php
  //}
}
add_action( 'wp_footer', 'hbf_inline_script' );
?>