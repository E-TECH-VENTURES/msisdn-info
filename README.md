## Synopsis
PHP package that returns MSISDN info (MNO, country dialling code, ...)

- takes MSISDN as an input
- returns MNO identifier, country dialling code, subscriber number and country identifier as defined with ISO 3166-1-alpha-2
- package is exposed via RPC API (fguillot/json-rpc)

[Data source (musalbas/mcc-mnc-table)](https://github.com/musalbas/mcc-mnc-table)

## Code example
// TODO

## Installation

Requirements:
- Git
- Composer
- VirtualBox
- Vagrant

After that, it only takes one command to set everything up:
```sh
composer install && vagrant up
```

RPC call can be tested using client_test.php in project root folder (127.0.0.1:8080/client_test.php)

## Tests
// TODO

## License

MIT

## Author
Andrej Zadnikar
