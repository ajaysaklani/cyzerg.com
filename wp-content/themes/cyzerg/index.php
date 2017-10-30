<?php get_header(); ?>



<?php if (is_search()) { ?>


<div class="searchresultspage"><div class="container"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('search-resultspage') ) : endif; ?></div></div>

<div id="main-content">
<div class="white-block search-resultspage">
	<div class="container">	
	<div id="container-white">
		<div id="content-area" class="clearfix">
	<h1 class="careers-heading">Search Results</h1>
	<div class="search-top-page"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('search-top-page') ) : endif; ?></div>
	
	<h2 class="serach-page">Search results for <?php echo $s ?></h2>
			<?php
		    $i=0;
			if ( have_posts() ) : 
				while ( have_posts() ) : the_post(); ?>  

					
					<?php if($i%2 == 0 ){  ?><article class="blogpost" id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article"><?php } else { ?><article class="blogpost2 blogpost" id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article"><?php } $i++; ?>

				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_pb_post_main_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' == et_get_option( 'divi_thumbnails_index', 'on' ) && '' !== $thumb ) : ?>
						<a href="<?php the_permalink(); ?>">
							<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height ); ?>
						</a>
				<?php
					endif;
				?>
		
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							
							  
						
<div class="post-content">
						
					<?php
						

						if ( 'on' !== et_get_option( 'divi_blog_style', 'false' ) )
							truncate_post( 270 );
						else
							the_content();
					?>
					
					<p><a class="learn-more" href="<?php the_permalink(); ?>">Learn More</a></p>
					</div>

					</article> <!-- .et_pb_post -->
			<?php
					endwhile;

					if ( function_exists( 'wp_pagenavi' ) )
						wp_pagenavi();
					else
						get_template_part( 'includes/navigation', 'index' );
				else :
					get_template_part( 'includes/no-results', 'index' );
				endif;
			?>
			
			<?php if(function_exists('wp_simple_pagination')) {
    wp_simple_pagination();
} ?> 
			


			<?php //get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
	</div>
	</div>
</div> <!-- #main-content -->
<div class="search-footer-bottom"> <div class="container-fluid"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('search-footer-bottom') ) : endif; ?></div> </div> 


<?php } else { ?>
<div class="blog-header"><div class="container"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('blog-header') ) : endif; ?></div></div>
<div id="main-content">
<div class="white-block">
	<div class="container">	
	<div id="container-white">
		<div id="content-area" class="clearfix">
			<div id="left-area">
		<?php
		    $i=0;
			if ( have_posts() ) : 
				while ( have_posts() ) : the_post(); ?>  

					
					<?php if($i%2 == 0 ){  ?><article class="blogpost" id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article"><?php } else { ?><article class="blogpost2 blogpost" id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article"><?php } $i++; ?>

				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_pb_post_main_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' == et_get_option( 'divi_thumbnails_index', 'on' ) && '' !== $thumb ) : ?>
						<a href="<?php the_permalink(); ?>">
							<?php print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height ); ?>
						</a>
				<?php
					endif;
				?>
		<div class="post-heading">
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							
							  <p class="meta">by <?php the_author_posts_link(); ?> | <?php _e("", "bonestheme"); ?> <time datetime="<?php echo the_time('F j, Y'); ?>" pubdate><?php the_date(); ?></time> | <?php the_category(', '); ?></p></div>
						
<div class="post-content">
						
					<?php
						

						if ( 'on' !== et_get_option( 'divi_blog_style', 'false' ) )
							truncate_post( 270 );
						else
							the_content();
					?>
					
					<p><a class="learn-more" href="<?php the_permalink(); ?>">Learn More</a></p>
					</div>

					</article> <!-- .et_pb_post -->
			<?php
					endwhile;

					if ( function_exists( 'wp_pagenavi' ) )
						wp_pagenavi();
					else
						get_template_part( 'includes/navigation', 'index' );
				else :
					get_template_part( 'includes/no-results', 'index' );
				endif;
			?>
			
			<?php if(function_exists('wp_simple_pagination')) {
    wp_simple_pagination();
} ?> 
			
			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
	</div>
	</div>
</div> <!-- #main-content -->

<div class="newsletter"><div class="container"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('newsletter') ) : endif; ?></div></div>
<?php } ?>
<?php get_footer(); ?>