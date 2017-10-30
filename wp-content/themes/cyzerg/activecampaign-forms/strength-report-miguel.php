<form id="_form_1069" accept-charset="utf-8" action="//cyzerg.activehosted.com/proc.php" enctype="multipart/form-data" method="post" _lpchecked="1">
    <h3><?php echo $h3; ?></h3>
<div style="display:none;">
    <input type="hidden" name="f" value="1069"><p></p>
    <input type="hidden" name="s" value=""><br>
    <input type="hidden" name="c" value="0"><br>
    <input type="hidden" name="m" value="0"><br>
    <input type="hidden" name="act" value="sub"><br>
    <input type="hidden" name="nlbox[]" value="5">
</div>
<div class="form-inner" style="padding:0;"><input type="text" name="fullname" placeholder="Full Name:" size="40" required aria-required=”true” style="margin-left:-10px;"><br>
        <input type="email" name="email" placeholder="Email Address:" size="40" required aria-required=”true” style="margin-left:-10px;"><br>
            <input type="text" name="phone" placeholder="Phone Number:" size="40" required aria-required=”true” pattern="(\+\d*)?[\( ]?\d{3}[-\) ]?\d{3}[- ]?\d{4}"  title="format: 555-555-1212" style="margin-left:-10px;"><br>
                <input type="submit" value="Schedule My Analysis" name="submit" class="wpcf7-submit" style="margin-left:-10px;padding: 10px; background:#229445;box-sizing:content-box;width:100%;max-width: 377px;">
</div>
</form>
<script>
    (function($) {
            $(function(){
                if($(window).width()>980) {
                    $('.et_pb_row').css('z-index','0');
                    $('#_form_1069')
                      .closest('.et_pb_column_1_3')
                      .hcSticky({stickTo: '.white-block'})
                      .closest('.et_pb_row').css('z-index','1');
                    $('.wrapper-sticky').css('height','auto');
                }
            });
    })(jQuery);
</script>


