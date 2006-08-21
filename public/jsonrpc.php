<?php

/*
 * jsonrpc.php - XML-RPC Backend for Stud.IP web services
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

# set include path
$include_path = ini_get('include_path');
$include_path .= PATH_SEPARATOR . dirname(__FILE__) . '/..';
ini_set('include_path', $include_path);

# requiring phpxmlrpc
require_once 'vendor/phpxmlrpc/xmlrpc.inc';
require_once 'vendor/phpxmlrpc/xmlrpcs.inc';
require_once 'vendor/phpxmlrpc/jsonrpc.inc';
require_once 'vendor/phpxmlrpc/jsonrpcs.inc';

# requiring xmlrpc_dispatcher
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/xmlrpc_dispatcher.php';

# requiring all the webservices
require_once 'lib/text_generation_web_service.php';

# create server
$dispatcher =& new Studip_Ws_XmlrpcDispatcher('TextGenerationWebService');
$server =& new jsonrpc_server($dispatcher->get_dispatch_map(), 0);

# start server
$server->service();
