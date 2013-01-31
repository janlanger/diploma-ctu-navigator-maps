<?php
use Nette\Environment;
use Nette\Utils\LimitedScope;
class StringTemplate extends Nette\Templating\Template
{
        public $content;

        /**
         * Renders template to output.
         * @return void
         */
        public function render()
        {
                $cache = Environment::getCache('StringTemplate');
                $key = md5($this->content);
                $content = $cache[$key];
                if ($content === NULL) { // not cached
                        if (!$this->getFilters()) {
                                $this->onPrepareFilters($this);
                        }
                        $this->setSource($this->content);
                        $cache[$key] = $content = $this->compile();
                }

                $this->__set('template', $this);
                /*Nette\Loaders\*/LimitedScope::evaluate($content, $this->getParameters());
        }
}
?>
