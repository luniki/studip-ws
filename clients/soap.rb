# soap.rb - Stud.IP example SOAP client
#
# Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License as
# published by the Free Software Foundation; either version 2 of
# the License, or (at your option) any later version.

require 'soap/wsdlDriver'

# generate proxy
WSDL_URL = "http://localhost/~mlunzena/studip_ws_example/public/soap.php?wsdl"
soap = SOAP::WSDLDriverFactory.new(WSDL_URL).create_rpc_driver

puts soap.generate_text('secret', 3)
