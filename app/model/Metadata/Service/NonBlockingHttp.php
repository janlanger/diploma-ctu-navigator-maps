<?php

namespace Maps\Model\Metadata\Service;


use Nette\Object;

/**
 * Executes non blocking HTTP request. Requires curl and exec()
 *
 * @package Maps\Model\Metadata\Service
 * @author Jan Langer <langeja1@fit.cvut.cz>
 */
class NonBlockingHttp extends Object {
    /** @var string */
    private $url;
    /** @var bool  */
    private $noHttpsCheck = FALSE;
    /** @var array  */
    private $headers = [];

    /**
     * @param $url URL to call
     */
    function __construct($url) {
        $this->url = $url;
    }

    /**
     * Executes the request
     */
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

    /**
     * Adds HTTP header
     * @param $header
     * @param $content
     */
    public function addHeader($header, $content) {
        $this->headers[$header] = $content;
    }

    /**
     * Don't check certificate
     * @param $noHttpsCheck
     */
    public function setNoHttpsCheck($noHttpsCheck) {
        $this->noHttpsCheck = $noHttpsCheck;
    }

    /**
     * @return bool
     */
    public function getNoHttpsCheck() {
        return $this->noHttpsCheck;
    }




}
