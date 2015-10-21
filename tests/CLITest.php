<?php

//
// CLITest - PHP Unit Testing
//

use Ejz\DOM;

class CLITest extends PHPUnit_Framework_TestCase {
    public function testCommon() {
        $_ = `./cli-dom '//head' 'http://github.com'`;
        $this -> assertTrue(strpos($_, '<title>') !== false);
    }
    public function testIndexLimit() {
        $_ = `cat tests/test.html | ./cli-dom --index 0 '//div[class(selector)]/@class' - 2>&1`;
        $this -> assertTrue(strpos($_, 'cl-1') !== false);
        $this -> assertTrue(strpos($_, 'cl-2') === false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
        //
        $_ = `cat tests/test.html | ./cli-dom --index 1 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') === false);
        $this -> assertTrue(strpos($_, 'cl-2') !== false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
        //
        $_ = `cat tests/test.html | ./cli-dom --index 100 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') === false);
        $this -> assertTrue(strpos($_, 'cl-2') === false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
        //
        $_ = `cat tests/test.html | ./cli-dom --i 0 --limit 2 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') !== false);
        $this -> assertTrue(strpos($_, 'cl-2') !== false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
        //
        $_ = `cat tests/test.html | ./cli-dom --index 0 --limit 0 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') === false);
        $this -> assertTrue(strpos($_, 'cl-2') === false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
        //
        $_ = `cat tests/test.html | ./cli-dom --i 0 --limit 100 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') !== false);
        $this -> assertTrue(strpos($_, 'cl-2') !== false);
        $this -> assertTrue(strpos($_, 'cl-6') !== false);
        //
        $_ = `cat tests/test.html | ./cli-dom --index 100 --limit 100 '//div[class(selector)]/@class' -`;
        $this -> assertTrue(strpos($_, 'cl-1') === false);
        $this -> assertTrue(strpos($_, 'cl-2') === false);
        $this -> assertTrue(strpos($_, 'cl-6') === false);
    }
    public function testXPath() {
        $_ = `cat tests/test.html | ./cli-dom '//div[class(selector)]' '(//div[class(selector)]//text())[position() mod 2 = 0 and position() > 1]' -`;
        $this -> assertTrue(strpos($_, ',one') !== false);
        $this -> assertTrue(strpos($_, ',two') !== false);
        $this -> assertTrue(strpos($_, ',three') !== false);
        $this -> assertTrue(strpos($_, ',four') !== false);
        $this -> assertTrue(strpos($_, ',five') !== false);
        $this -> assertTrue(strpos($_, ',six') !== false);
    }
    public function testFormat() {
        $_ = `cat tests/test.html | ./cli-dom -f '(//div[class(selector)])[1]' -`;
        $_ = nsplit($_);
        $this -> assertTrue(strpos($_[0], '<div') === 0);
        $this -> assertTrue($_[count($_) - 1] === '</div>');
    }
    public function testDelimiter() {
        $_ = `cat tests/test.html | ./cli-dom --limit 2 -d"<!>" '//div[class(selector)]/@class' '//div[class(selector)]/@class' '//div[class(selector)]/@class' -`;
        $this -> assertEquals(substr_count($_, '<!>'), 4);
    }
    public function testMultiline() {
        $_ = `cat tests/test.html | ./cli-dom '//div[class(selector)]' - | ./cli-dom -m '(//text())[2]' -`;
        $this -> assertTrue(strpos($_, 'one') !== false);
        $this -> assertTrue(strpos($_, 'six') !== false);
    }
    public function testReplace() {
        $_ = `cat tests/test.html | ./cli-dom -r '//span' '//div[class(selector)]' -`;
        $_ = DOM::init($_);
        $this -> assertEquals(6, $_ -> count('//body/span'));
        //
        $_ = `cat tests/test.html | ./cli-dom -i 0 -r '//span' '//div[class(selector)]' -`;
        $_ = DOM::init($_);
        $this -> assertEquals(1, $_ -> count('//body/span'));
        //
        $_ = `cat tests/test.html | ./cli-dom -i 1 -r '//span' '//div[class(selector)]' -`;
        $_ = DOM::init($_);
        $this -> assertEquals(1, $_ -> count('//body/span'));
        $this -> assertEquals($_ -> find('//body/span/text()', 0), '-2-');
        //
        $_ = `cat tests/test.html | ./cli-dom -i 1 -l 2 -r '//span' '//div[class(selector)]' -`;
        $_ = DOM::init($_);
        $this -> assertEquals(2, $_ -> count('//body/span'));
        $this -> assertEquals($_ -> find('//body/span/text()', 0), '-2-');
        $this -> assertEquals($_ -> find('//body/span/text()', 1), '-3-');
    }
}
