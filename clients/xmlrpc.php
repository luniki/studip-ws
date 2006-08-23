<?php

/*
 * xmlrpc.php - Stud.IP example XML-RPC client
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once '../vendor/phpxmlrpc/xmlrpc.inc';

define('XMLRPC_ENDPOINT', 'http://pomona.virtuos.uos.de/~mlunzena/studip_ws/public/xmlrpc.php');
$client =& new xmlrpc_client(XMLRPC_ENDPOINT);
$client->return_type = 'phpvals';

$message =& new xmlrpcmsg('generate_sentences',
                          array(php_xmlrpc_encode('secret'),
                                php_xmlrpc_encode(5)));
$result = $client->send($message);

# an error occured
if($result->faultCode())
  var_dump($result->faultString());
else
  var_dump($result->val);
