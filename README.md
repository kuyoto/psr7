# PSR-7

This package provides a [PSR-7](http://www.php-fig.org/psr/psr-7/) complaint http message implementation.

## Installation

The recommnended way to install this library is through [composer](https://getcomposer.org):

```bash
composer require kuyoto/psr7
```

## Usage

The HTTP message objects do not contain any other public methods than those defined in
[PSR-7](https://www.php-fig.org/psr/psr-7/) specification.

## Testing

``` bash
composer test
```

It is also possible to exclude tests that require a live internet connection:

``` bash
composer test -- --exclude-group internet
```

## License

The package is an open-sourced software licensed under the [MIT License](LICENSE.md).
