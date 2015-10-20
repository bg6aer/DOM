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
}
