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
    public function count($xpath) {
        $list = $this -> getList($xpath);
        $count = 0;
        foreach($list as $elem)
            $count += 1;
        return $count;
    }
    public function getList($xpath) {
        if(!$this -> _xpath) return array();
        if(strpos($xpath, '/') !== 0 and strpos($xpath, '(') !== 0) {
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
        return $this -> _xpath -> query($xpath);
    }
    public function find($xpath, $index = null, $replace = null) {
        if(is_string($index) and preg_match('~^\d+-\d+$~', $index))
            list($index, $limit) = explode('-', $index);
        $list = $this -> getList($xpath);
        if(!$list) $list = array();
        if(is_string($replace) or is_callable($replace)) {
            $array = $this -> toArray($list);
            $count = 0; foreach($list as $_) $count += 1;
            if($array and count($array) != $count)
                _warn(__FUNCTION__, 'INVALID COUNT!');
            $i = -1;
            foreach($list as $elem) {
                $i += 1;
                if(is_numeric($index) and !isset($limit) and $i != $index) continue;
                if(is_numeric($index) and isset($limit) and $i >= $index + $limit) continue;
                if(is_numeric($index) and isset($limit) and $i < $index) continue;
                if(is_callable($replace)) {
                    $_ = $replace($array[$i]);
                    if(preg_match('~^<\w+[^>]*>~', $_)) {
                        $_ = self::init($_);
                        $_ = $_ -> getList('//body/*');
                    } elseif($_) $_ = array($this -> _dom -> createTextNode($_));
                    else $_ = array();
                } else {
                    $_ = self::init($array[$i]);
                    @ $_ = $_ -> getList($replace);
                }
                if((is_array($_) and $_) or $_ instanceof \DOMNodeList) {
                    foreach($_ as $__) { $first = $__; break; }
                    $first = $this -> _dom -> importNode($first, true);
                    $elem -> parentNode -> replaceChild($first, $elem);
                } else $elem -> parentNode -> removeChild($elem);
            }
            return call_user_func_array(array($this, 'find'), array('/*', 0));
        } elseif($replace) {
            $i = -1;
            foreach($list as $elem) {
                $i += 1;
                if(is_numeric($index) and !isset($limit) and $i != $index) continue;
                if(is_numeric($index) and isset($limit) and $i >= $index + $limit) continue;
                if(is_numeric($index) and isset($limit) and $i < $index) continue;
                $elem -> parentNode -> removeChild($elem);
            }
            return call_user_func_array(array($this, 'find'), array('/*', 0));
        } else {
            $array = $this -> toArray($list);
            if(is_numeric($index) and !isset($limit))
                return @ $array[$index];
            if(is_numeric($index))
                return array_slice($array, $index, $limit);
            return $array;
        }
    }
    public function replace($xpath, $index = null, $callback = null) {
        $replace = true;
        if(is_callable($callback) or is_string($callback))
            $replace = $callback;
        return call_user_func_array(array($this, 'find'), array($xpath, $index, $replace));
    }
    public function delete() {
        return call_user_func_array(array($this, 'replace'), func_get_args());
    }
    public function __invoke() {
        return call_user_func_array(array($this, 'find'), func_get_args());
    }
    private function toArray($node, $level = 0) {
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
