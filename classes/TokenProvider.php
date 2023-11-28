<?php 

namespace _29kSMTP;

class TokenProvider implements \PHPMailer\PHPMailer\OAuthTokenProvider {
    /** 
        *@var Utils 
    */
    private $utils;
    public function __construct(Utils $utils) {
        $this->utils = $utils;
    }
    function getOauth64() {
        $access_token = $this->utils->getClient()->getAccessToken($this->utils->getStoredRefreshToken(), $this->utils->getStoredAccessToken(), $this->utils->getStoredIdToken(), function(string $accessToken, string $idToken) {
            $this->utils->updateAccesstoken($accessToken);
            $this->utils->updateIdToken($idToken);
        });
        error_log('Token provider\'s access token: ' . $access_token);
        $email = 'arjuntanwar@29kreativ.com';
        $authString = base64_encode("user=$email\001auth=Bearer $access_token\001\001");
        return $authString;
    }
};
?>