<?php
/**
 * Nextpay Donations Settings.
 *
 * Class that renders out the HTML for the settings screen and contains helpful
 * methods to simply the maintainance of the admin screen.
 *
 * @package Nextpay Donations
 * @author  Nextpay Team
 */
class NextpayDonations_Admin
{
    private $plugin_options;
    private $currency_codes;

    const PAGE_SLUG = 'nextpay-donations-options';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
    }

    /**
     * To be deprecated soon!
     */
    public function setOptions(
        $options,
        $code
    ) {
        $this->plugin_options = $options;
        $this->currency_codes = $code;
    }


    /**
     * Register the Menu.
     */
    public function menu()
    {
        add_menu_page(
            'تنظمیات حمایت مالی نکست پی',
            'حمایت مالی',
            'administrator',
            self::PAGE_SLUG,
            array($this, 'renderpage')
        );
    }

    public function renderpage()
    {
        $data = array(
            'pageSlug'    => NextpayDonations_Admin::PAGE_SLUG,
            'optionDBKey' => NextpayDonations::OPTION_DB_KEY,
        );
        echo NextpayDonations_View::render('admin', $data);
    }

    /**
     * Load CSS and JS on the settings page.
     */
    public function scripts($hook)
    {

        if ($hook != 'toplevel_page_nextpay-donations-options') {
            return;
        }
        $plugin = get_plugin_data(NextpayDonations::FILE, false, false);
        $version = $plugin['Version'];

        wp_register_style(
            'nextpay-donations',
            plugins_url('assets/tabs.css', NextpayDonations::FILE),
            array(),
            $version
        );
        wp_enqueue_style('nextpay-donations');

        wp_enqueue_script(
            'nextpay-donations',
            plugins_url('assets/tabs.js', NextpayDonations::FILE),
            array('jquery'),
            $version,
            false
        );
    }

    /**
     * Register the settings.
     */
    public function init()
    {
        add_settings_section(
            'account_setup_section',
            __('تنظمات درگاه', NextpayDonations::TEXT_DOMAIN),
            array($this, 'accountSetupCallback'),
            self::PAGE_SLUG
        );
        add_settings_field(
            'nextpay_api',
            __('کلید مجوزدهی', NextpayDonations::TEXT_DOMAIN),
            array($this, 'nextpayApiCallback'),
            self::PAGE_SLUG,
            'account_setup_section',
            array(
                'label_for' => 'nextpay_api',
                'description' => __(
                    'کلید مجوزدهی نکست پی درگاه شما',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );
        add_settings_field(
            'currency_code',
            __('واحد پولی', NextpayDonations::TEXT_DOMAIN),
            array($this, 'currencyCallback'),
            self::PAGE_SLUG,
            'account_setup_section',
            array(
                'label_for' => 'currency_code',
                'description' => __(
                    'واحد پولی مورد استفاده ',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_section(
            'optional_section',
            __('تنظمیات اختیاری', NextpayDonations::TEXT_DOMAIN),
            '',
            self::PAGE_SLUG
        );
        add_settings_field(
            'return_page_success',
            __('صفحه بازگشت (موفق)', NextpayDonations::TEXT_DOMAIN),
            array($this, 'returnPageSuccessCallback'),
            self::PAGE_SLUG,
            'optional_section',
            array(
                'label_for' => 'return_page_success',
                'description' => __(
                    'صفحه ای که مایل هستید تا بعد از پرداخت موفق کاربر را به آنجا هدایت کنید.',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_field(
            'return_page_failed',
            __('صفحه بازگشت (ناموفق)', NextpayDonations::TEXT_DOMAIN),
            array($this, 'returnPageFailedCallback'),
            self::PAGE_SLUG,
            'optional_section',
            array(
                'label_for' => 'return_page_failed',
                'description' => __(
                    'صفحه ای که مایل هستید تا بعد از پرداخت ناموفق کاربر را به آنجا هدایت کنید.',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_section(
            'default_section',
            __('مقادیر پیشفرض', NextpayDonations::TEXT_DOMAIN),
            '',
            self::PAGE_SLUG
        );
        add_settings_field(
            'amount',
            __('مقدار پرداخت', NextpayDonations::TEXT_DOMAIN),
            array($this, 'amountCallback'),
            self::PAGE_SLUG,
            'default_section',
            array(
                'label_for' => 'amount',
                'description' => __(
                    'مقدار پیشفرض پرداختی',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_field(
            'name',
            __('درخواست نام', NextpayDonations::TEXT_DOMAIN),
            array($this, 'nameCallback'),
            self::PAGE_SLUG,
            'default_section',
            array(
                'label_for' => 'name',
                'description' => __(
                    'درخواست نام پرداخت کننده',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_field(
            'email',
            __('درخواست ایمیل', NextpayDonations::TEXT_DOMAIN),
            array($this, 'emailCallback'),
            self::PAGE_SLUG,
            'default_section',
            array(
                'label_for' => 'email',
                'description' => __(
                    'درخواست ایمیل پرداخت کننده',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );

        add_settings_field(
            'desc',
            __('درخواست توضیحات', NextpayDonations::TEXT_DOMAIN),
            array($this, 'descCallback'),
            self::PAGE_SLUG,
            'default_section',
            array(
                'label_for' => 'desc',
                'description' => __(
                    'درخواست توضیحات از پرداخت کننده',
                    NextpayDonations::TEXT_DOMAIN
                ),
            )
        );


        add_settings_section(
            'tab_splitter',
            '',
            array($this, 'tabsCallback'),
            self::PAGE_SLUG
        );

        add_settings_section(
            'extras_section',
            __('اضافات', NextpayDonations::TEXT_DOMAIN),
            array($this, 'extrasCallback'),
            self::PAGE_SLUG
        );

        add_settings_field(
            'center_button',
            __(
                'فرم در وسط صفحه قرار گیرد',
                NextpayDonations::TEXT_DOMAIN
            ),
            array($this, 'centerButtonCallback'),
            self::PAGE_SLUG,
            'extras_section',
            array(
                'label_for' => 'center_button',
                'description' => ''
            )
        );
        add_settings_field(
            'new_tab',
            __(
                'باز کردن نکست پی در صفحه جدید',
                NextpayDonations::TEXT_DOMAIN
            ),
            array($this, 'newTabCallback'),
            self::PAGE_SLUG,
            'extras_section',
            array(
                'label_for' => 'new_tab',
                'description' => ''
            )
        );



        add_settings_section(
            'tab_splitter2',
            '',
            array($this, 'tabsCallbackHelp'),
            self::PAGE_SLUG
        );

        add_settings_section(
            'help_section',
            __('راهنمایی', NextpayDonations::TEXT_DOMAIN),
            array($this, 'helpCallback'),
            self::PAGE_SLUG
        );





        register_setting(
            NextpayDonations::OPTION_DB_KEY,
            NextpayDonations::OPTION_DB_KEY
        );
    }

    // -------------------------------------------------------------------------
    // Section Callbacks
    // -------------------------------------------------------------------------

    public function accountSetupCallback()
    {
        printf(
            '<p>%s</p>',
            __('فیلد های اجباری.', NextpayDonations::TEXT_DOMAIN)
        );
    }

    public function tabsCallback()
    {
        echo "</div><div id='nextpay-donations-tab-content-2'>";
    }

    public function tabsCallbackHelp()
    {
        echo "</div><div id='nextpay-donations-tab-content-3'>";
    }

    public function extrasCallback()
    {
        printf(
            '<p>%s</p>',
            __(
                'موارد اختیاری',
                NextpayDonations::TEXT_DOMAIN
            )
        );
    }

    public function helpCallback()
    {
        echo '<p>برای قرار دادن دکمه و یا فرم حمایت مالی در صفحه مورد نظر میتوانید از تگ زیر استفاده نمایید</p>' ;
        echo '<code>[nextpay-donation]</code>' ;
        echo '<p>نمونه کامل استفاده از  تگ :</p>';
        echo '<code>[nextpay-donation amount=2000 name=\'true\' email=\'true\' desc=\'true\' ]</code>';
        echo '<p>همینطور میتوانید در قسمت ابزارک ها , ابزارک مربوط به افزونه حمایت مالی نکست را به راحتی اضافه کنید';
    }

    // -------------------------------------------------------------------------
    // Fields Callbacks
    // -------------------------------------------------------------------------

    public function nextpayApiCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='nextpay_api' ";
        echo "name='{$optionKey}[nextpay_api]'' ";
        echo "value='{$options['nextpay_api']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function currencyCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<select id='currency_code' name='{$optionKey}[currency_code]'>";
        if (isset($options['currency_code'])) {
            $current_currency = $options['currency_code'];
        } else {
            $current_currency = 'USD';
        }
        foreach ($this->currency_codes as $key => $code) {
            echo '<option value="'.$key.'"';
            if ($current_currency == $key) {
                echo ' selected="selected"';
            }
            echo '>'.$code.'</option>';
        }
        echo "</select>";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function returnPageSuccessCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='return_page_success' ";
        echo "name='{$optionKey}[return_page_success]'' ";
        echo "value='{$options['return_page_success']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function returnPageFailedCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='return_page_failed' ";
        echo "name='{$optionKey}[return_page_failed]'' ";
        echo "value='{$options['return_page_failed']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }


    public function pageStyleCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='page_style' ";
        echo "name='{$optionKey}[page_style]'' ";
        echo "value='{$options['page_style']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }


    public function amountCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        echo "<input class='regular-text' type='text' id='amount' ";
        echo "name='{$optionKey}[amount]'' ";
        echo "value='{$options['amount']}' />";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function nameCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['name']) ?
            $options['name'] :
            false;
        echo "<input type='checkbox' id='name' ";
        echo "name='{$optionKey}[name]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function emailCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['email']) ?
            $options['email'] :
            false;
        echo "<input type='checkbox' id='email' ";
        echo "name='{$optionKey}[email]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function descCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['desc']) ?
            $options['desc'] :
            false;
        echo "<input type='checkbox' id='desc' ";
        echo "name='{$optionKey}[desc]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }


    public function centerButtonCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['center_button']) ?
            $options['center_button'] :
            false;
        echo "<input type='checkbox' id='center_button' ";
        echo "name='{$optionKey}[center_button]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function newTabCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['new_tab']) ?
            $options['new_tab'] :
            false;
        echo "<input type='checkbox' id='new_tab' ";
        echo "name='{$optionKey}[new_tab]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }

    public function removeLfCallback($args)
    {
        $optionKey = NextpayDonations::OPTION_DB_KEY;
        $options = get_option($optionKey);
        $checked = isset($options['remove_lf']) ?
            $options['remove_lf'] :
            false;
        echo "<input type='checkbox' id='remove_lf' ";
        echo "name='{$optionKey}[remove_lf]' value='1' ";
        if ($checked) {
            echo 'checked ';
        }
        echo "/>";

        echo "<p class='description'>{$args['description']}</p>";
    }


    // -------------------------------------------------------------------------
    // HTML and Form element methods
    // -------------------------------------------------------------------------

    /**
     * Checkbox.
     * Renders the HTML for an input checkbox.
     *
     * @param   string  $label      The label rendered to screen
     * @param   string  $name       The unique name to identify the input
     * @param   boolean $checked    If the input is checked or not
     */
    public static function checkbox($label, $name, $checked)
    {
        printf('<input type="checkbox" name="%s" value="true"', $name);
        if ($checked) {
            echo ' checked';
        }
        echo ' />';
        echo ' '.$label;
    }
}
