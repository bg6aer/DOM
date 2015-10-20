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
    public function getList($xpath, $index) {
        if(!$this -> _xpath) return array();
        if(strpos($xpath, '/') !== 0) {
            _warn(__FUNCTION__, "XPATH ({$xpath}) IS WRONG!");
            return array();
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
        if(is_numeric($index)) $xpath .= '[' . (intval($index) + 1) . ']';
        return $this -> _xpath -> query($xpath);
    }
    public function find($xpath, $index = null, $delete = false) {
        $list = $this -> getList($xpath, $index);
        if(!$list) $list = array();
        if(is_callable($delete)) {
            $array = $this -> toArray($list, 0);
            $count = 0; foreach($list as $_) $count += 1;
            if($array and count($array) != $count)
                _warn(__FUNCTION__, 'INVALID COUNT!');
            $i = 0;
            foreach($list as $elem) {
                $_ = $delete($array[$i]);
                if(preg_match('~^<\w+[^>]*>~', $_)) {
                    $_ = self::init($_);
                    $_ = $_ -> getList('//body/*', 0);
                } elseif($_) $_ = array($this -> _dom -> createTextNode($_));
                else $_ = array();
                foreach($_ as $__) { $first = $__; break; }
                if(isset($first)) {
                    $first = $this -> _dom -> importNode($first, true);
                    $elem -> parentNode -> replaceChild($first, $elem);
                } else $elem -> parentNode -> removeChild($elem);
                $i += 1;
            }
            return call_user_func_array(array($this, 'find'), array('/*', 0));
        } elseif($delete) {
            foreach($list as $elem)
                $elem -> parentNode -> removeChild($elem);
            return call_user_func_array(array($this, 'find'), array('/*', 0));
        } else {
            $array = $this -> toArray($list, 0);
            if(is_null($index)) return $array;
            return @ $array[$index];
        }
    }
    public function delete($xpath, $index = null, $callback = null) {
        return call_user_func_array(array($this, 'find'), array($xpath, $index, is_callable($callback) ? $callback : true));
    }
    public function replace() {
        return call_user_func_array(array($this, 'delete'), func_get_args());
    }
    public function __invoke() {
        return call_user_func_array(array($this, 'find'), func_get_args());
    }
    private function toArray($node, $level) {
        $formatF = ($this -> _format and $level) ? (chr(10) . str_repeat(' ', 4 * $level)) : '';
        $formatL = ($this -> _format) ? (chr(10) . str_repeat(' ', 4 * $level)) : '';
        $formatP = ($this -> _format) ? (chr(10) . str_repeat(' ', 4 * ($level + 1))) : '';
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
            $_ = preg_replace('~\s+~', ' ', $_);
            if($level) return $formatF . esc($_);
            else return $_;
        }
        if($node -> nodeType == XML_COMMENT_NODE) {
            $_ = trim($node -> nodeValue);
            if(!$_ and !is_numeric($_)) return '';
            $_ = nsplit($_); // comment can be multiline
            if(count($_) === 1) return $formatF . '<!-- ' . $_[0] . ' -->';
            $echo = array();
            $echo[] = $formatF . '<!--';
            foreach($_ as $line) $echo[] = $formatP . $line;
            $echo[] = $formatF . '-->';
            return implode('', $echo);
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
