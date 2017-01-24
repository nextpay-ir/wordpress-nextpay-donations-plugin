<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">
    <div id="icon-plugins" class="icon32"></div>
    <h2>حمایت مالی نکست پی</h2>

    <h2 class="nav-tab-wrapper">
        <ul id="nextpay-donations-tabs">
            <li id="nextpay-donations-tab_1" class="nav-tab nav-tab-active">تنظیمات عمومی</li>
            <li id="nextpay-donations-tab_2" class="nav-tab">تنظمیات پیشرفته</li>
            <li id="nextpay-donations-tab_3" class="nav-tab">راهنمایی</li>
        </ul>
    </h2>

    <form method="post" action="options.php">
        <?php settings_fields($optionDBKey); ?>
        <div id="nextpay-donations-tabs-content">
            <div id="nextpay-donations-tab-content-1">
                <?php do_settings_sections($pageSlug); ?>
            </div>
        </div>
        <?php submit_button(); ?>
    </form>
