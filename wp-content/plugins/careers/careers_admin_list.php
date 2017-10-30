<?php
	
$PageRows = 15;
$offset = ($PageNum - 1) * $PageRows;
$jobs = $wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' ORDER BY status DESC, id DESC LIMIT $offset,$PageRows");
$total_jobs = count($wpdb->get_results("SELECT * FROM wp_careers_plugin_jobs WHERE blog_id = '$blog_id' "));
$total_pages = ceil($total_jobs / $PageRows);
	
	?>
	<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?> &nbsp;&nbsp;<a title="<?php echo __('Create New Job','wp_careers_plugin'); ?>" href="<?php echo $current_url;?>&action=add"><img style="vertical-align:middle" src="<?php echo plugins_url( '/add.png', __FILE__ );?>" /></a></h2>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<br/>
	<table class="table table-hover table-bordered table-striped">
	<thead>
		<tr>
			<th><?php echo __('Title','wp_careers_plugin'); ?></th>
			<th width="15%"><?php echo __('Added on','wp_careers_plugin'); ?></th>
			<th width="15%"><?php echo __('Deadline','wp_careers_plugin'); ?></th>
			<th width="10%"><?php echo __('Status','wp_careers_plugin'); ?></th>
			<th width="20%" style="text-align: center;"><?php echo __('Actions','wp_careers_plugin'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if(count($jobs) > 0){
		foreach($jobs as $j){
			?>
	<tr>
		<td><span style="font-weight: bold; font-size: 14px;"><?php echo $j->title;?></span></td>
		<td><?php echo date(get_option('date_format'), strtotime($j->created_on));?></td>
		<td><?php echo date(get_option('date_format'), strtotime($j->end_date));?></td>
		<td><?php echo $j->status;?></td>
		<td style="text-align: center;">
			<?php echo CareersJobsNavIcons($current_url, $j->id);?>
		</td>
	</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="5"><?php echo __('No jobs to display','wp_careers_plugin'); ?></td></tr>
		<?php
	}
	
	?>
	</tbody>
	</table>
	<?php echo paginate_three($current_url, $PageNum, $total_pages); ?>
	</div>
