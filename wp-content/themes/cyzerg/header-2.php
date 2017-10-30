<?php if ( ! isset( $_SESSION ) ) session_start(); ?>
<!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<title><?php elegant_titles(); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<!-- begin Convert Experiments code--><script type="text/javascript">var _conv_host = (("https:" == document.location.protocol) ? "https://d9jmv9u00p0mv.cloudfront.net" : "http://cdn-1.convertexperiments.com");document.write(decodeURIComponent("%3Cscript src='" + _conv_host + "/js/10013191-10012686.js' type='text/javascript'%3E%3C/script%3E"));</script><!-- end Convert Experiments code -->
	<?php elegant_description(); ?>
	<?php elegant_keywords(); ?>
	<?php elegant_canonical(); ?> 
	<?php do_action( 'et_head_meta' ); ?>
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico">	
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php $template_directory_uri = get_template_directory_uri(); ?>
	<!--[if lt IE 9]>
	<script src="<?php echo esc_url( $template_directory_uri . '/js/html5.js"' ); ?>" type="text/javascript"></script>
	<![endif]-->
	<script type="text/javascript">
		document.documentElement.className = 'js';
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script type="text/javascript">
	jQuery( document ).ready(function($) {
 if (screen.width <= 767) {
	  $("#home-testimonials .tslider blockquote p").css({"font-size":"20px", "line-height":"26px","padding":"0","text-align":"center"});
		  /*  
		   #home-testimonials .tslider blockquote p { font-size:20px; line-height: 26px; padding: 0; text-align: center;}  */
           }
});
	</script>
<script type="text/javascript" class="secret-source">
  function getHeight(className)
  {
		var height = $("."+className).height();
		var half = parseInt("-"+ (height / 2));
		$('.'+className).css('position', 'absolute');
		$('.'+className).css('top', '50%');
		$('.'+className).css('margin-top', half);
  }
	jQuery(document).ready(function($) {
	   jQuery('#it-services .et_pb_column:last-child').addClass('information');
	   jQuery('#system-scalability .et_pb_column:last-child').addClass('information1');
	   jQuery('#technology-services .et_pb_column:last-child').addClass('information2');
	   jQuery('#logistic-solutions .et_pb_column:last-child').addClass('information3');
	   getHeight('information');
	   getHeight('information1');
	   getHeight('information2');
		getHeight('information3');
	});
	
 </script>

<script type="text/javascript">
/***********************************************
* Different CSS depending on OS (mac/pc)- � Dynamic Drive (www.dynamicdrive.com)
* This notice must stay intact for use
* Visit http://www.dynamicdrive.com/ for full source code
***********************************************/
var csstype="external" //Specify type of CSS to use. "Inline" or "external"
var mac_css='' //if "inline", specify mac css here
var pc_css='' //if "inline", specify PC/default css here
var mac_externalcss='/wp-content/themes/cyzerg/macstyle.css' //if "external", specify Mac css file here
var pc_externalcss=''; //if "external", specify Mac css file here
//////No need to edit beyond here////////////
var mactest=navigator.userAgent.indexOf("Mac")!=-1
if (csstype=="inline"){
document.write('<style type="text/css">')
if (mactest)
document.write(mac_css)
else
document.write(pc_css)
document.write('</style>')
}
else if (csstype=="external")
document.write('<link rel="stylesheet" type="text/css" href="'+ (mactest? mac_externalcss : pc_externalcss) +'">')

	
jQuery(window).load(function($) {
	document.domain = document.domain;
var heartCss = 'form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form{ clear:both; max-width:100%; width:100%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset{ max-width:100%; width:100%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-1 .input, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .input, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs_mobilephone, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .input{ margin:0;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset label{ display:none;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset .inputs-list label{ display:block;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs-form-field{ position:relative; width:48.3%;} .submitted-message{ color:#fff; font-family: "Open Sans",sans-serif; font-size:15px; text-align:center;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs_firstname{ margin-right:30px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="text"], form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="email"], form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="tel"]{ background:#fff; border:none; border-radius:2px; color:#000000; font-family: "Open Sans",sans-serif; font-size:14px; margin-bottom:30px; max-width:100%; padding:16px 17px; width:100%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs-form-field{ margin-right:30px; max-width:31%!important; position:relative; width:100%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs_mobilephone, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .input{ margin-right:0;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-1 textarea.hs-input{ border:none; border-radius:2px; color:#000; font-family: "Open Sans", sans-serif; font-size:14px; height:150px; margin:0 0 44px; max-width:100%; padding:16px 17px; resize:none; width:100%; overflow:auto;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset ul{ font-family: "Open Sans",sans-serif; line-height:26px; list-style:none; margin:0; padding:0; text-align:left;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset ul input{ margin-right:9px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form .hs_submit{ float:right; margin-top:-29px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="submit"]{ background:#f78f20; border:none; border-radius:0; color:#fff; cursor:pointer; font-size:17px; font-weight:700; padding:11px 19px; text-transform:uppercase; -webkit-appearance:none; width:113px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="submit"]:hover{ background:#fff; color:#000;} .hs-error-msgs.inputs-list{ left:0; position:absolute; top:53px;} .hs-error-msgs.inputs-list label{ color:#78a6ef; font-size:12px; line-height:normal;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="checkbox"]{ -webkit-appearance:checkbox;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form ul span{ color:#fff;} @media (max-width: 909px) { form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs_firstname, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs-form-field{ margin-right:15px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs_mobilephone.hs-form-field{ margin-right:0;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs-form-field{ width: 48.8%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs-form-field{ max-width:240px!important;}} @media (max-width: 767px) { form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs-form-field{ width:100%;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-2 .hs_firstname, form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs-form-field{ margin-right:0;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="text"], form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="email"], form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form input[type="tel"]{ margin-bottom:22px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-1 textarea.hs-input{ margin-bottom:15px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form fieldset.form-columns-3 .hs-form-field{ max-width:100%!important;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form .hs_submit{ margin-top:15px;} form#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1.hs-form ul span{ font-size:14px; line-height:normal;}}';	
	
  var css = '<style type="text/css">'+heartCss+'</style>';
  jQuery('#heartbeat').contents().find("head").append(css);
  jQuery('#heartbeat').contents().find("body").on('submit','#hsForm_bcf7c5d1-1602-49a5-88dc-d5a7aca4c094_1',function(){
		setTimeout(function(){
       var hasErrorMsg = jQuery('#heartbeat').contents().find("body .hs-error-msgs");
	    var hubEmail = jQuery('#heartbeat').contents().find("body input[type='email']");
	   if(hasErrorMsg.length == 0){
		jQuery("#heartbeat").attr('class','hub-submit');
		jQuery('#heartbeat').contents().find("body :input").hide('fast');
		}
      },800);
  });
 }); 
</script>
<!--[if lte IE 8]>
<script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2-legacy.js"></script>
<![endif]-->
<script charset="utf-8" type="text/javascript" src="//js.hsforms.net/forms/v2.js"></script>

<?php wp_head(); ?>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css">
<link href='https://fonts.googleapis.com/css?family=Quicksand:300,400,700' rel='stylesheet' type='text/css'>
<!--<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic' rel='stylesheet' type='text/css'>-->
<link href="https://cdn2.hubspot.net/hubfs/2199347/cyzerg_css/opensans.css" rel="stylesheet" />
	
	<!-- bxSlider Javascript file -->
	<script src="<?php bloginfo("stylesheet_directory");?>/js/jquery.bxslider.js"></script>
	<!-- bxSlider CSS file -->
	<link href="https://cdn2.hubspot.net/hubfs/2199347/cyzerg_css/jquery.bxslider.css" rel="stylesheet" />
		<script type="text/javascript"> 
		jQuery(document).ready(function($){
			$('.bxslider').bxSlider({
				slideWidth: 320,
				startSlide: 0,
				randomStart: false,
				minSlides: 1,
				maxSlides: 1,
				slideMargin: 12
			});
		});
	</script>
	<script type="text/javascript">stLight.options({publisher: "INSERTYOURPUBKEY", doNotHash:true, 
doNotCopy:true,hashAddressBar:false});</script>
 

</head>
<?php if(is_page()) { $page_slug = 'page-'.$post->post_name; } ?>
<body <?php body_class($page_slug); ?>>
    <?php if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) { gtm4wp_the_gtm_tag(); } ?>
	<header id="main-header">
		<div class="container clearfix">
		<?php
			$logo = ( $user_logo = et_get_option( 'divi_logo' ) ) && '' != $user_logo
				? $user_logo
				: $template_directory_uri . '/images/logo.png';
		?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_attr( $logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" id="logo" />
			</a>
			<div id="et-top-navigation" class="topmain-nav">
				<nav id="top-menu-nav">
				<?php
					$menuClass = 'nav';
					if ( 'on' == et_get_option( 'divi_disable_toptier' ) ) $menuClass .= ' et_disable_top_tier';
					$primaryNav = '';

					$primaryNav = wp_nav_menu( array( 'theme_location' => 'primary-menu', 'container' => '', 'fallback_cb' => '', 'menu_class' => $menuClass, 'menu_id' => 'top-menu', 'echo' => false ) );

					if ( '' == $primaryNav ) :
				?>
					<ul id="top-menu" class="<?php echo esc_attr( $menuClass ); ?>">
						<?php if ( 'on' == et_get_option( 'divi_home_link' ) ) { ?>
							<li <?php if ( is_home() ) echo( 'class="current_page_item"' ); ?>><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'Divi' ); ?></a></li>
						<?php }; ?>
						<?php show_page_menu( $menuClass, false, false ); ?>
						<?php show_categories_menu( $menuClass, false ); ?>
					</ul>
				<?php
					else :
						echo( $primaryNav );
					endif;
				?>
				</nav>
				<div id="et_top_search">
					<span id="et_search_icon"></span>
					<form role="search" method="get" class="et-search-form et-hidden" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php
						printf( '<input type="search" class="et-search-field" placeholder="%1$s" value="%2$s" name="s" title="%3$s" />',
							esc_attr_x( 'Search &hellip;', 'placeholder', 'Divi' ),
							get_search_query(),
							esc_attr_x( 'Search for:', 'label', 'Divi' )
						);
					?>
					</form>			
				</div>
				<!--<div id="call-us" style="display:none;">
					<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('call-top-page') ) : endif; ?>   				
				</div>-->
				<div id="question-top">
					<!--<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('question-top') ) : endif; ?>-->
					<!--HubSpot Call-to-Action Code -->
					<span class="hs-cta-wrapper" id="hs-cta-wrapper-212de9ab-a669-43d3-87da-3b3a7e10fe96">
						<span class="hs-cta-node hs-cta-212de9ab-a669-43d3-87da-3b3a7e10fe96" id="hs-cta-212de9ab-a669-43d3-87da-3b3a7e10fe96">
							<!--[if lte IE 8]><div id="hs-cta-ie-element"></div><![endif]-->
							<a href="http://cta-redirect.hubspot.com/cta/redirect/2199347/212de9ab-a669-43d3-87da-3b3a7e10fe96" ><img class="hs-cta-img" id="hs-cta-img-212de9ab-a669-43d3-87da-3b3a7e10fe96" style="border-width:0px;" src="https://no-cache.hubspot.com/cta/default/2199347/212de9ab-a669-43d3-87da-3b3a7e10fe96.png"  alt="New Call-to-action"/></a>
						</span>
						<script charset="utf-8" src="https://js.hscta.net/cta/current.js"></script>
						<script type="text/javascript">
							hbspt.cta.load(2199347, '212de9ab-a669-43d3-87da-3b3a7e10fe96', {});
						</script>
					</span>
					<!-- end HubSpot Call-to-Action Code -->
				</div> 
				<?php do_action( 'et_header_top' ); ?>
			</div> <!-- #et-top-navigation -->
		</div> <!-- .container -->
	</header> <!-- #main-header -->