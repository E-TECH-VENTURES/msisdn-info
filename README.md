## Synopsis
PHP package that returns MSISDN info (MNO, country dialling code, ...)

- takes MSISDN as an input
- returns MNO identifier, country dialling code, subscriber number and country identifier as defined with ISO 3166-1-alpha-2
- package is exposed via RPC API (fguillot/json-rpc)

[Data source (musalbas/mcc-mnc-table)](https://github.com/musalbas/mcc-mnc-table)

## Code example
You can create you own calls to server using
```sh
$client = new JsonRPC\Client($server_url);
$result = $client->execute('MSISDNLookup', [$MSISDN]);
```

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

RPC call can be tested using client_test.php in project root folder (127.0.0.1/client_test.php)

## Tests
Composer will setup the test environent for you. Run tests using:
```sh
vendor\bin\phpunit
```
from within a project root folder

## License

MIT

## Author
Andrej Zadnikar
