<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 3.5.13
 * Time: 12:04
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Tools;


use Nette\Application\Responses\JsonResponse;

class JsonErrorResponse extends JsonResponse {


    public function __construct($msg) {
        parent::__construct(['error'=>$msg]);
    }
}