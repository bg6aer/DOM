# DOM [![Travis Status for Ejz/DOM](https://travis-ci.org/Ejz/DOM.svg?branch=master)](https://travis-ci.org/Ejz/DOM)

DOM is native HTML parsing library for PHP. Supports XPath syntax.

### Quick start

```bash
$ mkdir myproject && cd myproject
$ curl -sS 'https://getcomposer.org/installer' | php
$ php composer.phar require ejz/dom:~1.0
```

Let's begin:

```php
<?php

define('ROOT', __DIR__);
require(ROOT . '/vendor/autoload.php');

use Ejz\DOM;

$yahoo = file_get_contents("http://yahoo.com/");
$dom = new DOM($yahoo);
echo $dom -> find('//title', 0), "\n";
echo $dom -> find('//title/text()', 0), "\n";
```

```
<title>Yahoo</title>
Yahoo
```

Whatever you select by XPath, library returns string or array of strings. No objects!

### CLI

Library is adopted for command-line interface (CLI) usage.

```bash
$ curl -sSL 'https://raw.githubusercontent.com/Ejz/DOM/master/i.sh' | sudo bash
```

After installation you can execute:

```bash
$ echo "<a href=''>Link</a>" | cli-dom '//a/text()' -
Link
$ echo "<a class='findme'>Find me</a>" | cli-dom '//a[class(findme)]/text()' -
Find me
```

You can use library to prettify some HTML output:

```bash
$ cli-dom -f '//head' 'https://php.net/'
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        PHP: Hypertext Preprocessor
    </title>
    <link rel="shortcut icon" href="https://php.net/favicon.ico"></link>
</head>
```

### CI: Codeship

[![Codeship Status for Ejz/DOM](https://codeship.com/projects/bcd7db20-6abb-0132-5494-2e0b75730361/status)](https://codeship.com/projects/53779)

### CI: Travis

[![Travis Status for Ejz/DOM](https://travis-ci.org/Ejz/DOM.svg?branch=master)](https://travis-ci.org/Ejz/DOM)
