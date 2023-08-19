<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;

/**
 * Action
 */
class BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // $container->get("view")->getEnvironment()->addGlobal('categorys', $this->getCatetory());
    }


}
