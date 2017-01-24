<?php
/*
Plugin Name: Nextpay Donations
Plugin URI: https://www.tipsandtricks-hq.com/nextpay-donations-widgets-plugin
Description: Easy and simple setup and insertion of Nextpay donate buttons with a shortcode or through a sidebar Widget. Donation purpose can be set for each button. A few other customization options are available as well.
Author: Tips and Tricks HQ, Johan Steen
Author URI: https://www.tipsandtricks-hq.com/
Version: 1.9.3
License: GPLv2 or later
Text Domain: nextpay-donations
*/

include_once('nextpay_utility.php');

/** Load all of the necessary class files for the plugin */
spl_autoload_register('NextpayDonations::autoload');

/**
 * Init Singleton Class for Nextpay Donations.
 *
 * @package Nextpay Donations
 * @author  Nextpay Team
 */
class NextpayDonations
{
    /** Holds the plugin instance */
    private static $instance = false;

    /** Define plugin constants */
    const MIN_PHP_VERSION  = '5.2.4';
    const MIN_WP_VERSION   = '3.0';
    const OPTION_DB_KEY    = 'nextpay_donations_options';
    const TEXT_DOMAIN      = 'nextpay-donations';
    const FILE             = __FILE__;


    // -------------------------------------------------------------------------
    // Define constant data arrays
    // -------------------------------------------------------------------------

    private $currency_codes = array(
        'IRR' => 'ریال',
        'IRT' => 'تومان',
    );

    /**
     * Singleton class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Initializes the plugin by setting localization, filters, and
     * administration functions.
     */
    private function __construct()
    {
        if (!$this->testHost()) {
            return;
        }

        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        $admin = new NextpayDonations_Admin();
        $admin->setOptions(
            get_option(self::OPTION_DB_KEY),
            $this->currency_codes
        );

        add_filter('widget_text', 'do_shortcode');
        add_shortcode('nextpay-donation', array(&$this,'nextpayShortcode'));
        add_action('wp_head', array($this, 'addCss'), 999);

        add_action(
            'widgets_init',
            create_function('', 'register_widget("NextpayDonations_Widget");')
        );
    }

    /**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @param  string  $classname  The name of the class
     * @return null    Return early if the class name does not start with the
     *                 correct prefix
     */
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'src_'.$className);
        $fileName .='.php';

        require $fileName;
    }


    /**
     * Fired when the plugin is uninstalled.
     */
    public function uninstall()
    {
        delete_option('nextpay_donations_options');
        delete_option('widget_nextpay_donations');
    }

    /**
     * Adds inline CSS code to the head section of the html pages to center the
     * Nextpay button.
     */
    public function addCss()
    {
        $opts = get_option(self::OPTION_DB_KEY);
        if (isset($opts['center_button']) and $opts['center_button'] == true) {
            echo '<style type="text/css">'."\n";
            echo '.nextpay-donations { text-align: center !important }'."\n";
            echo '</style>'."\n";
        }
    }

    /**
     * Create and register the Nextpay shortcode
     */
    public function nextpayShortcode($atts)
    {
        extract(
            shortcode_atts(
                array(
                    'name' => '',
                    'email' => '',
                    'desc' => '',
                    'amount' => '',
                ),
                $atts
            )
        );

        return $this->generateHtml(
            $name,
            $email,
            $desc,
            $amount,
            null
        );
    }

    /**
     * Generate the Nextpay button HTML code
     * @param bool $name
     * @param bool $email
     * @param bool $desc
     * @param int $amount
     * @param string $currency_code
     * @return string
     */
    public function generateHtml(
        $name = null,
        $email = null,
        $desc = null,
        $amount = null,
        $currency_code = null
    ) {
        $pd_options = get_option(self::OPTION_DB_KEY);
        // Set overrides for purpose and reference if defined
        if ( empty( $name ) ) { $name = array_key_exists('name', $pd_options) ? $pd_options['name'] : false ; }
        if ( empty( $email ) ) { $email = array_key_exists('email', $pd_options) ? $pd_options['email'] : false ; }
        if ( empty( $desc ) ) { $desc = array_key_exists('desc', $pd_options) ? $pd_options['desc'] : false ; }
        if ( empty( $amount ) ) { $amount = array_key_exists('amount', $pd_options) ? $pd_options['amount'] : '' ; }
        $currency_code = (!$currency_code) ? $pd_options['currency_code'] : $currency_code;

        $data = array(
            'pd_options' => $pd_options,
            'name' => $name,
            'email' => $email,
            'desc' => $desc,
            'amount' => $amount,
            'currency_code' => $currency_code,
        );

        return NextpayDonations_View::render('nextpay-button', $data);
    }

    // -------------------------------------------------------------------------
    // Environment Checks
    // -------------------------------------------------------------------------

    /**
     * Checks PHP and WordPress versions.
     */
    private function testHost()
    {
        // Check if PHP is too old
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            // Display notice
            add_action('admin_notices', array(&$this, 'phpVersionError'));
            return false;
        }

        // Check if WordPress is too old
        global $wp_version;
        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'wpVersionError'));
            return false;
        }
        return true;
    }

    /**
     * Displays a warning when installed on an old PHP version.
     */
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>'.
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Displays a warning when installed in an old Wordpress version.
     */
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Get the name of this plugin.
     *
     * @return string The plugin name.
     */
    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }
}

add_action('plugins_loaded', array('NextpayDonations', 'getInstance'));
