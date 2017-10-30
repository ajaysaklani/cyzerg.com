<div class="snp-fb snp-theme-sharpspring">
<?php
if(!empty($POPUP_META['snp_headline']))
{
	echo '<h2>' . $POPUP_META['snp_headline'] . '</h2>';
}
?>
<?php
if(!empty($POPUP_META['snp_subhead']))
{
	echo '<h3>' . $POPUP_META['snp_subhead'] . '</h3>';
}
?>
<!-- SharpSpring Form for Newsletter Sign-Up  -->
<script type="text/javascript">
	<?php
	if(!empty($POPUP_META['snp_sharpspring_params']))
	{
                echo $POPUP_META['snp_sharpspring_params'];
	}
	?>
</script>

	<?php
	if(!empty($POPUP_META['snp_sharpspring_url']))
	{
		echo '<script src="'.$POPUP_META['snp_sharpspring_url'].'"></script>'; 
	}
	?>

<script>
(function(w, $) {
    var handleMessage = function(ev) {

        var data = ev.data;


        if (data && data.formID) {
            $.fancybox2.update();
        }

    };

    if(typeof w.addEventListener != 'undefined') {
        w.addEventListener('message', handleMessage, false);
    }

}(window, jQuery));
</script>
<p class="disclaimer">Cyzerg will never sell, rent or otherwise distribute your email address. <a href="/privacy-policy/" target="_blank"> Click here to read our full privacy policy. </a>
</p>
</div>

