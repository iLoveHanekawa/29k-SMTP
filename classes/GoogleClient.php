<?php 
namespace _29kSMTP;
require_once __DIR__ . '/OAuthClient.php';
require_once __DIR__ . '/Error.php';

class GoogleClient extends OAuthClient {
    public function __construct() {
        parent::__construct(
            get_option('29k_smtp_google_api_key', ''),
            get_option('29k_smtp_google_secret_api_key'),
            'https://accounts.google.com/o/oauth2/v2/auth',
            'https://oauth2.googleapis.com/token',
            'https://mail.google.com/',
            site_url() . '/wp-json/_29kreativ/v1/oauth/smtp/code/google',
        );
    }
    public function getCodeUrl(): string {
        return $this->codeUrl . '?' . http_build_query([
            'scope'=> $this->scopes,
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'response_type' => 'code',
            'state' => 'state_parameter_passthrough_value',
            'redirect_uri' => $this->redirectUrl,
            'client_id' => $this->clientId,
            'prompt' => 'consent'
        ]);
    }
    public function exchangeCode(string $code, callable $updateCallback = null): string {
        $options = [
            'body' => http_build_query([
                'code' => $code, 
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUrl,
                'grant_type' => 'authorization_code',
            ]),
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ];
        $api_response = wp_remote_post( $this->tokenUrl, $options );
        $api_body = json_decode(wp_remote_retrieve_body($api_response));
        $access_token = $api_body->access_token;
        $updateCallback($api_body->refresh_token, $access_token, $api_body->id_token);
        return $access_token;
    }
    protected function exchangeRefreshToken(string $refreshToken, callable $updateCallback = null): string {
        error_log('in refreshToken request');
        error_log('refresh_token: ' . $refreshToken);
        $options = [
            'body' => http_build_query([
                'refresh_token' => $refreshToken, 
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUrl,
                'grant_type' => 'refresh_token'
            ]),
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        ];
        $response = wp_remote_post($this->tokenUrl, $options);
        $response_body = json_decode(wp_remote_retrieve_body($response));
        error_log('old access token: ' . get_option('google_access_token', ''));
        $accessToken = $response_body->access_token;
        error_log('new access token: ' . $accessToken);
        $updateCallback($accessToken, $response_body->id_token);
        return $accessToken;
    }
    protected function accessTokenExpired(string $idToken): bool {
        error_log('in AccessToken expired');
        error_log('id_token: ' . $idToken);
        if(!$idToken) {
            return true;
        }
        list($headerB64, $payloadB64, $signature) = explode('.', $idToken);
        $payload = base64_decode(strtr($payloadB64, '-_', '+/'));
        $payloadData = json_decode($payload, true);
        $currentTimestamp = time();
        if (isset($payloadData['exp']) && $payloadData['exp'] >= $currentTimestamp) {
            error_log('Returning with valid');
            return false;
        } else {
            error_log('Returning with expired');
            return true;
        }
    }
    public function getAccessToken(string $refreshToken, string $oldAccessToken, string $idToken, callable $updateCallback = null): string {
        if($this->accessTokenExpired($idToken)) {
            return $this->exchangeRefreshToken($refreshToken, $updateCallback);
        }
        else {
            return $oldAccessToken;
        }
    }
    public function makeRequest(string $refreshToken, string $oldAccessToken, string $idToken, string $endpoint, array $request_body, callable $updateCallback = null): array {
        $accessToken = $this->getAccessToken($refreshToken, $oldAccessToken, $idToken, $updateCallback);
        $options = [
            'body' => json_encode($request_body),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ),
        ];
        $response = wp_remote_post($endpoint, $options);
        $res_body = json_decode(wp_remote_retrieve_body($response));
        return $res_body;
    }
}
?>