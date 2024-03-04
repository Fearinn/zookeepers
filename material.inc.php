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

$this->keepers_info = array(
  1 => array(
    "name" => "Abdul",
    "points" => 3,
    "class" => array("mammal"),
    "operator" => "single"
  ),
  2 => array(
    "name" => "Adaeze",
    "points" => 2,
    "continent" => array("AS"),
    "operator" => "single"
  ),
  3 => array(
    "name" => "Afonso",
    "points" => 5,
    "class" => array("mammal"),
    "diet" => array("herbivore"),
    "operator" => "and"
  ),
  4 => array(
    "name" => "Ana",
    "points" => 3,
    "habitat" => array("PRA"),
    "operator" => "single"
  ),
  5 => array(
    "name" => "Ayaan",
    "points" => 3,
    "continent" => array(
      "EU",
      "OC"
    ),
    "operator" => "or"
  ),
  6 => array(
    "name" => "Bjorn",
    "points" => 4,
    "class" => array("reptile"),
    "operator" => "single"
  ),
  7 => array(
    "name" => "Dimitri",
    "points" => 3,
    "continent" => array("AM"),
    "operator" => "single"
  ),
  8 => array(
    "name" => "Gabriela",
    "points" => 4,
    "habitat" => array("TEM"),
    "operator" => "single"
  ),
  9 => array(
    "name" => "Heidi",
    "points" => 4,
    "diet" => "carnivore",
    "continent" => array("AS"),
    "operator" => "and"
  ),
  10 => array(
    "name" => "Helen",
    "points" => 5,
    "diet" => "herbivore",
    "continent" => array("AF"),
    "operator" => "and"
  ),
  11 => array(
    "name" => "Isabela",
    "points" => 4,
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "operator" => "and"
  ),
  12 => array(
    "name" => "Jen",
    "points" => 5,
    "status" => "EN",
    "operator" => "single"
  ),
  13 => array(
    "name" => "Jhon",
    "points" => 5,
    "diet" => "omnivore",
    "operator" => "single"
  ),
  14 => array(
    "name" => "Jung-woo",
    "points" => 2,
    "habitat" => array("TRO"),
    "operator" => "single"
  ),
  15 => array(
    "name" => "Kareena",
    "points" => 5,
    "habitat" => array(
      "AQU",
      "DES"
    ),
    "operator" => "OR"
  ),
  16 => array(
    "name" => "Kulap",
    "points" => 2,
    "continent" => array("AF"),
    "operator" => "single"
  ),
  17 => array(
    "name" => "Paco",
    "points" => 2,
    "diet" => "herbivore",
    "operator" => "single"
  ),
  18 => array(
    "name" => "Rui",
    "points" => 4,
    "habitat" => array("SAV"),
    "operator" => "single"
  ),
  19 => array(
    "name" => "Yu Yan",
    "points" => 2,
    "diet" => "carnivore",
    "operator" => "single"
  ),
  20 => array(
    "name" => "Zala",
    "points" => 3,
    "class" => array("bird"),
    "operator" => "single"
  ),
);

$this->species_info = array(
  12 => array(
    "name" => clienttranslate("Snowy Owl"),
    "scientific_name" => "Bubo scandiaca",
    "class" => "bird",
    "diet" =>
    "carnivore",
    "status" => "VU",
    "habitat" => array(
      "MTN",
      "PRA",
      "TEM"
    ),
    "continent" => array(
      "AM",
      "AS",
      "EU"
    ),
    "cost" => array(
      "plant" => 0,
      "meat" => 2,
      "kit" => 1
    ),
    "points" => 3
  ),
  67 => array(
    "name" => clienttranslate("McCord's snake-necked turtle"),
    "scientific_name" => "Chelodina mccordi",
    "class" => "reptile",
    "diet" => "omnivore",
    "status" => "CR",
    "habitat" => array(
      "TRO",
      "AQU"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 3
    ),
    "points" => 6,
  ),
  38 => array(
    "name" => clienttranslate("Egyptian tortoise"),
    "scientific_name" => "Testudo kleinmanni",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" =>
    "CR",
    "habitat" => array("DES"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  35 => array(
    "name" => clienttranslate("Spiny hill turtle"),
    "scientific_name" => "Heosemys spinosa",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),
  26 => array(
    "name" => clienttranslate("Komodo dragon"),
    "scientific_name" => "Varanus komodoensis",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array(
      "SAV",
      "TRO"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 5,
      "kit" => 1
    ),
    "points" => 3,
  ),
  21 => array(
    "name" => clienttranslate("Burmese python"),
    "scientific_name" => "Python bivittatus",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 1
    ),
    "points" => 3,
  ),
  7 => array(
    "name" => clienttranslate("American alligator"),
    "scientific_name" => "Alligator mississippiensis",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("AQU"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 5,
      "kit" => 0
    ),
    "points" => 1,
  ),
  36 => array(
    "name" => clienttranslate("Madagascar tree boa"),
    "scientific_name" => "Sanzinia madagascariensis",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array(
      "TRO",
      "PRA"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 0
    ),
    "points" => 1,
  ),
  50 => array(
    "name" => clienttranslate("Spur-thighed tortoise"),
    "scientific_name" => "Testudo graeca",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array("PRA"),
    "continent" => array("EU"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  22 => array(
    "name" => clienttranslate("Rhinoceros iguana"),
    "scientific_name" => "Cyclura cornuta",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array(
      "PRA",
      "SAV"
    ),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),
  14 => array(
    "name" => clienttranslate("Gila monster"),
    "scientific_name" => "Heloderma suspectum",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "NT",
    "habitat" => array("DES"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 2,
      "kit" => 0
    ),
    "points" => 1,
  ),
  31 => array(
    "name" => clienttranslate("Jamaican boa"),
    "scientific_name" => "Chilabothrus subflavus ",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 1
    ),
    "points" => 3,
  ),
  54 => array(
    "name" => clienttranslate("Nile crocodile"),
    "scientific_name" => "Crocodylus niloticus",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array(
      "PRA",
      "AQU"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 6,
      "kit" => 0
    ),
    "points" => 1,
  ),
  10 => array(
    "name" => clienttranslate("African penguin"),
    "scientific_name" => "Spheniscus demersus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "EN",
    "habitat" => array("AQU"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),

  4 => array(
    "name" => clienttranslate("Great white pelican"),
    "scientific_name" => "Pelecanus onocrotalus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("AQU"),
    "continent" => array("AF", "AS", "EU"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 0
    ),
    "points" => 1,
  ),
  55 => array(
    "name" => clienttranslate("Nicobar pigeon"),
    "scientific_name" => "Caloenas nicobarica",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  66 => array(
    "name" => clienttranslate("Bali myna"),
    "scientific_name" => "Leucopsar rothschildi",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "CR",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 3
    ),
    "points" => 6,
  ),
  28 => array(
    "name" => clienttranslate("Lesser sulphur-crested cockatoo"),
    "scientific_name" => "Cacatua sulphurea sulphurea",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array(
      "TRO",
      "PRA"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  2 => array(
    "name" => clienttranslate("Southern cassowary"),
    "scientific_name" => "Casuarius casuarius",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "LC",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 0
    ),
    "points" => 1,
  ),
  59 => array(
    "name" => clienttranslate("Steppe eagle"),
    "scientific_name" => "Aquila nipalensis",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "EN",
    "habitat" => array(
      "PRA",
      "DES"
    ),
    "continent" => array("AF", "AS", "EU"),
    "cost" => array(
      "plant" => 0,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  53 => array(
    "name" => clienttranslate("Griffon vulture"),
    "scientific_name" => "Gyps fulvus fulvus",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array(
      "PRA",
      "DES"
    ),
    "continent" => array("AF", "AS", "EU"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 0
    ),
    "points" => 1,
  ),
  3 => array(
    "name" => clienttranslate("Common barn owl"),
    "scientific_name" => "Tyto alba",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("PRA"),
    "continent" => array("AF", "AM", "AS", "EU", "OC"),
    "cost" => array(
      "plant" => 0,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  6 => array(
    "name" => clienttranslate("Laughing kookaburra"),
    "scientific_name" => "Dacelo novaeguineae",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("TEM", "PRA"),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  11 => array(
    "name" => clienttranslate("Red-tailed amazon"),
    "scientific_name" => "Amazona brasiliensis",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  49 => array(
    "name" => clienttranslate("Waldrapp ibis"),
    "scientific_name" => "Geronticus eremita",
    "class" => "bird",
    "diet" => "carnivore",
    "status" => "EN",
    "habitat" => array(
      "DES",
      "MTN"
    ),
    "continent" => array("AF", "AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  16 => array(
    "name" => clienttranslate("Long-billed corella"),
    "scientific_name" => "Cacatua tenuirostris tenuirostris",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "LC",
    "habitat" => array(
      "TEM",
      "PRA"
    ),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  52 => array(
    "name" => clienttranslate("Fischer's lovebird"),
    "scientific_name" => "Agapornis fischeri",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("SAV"),
    "continent" => array("EU,
   AF,
   AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  5 => array(
    "name" => clienttranslate("Golden-capped conure"),
    "scientific_name" => "Aratinga auricapillus aurifrons",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array(
      "TEM",
      "PRA"
    ),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  39 => array(
    "name" => clienttranslate("Blue-streaked lory"),
    "scientific_name" => "Eos reticulata",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  15 => array(
    "name" => clienttranslate("Great argus"),
    "scientific_name" => "Argusianus argus argus",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  32 => array(
    "name" => clienttranslate("European rabbit"),
    "scientific_name" => "Oryctolagus cuniculus",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array(
      "TEM",
      "PRA"
    ),
    "continent" => array("EU"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),
  56 => array(
    "name" => clienttranslate("African elephant"),
    "scientific_name" => "Loxodonta africana",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  30 => array(
    "name" => clienttranslate("Queensland koala"),
    "scientific_name" => "Phascolarctos cinereus adustus",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array("TEM"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  25 => array(
    "name" => clienttranslate("Chimpanzee"),
    "scientific_name" => "Pan troglodytes",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  24 => array(
    "name" => clienttranslate("Lar gibbon"),
    "scientific_name" => "Hylobates lar",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array(
      "TRO",
      "TEM"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  68 => array(
    "name" => clienttranslate("Western lowland gorilla"),
    "scientific_name" => "Gorilla gorilla gorilla",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  57 => array(
    "name" => clienttranslate("Ring-tailed lemur"),
    "scientific_name" => "Lemur catta",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array(
      "TEM",
      "SAV"
    ),
    "continent" => array(
      "AF",
      "AS"
    ),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),
  18 => array(
    "name" => clienttranslate("Black-headed spider monkey"),
    "scientific_name" => "Ateles fusciceps",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  34 => array(
    "name" => clienttranslate("Lion-tailed macaque"),
    "scientific_name" => "Macaca silenus",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  13 => array(
    "name" => clienttranslate("Golden lion tamarin"),
    "scientific_name" => "Leontopithecus rosalia",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 2
    ),
    "points" => 4,
  ),
  69 => array(
    "name" => clienttranslate("Sumatran orangutan"),
    "scientific_name" => "Pongo abelii",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 3
    ),
    "points" => 6,
  ),
  58 => array(
    "name" => clienttranslate("Jaguar"),
    "scientific_name" => "Panthera onca",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 0
    ),
    "points" => 1,
  ),
  41 => array(
    "name" => clienttranslate("African lion"),
    "scientific_name" => "Panthera leo",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 5,
      "kit" => 1
    ),
    "points" => 3,
  ),
  60 => array(
    "name" => clienttranslate("Persian leopard"),
    "scientific_name" => "Panthera pardus saxicolor",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "EN",
    "habitat" => array(
      "TEM",
      "MTN"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 2
    ),
    "points" => 4,
  ),
  43 => array(
    "name" => clienttranslate("Iberian lynx"),
    "scientific_name" => "Lynx pardinus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "EN",
    "habitat" => array("PRA"),
    "continent" => array("EU"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 2
    ),
    "points" => 4,
  ),
  17 => array(
    "name" => clienttranslate("Iberian wolf"),
    "scientific_name" => "Canis lupus signatus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("TEM"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 0
    ),
    "points" => 1,
  ),
  23 => array(
    "name" => clienttranslate("Red panda"),
    "scientific_name" => "Ailurus fulgens",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array(
      "TEM",
      "TRO"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4,
  ),
  37 => array(
    "name" => clienttranslate("Clouded leopard"),
    "scientific_name" => "Neofelis nebulosa",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array(
      "TRO",
      "TEM"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 3,
      "kit" => 1
    ),
    "points" => 3,
  ),
  29 => array(
    "name" => clienttranslate("Sumatran tiger"),
    "scientific_name" => "Panthera tigris sumatrae",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 3
    ),
    "points" => 6,
  ),
  51 => array(
    "name" => clienttranslate("Brown bear"),
    "scientific_name" => "Ursus arctos",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "LC",
    "habitat" => array("TEM"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 3,
      "meat" => 3,
      "kit" => 0
    ),
    "points" => 1,
  ),
  44 => array(
    "name" => clienttranslate("White rhinoceros"),
    "scientific_name" => "Ceratotherium simum",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  33 => array(
    "name" => clienttranslate("Grevy's zebra"),
    "scientific_name" => "Equus grevyi",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array(
      "SAV",
      "PRA"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),
  48 => array(
    "name" => clienttranslate("Addax"),
    "scientific_name" => "Addax nasomaculatus",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("DES"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  27 => array(
    "name" => clienttranslate("American bison"),
    "scientific_name" => "Bison bison bison",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("PRA"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  9 => array(
    "name" => clienttranslate("Eastern bongo"),
    "scientific_name" => "Tragelaphus eurycerus isaaci",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  19 => array(
    "name" => clienttranslate("Bactrian camel"),
    "scientific_name" => "Camelus ferus",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array(
      "DES",
      "PRA"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 5,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  20 => array(
    "name" => clienttranslate("Angolan giraffe"),
    "scientific_name" => "Giraffa camelopardalis angolensis",
    "class" => "mammal",
    "diet" => "herbivore", "status" => "LC",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  42 => array(
    "name" => clienttranslate("Hippopotamus"),
    "scientific_name" => "Hippopotamus amphibius",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array(
      "TRO",
      "SAV",
      "PRA"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  1 => array(
    "name" => clienttranslate("Black-faced impala"),
    "scientific_name" => "Aepyceros melampus petersi",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array(
      "SAV",
      "PRA"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 4,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  40 => array(
    "name" => clienttranslate("Cheetah"),
    "scientific_name" => "Acinonyx jubatus jubatus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "VU",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 1
    ),
    "points" => 3
  ),
  65 => array(
    "name" => clienttranslate("American flamingo"),
    "scientific_name" => "Phoenicopterus ruber",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "LC",
    "habitat" => array("AQU"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  64 => array(
    "name" => clienttranslate("One-horned rhinoceros"),
    "scientific_name" => "Rhinoceros unicornis",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  62 => array(
    "name" => clienttranslate("Manchurian crane"),
    "scientific_name" => "Grus japonensis",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "EN",
    "habitat" => array("PRA"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 2
    ),
    "points" => 4
  ),
  47 => array(
    "name" => clienttranslate("Green aracari"),
    "scientific_name" => "Pteroglossus viridis",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "LC",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  70 => array(
    "name" => clienttranslate("Black-and-white ruffed lemur"),
    "scientific_name" => "Varecia variegata variegata",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6
  ),
  46 => array(
    "name" => clienttranslate("Hyacinth macaw"),
    "scientific_name" => "Anodorhynchus hyacinthinus",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  63 => array(
    "name" => clienttranslate("Slender-tailed meerkat"),
    "scientific_name" => "Suricata suricatta",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array(
      "PRA",
      "SAV"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 0,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  8 => array(
    "name" => clienttranslate("Common Green Iguana"),
    "scientific_name" => "Iguana iguana",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "LC",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  45 => array(
    "name" => clienttranslate("Common Bottlenose Dolphin"),
    "scientific_name" => "Tursiops truncatus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("TRO"),
    "continent" => array(
      "AF",
      "AM",
      "AS",
      "EU",
      "OC"
    ),
    "cost" => array(
      "plant" => 0,
      "meat" => 5,
      "kit" => 0
    ),
    "points" => 1,
  ),
  61 => array(
    "name" => clienttranslate("Frilled Lizard"),
    "scientific_name" => "Chlamydosaurus kingii",
    "class" => "reptile",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("TEM"),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 0,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
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
