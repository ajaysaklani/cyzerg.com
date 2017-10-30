<?php
$SNP_THEMES['theme_sharpspring'] = array(
	'NAME' => 'SharpSpring',
	'STYLES' => 'style.css',
	'TYPES' => array(
		'iframe' => array('NAME' => 'SharpSpring'),
	),
	'COLORS' => array(
		'iframe' => array('NAME' => '--')
	),
	'FIELDS' => array(
		array(
			'id' => 'height',
			'type' => 'text',
			'title' => __('Default Height', 'nhp-opts'),
			'desc' => __('px (default: 600)', 'nhp-opts'),
			'class' => 'mini',
			'std' => '600'
		),
		array(
			'id' => 'sharpspring_params',
			'type' => 'textarea',
			'title' => __('SharpSpring Params', 'nhp-opts')
		),
		array(
			'id' => 'sharpspring_url',
			'type' => 'text',
			'title' => __('SharpSpring Script Src', 'nhp-opts')
		),
		array(
			'id' => 'headline',
			'type' => 'text',
			'title' => __('Headline', 'nhp-opts')
		),
		array(
			'id' => 'subhead',
			'type' => 'textarea',
			'title' => __('Subhead', 'nhp-opts')
		),
	)
);
?>
