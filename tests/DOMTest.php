<?php

//
// DOMTest - PHP Unit Testing
//

use Ejz\DOM;

class DOMTest extends PHPUnit_Framework_TestCase {
    public function getHTML() {
        $HTML = <<<HTML
<html>
<head>
<meta equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<a href="findme.html"> Регистрация </a>
<div class="class1 escape class2">
    <span> &lt;&amp;&gt; </span>
    <span> asd > asd asd & </span>
    <span> &copy; </span>
</div>
<div class="attr" attr="&quot;'&lt; &amp; &gt;">
</div>
<div class="anchor"></div>
<div class="next">text next to anchor</div>
<div class="parent" attr="parent">
    <div id="child"></div>
</div>
<div class="self-axis">
    <a id="a1" href=""><span></span></a>
    <a id="a2" href=""><span></span></a>
    <a id="a3" href=""><span class="select-me"></span></a>
    <a id="a4" href=""><span></span></a>
</div>
<div class="get-last">
    <a></a>
    <a></a>
    <a></a><!--       i'm comment        -->
    <!--       -->
    text1
    text2

    3
    <a class="last"></a>
</div>
<div class="multiline-comment"><!-- one
- two
-- three --></div>
<div class="contains-count">
    <img src="/img/myimage.gif" />
</div>
</html>
HTML;
        return $HTML;
    }
    public function testHTMLCount() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> count('/html'), 1);
    }
    public function testRoot() {
        $dom = new DOM($this -> getHTML());
        $this -> assertTrue(count($dom -> find('/*')) === 1);
        foreach($dom -> find('/*') as $root)
            $this -> assertTrue(strpos($root, '<html>') === 0);
    }
    public function testEncoding() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> find("//a[@href='findme.html']/text()", 0), "Регистрация");
    }
    public function testEscape() {
        $dom = new DOM($this -> getHTML());
        $escape = $dom -> find("//div[class(escape)]/span/text()");
        $this -> assertEquals($escape[0], "<&>");
        $this -> assertEquals($escape[1], "asd > asd asd &");
        $this -> assertEquals($escape[2], "©");
        $attr = $dom -> find("//div[class(attr)]/@attr", 0);
        $this -> assertEquals($attr, "\"'< & >");
    }
    public function testNextAxis() {
        $dom = new DOM($this -> getHTML());
        $_ = $dom -> find('//div[class(anchor)]/following::div[class(next)]/text()', 0);
        $this -> assertEquals('text next to anchor', $_);
    }
    public function testParentAxis() {
        $dom = new DOM($this -> getHTML());
        $parent = $dom -> find('//div[@id="child"]/parent::*/@attr', 0);
        $this -> assertEquals('parent', $parent);
    }
    public function testSelfAxis() {
        $dom = new DOM($this -> getHTML());
        $id = $dom -> find('//div[class(self-axis)]//a/self::*[span[class(select-me)]]/@id', 0);
        $this -> assertEquals('a3', $id);
    }
    public function testPosition() {
        $dom = new DOM($this -> getHTML());
        $class = $dom -> find('//div[class(get-last)]/a[position()=last()]/@class', 0);
        $this -> assertEquals('last', $class);
    }
    public function testBugWithZero() {
        $HTML = "<div> 0 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div>0</div>");
        //
        $HTML = "<div> 000 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div>000</div>");
        //
        $HTML = "<div> 0.0 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div>0.0</div>");
    }
    public function testBugWithZeroFormat() {
        $HTML = "<div> 0 </div>";
        $dom = new DOM($HTML, true);
        $div = $dom -> find('//div', 0);
        $nl = chr(10);
        $this -> assertTrue($div === "<div>{$nl}    0{$nl}</div>");
    }
    public function testAttr() {
        $HTML = "<link attr='<&>'>MyLink</link>";
        $dom = new DOM($HTML);
        $attr = $dom -> attr('attr');
        $this -> assertTrue($attr === "<&>");
        //
        $HTML = "<link attr='&lt;&amp;&gt;'>MyLink</link>";
        $dom = new DOM($HTML);
        $attr = $dom -> attr('attr');
        $this -> assertTrue($attr === "<&>");
    }
    public function testFormat() {
        $dom = new DOM($this -> getHTML(), true);
        $_ = $dom -> find('//div', 0);
        $this -> assertTrue(strpos($_, '    <span>') !== false);
        $this -> assertTrue(strpos($_, '<div') !== false);
    }
    public function testBugWithFormatComment() {
        $dom = new DOM($this -> getHTML(), true);
        $_ = $dom -> find('//div[class(get-last)]', 0);
        $CMP = <<<CMP
<div class="get-last">
    <a></a>
    <a></a>
    <a></a>
    <!-- i'm comment -->
    text1 text2 3
    <a class="last"></a>
</div>
CMP;
        $this -> assertEquals($CMP, $_);
    }
    public function testBugWithMultilineComment() {
        $dom = new DOM($this -> getHTML(), true);
        $_ = $dom -> find('//div[class(multiline-comment)]', 0);
        $CMP = <<<CMP
<div class="multiline-comment">
    <!--
        one
        - two
        -- three
    -->
</div>
CMP;
        $this -> assertEquals($CMP, $_);
    }
}
