<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Zookeepers implementation : © <Matheus Gomes> <matheusgomesforwork@gmail.com>
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * Zookeepers game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


$this->resource_types = array(
  1 => array(
    "label" => clienttranslate("plant"),
    "labeltr" => self::_("plant"),
    "total" => 20,
    "per_player" => 4
  ),
  2 => array(
    "label" => clienttranslate("meat/fish"),
    "labeltr" => self::_("meat/fish"),
    "total" => 20,
    "per_player" => 4,
  ),
  3 => array(
    "label" => clienttranslate("medical kit"),
    "labeltr" => self::_("medical kit"),
    "total" => 15,
    "per_player" => 3,
  )
);

$this->mainActions = array(
  1 => array("name" => "collectResources", "update_scores" => false),
  2 => array("name" => "saveSpecies", "update_scores" => true),
  3 => array("name" => "quarentineSpecies", "update_scores" => true),
  4 => array("name" => "discardSpecies", "update_scores" => true),
  5 => array("name" => "swapKeepers", "update_scores" => true),
  6 => array("name" => "discardKeeper", "update_scores" => true),
  7 => array("name" => "hireKeeper", "update_scores" => false),
);

$this->freeActions = array(
  1 => array("name" => "zooHelp", "update_scores" => true),
  2 => array("name" => "newSpecies", "update_scores" => false),
  3 => array("name" => "exchangeResources", "update_scores" => false),
  4 => array("name" => "collectFromExchange", "update_scores" => false),
  5 => array("name" => "returnFromExchange", "update_scores" => false),
);
