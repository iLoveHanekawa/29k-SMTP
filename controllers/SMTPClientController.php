<?php 

namespace _29kSMTP;
use WP_REST_Request;
require_once dirname(__DIR__) . '/classes/Utils.php';

class SMTPClientController {  
    /** 
        *@var Utils 
    */
    private $utils;
    public function __construct(Utils $utils) {
        $this->utils = $utils;
    }
    public function getCode() {
        wp_redirect($this->utils->getClient()->getCodeUrl());
        exit();
    }

    public function send(string $recipient_email, string $subject, string $html) {
        $to = $recipient_email;
        $body = '<html><body>' . $html . '</body></html>';
        // TODO change to admin email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );
        $mail = wp_mail( $to, $subject, $body, $headers );
        if($mail) { 
            error_log('success'); 
        } else { 
            error_log('failed'); 
        }
    }

    /**
        *@deprecated This function is deprecated as it is uses APIs instead of the Gmail SMTP server. Use the _29kreativ\SMTPClient::send() function instead.
     */
    // $recipient, $subject, $body, $headers,
    public function sendWithAPI(string $recipient_email, string $subject, string $message_body, array $headers = array()) {
        $access_token = $this->utils->getClient()->getAccessToken($this->utils->getStoredRefreshToken(), $this->utils->getStoredAccessToken(), $this->utils->getStoredIdToken(), function(string $accessToken, string $idToken) {
            $this->utils->updateAccesstoken($accessToken);
            $this->utils->updateIdToken($idToken);
        });
        $email_message = "From: arjuntanwar900@gmail.com\r\n";
        $email_message .= "To: $recipient_email\r\n";
        $email_message .= "Subject: $subject\r\n";
        $email_message .= "Content-Type: text/html; charset='UTF-8'\r\n";
        $email_message .= "\r\n";
        $email_message .= $message_body;

        // Base64 encode the RFC822 formatted email message
        $base64_message = base64_encode($email_message);

        $request_body = [
            "raw" => $base64_message,
        ];
        $options = [
            'body' => json_encode($request_body),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ];
        $response = wp_remote_post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', $options);
        $res_body = json_decode(wp_remote_retrieve_body($response));
        wp_send_json($res_body);
    }

    public function getTokens(WP_REST_Request $request) {
        $params = $request->get_query_params();
        $code = $params['code'];
        $client = $this->utils->getClient();
        $client->exchangeCode($code, function(string $refreshToken, string $accessToken, string $idToken) {
            $this->utils->updateAccesstoken($accessToken);
            $this->utils->updateRefreshToken($refreshToken);
            $this->utils->updateIdToken($idToken);
        });
        wp_redirect(site_url() . '/wp-admin/admin.php?page=29k-smtp-plugin-settings');
        exit();
    }
}

?>