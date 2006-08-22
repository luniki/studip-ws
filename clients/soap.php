<?php
/*
 * soap.php - Stud.IP example SOAP client
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once '../vendor/nusoap/nusoap.php';

define('WSDL_URL', 'http://pomona.virtuos.uos.de/~mlunzena/studip_ws/public/soap.php?wsdl');
$client =& new soap_client(WSDL_URL, TRUE);
$proxy = $client->getProxy();

$result = $proxy->generate_text('secret', 5);

# an error occured
if ($err = $proxy->getError())
  var_dump($err);
else
  var_dump($result);
