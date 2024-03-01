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
    "label" => clienttranslate("meat"),
    "labeltr" => self::_("meat"),
    "total" => 20,
    "per_player" => 4,
  ),
  3 => array(
    "label" => clienttranslate("kit"),
    "labeltr" => self::_("kit"),
    "total" => 15,
    "per_player" => 3,
  )
);

$this->species_info = array(
  1 => array(
    "name" => clienttranslate("black-faced impala"),
    "scientific_name" => "Aepiaceros melampus petersi",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "yellow",
    "habitat" => array("PRA", "SAV"),
    "continent" => array("AF"),
    "cost" => array("plant" => 4, "kit" => 1),
    "points" => 3,
  ),
  2 => array(
    "name" => clienttranslate("shouthern cassowary"),
    "scientific_name" => "Casuarius casuarius",
    "class" => "bird",
    "diet" => "onivore",
    "status" => "green",
    "habitat" => array("SAV", "TRO"),
    "continent" => array("OC"),
    "cost" => array("plant" => 2, "meat" => 2),
    "points" => 1,
  ),
  3 => array(
    "name" => clienttranslate("common barn owl"),
    "scientific_name" => "Tyto alba",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "green",
    "habitat" => array("PRA"),
    "continent" => array("AF, AM, AS, EU, OC"),
    "cost" => array("plant" => 2, "meat" => 2),
    "points" => 1,
  ),
  4 => array(
    "name" => clienttranslate("great white pelican"),
    "scientific_name" => "Pelecanus onocrolatus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "green",
    "habitat" => array("AQU"),
    "continent" => array("AF, AS, EU"),
    "cost" => array("meat" => 3),
    "points" => 1,
  ),
  5 => array(
    "name" => clienttranslate("golden-capped conure"),
    "scientific_name" => "Aratinga auricapillus aurifrons",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "blue",
    "habitat" => array("PRA", "TEM"),
    "continent" => array("AM"),
    "cost" => array("plant" => 1),
    "points" => 1,
  ),
  6 => array(
    "name" => clienttranslate("laughing kookaburra"),
    "scientific_name" => "Dacelo novaeguineae",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "green",
    "habitat" => array("TEM"),
    "continent" => array("OC"),
    "cost" => array("meat" => 1),
    "points" => 1,
  ),
  7 => array(
    "name" => clienttranslate("American alligator"),
    "scientific_name" => "Alligator mississippiensis",
    "class" => "reptile",
    "diet" => "carnivorous",
    "status" => "green",
    "habitat" => array("AQU"),
    "continent" => array("AM"),
    "cost" => array("meat" => 5),
    "points" => 1,
  ),
  8 => array(
    "name" => clienttranslate("common green iguana"),
    "scientific_name" => "Iguana iguana",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "green",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array("plant" => 3),
    "points" => 1,
  ),
  9 => array(
    "name" => clienttranslate("eastern bongo"),
    "scientific_name" => "Tragelaphus eurycerus isaaci",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "red",
    "habitat" => array("TRO"),
    "continent" => array("AF"),
    "cost" => array("plant" => 5, "kit" => 3),
    "points" => 6,
  ),
  10 => array(
    "name" => clienttranslate("African penguim"),
    "scientific_name" => "Spheniscus demersus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "orange",
    "habitat" => array("AQU"),
    "continent" => array("AF"),
    "cost" => array("meat" => 2, "kit" => 2),
    "points" => 4,
  ),
  11 => array(
    "name" => clienttranslate("red-tailed amazon"),
    "scientific_name" => "Amazona brasiliensis",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "blue",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array("plant" => 1),
    "points" => 1,
  ),
  12 => array(
    "name" => clienttranslate("snowy owl"),
    "scientific_name" => "Bubo scandiacus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "yellow",
    "habitat" => array("MTN", "PRA", "TEM"),
    "continent" => array("AM", "AS", "EU"),
    "cost" => array("meat" => 2, "kit" => 1),
    "points" => 3,
  ),
);

$this->keepers = array(
  1 => array("name" => "Adaeze", "points" => 2, "continent" => array("AS"), "operator" => "single"),
  2 => array("name" => "Afonso", "points" => 5, "class" => array("mammal"), "diet" => array("herbivore"), "operator" => "and"),
  3 => array("name" => "Ayaan", "points" => 3, "continent" => array("EU", "OC"), "operator" => "or")
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
