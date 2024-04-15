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

$this->mainActions = array(
  1 => array("name" => "collectResources", "update_scores" => false),
  2 => array("name" => "saveSpecies", "update_scores" => true),
  3 => array("name" => "quarantineSpecies", "update_scores" => true),
  4 => array("name" => "discardSpecies", "update_scores" => true),
  5 => array("name" => "hireKeeper", "update_scores" => false),
  6 => array("name" => "dismissKeeper", "update_scores" => true),
  7 => array("name" => "replaceKeeper", "update_scores" => true),
  8 => array("name" => "zooHelp", "update_scores" => true),
  9 => array("name" => "replaceObjective", "update_scores" => false)
);

$this->freeActions = array(
  1 => array("name" => "newSpecies", "update_scores" => false),
  // 2 => array("name" => "zooHelp", "update_scores" => true),
  3 => array("name" => "exchangeResources", "update_scores" => false),
  4 => array("name" => "collectFromExchange", "update_scores" => false),
  5 => array("name" => "returnFromExchange", "update_scores" => false),
);

$this->resource_types = array(
  "plant" => array(
    "label" => clienttranslate("plant"),
    "total" => 20,
    "per_player" => 4
  ),
  "meat" => array(
    "label" => clienttranslate("meat/fish"),
    "total" => 20,
    "per_player" => 4,
  ),
  "kit" => array(
    "label" => clienttranslate("medical kit"),
    "total" => 15,
    "per_player" => 3,
  )
);

$this->quarantines = array(
  1 => "ALL", 2 => "TEM", 3 => "SAV", 4 => "PRA", 5 => "DES", 6 => "AQU", 7 => "TRO"
);

$this->objectives_info = array(
  1 => array(
    "sprite_pos" => 1,
    "targets" => array(
      7 => array(
        "condition" => array(
          "class" => array("bird"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      8 => array(
        "condition" => array(
          "class" => array("bird"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      9 => array(
        "condition" => array(
          "diet" => array("herbivore"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      10 => array(
        "condition" => array(
          "diet" => array("herbivore"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      11 => array(
        "condition" => array(
          "status" => array("EN"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      12 => array(
        "condition" => array(
          "habitat" => array("PRA"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      13 => array(
        "condition" => array(
          "habitat" => array("PRA"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      14 => array(
        "condition" => array(
          "continent" => array("OC"),
          "operator" => "single"
        ),
        "bonus" => 5
      ),
    )
  ),
  2 => array(
    "sprite_pos" => 2,
    "targets" => array(
      7 => array(
        "condition" => array(
          "diet" => array("carnivore"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      8 => array(
        "condition" => array(
          "diet" => array("carnivore"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      9 => array(
        "condition" => array(
          "class" => array("mammal"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      10 => array(
        "condition" => array(
          "class" => array("mammal"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      11 => array(
        "condition" => array(
          "continent" => array("AM"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      12 => array(
        "condition" => array(
          "habitat" => array("SAV"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      13 => array(
        "condition" => array(
          "habitat" => array("SAV"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      14 => array(
        "condition" => array(
          "habitat" => array("DES"),
          "operator" => "single"
        ),
        "bonus" => 5
      )
    )
  ),
  3 => array(
    "sprite_pos" => 3,
    "targets" => array(
      7 => array(
        "condition" => array(
          "continent" => array("AF"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      8 => array(
        "condition" => array(
          "continent" => array("AF"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      9 => array(
        "condition" => array(
          "habitat" => array("TRO"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      10 => array(
        "condition" => array(
          "habitat" => array("TRO"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      11 => array(
        "condition" => array(
          "continent" => array("EU"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      12 => array(
        "condition" => array(
          "diet" => array("omnivore"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      13 => array(
        "condition" => array(
          "diet" => array("omnivore"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      14 => array(
        "condition" => array(
          "status" => array("CR"),
          "operator" => "single"
        ),
        "bonus" => 5
      ),
    )
  ),
  4 => array(
    "sprite_pos" => 5,
    "targets" => array(
      7 => array(
        "condition" => array(
          "status" => array("NT", "VU"),
          "operator" => "or"
        ),
        "bonus" => 2
      ),
      8 => array(
        "condition" => array(
          "status" => array("NT", "VU"),
          "operator" => "or"
        ),
        "bonus" => 2
      ),
      9 => array(
        "condition" => array(
          "continent" => array("AS"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      10 => array(
        "condition" => array(
          "continent" => array("AS"),
          "operator" => "single"
        ),
        "bonus" => 2
      ),
      11 => array(
        "condition" => array(
          "class" => array("reptile"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      12 => array(
        "condition" => array(
          "habitat" => array("TEM"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      13 => array(
        "condition" => array(
          "habitat" => array("TEM"),
          "operator" => "single"
        ),
        "bonus" => 3
      ),
      14 => array(
        "condition" => array(
          "habitat" => array("AQU", "MTN"),
          "operator" => "or"
        ),
        "bonus" => 5
      ),
    )
  ),
);

$this->keepers_info = array(
  6 => array(
    "name" => "Abdul",
    "level" => 3,
    "class" => "mammal",
    "operator" => "single"
  ),
  1 => array(
    "name" => "Adaeze",
    "level" => 2,
    "continent" => array("AS"),
    "operator" => "single"
  ),
  2 => array(
    "name" => "Afonso",
    "level" => 5,
    "class" => "mammal",
    "diet" => "herbivore",
    "operator" => "and"
  ),
  13 => array(
    "name" => "Ana",
    "level" => 3,
    "habitat" => array("PRA"),
    "operator" => "single"
  ),
  3 => array(
    "name" => "Ayaan",
    "level" => 3,
    "continent" => array(
      "EU",
      "OC"
    ),
    "operator" => "or"
  ),
  20 => array(
    "name" => "Bjorn",
    "level" => 4,
    "class" => "reptile",
    "operator" => "single"
  ),
  8 => array(
    "name" => "Dimitri",
    "level" => 3,
    "continent" => array("AM"),
    "operator" => "single"
  ),
  12 => array(
    "name" => "Gabriela",
    "level" => 4,
    "habitat" => array("TEM"),
    "operator" => "single"
  ),
  5 => array(
    "name" => "Heidi",
    "level" => 4,
    "diet" => "carnivore",
    "continent" => "AS",
    "operator" => "and"
  ),
  23 => array(
    "name" => "Helen",
    "level" => 5,
    "diet" => "herbivore",
    "continent" => "AF",
    "operator" => "and"
  ),
  9 => array(
    "name" => "Isabela",
    "level" => 4,
    "habitat" => "TRO",
    "continent" => "AS",
    "operator" => "and"
  ),
  22 => array(
    "name" => "Jen",
    "level" => 5,
    "status" => "EN",
    "operator" => "single"
  ),
  10 => array(
    "name" => "Jhon",
    "level" => 5,
    "diet" => "omnivore",
    "operator" => "single"
  ),
  24 => array(
    "name" => "Jung-woo",
    "level" => 2,
    "habitat" => array("TRO"),
    "operator" => "single"
  ),
  4 => array(
    "name" => "Kareena",
    "level" => 5,
    "habitat" => array(
      "AQU",
      "DES"
    ),
    "operator" => "or"
  ),
  11 => array(
    "name" => "Kulap",
    "level" => 2,
    "continent" => array("AF"),
    "operator" => "single"
  ),
  15 => array(
    "name" => "Paco",
    "level" => 2,
    "diet" => "herbivore",
    "operator" => "single"
  ),
  25 => array(
    "name" => "Rui",
    "level" => 4,
    "habitat" => array("SAV"),
    "operator" => "single"
  ),
  26 => array(
    "name" => "Yu Yan",
    "level" => 2,
    "diet" => "carnivore",
    "operator" => "single"
  ),
  16 => array(
    "name" => "Zala",
    "level" => 3,
    "class" => "bird",
    "operator" => "single"
  ),
  17 => array(
    "name" => "Maria",
    "level" => 1,
    "operator" => "any"
  ),
  18 => array(
    "name" => "Mario",
    "level" => 1,
    "operator" => "any"
  ),
  14 => array(
    "name" => "Penélope",
    "level" => 1,
    "operator" => "any"
  ),
  19 => array(
    "name" => "Paul",
    "level" => 1,
    "operator" => "any"
  ),
  27 => array(
    "name" => "Aiko",
    "level" => 1,
    "operator" => "any"
  ),
);

$this->species_info = array(
  12 => array(
    "name" => clienttranslate("snowy owl"),
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
    "name" => clienttranslate("mccord's snake-necked turtle"),
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
    "name" => clienttranslate("egyptian tortoise"),
    "scientific_name" => "Testudo kleinmanni",
    "class" => "reptile",
    "diet" => "herbivore",
    "status" => "CR",
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
    "name" => clienttranslate("spiny hill turtle"),
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
    "name" => clienttranslate("komodo dragon"),
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
    "name" => clienttranslate("burmese python"),
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
    "name" => clienttranslate("american alligator"),
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
    "name" => clienttranslate("madagascar tree boa"),
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
    "name" => clienttranslate("spur-thighed tortoise"),
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
    "name" => clienttranslate("rhinoceros iguana"),
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
    "name" => clienttranslate("gila monster"),
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
    "name" => clienttranslate("jamaican boa"),
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
    "name" => clienttranslate("nile crocodile"),
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
    "name" => clienttranslate("african penguin"),
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
    "name" => clienttranslate("great white pelican"),
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
    "name" => clienttranslate("nicobar pigeon"),
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
    "name" => clienttranslate("bali myna"),
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
    "name" => clienttranslate("lesser sulphur-crested cockatoo"),
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
    "name" => clienttranslate("southern cassowary"),
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
    "name" => clienttranslate("steppe eagle"),
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
    "name" => clienttranslate("griffon vulture"),
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
    "name" => clienttranslate("common barn owl"),
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
    "name" => clienttranslate("laughing kookaburra"),
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
    "name" => clienttranslate("red-tailed amazon"),
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
    "name" => clienttranslate("waldrapp ibis"),
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
    "name" => clienttranslate("long-billed corella"),
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
    "name" => clienttranslate("fischer's lovebird"),
    "scientific_name" => "Agapornis fischeri",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("SAV"),
    "continent" => array(
      "EU",
      "AF",
      "AS"
    ),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  5 => array(
    "name" => clienttranslate("golden-capped conure"),
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
    "name" => clienttranslate("blue-streaked lory"),
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
    "name" => clienttranslate("great argus"),
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
    "name" => clienttranslate("european rabbit"),
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
    "name" => clienttranslate("african elephant"),
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
    "name" => clienttranslate("queensland koala"),
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
    "name" => clienttranslate("chimpanzee"),
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
    "name" => clienttranslate("lar gibbon"),
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
    "name" => clienttranslate("western lowland gorilla"),
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
    "name" => clienttranslate("ring-tailed lemur"),
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
    "name" => clienttranslate("black-headed spider monkey"),
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
    "name" => clienttranslate("lion-tailed macaque"),
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
    "name" => clienttranslate("golden lion tamarin"),
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
    "name" => clienttranslate("sumatran orangutan"),
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
    "name" => clienttranslate("jaguar"),
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
    "name" => clienttranslate("african lion"),
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
    "name" => clienttranslate("persian leopard"),
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
    "name" => clienttranslate("iberian lynx"),
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
    "name" => clienttranslate("iberian wolf"),
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
    "name" => clienttranslate("red panda"),
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
    "name" => clienttranslate("clouded leopard"),
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
    "name" => clienttranslate("sumatran tiger"),
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
    "name" => clienttranslate("brown bear"),
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
    "name" => clienttranslate("white rhinoceros"),
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
    "name" => clienttranslate("grevy's zebra"),
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
    "name" => clienttranslate("addax"),
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
    "name" => clienttranslate("american bison"),
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
    "name" => clienttranslate("eastern bongo"),
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
    "name" => clienttranslate("bactrian camel"),
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
    "name" => clienttranslate("angolan giraffe"),
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
    "name" => clienttranslate("hippopotamus"),
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
    "name" => clienttranslate("black-faced impala"),
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
    "name" => clienttranslate("cheetah"),
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
    "name" => clienttranslate("american flamingo"),
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
    "name" => clienttranslate("one-horned rhinoceros"),
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
    "name" => clienttranslate("manchurian crane"),
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
    "name" => clienttranslate("green aracari"),
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
    "name" => clienttranslate("black-and-white ruffed lemur"),
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
    "name" => clienttranslate("hyacinth macaw"),
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
    "name" => clienttranslate("slender-tailed meerkat"),
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
    "name" => clienttranslate("common green iguana"),
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
    "name" => clienttranslate("common bottlenose dolphin"),
    "scientific_name" => "Tursiops truncatus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("AQU"),
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
    "name" => clienttranslate("frilled lizard"),
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
