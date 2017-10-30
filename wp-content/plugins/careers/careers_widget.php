<?php

function careers_widget() {
	register_widget( 'Careers_Widget' );
}

class Careers_Widget extends WP_Widget {

	function Careers_Widget() {
		$widget_ops = array( 'classname' => 'careers', 'description' => __('A widget that displays available jobs on your sidebar ', 'wp_careers_plugin') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'careers-widget' );
		
		$this->WP_Widget( 'careers-widget', __('Careers Widget', 'wp_careers_plugin'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if($title){
			echo $before_title . $title . $after_title;
		}
		
		global $wpdb;
		
		global $blog_id;
		if(!$blog_id){
			$blog_id = 'no_id';
		}
		
		$jobs = $wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status = 'published' ORDER BY status DESC, id DESC LIMIT 0,5");

		if(get_option('careers_page_id')){
			$permalink_o = get_permalink(get_option('careers_page_id'));
			if(parse_url($permalink_o, PHP_URL_QUERY)){
				$permalink = $permalink_o . '&';
			}else{
				$permalink = $permalink_o . '?';
			}
		}else{
			$permalink_o = '';
		}
		
		
		if(count($jobs) > 0){
		
		?>
		<ul>
		<?php
		foreach($jobs as $j){
			?>
			<li><a href="<?php echo $permalink;?>job_id=<?php echo $j->id;?>" title="<?php echo $j->title;?>"><?php echo CareersTrimText($j->title, 35);?></a></li>
			<?php
		}
		?>
		</ul>
		
		
		<?php
		
		$total_jobs = count($wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' AND status = 'published' "));
		if($total_jobs > 5){
			?>
			<br/>
			<br/>
			<p><a href="<?php echo $permalink_o;?>"><?php echo __('MORE JOBS &raquo;','wp_careers_plugin'); ?></a></p>
			<?php
		}
	}else{
		?>
		<p><?php echo __('No job openings at the moment','wp_careers_plugin'); ?></p>
		<?php
	}

		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Careers', 'wp_careers_plugin'),);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'wp_careers_plugin'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
	<?php
	}
}

