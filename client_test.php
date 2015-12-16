<?php

require 'vendor/autoload.php';

$client = new JsonRPC\Client('http://127.0.0.1');

$num = 43454345;
$result = $client->execute('MSISDNLookup', [$num]);
echo '<pre>', 'number: ', $num, '<br />', var_export($result, true) , '</pre>';

$num = 3864060507056;
$result = $client->execute('MSISDNLookup', [$num]);
echo '<pre>', 'number: ', $num, '<br />', var_export($result, true) , '</pre>';

$num = '5466h466';
$result = $client->execute('MSISDNLookup', [$num]);
echo '<pre>', 'number: ', $num, '<br />', var_export($result, true) , '</pre>';
