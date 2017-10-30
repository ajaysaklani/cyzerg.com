<?php
/*
Template Name: HomePage New2
*/
?>
<?php

get_header(2);

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() ); ?>

<div class="job-banner"> <div class="container-fluid"><?php echo do_shortcode('[types field="job-banner" id=""][/types]') ?></div> </div>

<div id="main-content">

<?php if ( ! $is_page_builder_used ) : ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( ! $is_page_builder_used ) : ?>

					<h1 class="main_title"><?php the_title(); ?></h1>
				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_featured_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
						print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
				?>

				<?php endif; ?>

					<div class="entry-content">
					<?php
						the_content();

						if ( ! $is_page_builder_used )
							wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
					?>
					
		

			<div class="et_pb_section newsletter-subscription-container et_pb_section_5 et_section_regular" id="newsletter-subscription" style="padding:24px 0 0;">
				<div class=" et_pb_row et_pb_row_7">
					<div class="et_pb_column et_pb_column_4_4 et_pb_column_12">
						<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_center  et_pb_text_11">
							<h2>Newsletter Subscription</h2>
							<p>Sign up for our Bi-weekly Newsletter and get the latest in logistics technology &amp; business insights delivered straight to your inbox.</p>
							<div class="hubspot-wrap">
								<script>
								  hbspt.forms.create({ 
								css: '',
									portalId: '2199347',
									formId: '7cf45675-2fc3-49d4-94f4-d4bd9ed6913b'
								  });
								</script>
							</div>
						</div> <!-- .et_pb_text -->
					</div> <!-- .et_pb_column -->
				</div> <!-- .et_pb_row -->
			</div>		

			<div class="et_pb_section home-getintouch-container et_pb_section_6 et_pb_with_background et_section_regular" id="home-getintouch" style="background:#3363AF; padding:0;">
				<div class=" et_pb_row et_pb_row_8">
					<div class="et_pb_column et_pb_column_4_4 et_pb_column_13">
						<div class="et_pb_text et_pb_module et_pb_bg_layout_light et_pb_text_align_center  et_pb_text_12">
							<h2>Have a Question? We're Here to Help.</h2>
							<p>Ask us anything. Our customer service team would love to hear from you.</p>
							
							<iframe id="heartbeat" onload="resizeIframe(this)" src="http://www.cyzerg.com/wp-content/themes/cyzerg/hub_newsletter.php" name="heart" scrolling="no" align="middle" frameborder="0"></iframe>
						</div> <!-- .et_pb_text -->
					</div> <!-- .et_pb_column -->
				</div> <!-- .et_pb_row -->
			</div>
			
				<?php
					if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
				?>

				</article> <!-- .et_pb_post -->

			<?php endwhile; ?>

<?php if ( ! $is_page_builder_used ) : ?>

			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->

<?php endif; ?>

</div> <!-- #main-content -->

<div class="careers-footer-bottom"> <div class="container-fluid"><?php echo do_shortcode('[types field="careers-footer-bottom" id=""][/types]') ?></div> </div>  


<?php get_footer(2); ?>