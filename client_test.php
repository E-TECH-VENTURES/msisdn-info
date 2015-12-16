<?php

require 'vendor/autoload.php';

use JsonRPC\Client as RPCClient;

$client = new RPCClient('http://127.0.0.1');

$result = $client->execute('MSISDNLookup', [43454345]);
echo '<pre>', var_export($result, true) , '</pre>';

$result = $client->execute('MSISDNLookup', [3864060507056]);
echo '<pre>', var_export($result, true) , '</pre>';

$result = $client->execute('MSISDNLookup', ['5466h466']);
echo '<pre>', var_export($result, true) , '</pre>';
