<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 3.5.13
 * Time: 12:12
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Log;


use Maps\Model\BaseEntity;

class ApiLog extends BaseEntity{
    private $client;
    private $ip;
    private $lastAccess;
    private $resource;

}