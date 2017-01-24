<?php
/**
 * The Class for the Widget.
 *
 * @package  Nextpay Donations
 * @author   Johan Steen <artstorm at gmail dot com>
 */
class NextpayDonations_Widget extends WP_Widget
{
    /**
     * Register the Widget.
     */
    protected $defaults;
    public function __construct()
    {

        $this->defaults = array(
            'title' => __('Donate', NextpayDonations::TEXT_DOMAIN),
            'text' => '',
            'amount' => '',
        );

        $widget_ops = array(
            'classname' => 'widget_nextpay_donations',
            'description' => __(
                'Nextpay Donation Button and Form',
                NextpayDonations::TEXT_DOMAIN
            )
        );
        parent::__construct('nextpay_donations', 'Nextpay Donations', $widget_ops);
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        extract($args);

        $title = null; $text = null; $name = null; $email= null; $desc= null ; $amount = null;

        // global $nextpay_donations;
        $nextpay_donations = NextpayDonations::getInstance();

        // Get the settings
        if (! empty( $instance['title'] ) ) { $title = apply_filters('widget_title', $instance['title']); }
        if (! empty( $instance['text'] ) ) { $text = $instance['text']; }
        if (! empty( $instance['amount'] ) ) { $amount = $instance['amount']; }

        $name = $instance[ 'name' ] ? 'true' : 'false';
        $email = $instance[ 'email' ] ? 'true' : 'false';
        $desc = $instance[ 'desc' ] ? 'true' : 'false';

        echo $before_widget;
        if ($title) {
            echo $before_title . $title . $after_title;
        }
        if ($text) {
            echo wpautop($text);
        }
        echo $nextpay_donations->generateHtml($name ,$email ,$desc ,$amount ,null);
        echo $after_widget;
    }
    
    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        $instance['title'] = strip_tags(stripslashes($new_instance['title']));
        $instance['text'] = $new_instance['text'];
        $instance['amount'] = $new_instance['amount'];
        $instance['name'] = $new_instance['name'];
        $instance['email'] = $new_instance['email'];
        $instance['desc'] = $new_instance['desc'];


        return $instance;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance)
    {
        // Default Widget Settings

        $instance = wp_parse_args((array) $instance, $this->defaults);

        $data = array(
            'instance' => $instance,
            'title_id' => $this->get_field_id('title'),
            'title_name' => $this->get_field_name('title'),
            'text_id' => $this->get_field_id('text'),
            'text_name' => $this->get_field_name('text'),
            'amount_id' => $this->get_field_id('amount'),
            'amount_name' => $this->get_field_name('amount'),
            'name_id' => $this->get_field_id('name'),
            'name_name' => $this->get_field_name('name'),
            'email_id' => $this->get_field_id('email'),
            'email_name' => $this->get_field_name('email'),
            'desc_id' => $this->get_field_id('desc'),
            'desc_name' => $this->get_field_name('desc'),
        );
        echo NextpayDonations_View::render('widget-form', $data);
    }
}
