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

define('JSONRPC_ENDPOINT', 'http://pomona.virtuos.uos.de/~mlunzena/studip_ws/public/jsonrpc.php');
# $client =& new jsonrpc_client(JSONRPC_ENDPOINT);

# $message =& new jsonrpcmsg('generate_sentences',
#                            array(php_jsonrpc_encode('secret'),
#                                  php_jsonrpc_encode(5)));
# $result = $client->send($message);

# # an error occured
# if($result->faultCode())
#   var_dump($result->faultString());
# else
#   var_dump(php_jsonrpc_decode($result->val));

# $x = new xmlrpcval();
# $j = new jsonrpcval();

# $a->id = NULL;
# var_dump(php_xmlrpc_encode($a));
# var_dump(php_jsonrpc_decode($x));

$result = jsonrpc_generate_sentences('secret', 5, TRUE);
#var_dump($result);


/**
* Generates sentences using Markov chains.
* @param string $p1
* @param integer $p2
* @param int $debug when 1 (or 2) will enable debugging of the underlying jsonrpc call (defaults to 0)
* @return array (or an jsonrpcresp obj instance if call fails)
*/
function jsonrpc_generate_sentences ($p1, $p2, $debug=0) {
  $client =& new jsonrpc_client(JSONRPC_ENDPOINT);
  #$client->return_type = 'jsonrpcvals';
  $client->setDebug($debug);
  $msg =& new jsonrpcmsg('generate_sentences');
  $p1 =& new jsonrpcval($p1, 'string');
  $msg->addparam($p1);
  $p2 =& new jsonrpcval($p2, 'int');
  $msg->addparam($p2);
  $res =& $client->send($msg, 0, '');
  if ($res->faultcode()) return $res;
  else return php_jsonrpc_decode($res->value());
}
