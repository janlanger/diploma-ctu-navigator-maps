<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Jan
 * Date: 1.5.13
 * Time: 23:06
 * To change this template use File | Settings | File Templates.
 */

namespace Maps\Model\Metadata;


use Nette\Object;

class NonBlockingHttp extends Object {

    private $url;

    private $noHttpsCheck = FALSE;
    private $headers = [];


    function __construct($url) {
        $this->url = $url;
    }

    public function execute() {
        $command = "curl –silent -L";
        if($this->noHttpsCheck) {
            $command .= ' -k';
        }

        if(!empty($this->headers)) {
            $headers = "";

            foreach($this->headers as $header=>$content) {
                $headers .= " -H ".escapeshellarg($header.":".$content);
            }
            $command.= $headers;
        }
        $command .= " ".escapeshellarg($this->url);

        shell_exec($command." >/dev/null 2>&1 &");
    }

    public function addHeader($header, $content) {
        $this->headers[$header] = $content;
    }

    public function setNoHttpsCheck($noHttpsCheck) {
        $this->noHttpsCheck = $noHttpsCheck;
    }

    public function getNoHttpsCheck() {
        return $this->noHttpsCheck;
    }




}
