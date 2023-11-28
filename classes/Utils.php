<?php 
namespace _29kSMTP;
class Utils {
    /** 
        *@var string 
    */
    private $refreshTokenKey;
    /** 
        *@var string 
    */
    private $accessTokenKey;
    /** 
        *@var string 
    */
    private $idTokenKey;
    /** 
        *@var int 
    */
    private $adminId;
        /** 
        *@var string 
    */
    private $SMTPProvider;
    /** 
        *@var string 
    */
    private $SMTPHost;
    private $SMTPUsername = '';
    private $SMTPFromEmail = '';
    private $STMPFromName = '';
    private $SMTPPassword = '';
    /** 
        *@var OAuthClient 
    */
    private $client;
    /** 
        *@var string 
    */
    private $defaultRecipient;
    private static $oAuthProviderList = ['google'];
    private function setConfig(string $provider) {
        // by default its wordpress@localhost which wp_mail() doesn't allow, we need to give it a different fromEmail
        // note that the email will not be 'something@service.com' instead it will be the authorized account's email
        switch($provider) {
            case "google":
                $this->SMTPHost = 'smtp.gmail.com';
                $this->client = new GoogleClient();
                $this->SMTPFromEmail = 'something@service.com';
                break;
            case "sendgrid":
                $this->SMTPHost = 'smtp.sendgrid.net';
                $this->client = null;
                $this->SMTPFromEmail = 'abhishek@29kreativ.com';
                $this->SMTPUsername = 'apikey';
                $this->SMTPPassword = get_option('29k_smtp_sendgrid_api_key', '');
                break;
            default:
                break;
        }
    }
    public function __construct(string $providerOption = null) {
        if(!$providerOption) $providerOption = get_option('29k_smtp_option');
        $this->defaultRecipient = get_option('29k_smtp_recipient');
        if($this->defaultRecipient === false || !is_email( $this->defaultRecipient)) $this->defaultRecipient = get_option('admin_email');
        $this->STMPFromName = '29k Team';
        $this->SMTPProvider = $providerOption;
        $this->setConfig($providerOption);
        $this->refreshTokenKey = $providerOption . '_refresh_token';
        $this->accessTokenKey = $providerOption . '_access_token';
        $this->idTokenKey = $providerOption . '_id_token';
        $adminUser = get_user_by('login', 'admin');
        $this->adminId = $adminUser->ID;
    }
    public function getDefaultRecipient() {
        return $this->defaultRecipient;
    }
    public function updateRefreshToken(string $value) {
        update_user_meta($this->adminId, $this->refreshTokenKey, $value);
    }
    public function updateAccesstoken(string $value) {
        update_user_meta($this->adminId, $this->accessTokenKey, $value);
    }
    public function updateIdToken(string $value) {
        update_user_meta($this->adminId, $this->idTokenKey, $value);
    }
    public function getStoredRefreshToken(): string {
        return get_user_meta($this->adminId, $this->refreshTokenKey, true);
    }
    public function getStoredAccessToken(): string {
        return get_user_meta($this->adminId, $this->accessTokenKey, true);
    }
    public function getStoredIdToken(): string {
        return get_user_meta($this->adminId, $this->idTokenKey, true);
    }
    public function getClient(): OAuthClient {
        return $this->client;
    }
    public function getHost(): string {
        return $this->SMTPHost;
    }
    public function isOAuthClient(): bool {
        return ($this->client instanceof OAuthClient);
    }
    public function getUsername() {
        return $this->SMTPUsername;
    }
    public function getPassword() {
        return $this->SMTPPassword;
    }
    public function getFromEmail() {
        return $this->SMTPFromEmail;
    }
    public function getFromName() {
        return $this->STMPFromName;
    }
    public static function getOAuthProviderList() {
        return Utils::$oAuthProviderList;
    }
    public function getProviderName() {
        return $this->SMTPProvider;
    }
}
?>