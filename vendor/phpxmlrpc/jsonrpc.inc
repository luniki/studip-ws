<?php
/**
 * JSON extension to the PHP-XMLRPC lib
 *
 * For more info see:
 * http://www.json.org/
 * http://json-rpc.org/
 *
 * @author Gaetano Giunta
 * @version $Id: jsonrpc.inc,v 1.14 2006/08/21 14:48:15 ggiunta Exp $
 * @copyright (c) 2005-2006 G. Giunta
 *
 **/

	// requires: xmlrpc.inc 2.0 or later

	// Note: the json spec omits \v, but it is present in ECMA-262
	$GLOBALS['ecma262_entities'] = array(
		'b' => chr(8),
		'f' => chr(12),
		'n' => chr(10),
		'r' => chr(13),
		't' => chr(9),
		'v' => chr(11)
	);

	// tables used for transcoding different charsets into us-ascii javascript

	$GLOBALS['ecma262_iso88591_Entities']=array();
	$GLOBALS['ecma262_iso88591_Entities']['in'] = array();
	$GLOBALS['ecma262_iso88591_Entities']['out'] = array();
	for ($i = 0; $i < 32; $i++)
	{

		$GLOBALS['ecma262_iso88591_Entities']['in'][] = chr($i);
		$GLOBALS['ecma262_iso88591_Entities']['out'][] = sprintf('\u%\'04x', $i);
	}
	for ($i = 160; $i < 256; $i++)
	{
		$GLOBALS['ecma262_iso88591_Entities']['in'][] = chr($i);
		$GLOBALS['ecma262_iso88591_Entities']['out'][] = sprintf('\u%\'04x', $i);
	}

	/**
	* Encode php strings to valid JSON unicode representation.
	* All chars outside ASCII range are converted to \uXXXX for maximum portability.
	* @param string $data (in iso-8859-1 charset by default)
	* @param string charset of source string
	* @param string charset of the encoded string
	* @return string
	* @access private
	* @todo add support for UTF-16 as destination charset instead of ASCII
	* @todo add support for UTF-16 as source charset
	*/
	function json_encode_entities($data, $src_encoding='', $dest_encoding='')
	{
		if ($src_encoding == '')
		{
			// lame, but we know no better...
			$src_encoding = $GLOBALS['xmlrpc_internalencoding'];
		}

		switch(strtoupper($src_encoding.'_'.$dest_encoding))
		{
			case 'ISO-8859-1_':
			case 'ISO-8859-1_US-ASCII':
				$escaped_data = str_replace(array('\\', '"', '/', "\t", "\n", "\r", chr(8), chr(11), chr(12)), array('\\\\', '\"', '\/', '\t', '\n', '\r', '\b', '\v', '\f'), $data);
				$escaped_data = str_replace($GLOBALS['ecma262_iso88591_Entities']['in'], $GLOBALS['ecma262_iso88591_Entities']['out'], $escaped_data);
				break;
			case 'ISO-8859-1_UTF-8':
				$escaped_data = str_replace(array('\\', '"', '/', "\t", "\n", "\r", chr(8), chr(11), chr(12)), array('\\\\', '\"', '\/', '\t', '\n', '\r', '\b', '\v', '\f'), $data);
				$escaped_data = utf8_encode($escaped_data);
				break;
			case 'ISO-8859-1_ISO-8859-1':
			case 'US-ASCII_US-ASCII':
			case 'US-ASCII_UTF-8':
			case 'US-ASCII_':
			case 'US-ASCII_ISO-8859-1':
			case 'UTF-8_UTF-8':
				$escaped_data = str_replace(array('\\', '"', '/', "\t", "\n", "\r", chr(8), chr(11), chr(12)), array('\\\\', '\"', '\/', '\t', '\n', '\r', '\b', '\v', '\f'), $data);
				break;
			case 'UTF-8_':
			case 'UTF-8_US-ASCII':
			case 'UTF-8_ISO-8859-1':
	// NB: this will choke on invalid UTF-8, going most likely beyond EOF
	$escaped_data = "";
	// be kind to users creating string jsonrpcvals out of different php types
	$data = (string) $data;
	$ns = strlen ($data);
	for ($nn = 0; $nn < $ns; $nn++)
	{
		$ch = $data[$nn];
		$ii = ord($ch);
//1 7 0bbbbbbb (127)
		if ($ii < 128)
		{
			/// @todo shall we replace this with a (supposedly) faster str_replace?
			switch($ii){
				case 8:
					$escaped_data .= '\b';
					break;
				case 9:
					$escaped_data .= '\t';
					break;
				case 10:
					$escaped_data .= '\n';
					break;
				case 11:
					$escaped_data .= '\v';
					break;
				case 12:
					$escaped_data .= '\f';
					break;
				case 13:
					$escaped_data .= '\r';
					break;
				case 34:
					$escaped_data .= '\"';
					break;
				case 47:
					$escaped_data .= '\/';
					break;
				case 92:
					$escaped_data .= '\\';
					break;
				default:
					$escaped_data .= $ch;
			} // switch
		}
//2 11 110bbbbb 10bbbbbb (2047)
		else if ($ii>>5 == 6)
		{
			$b1 = ($ii & 31);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ($b1 * 64) + $b2;
			$ent = sprintf ('\u%\'04x', $ii);
			$escaped_data .= $ent;
		}
//3 16 1110bbbb 10bbbbbb 10bbbbbb
		else if ($ii>>4 == 14)
		{
			$b1 = ($ii & 31);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ord($data[$nn+2]);
			$b3 = ($ii & 63);
			$ii = ((($b1 * 64) + $b2) * 64) + $b3;
			$ent = sprintf ('\u%\'04x', $ii);
			$escaped_data .= $ent;
		}
//4 21 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
		else if ($ii>>3 == 30)
		{
			$b1 = ($ii & 31);
			$ii = ord($data[$nn+1]);
			$b2 = ($ii & 63);
			$ii = ord($data[$nn+2]);
			$b3 = ($ii & 63);
			$ii = ord($data[$nn+3]);
			$b4 = ($ii & 63);
			$ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
			$ent = sprintf ('\u%\'04x', $ii);
			$escaped_data .= $ent;
		}
	}
				break;
			default:
				$escaped_data = '';
				error_log("Converting from $src_encoding to $dest_encoding: not supported...");
		} // switch
		return $escaped_data;

	/*
		$length = strlen($data);
		$escapeddata = "";
		for($position = 0; $position < $length; $position++)
		{
			$character = substr($data, $position, 1);
			$code = ord($character);
			switch($code)
			{
				case 8:
					$character = '\b';
					break;
				case 9:
					$character = '\t';
					break;
				case 10:
					$character = '\n';
					break;
				case 12:
					$character = '\f';
					break;
				case 13:
					$character = '\r';
					break;
				case 34:
					$character = '\"';
					break;
				case 47:
					$character = '\/';
					break;
				case 92:
					$character = '\\\\';
					break;
				default:
					if($code < 32 || $code > 159)
					{
						$character = "\u".str_pad(dechex($code), 4, '0', STR_PAD_LEFT);
					}
					break;
			}
			$escapeddata .= $character;
		}
		return $escapeddata;
		*/
	}

	/**
	* Parse a JSON string.
	* NB: try to accept any valid string according to ECMA, even though the JSON
	* spec is much more strict.
	* Assumes input is UTF-8...
	* @todo support for other source encodings than UTF-8
	* @todo finish 0xNN hexadecimal notation for numbers
	* @todo build elements of arrays/objects asap instead of counting chars many times
	*/
	function json_parse($data, $return_phpvals=false, $src_encoding='UTF-8', $dest_encoding='ISO-8859-1')
	{
		$data = preg_replace(array(
			// eliminate single line comments in '// ...' form
			'#^\s*//(.+)$#m',
			// eliminate multi-line comments in '/* ... */' form, at start of string
			'#^\s*/\*(.+)\*/#Us',
			// eliminate multi-line comments in '/* ... */' form, at end of string
			'#/\*(.+)\*/\s*$#Us'
		), '', $data);
		$data = trim($data); // remove excess whitespace
//echo "Parsing string (".$data.")\n";
		$len = strlen($data);
		switch($data[0])
		{
			case '[':
			case '{':
				// object and array notation: use the same parsing code
				if ($data[0] == '[')
				{
					if ($data[$len-1] != ']')
					{
						// invalid array
						$GLOBALS['_xh']['isf_reason'] = 'Invalid data (array missing closing bracket?)';
						return false;
					}
					$GLOBALS['_xh']['vt'] = 'array';
				}
				else
				{
					if ($data[$len-1] != '}')
					{
						// invalid object
						$GLOBALS['_xh']['isf_reason'] = 'Invalid data (object missing closing bracket?)';
						return false;
					}
					$GLOBALS['_xh']['vt'] = 'struct';
				}

				$data = trim(substr($data, 1, -1));
//echo "Parsing array/obj (".$data.")\n";
				if ($data == '')
				{
					// empty array/object
					$GLOBALS['_xh']['value'] = array();
				}
				else
				{
					$valuestack = array();
					$last = array('type' => 'sl', 'start' => 0);
					$len = strlen($data);
					$value = array();
					$keypos = null;
					//$ac = '';
					//$vt = null;
					//$start = 0;
					for ($i = 0; $i <= $len; $i++)
					{
						if ($i == $len || ($data[$i] == ',' && $last['type'] == 'sl'))
						{

							// end of element: push it onto array
							$slice = substr($data, $last['start'], ($i - $last['start']));
//echo "Found slice (".$slice.")\n";

							//$valuestack[] = $last; // necessario ???
							//$last = array('type' => 'sl', 'start' => ($i + 1));
							if ($GLOBALS['_xh']['vt'] == 'array')
							{
								if ($slice == '')
								{
									if ($return_phpvals)
									{
										$value[] = null;
									}
									else
									{
										$value[] = new jsonrpcval(null, 'null');
									}
								}
								else
								{
									if (!json_parse($slice, $return_phpvals, $src_encoding, $dest_encoding))
									{
										return false;
									}
									else
									{
										$value[] = $GLOBALS['_xh']['value'];
										$GLOBALS['_xh']['vt'] = 'array';
									}
								}
							}
							else
							{
								if (!$keypos)
								{
									$GLOBALS['_xh']['isf_reason'] = 'Invalid data (missing object member name?)';
									return false;
								}
								else
								{
									if (!json_parse(substr($data, $last['start'], $keypos-$last['start']), true, $src_encoding, $dest_encoding) ||
										$GLOBALS['_xh']['vt'] != 'string')
									{
										// object member name received unquoted: what to do???
										// be tolerant as much aswe can...
										$key = trim(substr($data, $last['start'], $keypos-$last['start']));
									}
									else
									{
										$key = $GLOBALS['_xh']['value'];
									}

//echo "Use extension: $use_extension\n";
									if (!json_parse(substr($data, $keypos+1, $i-$keypos-1), $return_phpvals, $src_encoding, $dest_encoding))
									{
										return false;
									}
									$value[$key] = $GLOBALS['_xh']['value'];
									$GLOBALS['_xh']['vt'] = 'struct';
									$keypos = null;
								}
							}
							$last['start'] = $i + 1;
						}
						else if (($data[$i] == '"' || $data[$i] == "'"))
						{
							// found beginning of string: run till end
							$ok = false;
							for ($j = $i+1; $j < $len; $j++)
							{
								if ($data[$j] == $data[$i])
								{
									$ok = true;
									break;
								}
								else if($data[$j] == '\\')
								{
									$j++;
								}
							}
							if ($ok)
							{
								$i = $j; // advance pointer to end of string
							}
							else
							{
								$GLOBALS['_xh']['isf_reason'] = 'Invalid data (string missing closing quote?)';
								return false;
							}
						}
						else if ($data[$i] == "[")
						{
							$valuestack[] = $last;
							$last = array('type' => 'ar', 'start' => $i);
						}
						else if ($data[$i] == '{')
						{
							$valuestack[] = $last;
							$last = array('type' => 'ob', 'start' => $i);
						}
						else if ($data[$i] == "]")
						{
							if ($last['type'] == 'ar')
								$last = array_pop($valuestack);
							else
							{
								$GLOBALS['_xh']['isf_reason'] = 'Invalid data (unmatched array closing bracket?)';
								return false;
							}
						}
						else if ($data[$i] == '}')
						{
							if ($last['type'] == 'ob')
								$last = array_pop($valuestack);
							else
							{
								$GLOBALS['_xh']['isf_reason'] = 'Invalid data (unmatched object closing bracket?)';
								return false;
							}
						}
						else if ($data[$i] == ':' && $last['type'] == 'sl' && !$keypos)
						{
//echo "Found key stop at pos. $i\n";
							$keypos = $i;
						}
						else if ($data[$i] == '/' && $i < $len-1 && $data[$i+1] == "*")
						{
							// found beginning of comment: run till end
							$ok = false;
							for ($j = $i+2; $j < $len-1; $j++)
							{
								if ($data[$j] == '*' && $data[$j+1] == '/')
								{
									$ok = true;
									break;
								}
							}
							if ($ok)
							{
								$i = $j+1; // advance pointer to end of string
							}
							else
							{
								$GLOBALS['_xh']['isf_reason'] = 'Invalid data (comment missing closing tag?)';
								return false;
							}
						}

					}
					$GLOBALS['_xh']['value'] = $value;
				}
				//return true;
				break;
			case '"':
			case "'":
				// quoted string: check for closing char first
				if ($data[$len-1] == $data[0] && $len > 1)
				{
					// decode string
					$GLOBALS['_xh']['ac'] = '';
					$delim = $data[0];
					for ($i = 1; $i < $len-1; $i++)
					{
						switch($data[$i])
						{
							case '\\':
								if ($i == $len-2)
								{
									break;
								}
								switch($data[$i+1])
								{
									case 'b':
									case 'f':
									case 'n':
									case 'r':
									case 't':
									case 'v':
										$GLOBALS['_xh']['ac'] .= $GLOBALS['ecma262_entities'][$data[$i+1]];
										$i++;
										break;
									case 'u':
										// most likely unicode code point
										if ($dest_encoding == 'UTF-8')
										{
											// aargh! encode the UTF code point into utf-8...
											/// @todo...
										}
										else
										{
											$GLOBALS['_xh']['ac'] .= chr(hexdec(substr($data, $i+4, 2)));
										}
									case 'x':
										//most likely unicode code point in hexadecimal
										// Note: the json spec omits this case, but ECMA-262 does not...
										// most likely unicode code point
										if ($dest_encoding == 'UTF-8')
										{
											// aargh! encode the UTF code point into utf-8...
											/// todo...
										}
										else
										{
											$GLOBALS['_xh']['ac'] .= chr(hexdec(substr($data, $i+2, 2)));
										}
									case '0':
									case '1':
									case '2':
									case '3':
									case '4':
									case '5':
									case '6':
									case '7':
									case '8':
									case '9':
										// Note: ECMA-262 forbids these escapes, we just skip it...
										break;
									default:
										// Note: Javascript 1.5 on http://developer.mozilla.org/en/docs/Core_JavaScript_1.5_Guide
										// mentions syntax /XXX with X octal number, but ECMA262
										// explicitly forbids it...
										$GLOBALS['_xh']['ac'] .= $data[$i+1];
										$i++;
								} // end of switch on slash char found
								break;
							case $delim:
								// found unquoted end of string in middle of string
								$GLOBALS['_xh']['isf_reason'] = 'Invalid data (unescaped quote char inside string?)';
								return false;
							default:
								$GLOBALS['_xh']['ac'] .= $data[$i];
						}
					} // end of loop on string chars
//echo "Found a string\n";
					$GLOBALS['_xh']['vt'] = 'string';
					if ($src_encoding == 'UTF-8' && ($dest_encoding == 'ISO-8859-1' || $dest_encoding == 'US-ASCII'))
					{
						$GLOBALS['_xh']['value'] = utf8_decode($GLOBALS['_xh']['ac']);
					}
					else
					{
						if ($dest_encoding == 'UTF-8' && ($src_encoding == 'ISO-8859-1' || $src_encoding == 'US-ASCII'))
						{
							$GLOBALS['_xh']['value'] = utf8_encode($GLOBALS['_xh']['ac']);
						}
						else
						{
							$GLOBALS['_xh']['value'] = $GLOBALS['_xh']['ac'];
						}
					}
				}
				else
				{
					// string without a terminating quote
					$GLOBALS['_xh']['isf_reason'] = 'Invalid data (string missing closing quote?)';
				}
				break;
			default:
				// be tolerant of uppercase chars in numbers/booleans/null
				$data = strtolower($data);
				if ($data == "true")
				{
//echo "Found a true\n";
					$GLOBALS['_xh']['value'] = true;
					$GLOBALS['_xh']['vt'] = 'boolean';
				}
				else if ($data == "false")
				{
//echo "Found a false\n";
					$GLOBALS['_xh']['value'] = false;
					$GLOBALS['_xh']['vt'] = 'boolean';
				}
				else if ($data == "null")
				{
//echo "Found a null\n";
					$GLOBALS['_xh']['value'] = null;
					$GLOBALS['_xh']['vt'] = 'null';
				}
				// we could use is_numeric here, but rules are slightly different,
				// e.g. 012 is NOT valid according to JSON
				else if (ereg("^[-]?(0|[1-9][0-9]*)(.[0-9]+)?([e][+-]?[0-9]+)?$" ,$data))
				{
					if (ereg('[.e]', $data))
					{
//echo "Found a double\n";
						// floating point
						$GLOBALS['_xh']['value'] = (double)$data;
						$GLOBALS['_xh']['vt'] = 'double';
					}
					else
					{
//echo "Found an int\n";
						//integer
						$GLOBALS['_xh']['value'] = (int)$data;
						$GLOBALS['_xh']['vt'] = 'int';
					}
					//return true;
				}
				else if (ereg("^0x[0-9a-fA-F]+$", $data))
				{
					// int in hex notation: not in JSON, but in ECMA...
					/// todo...
					$GLOBALS['_xh']['vt'] = 'int';
				}
				else
				{
					$GLOBALS['_xh']['isf_reason'] = 'Invalid data';
					return false;
				}
		} // switch $data[0]

		if (!$return_phpvals)
		{
			$GLOBALS['_xh']['value'] = new jsonrpcval($GLOBALS['_xh']['value'], $GLOBALS['_xh']['vt']);
		}

		return true;

	}

	/// @todo add support for src and dest encoding
	function json_parse_native($data)
	{
//echo "Parsing string - internal way (".$data.")\n";
		$out = json_decode($data, true);
		// decoding will be fine for a json error response, so we have to
		// check for it by hand here...
		if (!is_array($out) || !array_key_exists('result', $out)
			|| !array_key_exists('error', $out) || !array_key_exists('id', $out))
		{
			//$GLOBALS['_xh']['isf'] = 2;
			$GLOBALS['_xh']['isf_reason'] = 'JSON parsing did not return correct jsonrpc object';
			return false;
		}
		else if ($out['error'] != null)
		{
			$GLOBALS['_xh']['isf'] = 1;
			$GLOBALS['_xh']['value'] = $out['error'];
		}
		else
		{
			$GLOBALS['_xh']['value'] = $out;
		}
		return true;
	}

	function jsonrpc_parse_req($data, $return_phpvals=false, $use_extension=false)
	{
		$GLOBALS['_xh']['isf']=0;
		$GLOBALS['_xh']['isf_reason']='';
		if ($return_phpvals && $use_extension)
		{
			$ok = json_parse_native($data);
		}
		else
		{
			$ok = json_parse($data, $return_phpvals);
		}
		if ($ok)
		{
			if (!$return_phpvals)
				$GLOBALS['_xh']['value'] = @$GLOBALS['_xh']['value']->me['struct'];

			if (!is_array($GLOBALS['_xh']['value']) || !array_key_exists('method', $GLOBALS['_xh']['value'])
				|| !array_key_exists('params', $GLOBALS['_xh']['value']) || !array_key_exists('id', $GLOBALS['_xh']['value']))
			{
				$GLOBALS['_xh']['isf_reason'] = 'JSON parsing did not return correct jsonrpc request object';
				return false;
			}
			else
			{
				$GLOBALS['_xh']['method'] = $GLOBALS['_xh']['value']['method'];
				$GLOBALS['_xh']['params'] = $GLOBALS['_xh']['value']['params'];
				$GLOBALS['_xh']['id'] = $GLOBALS['_xh']['value']['id'];
				if (!$return_phpvals)
				{
					/// @todo we should check for appropriate type for method name and params array...
					$GLOBALS['_xh']['method'] = $GLOBALS['_xh']['method']->scalarval();
					$GLOBALS['_xh']['params'] = $GLOBALS['_xh']['params']->me['array'];
					$GLOBALS['_xh']['id'] = php_jsonrpc_decode($GLOBALS['_xh']['id']);
				}
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	* Parse a json chunk, expected to be in json-rpc esponse format.
	* Checks missing:
	* - no extra members in response
	* - no extra members in error struct
	* - resp. ID validation
	*/
	function jsonrpc_parse_resp($data, $return_phpvals=false, $use_extension=false)
	{
		$GLOBALS['_xh']['isf']=0;
		$GLOBALS['_xh']['isf_reason']='';
		if ($return_phpvals && $use_extension)
		{
			$ok = json_parse_native($data);
		}
		else
		{
			$ok = json_parse($data, $return_phpvals);
		}
		if ($ok)
		{
			if (!$return_phpvals)
			{
				$GLOBALS['_xh']['value'] = @$GLOBALS['_xh']['value']->me['struct'];
			}
			if (!is_array($GLOBALS['_xh']['value']) || !array_key_exists('result', $GLOBALS['_xh']['value'])
				|| !array_key_exists('error', $GLOBALS['_xh']['value']) || !array_key_exists('id', $GLOBALS['_xh']['value']))
			{
				//$GLOBALS['_xh']['isf'] = 2;
				$GLOBALS['_xh']['isf_reason'] = 'JSON parsing did not return correct jsonrpc request object';
				return false;
			}
			if (!$return_phpvals)
			{
				$d_error = php_jsonrpc_decode($GLOBALS['_xh']['value']['error']);
				$GLOBALS['_xh']['value']['id'] = php_jsonrpc_decode($GLOBALS['_xh']['value']['id']);
			}
			else
			{
				$d_error = $GLOBALS['_xh']['value']['error'];
			}
			$GLOBALS['_xh']['id'] = $GLOBALS['_xh']['value']['id'];
			if ($d_error != null)
			{
				$GLOBALS['_xh']['isf'] = 1;

				//$GLOBALS['_xh']['value'] = $d_error;
				if (is_array($d_error) && array_key_exists('faultCode', $d_error)
					&& array_key_exists('faultString', $d_error))
				{
					if($d_error['faultCode'] == 0)
					{
						// FAULT returned, errno needs to reflect that
						$d_error['faultCode'] = -1;
					}
					$GLOBALS['_xh']['value'] = $d_error;
				}
				// NB: what about jsonrpc servers that do NOT respect
				// the faultCode/faultString convention???
				// we force the error into a string. regardless of type...
				else //if (is_string($GLOBALS['_xh']['value']))
				{
					if ($return_phpvals)
					{
						$GLOBALS['_xh']['value'] = array('faultCode' => -1, 'faultString' => var_export($GLOBALS['_xh']['value']['error'], true));
					}
					else
					{
						$GLOBALS['_xh']['value'] = array('faultCode' => -1, 'faultString' => serialize_jsonrpcval($GLOBALS['_xh']['value']['error']));
					}
				}

			}
			else
			{
				$GLOBALS['_xh']['value'] = $GLOBALS['_xh']['value']['result'];
			}
			return true;

		}
		else
		{
			return false;
		}
	}

	class jsonrpc_client extends xmlrpc_client
	{
		// by default, no multicall exists for JSON-RPC, so do not try it
		var $no_multicall = true;
		// default return type of calls to json-rpc servers: jsonrpcvals
		var $return_type = 'jsonrpcvals';

		/*
		function jsonrpc_client($path, $server='', $port='', $method='')
		{
			$this->xmlrpc_client($path, $server, $port, $method);
			// we need to override the list of std supported encodings, since
			// according to ECMA-262, the standard charset is UTF-16
			$this->accepted_charset_encodings = array('UTF-16', 'UTF-8', 'ISO-8859-1', 'US-ASCII');
		}
		*/
	}


	class jsonrpcmsg extends xmlrpcmsg
	{
		var $id = null; // used to store request ID internally
		var $content_type = 'text/plain';

		function jsonrpcmsg($meth, $pars=0, $id=null)
		{
			// NB: a NULL id is allowed and has a very definite meaning!
			$this->id = $id;
			$this->xmlrpcmsg($meth, $pars);
		}

		function createPayload($charset_encoding='')
		{
			// @ todo: verify if all chars are allowed for method names or can
			// we just skip the js encoding on it?
			$this->payload = "{\n\"method\": \"" . json_encode_entities($this->methodname, '', $charset_encoding) . "\",\n\"params\": [ ";
			for($i = 0; $i < sizeof($this->params); $i++)
			{
				$p = $this->params[$i];
				// MB: we try to force serialization as json even though the object
				// param might be a plain xmlrpcval object.
				// This way we do not need to override addParam, aren't we lazy?
				$this->payload .= "\n  " . serialize_jsonrpcval($p, $charset_encoding) .
				",";
			}
			$this->payload = substr($this->payload, 0, -1) . "\n],\n\"id\": " . ($this->id == null ? 'null' : $this->id) . "\n}\n";
		}

		/**
		* @param string $data the xmlrpc response, eventually including http headers
		* @param bool   $headers_processed when true prevents parsing HTTP headers for interpretation of content-encoding and conseuqent decoding
		* @param string $return_type decides return type, i.e. content of response->value(). Either 'xmlrpcvals', 'xml' or 'phpvals'
		* @access private
		*/
		function &parseResponse($data='', $headers_processed=false, $return_type='jsonrpcvals')
		{
			if($this->debug)
			{
				print "<PRE>---GOT---\n" . htmlentities($data) . "\n---END---\n</PRE>";
			}

			if($data == '')
			{
				error_log('XML-RPC: jsonrpcmsg::parseResponse: no response received from server.');
				$r =& new jsonrpcresp(0, $GLOBALS['xmlrpcerr']['no_data'], $GLOBALS['xmlrpcstr']['no_data']);
				return $r;
			}

			$GLOBALS['_xh']=array();

			$raw_data = $data;
			// parse the HTTP headers of the response, if present, and separate them from data
			if(ereg("^HTTP",$data))
			{
				$r =& $this->parseResponseHeaders($data, $headers_processed);
				if ($r)
				{
					// parent class implementation of parseResponseHeaders returns in case
					// of error an object of the wrong type: recode it into correct object
					$rj =& new jsonrpcresp(0, $r->faultCode(), $r->faultString());
					return $rj;
				}
			}
			else
			{
				$GLOBALS['_xh']['headers'] = array();
				$GLOBALS['_xh']['cookies'] = array();
			}

			// be tolerant of extra whitespace in response body
			$data = trim($data);

			// be tolerant of junk after methodResponse (e.g. javascript ads automatically inserted by free hosts)
			$end = strrpos($data, '}');
			if ($end)
			{
				$data = substr($data, 0, $end+1);
			}
			// if user wants back raw json, give it to him
			if ($return_type == 'json')
			{
				$r =& new jsonrpcresp($data, 0, '', 'json');
				$r->hdrs = $GLOBALS['_xh']['headers'];
				$r->_cookies = $GLOBALS['_xh']['cookies'];
				$r->raw_data = $raw_data;
				return $r;
			}

			// @todo shall we try to check for non-unicode json received ???

			if (!jsonrpc_parse_resp($data, $return_type=='phpvals'))
			{
				if ($this->debug)
				{
					/// @todo echo something for user?
				}

				$r =& new jsonrpcresp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
					$GLOBALS['xmlrpcstr']['invalid_return'] . ' ' . $GLOBALS['_xh']['isf_reason']);
			}
			//elseif ($return_type == 'jsonrpcvals' && !is_object($GLOBALS['_xh']['value']))
			//{
				// then something odd has happened
				// and it's time to generate a client side error
				// indicating something odd went on
			//	$r = & new jsonrpcresp(0, $GLOBALS['xmlrpcerr']['invalid_return'],
			//		$GLOBALS['xmlrpcstr']['invalid_return']);
			//}
			else
			{
				$v = $GLOBALS['_xh']['value'];

				if ($this->debug)
				{
					print "<PRE>---PARSED---\n" ;
					var_export($v);
					print "\n---END---</PRE>";
				}

				if($GLOBALS['_xh']['isf'])
				{
					$r =& new jsonrpcresp(0, $v['faultCode'], $v['faultString']);
				}
				else
				{
					$r =& new jsonrpcresp($v, 0, '', $return_type);
				}
				$r->id = $GLOBALS['_xh']['id'];
			}

			$r->hdrs = $GLOBALS['_xh']['headers'];
			$r->_cookies = $GLOBALS['_xh']['cookies'];
			$r->raw_data = $raw_data;
			return $r;
		}
	}

	class jsonrpcresp extends xmlrpcresp
	{
		var $content_type = 'text/plain'; // NB: forces us to send US-ASCII over http
		var $id = null;

		/// @todo override creator, to set proper valtyp and id!

		function serialize($charset_encoding='')
		{
			$this->payload = serialize_jsonrpcresp($this, $this->id, $charset_encoding);
			return $this->payload;
		}

	}

	class jsonrpcval extends xmlrpcval
	{
		function serialize($charset_encoding='')
		{
/*echo "GO GO GO!";*/
			return serialize_jsonrpcval($this, $charset_encoding);
		}
	}

	/**
	* Takes a json value in PHP jsonrpcval object format
	* and translates it into native PHP types.
	*
	* @param  jsonrpcval $jsonrpc_val
	* @param  array      $options if 'decode_php_objs' is set in the options array, jsonrpc objects can be decoded into php objects
	* @return mixed
	*/
	function php_jsonrpc_decode($jsonrpc_val, $options='')
	{
		$kind = $jsonrpc_val->kindOf();

		if($kind == 'scalar')
		{
			return $jsonrpc_val->scalarval();
		}
		elseif($kind == 'array')
		{
			$size = $jsonrpc_val->arraysize();
			$arr = array();

			for($i = 0; $i < $size; $i++)
			{
				$arr[] = php_jsonrpc_decode($jsonrpc_val->arraymem($i), $options);
			}
			return $arr;
		}
		elseif($kind == 'struct')
		{
			$jsonrpc_val->structreset();
			// If user said so, try to rebuild php objects for specific struct vals.
			/// @todo should we raise a warning for class not found?
			// shall we check for proper subclass of xmlrpcval instead of
			// presence of _php_class to detect what we can do?
			if (@in_array('decode_php_objs', $options) && $jsonrpc_val->_php_class != ''
				&& class_exists($jsonrpc_val->_php_class))
			{
				$obj = @new $jsonrpc_val->_php_class;
				while(list($key,$value) = $jsonrpc_val->structeach())
				{
					$obj->$key = php_jsonrpc_decode($value, $options);
				}
				return $obj;
			}
			else
			{
				$arr = array();
				while(list($key,$value) = $jsonrpc_val->structeach())
				{
					$arr[$key] = php_jsonrpc_decode($value, $options);
				}
				return $arr;
			}
		}
	}

	/**
	* Takes native php types and encodes them into jsonrpc PHP object format.
	* It will not re-encode jsonrpcval objects.
	*
	* @param mixed $php_val the value to be converted into a jsonrpcval object
	* @param array $options	can include 'encode_php_objs'
	* @return jsonrpcval
	*/
	function &php_jsonrpc_encode($php_val, $options='')
	{
		$type = gettype($php_val);
		$jsonrpc_val =& new jsonrpcval;

		switch($type)
		{
			case 'array':
				// PHP arrays can be encoded to either objects or arrays,
				// depending on wheter they are hashes or plain 0..n integer indexed
				// A shorter one-liner would be
				// $tmp = array_diff(array_keys($php_val), range(0, count($php_val)-1));
				// but execution time skyrockets!
				$j = 0;
				$arr = array();
				$ko = false;
				foreach($php_val as $key => $val)
				{
					$arr[$key] =& php_jsonrpc_encode($val, $options);
					if(!$ko && $key !== $j)
					{
						$ko = true;
					}
					$j++;
				}
				if($ko)
				{
					$jsonrpc_val->addStruct($arr);
				}
				else
				{
					$jsonrpc_val->addArray($arr);
				}
				break;
			case 'object':
				if(is_a($php_val, 'jsonrpcval'))
				{
					$jsonrpc_val = $php_val;
				}
				else
				{
					$arr = array();
					while(list($k,$v) = each($php_val))
					{
						$arr[$k] = php_jsonrpc_encode($v, $options);
					}
					$jsonrpc_val->addStruct($arr);
					if (@in_array('encode_php_objs', $options))
					{
						// let's save original class name into xmlrpcval:
						// might be useful later on...
						$jsonrpc_val->_php_class = get_class($php_val);
					}
				}
				break;
			case 'integer':
				$jsonrpc_val->addScalar($php_val, $GLOBALS['xmlrpcInt']);
				break;
			case 'double':
				$jsonrpc_val->addScalar($php_val, $GLOBALS['xmlrpcDouble']);
				break;
			case 'string':
				$jsonrpc_val->addScalar($php_val, $GLOBALS['xmlrpcString']);
				break;
			case 'boolean':
				$jsonrpc_val->addScalar($php_val, $GLOBALS['xmlrpcBoolean']);
				break;
			case 'NULL':
				$jsonrpc_val->addScalar($php_val, $GLOBALS['xmlrpcNull']);
				break;
			// catch "resource", "user function", "unknown type"
			default:
				break;
			}
			return $jsonrpc_val;
	}

	/**
	* Serialize a jsonrpcresp (or xmlrpcresp) as json.
	* Moved outside of the corresponding class to ease multi-serialization of
	* xmlrpcresp objects
	* @param xmlrpcresp or jsonrpcresp $resp
	* @param integer $id
	* @return string
	*
	* @access private
	*/
	function serialize_jsonrpcresp($resp, $id="null", $charset_encoding='')
	{
		$result = "{\n\"id\": " . ($id === null ? 'null' : $id) . ", ";
		if($resp->errno)
		{
			// let non-ASCII response messages be tolerated by clients
			// by encoding non ascii chars
			$result .= "\"error\": { \"faultCode\": " . $resp->errno . ", \"faultString\": \"" . json_encode_entities($resp->errstr, null, $charset_encoding) . "\" }, \"result\": null";
		}
		else
		{
			if(!is_object($resp->val) || !is_a($resp->val, 'xmlrpcval'))
			{
				if (is_string($resp->val) && $resp->valtyp == 'json')
				{
					$result .= "\"error\": null, \"result\": " . $resp->val;
				}
				else
				{
					/// @todo try to build something serializable?
					die('cannot serialize jsonrpcresp objects whose content is native php values');
				}
			}
			else
			{
				$result .= "\"error\": null, \"result\": " .
					serialize_jsonrpcval($resp->val, $charset_encoding);
			}
		}
		$result .= "\n}";
		return $result;
	}

	/**
	* Serialize a jsonrpcval (or xmlrpcval) as json.
	* Moved outside of the corresponding class to ease multi-serialization of
	* xmlrpcval objects
	* @access private
	*/
	function serialize_jsonrpcval($value, $charset_encoding='')
	{
/*echo "HERE WE GO!";
var_dump($value);*/

		reset($value->me);
		list($typ, $val) = each($value->me);

		$rs = '';
		switch(@$GLOBALS['xmlrpcTypes'][$typ])
		{
			case 3:
				// struct
				if ($value->_php_class)
				{
					/// @todo implement json-rpc extension for object serialization
					//$rs.='<struct php_class="' . $this->_php_class . "\">\n";
				}
				else
				{
					$rs .= "{ ";
				}
				foreach($val as $key2 => $val2)
				{
					$rs .= '"'.json_encode_entities($key2, null, $charset_encoding).'": ';
					$rs .= serialize_jsonrpcval($val2, $charset_encoding);
					$rs .= ", ";
				}
				$rs = substr($rs, 0, -2) . ' }';
				break;
			case 2:
				// array
				$rs .= "[ ";
				$len = sizeof($val);
				for($i = 0; $i < $len; $i++)
				{
					$rs .= serialize_jsonrpcval($val[$i], $charset_encoding);
					$rs .= ", ";
				}
				$rs = substr($rs, 0, -2) . " ]";
				break;
			case 1:
				switch($typ)
				{
					case $GLOBALS['xmlrpcBase64']:
						// treat base 64 values as strings ???
						$rs .= '"' . base64_encode($val) . '"';
						break;
					case $GLOBALS['xmlrpcBoolean']:
						$rs .= ($val ? 'true' : 'false');
						break;
					case $GLOBALS['xmlrpcString']:
						$rs .= '"' . json_encode_entities($val, null, $charset_encoding). '"';
						break;
					case $GLOBALS['xmlrpcI4']:
					case $GLOBALS['xmlrpcInt']:
					case $GLOBALS['xmlrpcDateTime']:
						$rs .= "${val}";
						break;
					case $GLOBALS['xmlrpcDouble']:
						// add a .0 in case valueis integer.
						// This helps us carrying around floats
						if (strpos("${val}", '.') !== false || strpos("${val}", 'e') !== false)
						{
							$rs .= "${val}";
						}
						else
						{
							$rs .= "${val}.0";
						}
						break;
					default:
						$rs .= "null";
				}
				break;
			default:
				break;
		}
		return $rs;
	}
?>
