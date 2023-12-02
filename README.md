# PhpAdmin
PhpAdmin is a simple, lightweight and easy dashboard for VulcanPhp micro mvc framework

## What is PhpAdmin
PhpAdmin is more than a admin dashboard, it has most used functionality <br />
to create highly functional content management system for Php Applications.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install PhpAdmin

```bash
$ composer require vulcanphp/phpadmin
```

## Register PhpAdmin

```php

use VulcanPhp\Core\Foundation\Application;

require_once __DIR__ . '/vendor/autoload.php';

Application::create(__DIR__)
    //Register PhpAdmin Kernel to Application, Thats it.. 
    ->registerKernel(VulcanPhp\PhpAdmin\PhpAdminKernel::class)
    ->run();


```

## Documentation
Note: Detailed documentation for PhpAdmin is coming soon ..
...

## Used PhpAdmin Built-in Extensions
- [Bread](https://github.com/vulcanphp/bread)
- [DTS](https://github.com/vulcanphp/dts)
- [FusionChart](https://github.com/vulcanphp/fusionchart)
- [PhpCm](https://github.com/vulcanphp/phpcm)
- [PhpPage](https://github.com/vulcanphp/phppage)
- [QForm](https://github.com/vulcanphp/qform)
- [SimpleAuth](https://github.com/vulcanphp/simpleauth)
- [SvgMap](https://github.com/vulcanphp/svgmap)
- [Whoer](https://github.com/vulcanphp/whoer)