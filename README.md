# DOM [![Travis Status for Ejz/DOM](https://travis-ci.org/Ejz/DOM.svg?branch=master)](https://travis-ci.org/Ejz/DOM)

DOM is native HTML parsing library for PHP. Supports XPath syntax.

### Quick start

```bash
$ mkdir myproject && cd myproject
$ curl -sS 'https://getcomposer.org/installer' | php
$ nano -w composer.json
```

Insert following code:

```javascript
{
    "require": {
        "ejz/dom": "~1.0"
    }
}
```

Now install dependencies:

```bash
$ php composer.phar install
```

Let's begin:

```php
<?php

define('ROOT', __DIR__);
require(ROOT . '/vendor/autoload.php');

use Ejz\DOM;

$yahoo = file_get_contents("http://yahoo.com/");
$dom = new DOM($yahoo);
echo $dom -> find('//title', 0), chr(10);
echo $dom -> find('//title/text()', 0), chr(10);
```

```
<title>Yahoo</title>
Yahoo
```

Whatever you select by XPath, library returns string or array of strings. No objects!

### CLI

Library is easily adopted for command-line interface (CLI) usage. From the very beggining:

```bash
$ T=$(mktemp -d) && cd $T
$ curl -sS 'https://getcomposer.org/installer' | php
$ php composer.phar require ejz/dom:~1.0
$ cd vendor/ejz/dom/
$ curl -sS 'https://getcomposer.org/installer' | php
$ php composer.phar install
$ chmod a+x install.sh
$ sudo ./install.sh
$ cd ~ && rm -rf $T
```

After installation you can execute:

```bash
$ echo "<a href=''>Link</a>" | cli-dom '//a/text()'
Link
$ echo "<a class='findme' href=''>Find me</a>" | cli-dom '//a[class(findme)]/text()'
Find me
$ echo "string" | cli-dom '//*'
<html><body><p>string</p></body></html>
<body><p>string</p></body>
<p>string</p>
```

### CI: Codeship

[![Codeship Status for Ejz/DOM](https://codeship.com/projects/bcd7db20-6abb-0132-5494-2e0b75730361/status)](https://codeship.com/projects/53779)

### CI: Travis

[![Travis Status for Ejz/DOM](https://travis-ci.org/Ejz/DOM.svg?branch=master)](https://travis-ci.org/Ejz/DOM)
