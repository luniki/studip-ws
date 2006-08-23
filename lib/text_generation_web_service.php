<?php

/*
 * text_generation_web_service.php - Text generation service.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'markov_chainer.php';

require_once '../test/fixtures/user_struct.php';

/**
 * Text generation service using markov chains.
 *
 * @package   studip
 * @package   webservice
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */
class TextGenerationWebService extends Studip_Ws_Service {

  function TextGenerationWebService () {
    $this->add_api_method('generate_text',
                          array('string', 'int'),
                          'string',
                          'Generates text using Markov chains.');
    $this->add_api_method('generate_sentences',
                          array('string', 'int'),
                          array(array('UserStruct')),
                          'Generates sentences using Markov chains.');
  }
  

  # first argument is api-key used to authenticate request
  function before_filter($name, &$args) {
    $api_key = current($args);
    if ($api_key !== 'secret')
      return new Studip_Ws_Fault('Could not authenticate client.');
  }


  # example filter applied to the service's result
  function after_filter($name, &$args, &$result) {
    if ($name === 'generate_text')
      $result = strtolower($result);
  }


  # example service operation; generates some sentences using markov chains
  function generate_sentences_action($api_key, $number_of_sentences) {

    $user = new UserStruct();
    $user->name = "hallo";
    $user->id   = 1;
    return array(array($user, $user),array($user, $user));
    
    $result = array();

    # create 2nd order MarkovChainer 
    $order = 2;
    $text = dirname(__FILE__).'/grimm.txt';
    $mc =& new MarkovChainer($order);
    $mc->add_text(file_get_contents($text));
    
    if ($number_of_sentences < 1)
      $number_of_sentences = 1;
    
    for ($i = 0; $i < $number_of_sentences; ++$i)
      $result[] = $mc->generate_sentence();

    return $result;
  }


  # example service operation; generates some text using markov chains
  function generate_text_action($api_key, $number_of_sentences) {
    return join("\n", $this->generate_sentences_action($api_key,
                                                       $number_of_sentences));
  }
}
