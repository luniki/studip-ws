<?php

/*
 * xmlrpc_epi.php - XML-RPC Backend for Stud.IP WS using native extension
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

# requiring xmlrpc_dispatcher
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/xmlrpc_epi_dispatcher.php';

# requiring all the webservices
require_once 'lib/text_generation_web_service.php';

# create server
$dispatcher =& new Studip_Ws_XmlrpcEpiDispatcher('TextGenerationWebService');
$dispatcher->service($HTTP_RAW_POST_DATA);
