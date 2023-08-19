<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\App;
use Slim\Psr7\Factory\ResponseFactory;

class HttpBasicAuth
{
    public $app;

    protected $realm;

    protected $username;

    protected $password;

    /**
     * Constructor
     *
     * @param   string  $username   The HTTP Authentication username
     * @param   string  $password   The HTTP Authentication password
     * @param   string  $realm      The HTTP Authentication realm
     */
    public function __construct($username, $password, $realm, App $app)
    {
        $this->username = $username;
        $this->password = $password;
        $this->realm = $realm;
        $this->app = $app;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {

        if (!$this->needAuth($request)) {
            return $handler->handle($request);
        }

        $authUser = null;
        $authPass = null;

        if ($authUser == null && preg_match("/Basic\s+(.*)$/i", $request->getHeaderLine("Authorization"), $matches)) {
            $explodedCredential = explode(":", base64_decode($matches[1]), 2);
            if (count($explodedCredential) == 2) {
                list($authUser, $authPass) = $explodedCredential;
            }
        }

        if ($authUser && $authPass && $authUser === $this->username && $authPass === $this->password) {
            return $handler->handle($request);
        } else {
            $response = (new ResponseFactory)->createResponse(401)
                        ->withHeader('WWW-Authenticate', sprintf('Basic realm="%s"', "product"));
            return $response;
        }
    }

    private function needAuth(Request $request) {

        $secured_urls = array(
            array("path" => "/product.+")
        );
        
        foreach ($secured_urls as $surl) {
            $patternAsRegex = $surl['path'];
            if (substr($surl['path'], -1) === '/') {
                $patternAsRegex = $patternAsRegex . '?';
            }
            $patternAsRegex = '@^' . $patternAsRegex . '$@';

            if (preg_match($patternAsRegex, $request->getUri()->getPath())) {
                return true;
            }
        }
        return false;
    }
}
