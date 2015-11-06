<?php

namespace Ejz;

class DOM {
    public $html = null;
    private $_dom = null;
    private $_xpath = null;
    private $_format = false;
    private $_no_empty = false;
    private $_settings = array();
    private $_evaluate = 'string-join,boolean,ceiling,choose,concat,contains,count,current,document,element-available,false,floor,format-number,function-available,generate-id,id,key,lang,last,local-name,name,namespace-uri,normalize-space,not,number,position,round,starts-with,string,string-length,substring,substring-after,substring-before,sum,system-property,translate,true,unparsed-entity-url';
    public function __construct($html = '', $settings = array('format' => false, 'no-empty' => false)) {
        self::init($html, $settings, $this);
    }
    public static function init($html, $settings = array('format' => false, 'no-empty' => false), $pointer = null) {
        @ $format = $settings['format'];
        @ $no_empty = $settings['no-empty'];
        if(is_null($pointer)) $pointer = new self();
        if(!is_string($html)) $html = "<html></html>";
        $html = trim($html);
        if(!$html) $html = "<html></html>";
        $html = str_replace(chr(0), '', $html);
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
        $pointer -> _no_empty = $no_empty;
        $pointer -> _settings = $settings;
        $pointer -> _xpath = new \DOMXpath($pointer -> _dom);
        $pointer -> _dom -> normalizeDocument();
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
        $evaluate = explode(',', $this -> _evaluate);
        $isEvaluate = function($xpath) use($evaluate) {
            $xpath = preg_replace('~".*?"~', '', $xpath);
            $xpath = preg_replace('~\'.*?\'~', '', $xpath);
            foreach($evaluate as $_)
                if(strpos($xpath, "({$_}(") === 0 or strpos($xpath, "{$_}(") === 0)
                    return true;
            return false;
        };
        if($isEvaluate($xpath))
            return $this -> _xpath -> evaluate($xpath);
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
                        $_ = self::init($_, $this -> _settings);
                        $_ = $_ -> getList('//body/*');
                    } elseif($_) $_ = array($this -> _dom -> createTextNode($_));
                    else $_ = array();
                } else {
                    $_ = self::init($array[$i], $this -> _settings);
                    @ $_ = $_ -> getList($replace);
                    if(is_string($_) or is_numeric($_))
                        $_ = array($this -> _dom -> createTextNode($_ . ''));
                }
                if((is_array($_) and $_) or ($_ instanceof \DOMNodeList and $_ -> length)) {
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
            if(is_numeric($index) and !isset($limit) and is_array($array))
                return @ $array[$index];
            if(is_numeric($index) and !isset($limit))
                return $array;
            if(is_numeric($index) and is_array($array))
                return array_slice($array, $index, $limit);
            if(is_numeric($index))
                return $array;
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
        $formatNL = ($this -> _format) ? chr(10) : ' ';
        $array = array();
        if(!$node) return array();
        if($node instanceof \DOMAttr) return $node -> value;
        if($node instanceof \DOMNodeList) {
            foreach($node as $n)
                $array[] = $this -> toArray($n, $level);
            return $array;
        }
        if(is_string($node)) return $node;
        if($node -> nodeType == XML_TEXT_NODE) {
            $_ = trim($node -> nodeValue);
            if(!$_ and !is_numeric($_) and $node -> nodeValue and !$this -> _no_empty)
                return $formatNL;
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
                // if($t === chr(10) and $inner)
                if($t or $t === '0') $inner[] = $t;
            }
        $attr = implode('', $attr);
        $inner = implode('', $inner);
        $inner = str_replace(chr(10) . chr(10), chr(10), $inner);
        $is_numeric = is_numeric($inner);
        if(!$inner and !$is_numeric and in_array($tag, explode(',', 'br,img,hr,param,meta')))
            return sprintf($closed, $attr);
        if(!$inner and !$is_numeric) return sprintf($collectorE, $attr);
        if($inner === chr(10)) return sprintf($collector, $attr, '');
        $_ = sprintf($collector, $attr, $inner);
        return str_replace(chr(10) . chr(10), chr(10), $_);
    }
}
