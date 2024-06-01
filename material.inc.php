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
    "label" => clienttranslate("plant(s)"),
    "total" => 20,
    "per_player" => 4
  ),
  "meat" => array(
    "label" => clienttranslate("meat/fish"),
    "total" => 20,
    "per_player" => 4,
  ),
  "kit" => array(
    "label" => clienttranslate("medical kit(s)"),
    "total" => 15,
    "per_player" => 3,
  )
);

$this->quarantines_info = array(
  1 => "ALL", 2 => "TEM", 3 => "SAV", 4 => "PRA", 5 => "DES", 6 => "AQU", 7 => "TRO"
);

$this->FM_quarantines_info = array(
  1 => "ALL", 3 => "SAV", 4 => "PRA", 7 => "TRO"
);

$this->status = array(
  10000 => "CR", 1000 => "EN", 100 => "VU", 10 => "NT", 1 => "LC"
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
  1 => array(
    "keeper_name" => "Abdul",
    "level" => 3,
    "class" => array("mammal"),
    "operator" => "single"
  ),
  2 => array(
    "keeper_name" => "Adaeze",
    "level" => 2,
    "continent" => array("AS"),
    "operator" => "single"
  ),
  3 => array(
    "keeper_name" => "Afonso",
    "level" => 5,
    "class" => array("mammal"),
    "diet" => array("herbivore"),
    "operator" => "and"
  ),
  4 => array(
    "keeper_name" => "Ben",
    "level" => 1,
    "operator" => "any"
  ),
  5 => array(
    "keeper_name" => "Dimitri",
    "level" => 3,
    "continent" => array("AM"),
    "operator" => "single"
  ),
  6 => array(
    "keeper_name" => "John",
    "level" => 5,
    "diet" => array("omnivore"),
    "operator" => "single"
  ),
  7 => array(
    "keeper_name" => "Aiko",
    "level" => 1,
    "operator" => "any"
  ),
  8 => array(
    "keeper_name" => "Ana",
    "level" => 3,
    "habitat" => array("PRA"),
    "operator" => "single"
  ),
  9 => array(
    "keeper_name" => "Ayaan",
    "level" => 3,
    "continent" => array(
      "EU",
      "OC"
    ),
    "operator" => "or"
  ),
  10 => array(
    "keeper_name" => "Bjorn",
    "level" => 4,
    "class" => array("reptile"),
    "operator" => "single"
  ),
  11 => array(
    "keeper_name" => "Gabriela",
    "level" => 4,
    "habitat" => array("TEM"),
    "operator" => "single"
  ),
  12 => array(
    "keeper_name" => "Kulap",
    "level" => 2,
    "continent" => array("AF"),
    "operator" => "single"
  ),
  13 => array(
    "keeper_name" => "Heidi",
    "level" => 4,
    "diet" => array("carnivore"),
    "continent" => array("AS"),
    "operator" => "and"
  ),
  14 => array(
    "keeper_name" => "Helen",
    "level" => 5,
    "diet" => array("herbivore"),
    "continent" => array("AF"),
    "operator" => "and"
  ),
  15 => array(
    "keeper_name" => "Isabela",
    "level" => 4,
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "operator" => "and"
  ),
  16 => array(
    "keeper_name" => "Jen",
    "level" => 5,
    "status" => array("EN"),
    "operator" => "single"
  ),
  17 => array(
    "keeper_name" => "Jung-woo",
    "level" => 2,
    "habitat" => array("TRO"),
    "operator" => "single"
  ),
  18 => array(
    "keeper_name" => "Penélope",
    "level" => 1,
    "operator" => "any"
  ),
  19 => array(
    "keeper_name" => "Kareena",
    "level" => 5,
    "habitat" => array(
      "AQU",
      "DES"
    ),
    "operator" => "or"
  ),
  20 => array(
    "keeper_name" => "Rui",
    "level" => 4,
    "habitat" => array("SAV"),
    "operator" => "single"
  ),
  21 => array(
    "keeper_name" => "Mario",
    "level" => 1,
    "operator" => "any"
  ),
  22 => array(
    "keeper_name" => "Paco",
    "level" => 2,
    "diet" => array("herbivore"),
    "operator" => "single"
  ),
  23 => array(
    "keeper_name" => "Yu Yan",
    "level" => 2,
    "diet" => array("carnivore"),
    "operator" => "single"
  ),
  24 => array(
    "keeper_name" => "Zala",
    "level" => 3,
    "class" => array("bird"),
    "operator" => "single"
  )
);

$this->FM_keepers_info = array(
  1 => array(
    "keeper_name" => "Abdul",
    "level" => 3,
    "class" => array("mammal"),
    "operator" => "single"
  ),
  2 => array(
    "keeper_name" => "Adaeze",
    "level" => 2,
    "continent" => array("AS"),
    "operator" => "single"
  ),
  // 3 => array(
  //   "keeper_name" => "Afonso",
  //   "level" => 5,
  //   "class" => array("mammal"),
  //   "diet" => array("herbivore"),
  //   "operator" => "and"
  // ),
  4 => array(
    "keeper_name" => "Ben",
    "level" => 1,
    "operator" => "any"
  ),
  5 => array(
    "keeper_name" => "Dimitri",
    "level" => 3,
    "continent" => array("AM"),
    "operator" => "single"
  ),
  // 6 => array(
  //   "keeper_name" => "John",
  //   "level" => 5,
  //   "diet" => array("omnivore"),
  //   "operator" => "single"
  // ),
  7 => array(
    "keeper_name" => "Aiko",
    "level" => 1,
    "operator" => "any"
  ),
  8 => array(
    "keeper_name" => "Ana",
    "level" => 3,
    "habitat" => array("PRA"),
    "operator" => "single"
  ),
  9 => array(
    "keeper_name" => "Ayaan",
    "level" => 3,
    "continent" => array(
      "EU",
      "OC"
    ),
    "operator" => "or"
  ),
  // 10 => array(
  //   "keeper_name" => "Bjorn",
  //   "level" => 4,
  //   "class" => array("reptile"),
  //   "operator" => "single"
  // ),
  // 11 => array(
  //   "keeper_name" => "Gabriela",
  //   "level" => 4,
  //   "habitat" => array("TEM"),
  //   "operator" => "single"
  // ),
  12 => array(
    "keeper_name" => "Kulap",
    "level" => 2,
    "continent" => array("AF"),
    "operator" => "single"
  ),
  // 13 => array(
  //   "keeper_name" => "Heidi",
  //   "level" => 4,
  //   "diet" => array("carnivore"),
  //   "continent" => array("AS"),
  //   "operator" => "and"
  // ),
  // 14 => array(
  //   "keeper_name" => "Helen",
  //   "level" => 5,
  //   "diet" => array("herbivore"),
  //   "continent" => array("AF"),
  //   "operator" => "and"
  // ),
  // 15 => array(
  //   "keeper_name" => "Isabela",
  //   "level" => 4,
  //   "habitat" => array("TRO"),
  //   "continent" => array("AS"),
  //   "operator" => "and"
  // ),
  // 16 => array(
  //   "keeper_name" => "Jen",
  //   "level" => 5,
  //   "status" => array("EN"),
  //   "operator" => "single"
  // ),
  17 => array(
    "keeper_name" => "Jung-woo",
    "level" => 2,
    "habitat" => array("TRO"),
    "operator" => "single"
  ),
  18 => array(
    "keeper_name" => "Penélope",
    "level" => 1,
    "operator" => "any"
  ),
  // 19 => array(
  //   "keeper_name" => "Kareena",
  //   "level" => 5,
  //   "habitat" => array(
  //     "AQU",
  //     "DES"
  //   ),
  //   "operator" => "or"
  // ),
  // 20 => array(
  //   "keeper_name" => "Rui",
  //   "level" => 4,
  //   "habitat" => array("SAV"),
  //   "operator" => "single"
  // ),
  21 => array(
    "keeper_name" => "Mario",
    "level" => 1,
    "operator" => "any"
  ),
  22 => array(
    "keeper_name" => "Paco",
    "level" => 2,
    "diet" => array("herbivore"),
    "operator" => "single"
  ),
  23 => array(
    "keeper_name" => "Yu Yan",
    "level" => 2,
    "diet" => array("carnivore"),
    "operator" => "single"
  ),
  24 => array(
    "keeper_name" => "Zala",
    "level" => 3,
    "class" => array("bird"),
    "operator" => "single"
  )
);

$this->species_info = array(
  1 => array(
    "name" => clienttranslate("Black-Faced Impala"),
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
  2 => array(
    "name" => clienttranslate("African Lion"),
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
  3 => array(
    "name" => clienttranslate("Rhinoceros Iguana"),
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
  4 => array(
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
  5 => array(
    "name" => clienttranslate("Snowy Owl"),
    "scientific_name" => "Bubo scandiaca",
    "class" => "bird",
    "diet" => "carnivore",
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
  6 => array(
    "name" => clienttranslate("Golden-Capped Conure"),
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
  7 => array(
    "name" => clienttranslate("Laughing Kookaburra"),
    "scientific_name" => "Dacelo novaeguineae",
    "class" => "bird",
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
  9 => array(
    "name" => clienttranslate("Eastern Bongo"),
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
  10  => array(
    "name" => clienttranslate("Nile Crocodile"),
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
  11 => array(
    "name" => clienttranslate("Golden Lion Tamarin"),
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
  12 => array(
    "name" => clienttranslate("Jamaican Boa"),
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
  13 => array(
    "name" => clienttranslate("Red Panda"),
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
  14 => array(
    "name" => clienttranslate("European Rabbit"),
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
  15 => array(
    "name" => clienttranslate("Red-Tailed Amazon"),
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
  16 => array(
    "name" => clienttranslate("Gila Monster"),
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
  17 => array(
    "name" => clienttranslate("Long-Billed Corella"),
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
  18 => array(
    "name" => clienttranslate("Black-Headed Spider Monkey"),
    "scientific_name" => "Ateles fusciceps",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AM"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 3
    ),
    "points" => 6,
  ),
  19 => array(
    "name" => clienttranslate("Bactrian Camel"),
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
    "name" => clienttranslate("Griffon Vulture"),
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
  21 => array(
    "name" => clienttranslate("Southern Cassowary"),
    "scientific_name" => "Casuarius casuarius",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "LC",
    "habitat" => array(
      "TRO",
      "SAV"
    ),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 2,
      "meat" => 2,
      "kit" => 0
    ),
    "points" => 1,
  ),
  22 => array(
    "name" => clienttranslate("Grevy's Zebra"),
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
  23 => array(
    "name" => clienttranslate("Common Barn Owl"),
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
  24 => array(
    "name" => clienttranslate("Lar Gibbon"),
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
  26 => array(
    "name" => clienttranslate("Iberian Lynx"),
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

  27 => array(
    "name" => clienttranslate("American Alligator"),
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
  28 => array(
    "name" => clienttranslate("Lesser Sulphur-Crested Cockatoo"),
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
  29 => array(
    "name" => clienttranslate("Sumatran Tiger"),
    "scientific_name" => "Panthera tigris sumatrae",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "CR",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 3
    ),
    "points" => 6,
  ),
  30 => array(
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
  31 => array(
    "name" => clienttranslate("Lion-Tailed Macaque"),
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
  32 => array(
    "name" => clienttranslate("Great White Pelican"),
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
  33 => array(
    "name" => clienttranslate("Spiny Hill Turtle"),
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
  34 => array(
    "name" => clienttranslate("Burmese Python"),
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
  35 => array(
    "name" => clienttranslate("Great Argus"),
    "scientific_name" => "Argusianus argus argus",
    "class" => "bird",
    "diet" => "omnivore",
    "status" => "NT",
    "habitat" => array("TRO"),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 1,
      "meat" => 1,
      "kit" => 0
    ),
    "points" => 1,
  ),
  36 => array(
    "name" => clienttranslate("Komodo Dragon"),
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
  37 => array(
    "name" => clienttranslate("Iberian Wolf"),
    "scientific_name" => "Canis lupus signatus",
    "class" => "mammal",
    "diet" => "carnivore",
    "status" => "LC",
    "habitat" => array("TEM"),
    "continent" => array("EU"),
    "cost" => array(
      "plant" => 0,
      "meat" => 4,
      "kit" => 0
    ),
    "points" => 1,
  ),
  38 => array(
    "name" => clienttranslate("Egyptian Tortoise"),
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
  39 => array(
    "name" => clienttranslate("Waldrapp Ibis"),
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

  40 => array(
    "name" => clienttranslate("Angolan Giraffe"),
    "scientific_name" => "Giraffa camelopardalis angolensis",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "LC",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),
  41 => array(
    "name" => clienttranslate("Madagascar Tree Boa"),
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
  42 => array(
    "name" => clienttranslate("American Bison"),
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
  43 => array(
    "name" => clienttranslate("Clouded Leopard"),
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
  44 => array(
    "name" => clienttranslate("White Rhinoceros"),
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




  45 => array(
    "name" => clienttranslate("Common Bottlenose Dolphin"),
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
  46 => array(
    "name" => clienttranslate("Green Aracari"),
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
  47 => array(
    "name" => clienttranslate("Hyacinth Macaw"),
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
  49 => array(
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


  50 => array(
    "name" => clienttranslate("Queensland Koala"),
    "scientific_name" => "Phascolarctos cinereus adustus",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array("TEM"),
    "continent" => array("OC"),
    "cost" => array(
      "plant" => 3,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  51 => array(
    "name" => clienttranslate("African Elephant"),
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
  52 => array(
    "name" => clienttranslate("Blue-Streaked Lory"),
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
  53 => array(
    "name" => clienttranslate("Brown Bear"),
    "scientific_name" => "Ursus arctos",
    "class" => "mammal",
    "diet" => "omnivore",
    "status" => "LC",
    "habitat" => array("TEM"),
    "continent" => array("AM", "AS", "EU"),
    "cost" => array(
      "plant" => 3,
      "meat" => 3,
      "kit" => 0
    ),
    "points" => 1,
  ),
  54 => array(
    "name" => clienttranslate("Fischer's Lovebird"),
    "scientific_name" => "Agapornis fischeri",
    "class" => "bird",
    "diet" => "herbivore",
    "status" => "NT",
    "habitat" => array("SAV"),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 1,
      "meat" => 0,
      "kit" => 0
    ),
    "points" => 1,
  ),

  55 => array(
    "name" => clienttranslate("Ring-Tailed Lemur"),
    "scientific_name" => "Lemur catta",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "EN",
    "habitat" => array(
      "TEM",
      "SAV"
    ),
    "continent" => array("AF"),
    "cost" => array(
      "plant" => 2,
      "meat" => 0,
      "kit" => 2
    ),
    "points" => 4,
  ),

  56 => array(
    "name" => clienttranslate("Steppe Eagle"),
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
  57 => array(
    "name" => clienttranslate("African Penguin"),
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

  58 => array(
    "name" => clienttranslate("Spur-Thighed Tortoise"),
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
  59 => array(
    "name" => clienttranslate("Nicobar Pigeon"),
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

  60 => array(
    "name" => clienttranslate("Persian Leopard"),
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
  62 => array(
    "name" => clienttranslate("Slender-Tailed Meerkat"),
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
  63 => array(
    "name" => clienttranslate("One-Horned Rhinoceros"),
    "scientific_name" => "Rhinoceros unicornis",
    "class" => "mammal",
    "diet" => "herbivore",
    "status" => "VU",
    "habitat" => array(
      "TRO",
      "PRA"
    ),
    "continent" => array("AS"),
    "cost" => array(
      "plant" => 6,
      "meat" => 0,
      "kit" => 1
    ),
    "points" => 3,
  ),
  64 => array(
    "name" => clienttranslate("Manchurian Crane"),
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
  65 => array(
    "name" => clienttranslate("American Flamingo"),
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
  66 => array(
    "name" => clienttranslate("Bali Myna"),
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
  67 => array(
    "name" => clienttranslate("Mccord's Snake-Necked Turtle"),
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
  68 => array(
    "name" => clienttranslate("Sumatran Orangutan"),
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
  69 => array(
    "name" => clienttranslate("Western Lowland Gorilla"),
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
  70 => array(
    "name" => clienttranslate("Black-And-White Ruffed Lemur"),
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
);
