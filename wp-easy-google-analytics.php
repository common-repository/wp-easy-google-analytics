<?php
/*
  Plugin Name: WP Easy Google Analytics
  Description: Easy Google Analytics code integration on WordPress website
  Author: Biagio Di Pace
  Author URI: http://www.bdpweb.it/
  Version: 1.1
  License: GPL2
 */
/*  Copyright 2017 Biagio Di Pace  (email : biagio@bdpweb.it)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

 */


// Register function to be called when the plugin is activated
register_activation_hook(__FILE__, 'wp_easy_google_analytics_options');
register_deactivation_hook(__FILE__, 'wp_easy_google_analytics_uninstall');

//

function wp_easy_google_analytics_options() {
    if (get_option('wp_easy_google_analytics_options') === false) {
        $new_options['ga_account_name'] = "UA-000000-0";
        $new_options['version'] = "1.0";
        add_option('wp_easy_google_analytics_options', $new_options);
    }
}

function wp_easy_google_analytics_uninstall() {
    if (get_option('wp_easy_google_analytics_options') != false) {
        delete_option('wp_easy_google_analytics_options');
    }
}

add_action('admin_menu', 'wp_easy_google_analytics_settings_menu');

function wp_easy_google_analytics_settings_menu() {
    add_options_page('WP Easy Google Analytics Configuration', 'WP Easy Google Analytics', 'manage_options', 'wp_easy_google_analytics', 'wp_easy_google_analytics_config_page');
}

function wp_easy_google_analytics_config_page() {
    // Retrieve plugin configuration options from database
    $options = get_option('wp_easy_google_analytics_options');
    ?>
    <div class="wrap">
        <h2>WP Easy Google Analytics</h2>
        <?php if (isset($_GET['message']) && $_GET['message'] == '1') {
            ?>
            <div id='message' class='updated fade'><p><strong>Settings
                        Saved</strong></p></div>
        <?php } ?>
        <form method="post" action="admin-post.php">
            <input type="hidden" name="action"
                   value="save_wp_easy_google_analytics_options" />
            <!-- Adding security through hidden referrer field -->
            <?php wp_nonce_field('wp_easy_google_analytics'); ?>
            Account Name: <input type="text" name="ga_account_name"
                                 value="<?php echo esc_html($options['ga_account_name']);
            ?>"/><br />
            <br />
            <input type="submit" value="Submit"
                   class="button-primary"/>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'wp_easy_google_analytics_admin_init');

function wp_easy_google_analytics_admin_init() {
    add_action('admin_post_save_wp_easy_google_analytics_options', 'process_wp_easy_google_analytics_options');
}

function process_wp_easy_google_analytics_options() {
    // Check that user has proper security level
    if (!current_user_can('manage_options'))
        wp_die('Not allowed');
    // Check that nonce field created in configuration form
    // is present
    check_admin_referer('wp_easy_google_analytics');

    // Retrieve original plugin options array
    $options = get_option('wp_easy_analytics_options');
    // Cycle through all text form fields and store their values
    // in the options array
    foreach (array('ga_account_name') as $option_name) {
        if (isset($_POST[$option_name])) {
            $options[$option_name] = sanitize_text_field($_POST[$option_name]);
        }
    }


// Store updated options array to database
    update_option('wp_easy_google_analytics_options', $options);

    // Redirect the page to the configuration form that was
    // processed
    wp_redirect(add_query_arg(
                    array('page' => 'wp_easy_google_analytics',
        'message' => '1'), admin_url('options-general.php')));

    exit;
}

add_action('wp_head', 'wp_easy_google_analytics_script');

function wp_easy_google_analytics_script() {
    $options = get_option('wp_easy_google_analytics_options');
    ?>
    <script type="text/javascript">
        (function (i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function () {
                (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
        })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

        ga('create', '<?php echo esc_html($options['ga_account_name']); ?>', 'auto');
        ga('send', 'pageview');

    </script>
    <?php
}
