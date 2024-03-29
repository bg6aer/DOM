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
<a href="findme.html"> Регистрация  </a>
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
<div class="test-delete">
    <span class="cl-one">1</span>
    <span class="cl-two">2</span>
    <span class="cl-three">3</span>
    <span class="cl-four">4</span>
</div>
</html>
HTML;
        return $HTML;
    }
    public function testSliceIndex() {
        $dom = new DOM($this -> getHTML());
        $this -> assertTrue(is_array($dom -> find('//div[class(test-delete)]/span')));
        $this -> assertTrue(count($dom -> find('//div[class(test-delete)]/span')) === 4);
        $this -> assertTrue(is_array($dom -> find('//div[class(test-delete)]/span', '1-2')));
        $this -> assertTrue(count($dom -> find('//div[class(test-delete)]/span', '1-2')) === 2);
        $this -> assertFalse(is_array($dom -> find('//div[class(test-delete)]/span', 0)));
        $this -> assertTrue(is_string($dom -> find('//div[class(test-delete)]/span', 0)));
        //
        $_ = $dom -> find('//div[class(test-delete)]/span', '1-2');
        $_ = new DOM(implode('', $_));
        $_ = $_ -> find('//text()');
        $_ = implode('', $_);
        $this -> assertEquals($_, '23');
    }
    public function testHTMLCount() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> count('/html'), 1);
    }
    public function testCount() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> count('//div[class(attr)]'), 1);
    }
    public function testRoot() {
        $dom = new DOM($this -> getHTML());
        $this -> assertTrue(count($dom -> find('/*')) === 1);
        foreach($dom -> find('/*') as $root)
            $this -> assertTrue(strpos($root, '<html>') === 0);
    }
    public function testEncoding() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> find("//a[@href='findme.html']/text()", 0), " Регистрация  ");
    }
    public function testConcat() {
        $dom = new DOM($this -> getHTML());
        $concat = $dom -> find("concat('1','2')", 0);
        $this -> assertTrue($concat === "12");
        //
        $dom = new DOM($this -> getHTML());
        $concat = $dom -> find("concat(//*[@id='a1']/@id,'_',//*[@id='a2']/@id)");
        $this -> assertTrue($concat === "a1_a2");
        //
        $dom = new DOM($this -> getHTML());
        $concat = $dom -> find("(concat(//*[@id='a1']/@id,'_',//*[@id='a2']/@id))");
        $this -> assertTrue($concat === "a1_a2");
    }
    public function testEscape() {
        $dom = new DOM($this -> getHTML(), array('trim' => true));
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
    public function testReplace() {
        $dom = new DOM($this -> getHTML());
        $html = $dom -> replace('//div[class(test-delete)]/*[class(cl-one)]');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML());
        $html = $dom -> replace('//div[class(test-delete)]/span', 0);
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/span/text()', 0);
        $this -> assertEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML());
        $html = $dom -> replace('//div[class(test-delete)]/span', 1);
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/span/text()', 0);
        $this -> assertEquals('1', $_);
        //
        $dom = new DOM($this -> getHTML());
        $html = $dom -> replace('//div[class(test-delete)]/span', '1-2');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/span/text()');
        $_ = implode('', $_);
        $this -> assertEquals('14', $_);
    }
    public function testReplaceString() {
        $dom = new DOM($this -> getHTML(), array('no-empty' => true, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 0, '//text()');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => true, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 1, '//text()');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => true, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, '//text()');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1234', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => true, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', '1-2', '//text()');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('23', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => true, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, 'concat(" ",count(./*)," ")');
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1111', $_);
    }
    public function testReplaceStringWithWS() {
        $dom = new DOM($this -> getHTML(), array('trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 0, '//text()');
        $dom = new DOM($html, array('trim' => true));
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1', $_);
        //
        $dom = new DOM($this -> getHTML(), array('trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 1, '//text()');
        $dom = new DOM($html, array('trim' => true));
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals(' ', $_);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 1);
        $this -> assertEquals('2', $_);
    }
    public function testReplaceCallback() {
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 0, function($string) {
            return $string;
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, function($string) {
            return $string;
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', 1, function($string) {
            return '';
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertNotEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, function($string) {
            $dom = DOM::init($string);
            $count = $dom -> count('//*[@class="cl-one"]');
            if($count) return $string;
            return '';
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertNotEquals('2', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, function($string) {
            $dom = DOM::init($string);
            $text = $dom -> find('//text()', 0);
            return $text;
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertNotEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertNotEquals('2', $_);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1234', $_);
        //
        $dom = new DOM($this -> getHTML(), array('no-empty' => 1, 'trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', '1-2', function($string) {
            $dom = DOM::init($string);
            $text = $dom -> find('//text()', 0);
            return $text;
        });
        $dom = new DOM($html);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('23', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-four)]/text()', 0);
        $this -> assertEquals('4', $_);
    }
    public function testReplaceCallbackWithWS() {
        $dom = new DOM($this -> getHTML(), array('trim' => true));
        $html = $dom -> replace('//div[class(test-delete)]/span', null, function($string) {
            $dom = DOM::init($string, array('trim' => true));
            $text = $dom -> find('//text()', 0);
            return $text;
        });
        $dom = new DOM($html, array('trim' => true));
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-one)]/text()', 0);
        $this -> assertNotEquals('1', $_);
        $_ = $dom -> find('//div[class(test-delete)]/*[class(cl-two)]/text()', 0);
        $this -> assertNotEquals('2', $_);
        $_ = $dom -> find('//div[class(test-delete)]/text()', 0);
        $this -> assertEquals('1 2 3 4', $_);
    }
    public function testBugWithWhiteSpace() {
        $html = "<div> \t\n  <p> \t\n  1 \t\n  </p></div>";
        //
        $dom = new DOM($html, array('trim' => true, 'no-empty' => false));
        $this -> assertEquals(' ', $dom -> find('//div//text()', 0));
        $this -> assertEquals('1', $dom -> find('//div//text()', 1));
        //
        $dom = new DOM($html, array('trim' => false, 'no-empty' => false));
        $this -> assertEquals(" \t\n  ", $dom -> find('//div//text()', 0));
        $this -> assertEquals(" \t\n  1 \t\n  ", $dom -> find('//div//text()', 1));
        //
        $dom = new DOM($html, array('trim' => true, 'no-empty' => true));
        $this -> assertEquals("1", $dom -> find('//div//text()', 0));
    }
    public function testBugWithZero() {
        $HTML = "<div> 0 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div> 0 </div>");
        //
        $HTML = "<div> 000 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div> 000 </div>");
        //
        $HTML = "<div> 0.0 </div>";
        $dom = new DOM($HTML);
        $div = $dom -> find('//div', 0);
        $this -> assertTrue($div === "<div> 0.0 </div>");
    }
    public function testBugWithZeroFormat() {
        $HTML = "<div> 0 </div>";
        $dom = new DOM($HTML, array('format' => true));
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
        $dom = new DOM($this -> getHTML(), array('format' => true));
        $_ = $dom -> find('//div', 0);
        $this -> assertTrue(strpos($_, '    <span>') !== false);
        $this -> assertTrue(strpos($_, '<div') !== false);
    }
    public function testFormatEmpty() {
        $dom = new DOM("<div><p></p><p> </p><p>\n\n\n</p><p> text </p></div>", array('format' => true));
        $_ = $dom -> find('//p');
        $this -> assertEquals($_[0], '<p></p>');
        $this -> assertEquals($_[1], "<p>\n</p>");
        $this -> assertEquals($_[2], "<p>\n</p>");
        $this -> assertEquals($_[3], "<p>\n    text\n</p>");
    }
    public function testBugWithFormatComment() {
        $dom = new DOM($this -> getHTML(), array('format' => true));
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
        $dom = new DOM($this -> getHTML(), array('format' => true));
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
