	<footer id="main-footer">
		<?php get_sidebar( 'footer' ); ?>

		<div id="footer-bottom">
			<div class="container clearfix">
				<div class="footer_bottom"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer_bottom') ) : endif; ?></div>
			</div>	<!-- .container -->
		</div>
	</footer> <!-- #main-footer -->
	<?php wp_footer(); ?>
</body>
</html>
