<?php
	
$PageRows = 15;
$offset = ($PageNum - 1) * $PageRows;
$forms = $wpdb->get_results("SELECT * FROM $table_name WHERE blog_id = '$blog_id' ORDER BY id DESC LIMIT $offset,$PageRows");
$total_forms = count($wpdb->get_results("SELECT * FROM $table_name WHERE blog_id = '$blog_id' "));
$total_pages = ceil($total_forms / $PageRows);
	
	?>
	<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Application Forms','wp_careers_plugin'); ?></h2>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<br/>
	<form method="post" action="<?php echo $current_url;?>&action=list">
	<p><input type="text" id="title" name="title" placeholder="<?php echo __('Title of a new form','wp_careers_plugin'); ?>" value="<?php echo $form_title; ?>" size="40"> <input class="button" type="submit" name="save" value="<?php echo __('Save Form','wp_careers_plugin') ?>" /></p>
	</form>
	<br/>
	<table class="table table-hover table-bordered table-striped">
	<thead>
		<tr>
			<th><?php echo __('Title','wp_careers_plugin'); ?></th>
			<th width="20%" style="text-align: center;"><?php echo __('Actions','wp_careers_plugin'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if(count($forms) > 0){
		foreach($forms as $j){
			?>
	<tr>
		<td><span style="font-weight: bold; font-size: 14px;"><?php echo $j->title;?></span></td>
		<td style="text-align: center;">
			<a href="<?php echo $current_url;?>&action=edit&form_id=<?php echo $j->id;?>" title="<?php echo __('Edit','wp_careers_plugin');?>"><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/pencil.png', __FILE__ );?>" /></a>
		</td>
	</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="2"><?php echo __('No forms to display','wp_careers_plugin'); ?></td></tr>
		<?php
	}
	
	?>
	</tbody>
	</table>
	<?php echo paginate_three($current_url, $PageNum, $total_pages); ?>
	</div>
