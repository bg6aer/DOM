<?php

//
// DOMTest - PHP Unit Testing
//

use Ejz\DOM;

class DOMTest extends PHPUnit_Framework_TestCase {
    public function getHTML() {
        $HTML = <<<HTML
<html>
<meta equiv="Content-Type" content="text/html; charset=utf-8">
<a href="findme.html">Регистрация</a>
<div class="class1 escape class2">
    &lt;&amp;&gt;
    <&>
    < & >
    &copy;
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
    <a></a>
    <a class="last"></a>
</div>
<div class="contains-count">
    <img src="/img/myimage.gif" />
</div>
</html>
HTML;
        return $HTML;
    }
    public function testMeta() {
        $dom = new DOM($this -> getHTML());
        $this -> assertEquals($dom -> count('/html'), 1);
    }
}
