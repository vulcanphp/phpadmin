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
- [Bread](#bread)
- [DTS](#dts)
- [FusionChart](#fusionchart)
- [PhpCm](#phpcm)
- [PhpPage](#phppage)
- [QForm](#qform)
- [SimpleAuth](#simpleauth)
- [SvgMap](#svgmap)
- [Whoer](#whoer)