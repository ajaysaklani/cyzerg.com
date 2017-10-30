<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/*
Plugin Name: Simple Pagination
Plugin URI: http://wordpress.org/plugin/simple-pagination/
Description: Simple Pagination allows to set up an advanced pagination for posts and comments. You have an easier navigation on your WordPress.
Version: 2.1.7
Author: GeekPress
Author URI: http://www.geekpress.fr/

	Copyright 2011 Jonathan Buttigieg

	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


// Define contants
define( 'SP_VERSION' , '2.1.7' );
define( 'SP_URL' , plugins_url(plugin_basename(dirname(__FILE__)).'/') );

class Simple_Pagination {

	private $options 	= array(); // Set $fields in array
	private $settings 	= array(); // Set $setting in array

	private $base;
	private $add_args;
	private $add_fragment;
	private $output; // - Ouput of the pagination

	function Simple_Pagination()
	{

		// Add translations
		if (function_exists('load_plugin_textdomain'))
			load_plugin_textdomain( 'simple-pagination', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');


		// Add menu page
		add_action('admin_menu', array(&$this, 'add_submenu'));

		// Settings API
		add_action('admin_init', array(&$this, 'settings_api_init'));


		// load the values recorded
		$this->options = get_option('_simple_pagination');

		if( isset( $this->options['css'] ) && $this->options['css'] )
			add_action('wp_print_styles', array(&$this, 'load_css') );


		//tell wp what to do when plugin is activated
		if (function_exists('register_activation_hook'))
			register_activation_hook(__FILE__, array(&$this, 'activate'));

	}


	/**
	 * method activate
	 *
	 * This function is called when plugin is activated.
	 *
	 * @since 1.0
	**/
	function activate()
	{
		$options = array(
				'text_pages' 					=> 'Pages:',
				'text_first_page' 				=> '&laquo;',
		 		'text_last_page' 				=> '&raquo;',
		 		'text_previous_page' 			=> '&lsaquo;',
		 		'text_next_page' 				=> '&rsaquo;',
				'css' 							=> true,
				'css_file'						=> 'default',
				'before_pagination' 			=> '<div class="pagination">',
				'after_pagination' 				=> '</div>',
				'always_show'					=> false,
				'show_all' 						=> false,
				'range' 						=> 3,
				'anchor'						=> 0,
				'larger_page_to_show' 			=> 0,
				'larger_page_multiple'			=> 0,
				'comments_show_all' 			=> false,
				'comments_always_show' 			=> false,
				'comments_range' 				=> 3,
				'comments_anchor'				=> 0,
				'comments_larger_page_to_show' 	=> 0,
				'comments_larger_page_multiple'	=> 0,
		);
		add_option('_simple_pagination', $options);
	}


	/**
	 * method load_styles
	 *
	 * This function insert file css in the <head>
	 *
	 * @since 1.0
	**/
	function load_css()
	{
		wp_register_style('simple-pagination-css', SP_URL .'css/'. $this->options['css_file'] .'.css', array(), SP_VERSION, 'screen');
		wp_enqueue_style( 'simple-pagination-css' );
	}


	/*
	 * method get_settings
	 *
	 * @since 1.0
	*/
	function get_settings()
	{


		$this->settings['text_pages'] = array(
			'section' 	=> 'general',
			'title'		=> __('Pagination Label', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The text to display before the list of pages.
						    <br/>
						    %CURRENT_PAGE% - The number of the current page.
							<br/>
							%TOTAL_PAGES% - The total number of pages.', 'simple-pagination')
		);


		$this->settings['text_previous_page'] = array(
			'section' 	=> 'general',
			'title'		=> __('Previous Page', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The text to display for the previous page link.<br/>Default: &lsaquo;', 'simple-pagination')
		);


		$this->settings['text_next_page'] = array(
			'section' 	=> 'general',
			'title'		=> __('Next Page', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The text to display for the next page link.<br/>Default: &rsaquo;', 'simple-pagination')
		);


		$this->settings['text_first_page'] = array(
			'section' 	=> 'general',
			'title'		=> __('First Page', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The text to display for the first page link.<br/>Default: &laquo;', 'simple-pagination')
		);


		$this->settings['text_last_page'] = array(
			'section' 	=> 'general',
			'title'		=> __('Last Page', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The text to display for the last page link.<br/>Default: &raquo;', 'simple-pagination')
		);


		$this->settings['css_file'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('Choose a CSS Style', 'simple-pagination'),
			'type'		=> 'select',
			'choices'	=> array(	'default'		=> 'Default CSS',
									'sabros.us' 	=> 'Sabros.us',
									'digg' 			=> 'Digg',
									'flickr'		=> 'Flickr',
									'green' 		=> 'Green',
									'grey'			=> 'Grey'
						   )
		);


		$this->settings['css'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('Simple Pagination CSS file', 'simple-pagination'),
			'type'		=> 'checkbox',
			'desc'		=> __('Include the default stylesheet of Simple Pagination?', 'simple-pagination')
		);


		$this->settings['before_pagination'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('Before Pagination Markup', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The HTML markup to display before the pagination code.<br/>Default: &lt;div class="pagination"&gt;', 'simple-pagination')
		);


		$this->settings['after_pagination'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('After Pagination Markup', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The HTML markup to display after the pagination code.<br/>Default: &lt;/div&gt;', 'simple-pagination')
		);

		$this->settings['before_link'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('Before Link Markup', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The HTML markup to display before the link code.<br/>Default: none.', 'simple-pagination')
		);


		$this->settings['after_link'] = array(
			'section' 	=> 'advanced-html-css',
			'title'		=> __('After Link Markup', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The HTML markup to display after the link code.<br/>Default: none.', 'simple-pagination')
		);


		$this->settings['always_show'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Always Show Page Navigation', 'simple-pagination'),
			'type'		=> 'checkbox',
			'std'		=> 1,
			'desc'		=> __('Show navigation even if there\'s only one page.', 'simple-pagination')
		);

		$this->settings['show_all'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Show all pages?', 'simple-pagination'),
			'type'		=> 'checkbox',
			'std'		=> 1,
			'desc'		=> __('If is checked, show all pages of the pagination.', 'simple-pagination')
		);

		$this->settings['range'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Page Range', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The number of page links to show before and after the current page.<br/>Default: 3', 'simple-pagination')
		);

		$this->settings['anchor'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Page Anchors', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The number of links to always show at beginning and end of pagination.<br/>Default: 0', 'simple-pagination')
		);


		$this->settings['larger_page_to_show'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Number Of Larger Page Numbers To Show', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('For example, Simple Pagination will display: Pages: 1, 2, 3, 4, 5, 10, 20, 30.<br/>Default: 0', 'simple-pagination')
		);

		$this->settings['larger_page_multiple'] = array(
			'section' 	=> 'advanced-pagination',
			'title'		=> __('Show Larger Page Numbers In Multiples Of', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('For example, if multiple is 5, it will show: 5, 10, 15.<br/>Default: 0', 'simple-pagination')
		);


		$this->settings['comments_always_show'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Always Show Page Navigation', 'simple-pagination'),
			'type'		=> 'checkbox',
			'std'		=> 1,
			'desc'		=> __('Show navigation even if there\'s only one page.', 'simple-pagination')
		);

		$this->settings['comments_show_all'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Show all pages?', 'simple-pagination'),
			'type'		=> 'checkbox',
			'std'		=> 1,
			'desc'		=> __('If is checked, show all pages of the pagination.', 'simple-pagination')
		);

		$this->settings['comments_range'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Page Range', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The number of page links to show before and after the current page.<br/>Default: 3', 'simple-pagination')
		);

		$this->settings['comments_anchor'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Page Anchors', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('The number of links to always show at beginning and end of pagination.<br/>Default: 0', 'simple-pagination')
		);


		$this->settings['comments_larger_page_to_show'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Number Of Larger Page Numbers To Show', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('For example, Simple Pagination will display: Pages: 1, 2, 3, 4, 5, 10, 20, 30.<br/>Default: 0', 'simple-pagination')
		);

		$this->settings['comments_larger_page_multiple'] = array(
			'section' 	=> 'advanced-comments',
			'title'		=> __('Show Larger Page Numbers In Multiples Of', 'simple-pagination'),
			'type'		=> 'text',
			'desc'		=> __('For example, if multiple is 5, it will show: 5, 10, 15.<br/>Default: 0', 'simple-pagination')
		);

	}


	/*
	 * method create_setting
	 * $args : array
	 *
	 * @since 1.0
	*/
	function create_settings( $args = array() ) {

		extract( $args );

		$field_args = array(
			'type'      => $type,
			'id'        => isset($id) ? $id : null,
			'desc'      => isset($desc) ? $desc : null,
			'label_for' => isset($id) ? $id : null,
			'std'		=> isset($std) ? $std : null,
			'choices'	=> isset($choices) ? $choices : null
		);


		add_settings_field( $id, $title, array( $this, 'display_settings' ), __FILE__, $section, $field_args );
	}


	/**
	 * method display_settings
	 *
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	public function display_settings( $args = array() ) {

		extract( $args );
		
		$options = $this->options;

		switch ( $type ) {

			case 'checkbox':
				
				$value = isset( $options[$id] ) ? 1 : null;
				echo '<fieldset>
						<legend class="screen-reader-text"><span>' . $label_for . '</span></legend>
						<label for="' . $id . '">
							<input class="checkbox" type="checkbox" id="' . $id . '" name="_simple_pagination[' . $id . ']" value="1" ' . checked( $value, 1, false ) . ' />	' . $desc . '
						</label>
					</fieldset>';

				break;

			case 'select':

				echo '<select id="' . $id . '" name="_simple_pagination[' . $id . ']">';

				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';

				echo '</select>';

				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';

				break;

			case 'text':
			default:

		 		$value = isset( $options[$id] ) ? esc_attr( $options[$id] ) : null;
		 		echo '<input class="regular-text" type="text" id="' . $id . '" name="_simple_pagination[' . $id . ']"  value="' . $value . '" />';

		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';

		 		break;

		}
	}


	/**
	 * method settings_api_init
	 *
	 * Register settings with the WP Settings API
	 *
	 * @since 1.0
	 */
	function settings_api_init()
	{

		register_setting('_simple_pagination', '_simple_pagination', array ( &$this, 'validate_settings' ));

		add_settings_section('help', __('Help', 'simple-pagination'), array(&$this, 'help_section_callback'), __FILE__);
		add_settings_section('general', __('Texts for navigation links', 'simple-pagination'), array(&$this, 'general_section_callback'), __FILE__);
		add_settings_section('advanced-html-css', __('HTML/CSS Advanced Settings', 'simple-pagination'), create_function('' , 'return false;'), __FILE__);
		add_settings_section('advanced-pagination', __('Posts Advanced Settings', 'simple-pagination'), create_function('' , 'return false;'), __FILE__);
		add_settings_section('advanced-comments', __('Comments Advanced Settings', 'simple-pagination'), create_function('' , 'return false;'), __FILE__);

		// Get the configuration of fields
		$this->get_settings();

		// Generate fields
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->create_settings( $setting );
		}
	}


	/**
	*  method general_section_callback
	*
	* @since 1.0
	*/
	function help_section_callback() { ?>

		<script type="text/javascript">
			jQuery(function() {
				jQuery('#showAdvancedOptions').click(function() {
					jQuery(this).hide();
					jQuery('#boxAdvancedOptions').slideDown();

					return false;
				});

				jQuery('#hideAdvancedOptions').click(function() {
					jQuery('#boxAdvancedOptions').slideUp();
					jQuery('#showAdvancedOptions').show();

					return false;
				});
			});
		</script>

		<p><?php _e('Display pagination in the loop of posts <strong>&lt;?php wp_simple_pagination(); ?&gt;</strong>', 'simple-pagination'); ?>
		<br/>
		<?php _e('Display pagination in the loop of comments <strong>&lt;?php wp_simple_comments_pagination(); ?&gt;</strong>', 'simple-pagination'); ?>
		</p>
		<a id="showAdvancedOptions" href="#"><?php _e('Show Help Advanced Options', 'simple-pagination'); ?></a>

		<div id="boxAdvancedOptions" style="display: none;">

			<p><?php _e('The function accepts an array that can change the values ​​registered from the administration. This is used if you want to configure a different pagination on a specific template.', 'simple-pagination'); ?></p>

			<p><?php _e('Here is a list of all the settings you can change:', 'simple-pagination'); ?></p>
			<table class="widefat">
				<thead>
					<tr>
						<th class="manage-column" scope="col"><?php _e('Key', 'simple-pagination'); ?></th>
						<th class="manage-column" scope="col"><?php _e('Default value', 'simple-pagination'); ?></th>
						<th class="manage-column" scope="col"><?php _e('Type', 'simple-pagination'); ?></th>
						<th class="manage-column" scope="col"><?php _e('Description', 'simple-pagination'); ?></th>
					</tr>
				</thead>
				<tr class="alternate">
					<td><strong>text_pages</strong></td>
					<td>Pages:</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The text to display before the list of pages', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>text_first_page</strong></td>
					<td>&laquo;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The text to display for the first page link', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>text_last_page</strong></td>
					<td>&raquo;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The text to display for the last link', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>text_previous_page</strong></td>
					<td>&lsaquo;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The text to display for the previous page link', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>text_next_page</strong></td>
					<td>&rsaquo;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The text to display for the next page link', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>css</strong></td>
					<td>true</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('If it\'s true, include the default stylesheet of Simple Pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>css_file</strong></td>
					<td>Default CSS</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The StyleSheet of the pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>before_pagination</strong></td>
					<td>&lt;div class="pagination"&gt;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The HTML markup to display before the pagination code', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>after_pagination</strong></td>
					<td>&lt;/div&gt;</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The HTML markup to display after the pagination code', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>before_link</strong></td>
					<td>empty</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The HTML markup to display before the link code', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>after_link</strong></td>
					<td>empty</td>
					<td><?php _e('Posts/Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The HTML markup to display after the link code', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>always_show</strong></td>
					<td>false</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('Show navigation even if there\'s only one page', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>show_all</strong></td>
					<td>false</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('If it\'s true, show all pages of the pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>range</strong></td>
					<td>3</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('The number of page links to show before and after the current page', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>anchor</strong></td>
					<td>0</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('The number of links to always show at beginning and end of pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>larger_page_to_show</strong></td>
					<td>0</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('The number of larger page numbers to show', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>larger_page_multiple</strong></td>
					<td>0</td>
					<td><?php _e('Posts', 'simple-pagination'); ?></td>
					<td><?php _e('Show larger page numbers in multiples of<br/>For example, if multiple is 5, it will show: 5, 10, 15', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>comments_always_show</strong></td>
					<td>false</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('Show navigation even if there\'s only one page', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>comments_show_all</strong></td>
					<td>false</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('If it\'s true, show all pages of the pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>comments_range</strong></td>
					<td>3</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The number of page links to show before and after the current page', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>comments_anchor</strong></td>
					<td>0</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The number of links to always show at beginning and end of pagination', 'simple-pagination'); ?></td>
				</tr>
				<tr>
					<td><strong>comments_larger_page_to_show</strong></td>
					<td>0</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('The number of larger page numbers to show', 'simple-pagination'); ?></td>
				</tr>
				<tr class="alternate">
					<td><strong>comments_larger_page_multiple</strong></td>
					<td>0</td>
					<td><?php _e('Comments', 'simple-pagination'); ?></td>
					<td><?php _e('Show larger page numbers in multiples of<br/>For example, if multiple is 5, it will show: 5, 10, 15', 'simple-pagination'); ?></td>
				</tr>
			</table>

			<p><?php _e('Here, an example of advanced options in a custom template:', 'simple-pagination'); ?></p>
			<pre style="background-color: #EDEDFF; color: black; background: #F0F0F0; border: 1px solid #DADADA; padding: 11px; font-size: 11px; line-height: 1.3em; overflow: auto;">
				&lt;?php

					$args = array(
								'before_pagination' => '&lt;div class="my-class"&gt;',
								'show_all' 			=> true,
					);

					wp_simple_pagination( $args );

				?&gt;
			</pre>
			<br/>
			<a id="hideAdvancedOptions" href="#"><?php _e('Hide Help Advanced Options', 'simple-pagination'); ?></a>
		</div>
	<?php
	}



	/**
	*  method general_section_callback
	*
	* @since 1.0
	*/
	function general_section_callback() {
		echo '<p>' . __('Leaving a field blank will hide that part of the navigation.', 'simple-pagination') . '</p>';
	}


	/**
	*  method validate_settings
	*
	* @since 1.0
	*/
	function validate_settings($input) {

		$input['text_pages'] 			= wp_strip_all_tags($input['text_pages'], true);
		$input['text_first_page'] 		= wp_strip_all_tags($input['text_first_page'], true);
		$input['text_last_page'] 		= wp_strip_all_tags($input['text_last_page'], true);
		$input['text_previous_page'] 	= wp_strip_all_tags($input['text_previous_page'], true);
		$input['text_next_page'] 		= wp_strip_all_tags($input['text_next_page'], true);
		$input['before_pagination'] 	= esc_html($input['before_pagination']);
		$input['after_pagination'] 		= esc_html($input['after_pagination']);
		$input['before_link'] 			= esc_html($input['before_link']);
		$input['after_link'] 			= esc_html($input['after_link']);
		$input['css_file'] 				= sanitize_key($input['css_file']);
		$input['range'] 				= ( absint($input['range']) !=0 ) ? absint($input['range']) : 1;
		$input['anchor'] 				= absint($input['anchor']);
		$input['larger_page_to_show'] 	= absint($input['larger_page_to_show']);
		$input['larger_page_multiple'] 	= absint($input['larger_page_multiple']);

		return $input;
	}


	/**
	*  method add_submenu
	*
	* @since 1.0
	*/
	function add_submenu()
	{

		// Add submenu in menu "Settings"
		add_submenu_page( 'options-general.php', 'Simple Pagination', __('Simple Pagination','simple-pagination'), 'administrator', __FILE__, array(&$this, 'display_page') );
	}


	/**
	*  method display_page
	*
	* @since 1.O
	*/
	function display_page()
	{
		// Check if user can access to the plugin
		if (!current_user_can('administrator'))
			wp_die( __('You do not have sufficient permissions to access this page.') );
		?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2>Simple Pagination</h2>
			<form method="post" action="options.php">
			    <?php
			    	settings_fields('_simple_pagination');
  					do_settings_sections(__FILE__);
					submit_button( __('Save Changes') );
				?>
			</form>
		</div>
	<?php
	}



	/**
	*  method generateOutput
	*
	* @since 1.O
	*/

	function generateOutput( $args = array() )
	{

		$this->options = wp_parse_args( $args, $this->options);

		global $wp_query, $wp_rewrite;

		// value of the current page

		if( isset( $this->options['type'] ) && $this->options['type'] == 'comments' ) {

			$wp_query->query_vars['cpage'] > 1 ? $current = $wp_query->query_vars['cpage'] : $current = 1; // Current page
			$total               = $wp_query->comment_count; // Total comments
			$this->add_fragment  = '#comments'; // Add fragment to URL
			$range               = $this->options['comments_range']; // Range
			$anchor              = $this->options['comments_anchor']; // Anchor
			$large_page_multiple = $this->options['comments_larger_page_multiple']; // Large page multiple
			$larger_page_to_show = $this->options['comments_larger_page_to_show']; // Large page to show
			$always_show         = $this->options['comments_always_show']; // Always show
			$show_all            = $this->options['comments_show_all']; // Show all
		}
		else {

			$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1; // Current page
			$total               = $wp_query->max_num_pages; // Total posts
			$range               = $this->options['range']; // Range
			$anchor              = $this->options['anchor']; // Anchor
			$large_page_multiple = $this->options['larger_page_multiple']; // Large page multiple
			$larger_page_to_show = $this->options['larger_page_to_show']; // Large page to show
			$always_show         = isset( $this->options['always_show'] ) ? $this->options['always_show'] : false; // Always show
			$show_all            = isset( $this->options['show_all'] ) ? $this->options['show_all'] : false; // Show all
		}


		$start_page = $current - floor( $range/2 );

		if ( $start_page <= 0 )
			$start_page = 1;

		$end_page = $current + ceil( ($range+1)/2 );

		if ( ( $end_page - $start_page ) != $range )
			$end_page = $start_page + $range;

		if ( $end_page > $total ) {
			$start_page = $total - $range;
			$end_page = $total;
		}


		// Rewrite link
		if( $wp_rewrite->using_permalinks() ) {
		
			if( isset( $this->options['type'] ) && $this->options['type'] == 'comments' )  {
				$this->base = user_trailingslashit(trailingslashit(get_permalink()) . 'comment-page-%#%', 'commentpaged');
			}
			else {
				
				$this->base = user_trailingslashit( trailingslashit( remove_query_arg( 's', strtok(get_pagenum_link( 1 ), '?') ) ) . $wp_rewrite->pagination_base . '/%#%/', 'paged' );
				if ( get_pagenum_link( 1 ) != strtok(get_pagenum_link( 1 ), '?') ) {
					
					$this->base = user_trailingslashit( trailingslashit( remove_query_arg( 's', strtok(get_pagenum_link( 1 ), '?') ) ) . $wp_rewrite->pagination_base . '/%#%/'.rtrim(str_replace('%2F','',substr(get_pagenum_link( 1 ),strlen(strtok(get_pagenum_link( 1 ), '?')))),'/'), 'paged' );
					
				}
				
			}
				
		}
		else {
			if( isset( $this->options['type'] ) && $this->options['type'] == 'comments' )
				$this->base = @add_query_arg('cpage','%#%');
			else
				$this->base = @add_query_arg('paged','%#%');
		}
		
		// Search value
		if( !empty($wp_query->query_vars['s']) )
			$this->add_args = array( 's' => get_query_var( 's' ) );


		// Check of pagination is always show and check if the total of pages is egal 1
		if( !$always_show && $total == 1 )
			return false;


		// Text of the beginning
		$text_pages = str_replace(  array( "%CURRENT_PAGE%", "%TOTAL_PAGES%" ),
									array( number_format_i18n( $current ), number_format_i18n( $total ) ),
									$this->options['text_pages']
								 );

         // Before link Markup
         $before_link = isset( $this->options['before_link'] ) ? html_entity_decode($this->options['before_link']) : '';

         // After link Markup
         $after_link = isset( $this->options['after_link'] ) ? html_entity_decode($this->options['after_link']) : '';


         // Beginning of the HTML markup of pagination
         $this->output .= isset( $this->options['before_pagination'] ) ? html_entity_decode($this->options['before_pagination']) : '';
         if( $text_pages )
         	$this->output.= '<span class="pages">' . $text_pages . '</span>';

		 // First Page
		 if( $current-1 > floor($range+1/2) ) :

	    	$link = $this->formate_link(1);

	    	if( !empty( $this->options['text_first_page'] ) )
                	$this->output .= $before_link . '<a class="first" href="' . esc_url($link) . '">' . $this->options['text_first_page'] . '</a>' . $after_link;

	    endif;

		$larger_pages_array = array();
		if ( $large_page_multiple )
			for ( $i = $large_page_multiple; $i <= $total; $i+= $large_page_multiple )
				$larger_pages_array[] = $i;

		$larger_page_start = 0;
		foreach ( $larger_pages_array as $larger_page ) {
			if ( $larger_page+floor(($range+1)/2) < $start_page && $larger_page_start < $larger_page_to_show ) {

				$link = $this->formate_link($larger_page);

				$this->output .= $before_link . '<a class="larger-pages" href="' . esc_url($link) . '">' . $larger_page . '</a>' . $after_link;
				$larger_page_start++;
			}
		}

		//Previous Page
		if ( $current && 1 < $current ) :

                $link = $this->formate_link($current - 1);

                if( !empty( $this->options['text_previous_page'] ) )
                		$this->output .= $before_link . '<a class="previous" href="' . esc_url($link) . '">' . $this->options['text_previous_page'] . '</a>' . $after_link;
        endif;

		$dots = false;
        for ( $n = 1; $n <= $total; $n++ ) :
                $n_display = number_format_i18n($n);
                if ( $n == $current ) :
                        $this->output .= $before_link . '<span class="current">' .$n_display . '</span>' . $after_link;
                        $dots = true;
                else :

                        if ( $show_all || ( $n <= $anchor || ( $current && $n >= $current - $range && $n <= $current + $range ) || $n > $total - $anchor ) ) :

                                $link = $this->formate_link($n);

                                $this->output .= $before_link . '<a href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">'. $n_display . '</a>' . $after_link;
                                $dots = true;

                        elseif ( $dots && !$show_all && $anchor >=1 ) :
                                $this->output .= $before_link . '<span class="dots">...</span>' . $after_link;
                                $dots = false;
                        endif;
                endif;
        endfor;

        // Next Page
        if ( $current && ( $current < $total || -1 == $total ) ) :

                $link = $this->formate_link(number_format_i18n($current + 1));

                if( !empty($this->options['text_next_page']) )
                	$this->output .= $before_link . '<a class="next" href="' . esc_url($link) . '">' . $this->options['text_next_page'] . '</a>' . $after_link;
        endif;

	    $larger_page_end = 0;
		foreach ( $larger_pages_array as $larger_page ) {
			if ( $larger_page-(floor($range/2)) > $end_page && $larger_page_end < $larger_page_to_show ) {

				$link = $this->formate_link($larger_page);

				$this->output .= $before_link . '<a class="larger-pages" href="' . esc_url($link) . '">' . $larger_page . '</a>' . $after_link;

				$larger_page_end++;
			}
		}


		// Last Page
	    if( $current < $total-(floor($range+1/2)) ) :

	    	$link = $this->formate_link($total);

	    	if( !empty( $this->options['text_last_page'] ) )
                	$this->output .= $before_link . '<a class="last" href="' . esc_url($link) . '">' . $this->options['text_last_page'] . '</a>' . $after_link;

	    endif;

	    $this->output.= isset( $this->options['after_pagination'] ) ? html_entity_decode($this->options['after_pagination']) : '';

	    echo $this->output;
	}



	/**
	*  method formate_link
	*
	* @since 1.O
	*/
	function formate_link( $page )
	{
		
		global $wp_rewrite;
		
		$link = str_replace('%_%', "?page=%#%", $this->base);
        $link = str_replace('%#%', $page, $link);
        $link = str_replace( $wp_rewrite->pagination_base . '/1/','', $link );
        $link = str_replace('?paged=1','', $link);

        if ( $this->add_args )
        	$link = add_query_arg( $this->add_args, $link );

		if( $this->add_fragment )
			$link .= $this->add_fragment;

        return str_replace(' ','+', $link);
	}

}

// Start this plugin once all other plugins are fully loaded
global $Simple_Pagination; $Simple_Pagination = new Simple_Pagination();


/**
*  method wp_simple_pagination
*
* Affiche le contenu de la pagination.
*
* @since 1.O
*/

function wp_simple_pagination( $args = array() )
{
	$Simple_Pagination = new Simple_Pagination();
	echo $Simple_Pagination->generateOutput( $args );
}



/**
*  method wp_simple_comments_pagination
*
* Affiche le contenu de la pagination.
*
* @since 2.O
*/
function wp_simple_comments_pagination( $args = array() )
{
	$Simple_Pagination = new Simple_Pagination();
	$args['type'] = 'comments';
	echo $Simple_Pagination->generateOutput( $args );
}
