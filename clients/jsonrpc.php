<?php

/*
 * jsonrpc.php - Stud.IP example JSON-RPC client
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once '../vendor/phpxmlrpc/xmlrpc.inc';
require_once '../vendor/phpxmlrpc/jsonrpc.inc';

define('JSONRPC_ENDPOINT', 'http://localhost/~mlunzena/studip_ws_example/public/jsonrpc.php');
$client =& new jsonrpc_client(JSONRPC_ENDPOINT);

$message =& new jsonrpcmsg('textgenerationwebservice.generate_text',
                           array(php_jsonrpc_encode('secret'),
                                 php_jsonrpc_encode(5)));
$result = $client->send($message);

# an error occured
if($result->faultCode())
  var_dump($result->faultString());
else
  var_dump(php_jsonrpc_decode($result->val));
