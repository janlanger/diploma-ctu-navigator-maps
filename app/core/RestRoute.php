<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 15.4.13
 * Time: 22:47
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Core;

use Nette\Application\IRouter;
use Nette\InvalidArgumentException;
use Nette\Http\Request as HttpRequest;
use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Nette\InvalidStateException;
use Nette\Utils\Strings;
use Nette;

/**
 * @autor Adam Štipák <adam.stipak@gmail.com>
 */
class RestRoute implements IRouter {

    private $presenter;

    private $prefix;

    private $data;

    private $itemCallback;

    private $action;

    private $presenterMap;


    public function __construct($presenter, $action, $prefix) {
        $this->presenter = $presenter;
        $this->action = $action;
        $this->prefix = $prefix;
    }

    /**
     * Maps HTTP request to a Request object.
     * @return Request|NULL
     */
    function match(Nette\Http\IRequest $httpRequest) {
        $path = substr($httpRequest->getUrl()->getPath(), strlen($httpRequest->getUrl()->getBasePath()));


        if(!Strings::startsWith($path, $this->prefix)) {
            return NULL;
        }
        $path = substr($path, strlen($this->prefix)+1);
        $parts = explode("/",$path);


        $params = $httpRequest->getQuery();

        if(count($parts) > 2) {
            $parts[0].= ucfirst($parts[2]);
            $params['subQuery'] = $parts[2];
            unset($parts[2]);
        }
        $presenter = $this->presenter;
        $params['action'] = array_shift($parts);
        $params['id'] = array_shift($parts);

        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            array('secured' => $httpRequest->isSecured())
        );
    }

    /**
     * Constructs absolute URL from Request object.
     * @return string|NULL
     */
    function constructUrl(Request $appRequest, Nette\Http\Url $refUrl) {
        if ($appRequest->getPresenterName() !== $this->presenter) {
            return NULL;
        }

        $params = $appRequest->getParameters();

        if(isset($params['subQuery'])) {
            $params['action'] = str_replace(ucfirst($params['subQuery']), "", $params['action']);
        }

        $urlParts = [trim($refUrl->baseUrl,"/"), $this->prefix, $params['action']];
        if(isset($params['id'])) {
            $urlParts[] = $params['id'];
        }
        if (isset($params['subQuery'])) {
            $urlParts[] = $params['subQuery'];
        }

        $uri = implode("/", $urlParts);

        unset($params["id"], $params["action"], $params["subQuery"]);

        $query = http_build_query($params, '', '&');
        if ($query !== '') $uri .= '?' . $query;

        return $uri;
    }
}