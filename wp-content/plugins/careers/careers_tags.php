<?php

function CareersTagsExec(){
	global $wpdb;
	$table_name = "wp_careers_plugin_tags";
	$current_url = $_SERVER["REQUEST_URI"];
	
	global $blog_id;
	if(!$blog_id){
		$blog_id = 'no_id';
	}
	
	$tag_name = '';
	
	$error_msg = '';
	$success_msg = '';
	
	$action = 'list';
	
	$id = null;
	$tag_id = null;
	
	$PageNum = 1;
	
	if(isset($_GET['action']) AND $_GET['action'] != ''){
		$action = $_GET['action'];
		$current_url = str_replace('&action=' . $_GET['action'] , '', $current_url);
	}
	
	if(isset($_GET['tag_id']) AND $_GET['tag_id'] != ''){
		$tag_id = $_GET['tag_id'];
		$current_url = str_replace('&tag_id=' . $_GET['tag_id'] , '', $current_url);
	}
	
	if(isset($_GET['pnum']) AND $_GET['pnum'] != ''){
		$PageNum = intval($_GET['pnum']);
		$current_url = str_replace('&pnum=' . $_GET['pnum'] , '', $current_url);
	}
	
	
	
	if(isset($_POST['save'])){
		$values = array();
		$format = array();
		
		if(!isset($_POST['title']) OR $_POST['title'] == ''){
			$error_msg .= __(' Form title is required.','wp_careers_plugin');
		}else{
			$tag_name = stripslashes($_POST['title']);
			$values['name'] = stripslashes($_POST['title']);
			$format[] = "%s";
		}
		
		
		if($error_msg == ''){
			if($action == 'edit' AND $tag_id != null){
				$wpdb->update($table_name, $values, array('id'=>$tag_id), $format);
				$success_msg = __('The tag has been saved','wp_careers_plugin');
			}else{
			
			
				$values['blog_id'] = $blog_id;
				$format[] = "%s";
			
				if($wpdb->insert($table_name, $values, $format)){
					$success_msg = __('The tag has been saved','wp_careers_plugin');
				}else{
					$error_msg .= __(' An unknown error occured. Please try again.','wp_careers_plugin');
				}
			
			}
		}
	}
	
	if(isset($_POST['delete_tag'])){
		$wpdb->query("DELETE FROM $table_name WHERE id = $tag_id");
		$action = 'list';
		$success_msg = __('The tag has been deleted.','wp_careers_plugin');
	}
	
	if($action == 'edit'){
		$el = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $tag_id ORDER BY id DESC LIMIT 0,1");
		if($el){
			$tag_name = $el->name;	
		}
		
		
		?>
		
		<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Job Categories','wp_careers_plugin'); ?></h2>

	<br/>
<?php
if($error_msg){
	echo '<div id="message" class="error"><p>' . $error_msg . '</p></div>';
}
if($success_msg){
	echo '<div id="message" class="updated"><p>' . $success_msg . '</p></div>';
}
?>
	<form method="post" action="<?php echo $current_url;?>&action=edit&tag_id=<?php echo $tag_id;?>">
			<p><b><label for="title"><?php echo __('Name','wp_careers_plugin'); ?><span style="color: red;">*</span></label></b><br/><input type="text" id="title" name="title" value="<?php echo $tag_name; ?>" size="40"></p>

			
			<div class="submit"><input class="button-primary" type="submit" name="save" value="<?php echo __('Save','wp_careers_plugin') ?>" />&nbsp;&nbsp;&nbsp; <input class="button delete_button" type="submit" name="delete_tag" value="<?php echo __('Delete','wp_careers_plugin') ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?','wp_careers_plugin') ?>');"/></div>

			</form>
</div>
		<?php
		
		
	}else{
	
	$PageRows = 15;
$offset = ($PageNum - 1) * $PageRows;
$forms = $wpdb->get_results("SELECT * FROM $table_name WHERE blog_id = '$blog_id' ORDER BY id DESC LIMIT $offset,$PageRows");
$total_forms = count($wpdb->get_results("SELECT * FROM $table_name WHERE blog_id = '$blog_id' "));
$total_pages = ceil($total_forms / $PageRows);
	
	?>
	<div class="wrap">
	<h2><img style="vertical-align:middle" src="<?php echo plugins_url( '/reseller_account_b.png', __FILE__ );?>" /> <?php echo __('Wp Careers','wp_careers_plugin'); ?>: <?php echo __('Job Categories','wp_careers_plugin'); ?></h2>
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
	<p><input type="text" id="title" name="title" placeholder="<?php echo __('Name of a new tag','wp_careers_plugin'); ?>" value="<?php echo $tag_name; ?>" size="40"> <input class="button" type="submit" name="save" value="<?php echo __('Save','wp_careers_plugin') ?>" /></p>
	</form>
	<br/>
	<table class="table table-hover table-bordered table-striped">
	<thead>
		<tr>
			<th><?php echo __('Name','wp_careers_plugin'); ?></th>
			<th width="20%" style="text-align: center;"><?php echo __('Actions','wp_careers_plugin'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	if(count($forms) > 0){
		foreach($forms as $j){
			?>
	<tr>
		<td><span style="font-weight: bold; font-size: 14px;"><?php echo $j->name;?></span></td>
		<td style="text-align: center;">
			<a href="<?php echo $current_url;?>&action=edit&tag_id=<?php echo $j->id;?>" title="<?php echo __('Edit','wp_careers_plugin');?>"><img style="vertical-align:middle" width="24px" height="24px" src="<?php echo plugins_url( '/pencil.png', __FILE__ );?>" /></a>
		</td>
	</tr>
			<?php
		}
	}else{
		?>
		<tr><td colspan="2"><?php echo __('No tags to display','wp_careers_plugin'); ?></td></tr>
		<?php
	}
	
	?>
	</tbody>
	</table>
	<?php echo paginate_three($current_url, $PageNum, $total_pages); ?>
	</div>
	
	
	<?php
	}
}
