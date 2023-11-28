<?php
namespace _29kSMTP;
abstract class OAuthClient {
    /** 
        *@var string
    */
    protected $clientId;
    /** 
        *@var string 
    */
    protected $clientSecret;
    /** 
        *@var string  
    */
    protected $codeUrl;
    /** 
        *@var string  
    */
    protected $tokenUrl;
    /** 
        *@var string  
    */
    protected $scopes;
    /** 
        *@var string  
    */
    protected $redirectUrl;
    protected function __construct(string $clientId, string $clientSecret, string $codeUrl, string $tokenUrl, string $scopes, string $redirectUrl) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->codeUrl = $codeUrl;
        $this->tokenUrl = $tokenUrl;
        $this->scopes = $scopes;
        $this->redirectUrl = $redirectUrl;
    }
    abstract public function getCodeUrl(): string;
    abstract public function exchangeCode(string $code, callable $updateCallback = null): string;
    abstract protected function exchangeRefreshToken(string $refreshToken, callable $updateCallback = null): string;
    abstract protected function accessTokenExpired(string $idToken): bool;
    abstract public function getAccessToken(string $refreshToken, string $oldAccessToken, string $idToken, callable $updateCallback = null): string;
    abstract public function makeRequest(string $refreshToken, string $oldAccessToken, string $idToken, string $endpoint, array $request_body, callable $updateCallback = null): array;
}
?>