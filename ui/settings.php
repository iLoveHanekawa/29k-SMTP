<div class="wrap">
    <?php 
        $current_google_api_key = get_option('29k_smtp_google_api_key', '');
        $current_google_secret_api_key = get_option('29k_smtp_google_secret_api_key', '');
        $current_smtp_option = get_option('29k_smtp_option', '');
        $current_sendgrid_api_key = get_option('29k_smtp_sendgrid_api_key', '');
        $use_google_api = get_option('29k_smtp_use_google_api', '');
        $recipient = get_option('29k_smtp_recipient');
    ?>
    <h1>29kreativ SMTP Settings</h1>
    <h3>API keys</h3>
    <form class="um-settings-form" method="post" action="">
        <table class='form-table'>
        
            <!-- put new code here -->

            <tr valign="top">
                <th scope="row">
                    <label>Email Service Provider:</label>
                </th>
                <td>
                    <label>
                        <input type="radio" name="29k_smtp_option" value="google" <?php checked($current_smtp_option, 'google'); ?>>
                        Google
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="29k_smtp_option" value="sendgrid" <?php checked($current_smtp_option, 'sendgrid'); ?>>
                        Sendgrid
                    </label>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label>Google API or SMTP</label>
                </th>
                <td>
                    <b>Warning! This option will only work if "Google" is selected above</b>
                    <br/>
                    <label>
                        <input type="checkbox" name="29k_smtp_use_google_api" value="true" <?php checked($use_google_api, 'true'); ?>>
                        Use Google APIs (if checked, Google API is used; if unchecked, SMTP server is used to send emails)
                    </label>
                    <br>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="29k_smtp_sendgrid_api_key">Sendgrid API Key:</label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="29k_smtp_sendgrid_api_key" name="29k_smtp_sendgrid_api_key" value="<?php echo esc_attr($current_sendgrid_api_key); ?>">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="29k_smtp_google_api_key">Google ClientID Key:</label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="29k_smtp_google_api_key" name="29k_smtp_google_api_key" value="<?php echo esc_attr($current_google_api_key); ?>">
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="29k_smtp_google_secret_api_key">Google Client Secret Key:</label>
                </th>
                <td>
                    <input type="text" class="regular-text" id="29k_smtp_google_secret_api_key" name="29k_smtp_google_secret_api_key" value="<?php echo esc_attr($current_google_secret_api_key); ?>">
                </td>
            </tr>
            
            
            <tr valign="top">
                <th scope="row">
                    <label for='29k_smtp_recipient'>Default Email Recipient:</label>
                </th>
                <td>
                    <input type="text" class="regular-text" id='29k_smtp_recipient' name='29k_smtp_recipient' value="<?php echo esc_attr($recipient); ?>">
                    <p>Note: This will be the default for SMTP only if the plugin's REST route: '/send' is used. WP_mail() uses its own recipient field which must be provided in function calls so this email won't be used there. </p>
                <p>Note: However, it will be the intended default if API is used. If this value is incorrect, emails will be sent to admin email.</p>
                </td>
            </tr>
            
        </table>
        <a class="button-secondary" href=<?php echo site_url() . '/wp-json/_29kreativ/v1/oauth/smtp/permission?provider=google' ?>>Get permission from Google</a>
        <br/>
        <p>Warning! Always Save before Testing!</p>
        <a class="button-secondary" href=<?php echo site_url() . '/wp-json/_29kreativ/v1/oauth/smtp/' . ($use_google_api === 'true' ? 'apisend' : 'send') ?>>Test mail</a>
        <br/>
        <br/>
        <button type="submit" name="29k_smtp_settings_submit" class="button-primary">Save Settings</button>
    </form>
</div>