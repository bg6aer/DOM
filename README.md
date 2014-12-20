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
<title>Yahoo!</title>
Yahoo!
```
