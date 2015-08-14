<?php

namespace Ejz;

class DOM {
    public $html = null;
    private $_dom = null;
    private $_xpath = null;
    private $_format = false;
    public function __construct($html = '', $format = false) {
        self::init($html, $format, $this);
    }
    public static function init($html, $format = false, $pointer = null) {
        if(is_null($pointer)) $pointer = new self();
        if(!is_string($html)) $html = "<html></html>";
        $html = trim($html);
        if(!$html) $html = "<html></html>";
        $prefix = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>';
        if(strpos($html, '<' . '?xml') === 0) ;
        else $html = $prefix . chr(10) . $html . chr(10);
        $pointer -> html = $html;
        $dom = new \DOMDocument();
        $dom -> preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $dom -> loadHTML($pointer -> html);
        libxml_clear_errors();
        $pointer -> _dom = $dom;
        $pointer -> _format = $format;
        $pointer -> _xpath = new \DOMXpath($pointer -> _dom);
        return $pointer;
    }
    public function attr($attr) { return $this -> find("//@{$attr}", 0); }
    public function text() { return $this -> find("//text()", 0); }
    public function count($xpath) { return count($this -> find($xpath)); }
    public function find($xpath, $index = null, $delete = false) {
        if(strpos($xpath, '/') !== 0) {
            _warn(__FUNCTION__, "XPATH ({$xpath}) IS WRONG!");
            if(is_null($index)) return array();
            return null;
        }
        $xpath = preg_replace(
            '~class\((?P<class>.*?)\)~i',
            'contains(concat(" ",normalize-space(@class)," ")," $1 ")',
            $xpath
        );
        $xpath = preg_replace(
            '~lower-case\((?P<lower>.*?)\)~i',
            'translate($1,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")',
            $xpath
        );
        if(!$this -> _xpath) return array();
        $list = $this -> _xpath -> query($xpath);
        if($list === false) {
            $array = array();
        } elseif($delete) {
            foreach($list as $elem) $elem -> parentNode -> removeChild($elem);
            return call_user_func_array(array($this, 'find'), array('/*', 0));
        } else $array = $this -> toArray($list, 0);
        if(is_null($index)) return $array;
        return @ $array[$index];
    }
    public function delete($xpath) {
        return call_user_func_array(array($this, 'find'), array($xpath, null, true));
    }
    public function __invoke($xpath, $index = null, $delete = false) {
        return call_user_func_array(array($this, 'find'), array($xpath, $index, $delete));
    }
    private function toArray($node, $level) {
        $formatF = ($this -> _format and $level) ? (chr(10) . str_repeat(' ', 4 * $level)) : '';
        $formatL = ($this -> _format) ? (chr(10) . str_repeat(' ', 4 * $level)) : '';
        $array = array();
        if(!$node) return array();
        if($node instanceof \DOMAttr) return $node -> value;
        if($node instanceof \DOMNodeList) {
            foreach($node as $n) $array[] = $this -> toArray($n, $level);
            return $array;
        }
        if($node -> nodeType == XML_TEXT_NODE) {
            $_ = trim($node -> nodeValue);
            if(!$_ and !is_numeric($_)) return '';
            if($level) return $formatF . esc($_);
            else return $_;
        }
        if($node -> nodeType == XML_COMMENT_NODE) {
            $_ = trim($node -> nodeValue);
            if(!$_ and !is_numeric($_)) return '';
            return $formatF . '<!-- ' . $_ . ' -->';
        }
        @ $tag = $node -> tagName;
        if(!$tag) return '';
        $collector = "{$formatF}<{$tag}%s>%s{$formatL}</{$tag}>";
        $collectorE = "{$formatF}<{$tag}%s></{$tag}>";
        $closed = "{$formatF}<{$tag}%s />";
        $attr = array();
        $inner = array();
        if($node -> hasAttributes())
            foreach($node -> attributes as $a)
                $attr[] = ' ' . sprintf('%s="%s"', $a -> nodeName, fesc($a -> nodeValue));
        if($node -> hasChildNodes())
            foreach($node -> childNodes as $childNode) {
                $t = $this -> toArray($childNode, $level + 1);
                if($t or $t === '0') $inner[] = $t;
            }
        $attr = implode('', $attr);
        $inner = implode('', $inner);
        $is_numeric = is_numeric($inner);
        if(!$inner and !$is_numeric and in_array($tag, explode(',', 'br,img,hr,param,meta')))
            return sprintf($closed, $attr);
        if(!$inner and !$is_numeric) return sprintf($collectorE, $attr);
        return sprintf($collector, $attr, $inner);
    }
}
