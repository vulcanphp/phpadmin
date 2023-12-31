# PhpAdmin
PhpAdmin is a simple, lightweight and easy dashboard for VulcanPhp micro mvc framework

![dashboard](https://github.com/vulcanphp/phpadmin/assets/128284645/7ccfee72-3f4a-40a1-b871-b63d44ac3b30)
![page-builder](https://github.com/vulcanphp/phpadmin/assets/128284645/ce91cb93-bccc-4792-a785-3ec9f2659004)

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
