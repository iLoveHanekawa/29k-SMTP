<?php 
namespace _29kSMTP;
class Error {
    /** 
        *@var string 
    */
    private $key;
    /** 
        *@var array 
    */
    private $messages;
    public function __construct(string $key, array $messages) {
        $this->key = $key;
        $this->messages = $messages;
    }
    public function getKey() {
        return $this->key;
    }
    public function getMessages() {
        return $this->messages;
    }
}
?>