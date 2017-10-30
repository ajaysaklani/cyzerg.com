<form id="_form_1026" accept-charset="utf-8" action="//cyzerg.activehosted.com/proc.php" enctype="multipart/form-data" method="post" _lpchecked="1">
    <h3><?php echo $h3; ?></h3>
<div style="display:none;">
    <input type="hidden" name="f" value="1026"><p></p>
    <input type="hidden" name="s" value=""><br>
    <input type="hidden" name="c" value="0"><br>
    <input type="hidden" name="m" value="0"><br>
    <input type="hidden" name="act" value="sub"><br>
    <input type="hidden" name="nlbox[]" value="2">
    <input type="hidden" name="field[3]" value="<?php the_title(); ?>">
    <input type="hidden" name="field[4]" value="<?php the_permalink(); ?>">
</div>
<div class="form-inner"><input type="text" name="fullname" placeholder="Full Name:" size="40" required aria-required=”true”><br>
<input type="email" name="email" placeholder="Email Address:" size="40" required aria-required=”true”><br>
    <input type="text" name="phone" placeholder="Phone Number:" size="40" required aria-required=”true” pattern="(\+\d*)?[\( ]?\d{3}[-\) ]?\d{3}[- ]?\d{4}"  title="format: 555-555-1212"><br>
<textarea class="wpcf7-textarea" cols="40" name="field[1]" placeholder="How may we help you?" rows="10"></textarea><br>
<input type="submit" value="Go!" name="submit" class="wpcf7-submit">
</div>
</form>


