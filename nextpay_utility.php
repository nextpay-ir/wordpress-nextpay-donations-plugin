<?php

add_action('init', 'nx_donations_listener');

function nx_donations_listener(){
    if (isset($_REQUEST['nxd_nextpay'])) {
        if($_REQUEST['nxd_nextpay'] == "process"){
            nx_donations_do_payment();
            exit;
        }elseif($_REQUEST['nxd_nextpay'] == "verify"){
            nx_donations_validate_nextpay();
            exit;
        }
    }
}

function nx_donations_do_payment(){


    if(!session_id()) {
        session_start();
    }

    if (isset($_REQUEST['amount']))
        $amount = $_REQUEST['amount'];
    else{
        wp_redirect( site_url() );
        exit;
    }

    $name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : 'نامشخص';
    $email = isset( $_REQUEST['email'] ) ? $_REQUEST['email'] : 'نامشخص';
    $desc = isset( $_REQUEST['desc'] ) ? $_REQUEST['desc'] : 'وارد نشده';



    $pd_options = get_option(NextpayDonations::OPTION_DB_KEY);

    $apikey = $pd_options['nextpay_api'];
    $amount = ($pd_options['currency_code'] == 'IRR') ? $amount/10 : ($amount);
    $callbackURL = site_url() . '/?nxd_nextpay=verify';
    $order_id = md5(uniqid(rand(), true));


    $_SESSION['nx_donate'] = array(
        "order_id" => $order_id ,
        "amount" => $amount ,
        "name" => $name ,
        "email" => $email,
        "desc" => $desc

    );

    $client = new SoapClient('https://api.nextpay.org/gateway/token.wsdl', array('encoding' => 'UTF-8'));
    $result = $client->TokenGenerator(
        array(
            'api_key' 	=> $apikey,
            'amount' 	=> $amount,
            'order_id' 	=> $order_id,
            'callback_uri' 	=> $callbackURL
        )
    );
    $result = $result->TokenGeneratorResult;
    //Redirect to Nextpay
    if(intval($result->code) == -1)
    {
        Header('Location: https://api.nextpay.org/gateway/payment/'.$result->trans_id);
    }
    else
    {
        echo 'Error: ' . $result->code ;
        exit;
    }



}

function nx_donations_validate_nextpay() {

    if(!session_id()) {
        session_start();
    }

    if(isset($_REQUEST['trans_id']) && isset($_REQUEST['order_id'])){

        $pd_options = get_option(NextpayDonations::OPTION_DB_KEY);

        if($_SESSION['nx_donate']['order_id'] != $_REQUEST['order_id']){
            if($pd_options['return_page_failed']){
                wp_redirect( $pd_options['return_page_failed'] );
            }else{
                wp_redirect( site_url() );
            }
            exit;
        }



        $apikey = $pd_options['nextpay_api'];
        $amount = $_SESSION['nx_donate']['amount'];

        $client = new SoapClient('https://api.nextpay.org/gateway/verify.wsdl', array('encoding' => 'UTF-8'));
        $result = $client->PaymentVerification(
            array(
                'api_key' 	=> $apikey,
                'trans_id' => $_REQUEST['trans_id'],
                'amount' 	=> $amount,
                'order_id' 	=> $_REQUEST['order_id'],
            )
        );
        $result = $result->PaymentVerificationResult;

        if ($result->code == 0) {
            $name = $_SESSION['nx_donate']['name'] ;
            $email = $_SESSION['nx_donate']['email'] ;
            $desc = $_SESSION['nx_donate']['desc'] ;
            $admin_email = get_bloginfo('admin_email');
            $subject = 'پرداخت حمایت مالی';
            $body = "یک پرداخت توسط  : $name" .
                "\n\nایمیل : $email" .
                "\n\nتوضیحات : $desc" .
                "\n\nمبلغ پرداخت شده : $amount" ;
            wp_mail($admin_email, $subject, $body);

            if($pd_options['return_page_success']){
                wp_redirect( $pd_options['return_page_success'] );
            }else{
                wp_redirect( site_url() );
            }
            exit;
        }

        if($pd_options['return_page_failed']){
            wp_redirect( $pd_options['return_page_failed'] );
        }else{
            wp_redirect( site_url() );
        }
        exit;

    }else{
        wp_redirect( site_url() );
        exit;
    }


}
