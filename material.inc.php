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
