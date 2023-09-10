# BaksDev Payment

![Version](https://img.shields.io/badge/version-6.3.4-blue) ![php 8.1+](https://img.shields.io/badge/php-min%208.1-red.svg)

Модуль способов оплаты

## Установка

``` bash
$ composer require baks-dev/payment
```

## Дополнительно

Установка файловых ресурсов в публичную директорию (javascript, css, image ...):

``` bash
$ php bin/console baks:assets:install
```

Изменения в схеме базы данных с помощью миграции

``` bash
$ php bin/console doctrine:migrations:diff

$ php bin/console doctrine:migrations:migrate
```

Тесты

``` bash
$ php bin/phpunit --group=payment
```


## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.


