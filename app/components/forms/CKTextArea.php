<?php

namespace SeriesCMS\Components\Forms;

use Nette\Forms\Controls\TextArea;

/**
 * Description of CKTextArea
 *
 * @author Jan -Quinix- Langer
 */
class CKTextArea extends TextArea {
    
    private $processed = false;

    public function __construct($label, Form $form, $name, $cols = NULL, $rows = NULL) {
        parent::__construct($label, $cols, $rows);
        
    }  
    
    
    public function getValue() {    
        $value = parent::getValue();
        $form = $this->getForm();
        if ($form->isSubmitted() && !$this->processed) {            
                //process thumbs
                $dom = new \DOMDocument();
                $dom->substituteEntities = false;
                $dom->loadHTML("<?xml encoding=\"UTF-8\"><div>$value</div>");
                $dom->removeChild($dom->firstChild);
                $dom->removeChild($dom->firstChild);
                $dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);
            
                $links = $dom->getElementsByTagName('a');
                foreach ($links as $link) {
                    $imgs = $link->getElementsByTagName('img');
                    if ($imgs->length > 0) {
                        $link->setAttribute('class','fancybox');
                        foreach ($imgs as $img) {
                          //  dump($img->getAttribute('class'));
                            if($img->getAttribute('class') == 'nothumb') {
                                if($img->getAttribute('width') == 200)
                                    $img->removeAttribute('width');
                            }
                            else {
                                if($img->getAttribute('src') == $link->getAttribute('href')) {
                                    $img->removeAttribute("width");
                                    $img->setAttribute('src', \SeriesCMS\Templates\TemplateHelpers::thumbnail($img->getAttribute('src'), 150, 150, 85, true));
                                }
                            }
                        }
                    }
                }
            
            $value = $dom->saveHTML();
            $value = substr($value, 5, -7);
            $value = \SeriesCMS\Tools\Mixed::sanitazeCKEditor($value);
            $this->value = $value;
            $this->processed = true;
        }
        
        //proces internal links
        return $value;
    }
    public function setValue($value) {
        $this->processed = false;
        parent::setValue($value);
    }
    
}
