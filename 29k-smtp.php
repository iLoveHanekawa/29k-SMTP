<?php 
/*
* Plugin Name: 29K SMTP Plugin
* Description: SMTP plugin with Google and Sendgrid Support
* Version: 1.0
* Author: 29K team
*/

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/controllers/SMTPClientController.php';
require_once __DIR__ . '/classes/TokenProvider.php';
require_once __DIR__ . '/classes/SMTPPlugin.php';
require_once __DIR__ . '/classes/GoogleClient.php';

$plugin = _29kSMTP\SMTPPlugin::getPluginInstance();

$plugin->addOAuthRestRoutes(_29kSMTP\Utils::getOAuthProviderList());

$plugin->addPluginAdminSettings(
    plugin_basename(__FILE__),
    '29kreativ SMTP',
    '29K SMTP',
    '29k-smtp-plugin-settings',
    [
        '29k_smtp_google_api_key',
        '29k_smtp_google_secret_api_key',
        '29k_smtp_option',
        '29k_smtp_sendgrid_api_key',
        '29k_smtp_use_google_api',
        '29k_smtp_recipient'
    ],
    '29k_smtp_settings_submit',
    dirname(__FILE__) . '/ui/settings.php',
    'dashicons-email'
);

$plugin->addRestRoute('GET', '_29kreativ/v1/oauth/smtp', 'send', function (WP_REST_Request $request) {
    $utils = new _29kSMTP\Utils();
    $params = $request->get_query_params();
    $clientController = new _29kSMTP\SMTPClientController($utils);
    $sendTo = $utils->getDefaultRecipient();
    if(isset($params['recipient'])) {
        $sendTo = $params['recipient'];
    }
    if(isset($params['subject'])) {
        $subject = $params['subject'];
    }
    else {
        $subject = 'PHPMailer ' . $utils->getProviderName() . ' SMTP test';
    }
    if(isset($params['body'])) {
        $body = $params['body'];
    }
    else {
        $body = '<h1>This is a test email.</h1> <p>Service used: ' . $utils->getProviderName() . '</p>';
    }
    $clientController->send($sendTo, $subject, $body);
});

$plugin->addRestRoute('GET', '_29kreativ/v1/oauth/smtp', 'apisend', function (WP_REST_Request $request) {
    $utils = new _29kSMTP\Utils();
    $params = $request->get_query_params();
    $sendTo = $utils->getDefaultRecipient();
    if(isset($params['recipient'])) {
        $sendTo = $params['recipient'];
    }
    if(isset($params['subject'])) {
        $subject = $params['subject'];
    }
    else {
        $subject = 'PHPMailer ' . $utils->getProviderName() . ' SMTP test';
    }
    if(isset($params['body'])) {
        $body = $params['body'];
    }
    else {
        $body = '<h1>This is a test email.</h1> <p>Service used: Google API' . '</p>';
    }
    $clientController = new _29kSMTP\SMTPClientController($utils);
    $clientController->sendWithAPI($sendTo, $subject, $body);
});

$plugin->addRestRoute('GET', '_29kreativ/v1/oauth/smtp', 'permission', function (WP_REST_Request $request) {
    $params = $request->get_query_params();
    $provider = $params['provider'];
    $utils = new _29kSMTP\Utils($provider);
    $clientController = new _29kSMTP\SMTPClientController($utils);
    $clientController->getCode();
});

$plugin->setPHPMailer(function (PHPMailer\PHPMailer\PHPMailer $mail) {
    $utils = new _29kSMTP\Utils();
    $mail->IsSMTP();    
    $mail->Port = 587;
    $mail->ContentType = 'text/html';
    $mail->SMTPAuth = true;         
    // Exercise caution enabling this. This must only be enabled while testing in admin panel otherwise it stops the execution flow.
    // $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->From = $utils->getFromEmail();
    $mail->FromName = $utils->getFromName();    
    $mail->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
    $mail->Host = $utils->getHost();
    if($utils->isOAuthClient()) {
        $mail->AuthType = 'XOAUTH2';
        $tokenProvider = new _29kSMTP\TokenProvider($utils);
        $mail->setOAuth($tokenProvider);
    }
    else {
        $mail->Username = $utils->getUsername();
        $mail->Password = $utils->getPassword();
    }
    // TODO remove this before shipping to production
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    return $mail;
});

$plugin->addMailFrom('something@service.com');

$plugin->addMailFailedCallback(function (WP_ERROR $error) {
    error_log($error->get_error_message());
});

$plugin->addMailFromName('Arjun Tanwar');
?>