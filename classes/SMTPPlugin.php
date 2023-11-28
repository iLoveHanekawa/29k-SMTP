<?php 
namespace _29kSMTP;
require_once __DIR__ . '/BasePlugin.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
use WP_REST_Request;
use PHPMailer;

class SMTPPlugin extends BasePlugin {
    private static $instance = null;
    private function __construct() {}
    public static function getPluginInstance(): SMTPPlugin {
        if(self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function setPHPMailer(callable $callback) {
        add_action('init', function() use(&$callback) {
            add_action('phpmailer_init', function(PHPMailer\PHPMailer\PHPMailer $mail) use(&$callback) {
                $callback($mail);
            }, 10, 1 );
        });
    }
    public function addOAuthRestRoutes(array $providers) {
        foreach($providers as $provider) {
            $this->addRestRoute('GET', '_29kreativ/v1/oauth/smtp/code', $provider, function (WP_REST_Request $request) use(&$provider) {
                $utils = new Utils($provider);
                $clientController = new SMTPClientController($utils);
                $clientController->getTokens($request);
            });
        }
    }
}
?>