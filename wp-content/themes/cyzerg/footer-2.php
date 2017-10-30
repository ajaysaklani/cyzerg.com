	<footer id="main-footer">
		<?php get_sidebar( 'footer' ); ?>
		
		
		<div class="footer-social-container">
		<div class="container">
		<div class="footer-social-container-inner">
		<?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer_social') ) : endif; ?></div>
		</div>
		</div>
		</div>

		<div id="footer-bottom">
			<div class="container clearfix">
				<div class="footer_bottom"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('footer_bottom') ) : endif; ?></div>
			</div>	<!-- .container -->
		</div>
	</footer> <!-- #main-footer -->
	<?php wp_footer(); ?>
<script type="text/javascript">
jQuery(function test(){          
   jQuery('.address-block .et_pb_column:first-child').addClass('larg-screen');
   jQuery('.contact-form-block .et_pb_column:first-child').addClass('larg-screen');
   jQuery('#landing-wrapper .et_pb_row:last-child').addClass('headline-focus');
   jQuery('#landing-wrapper .et_pb_row:first-child .et_pb_column_1_3').addClass('mb-row');
   jQuery('.footer-widget:nth-child(2)').addClass('footer-articles');
   jQuery('.footer-widget:first-child').addClass('footer-widget-first');
                             
});
</script>
<script>
(function($,w){
  function fixBlogTitleHeight(){
    $('.blogpost .post-heading').each(function(){
      var $this=$(this);
      $this.css('margin-top','').find('h2').css('font-size','')
      if($this.height()>72){
        $this.find('h2').css('font-size','16px');
        $this.css('margin-top',(-1*$this.height()-26)+'px')
      }
    });
  }
  $(fixBlogTitleHeight);
  $(w).on('resize',fixBlogTitleHeight);
}(jQuery,window));
</script>
<script type="text/javascript" src="<?php bloginfo("stylesheet_directory");?>/js/jquery.cycle2.swipe.js"></script> 
 
</body>
</html>