<?php
wp_register_style(
    'nextpay-donations',
    plugins_url('assets/button.css', dirname(__FILE__)),
    array()
);
wp_enqueue_style('nextpay-donations');
?>

<form action="<?php echo site_url() . '/?nxd_nextpay=process'; ?>" method="post"<?php
if (isset($pd_options['new_tab'])) {
    echo ' target="_blank"';
}
?>>
    <div class="nextpay-donations">
        <div id="nextpay-donations-container">
            <?php
            # Build the form
            $nextpay_donate_form = '';
            $indent = str_repeat(" ", 8);

            // Optional Settings
            if ($name)
                $nextpay_donate_form .=  $indent.'<input type="text" placeholder="نام و نام خانوادگی" name="name"/><br>'.PHP_EOL;
            if ($email)
                $nextpay_donate_form .=  $indent.'<input type="text" placeholder="ایمیل" name="email"/><br>'.PHP_EOL;
            if ($desc)
                $nextpay_donate_form .=  $indent.'<textarea type="text" placeholder="توضیحات" name="desc"></textarea><br>'.PHP_EOL;
            if ($amount){
                if(!is_numeric($amount)){
                    wp_die('Error! Donation amount must be a numeric value.');
                }
                $nextpay_donate_form .=  $indent.'<input type="hidden" name="amount" value="' . apply_filters( 'nextpay_donations_amount', $amount ) . '" />'.PHP_EOL;
            }else{
                if ( $currency_code == 'IRR'){ $currency_text = 'ریال'; }else{ $currency_text = 'تومان ';}
                $nextpay_donate_form .=  $indent.'<input type="text" name="amount" placeholder="مقدار به ' . $currency_text . '" /><br>'.PHP_EOL;
            }

            echo $nextpay_donate_form;
            ?>

            <button id="nextpay-donations-btn" >پرداخت
                <span style="font-size: 10px;">
                    <?php
                    if ($amount){
                        echo $amount ;
                        if ( $currency_code == 'IRR'){
                            echo 'ریال ';
                        }else{
                            echo 'تومان ';
                        }
                    }
                    ?>
                </span>
            </button>
        </div>
    </div>
</form>
<!-- End Nextpay Donations -->
