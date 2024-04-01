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
 * zookeepers.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

class Zookeepers extends Table
{
    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels(array(
            "mainAction" => 10,
            "totalToReturn" => 11,
            "previouslyReturned" => 12,
            "freeAction" => 13,
            "selectedPosition" => 14,
            "selectedSpecies" => 15,
            "selectedBackup" => 16,
            "highestSaved" => 17,
            "secondStep" => 18,
            "lastTurn" => 19,

            "scoreTracking" => 100
        ));

        $this->resources = self::getNew("module.common.deck");
        $this->resources->init("resource");

        $this->keepers = self::getNew("module.common.deck");
        $this->keepers->init("keeper");

        $this->species = self::getNew("module.common.deck");
        $this->species->init("species");
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "zookeepers";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = array())
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode(',', $values);
        self::DbQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        self::reloadPlayersBasicInfos();

        $players = self::loadPlayersBasicInfos();

        $resources_deck = array();
        foreach ($this->resource_types as $type => $resource) {
            $resources_deck[] = array("type" => $resource["label"], "type_arg" => $type, "nbr" => ($resource["total"] - $resource["per_player"] * count($players)));
        }

        $resources_to_players = array();
        foreach ($this->resource_types as $type_arg => $resource) {
            $resources_to_players[] = array("type" => $resource["label"], "type_arg" => $type_arg, "nbr" => $resource["per_player"]);
        }

        foreach ($players as $player_id => $player) {
            $this->resources->createCards($resources_to_players, "hand", $player_id);
        }

        $this->resources->createCards($resources_deck, "deck");
        $this->resources->shuffle("deck");

        $keepers_info = $this->keepers_info;
        ksort($keepers_info);

        // temporary data, tests only
        $starting_keepers = array();

        foreach ($this->filterByLevel($keepers_info, 1) as $keeper_id => $keeper) {
            $starting_keepers[] = array("type" => $keeper["name"], "type_arg" => $keeper_id, "nbr" => 1);
        }
        $this->keepers->createCards($starting_keepers, "deck");
        $this->keepers->shuffle("deck");
        foreach ($players as $player_id => $player) {
            self::DbQuery("UPDATE keeper SET pile=0 WHERE card_location='deck'");
            $this->keepers->pickCardForLocation("deck", "board:1", $player_id);
        }

        $this->keepers->moveAllCardsInLocation("deck", "box");

        $other_keepers = array();
        foreach ($this->filterByLevel($keepers_info, 1, true) as $keeper_id => $keeper) {
            $other_keepers[] = array("type" => $keeper["name"], "type_arg" => $keeper_id, "nbr" => 1);
        }
        $this->keepers->createCards($other_keepers, "deck");
        $this->keepers->shuffle("deck");
        for ($pile = 1; $pile <= 4; $pile++) {
            $location = "deck:" . $pile;
            $this->keepers->pickCardsForLocation(5, "deck", $location);
            self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_location='$location'");
            $this->keepers->shuffle($location);
        }

        $species_deck = array();
        $species_info = $this->species_info;

        ksort($species_info, 1);

        foreach ($species_info as $species_id => $species) {
            $species_deck[] = array(
                "type" => $species["name"],
                "type_arg" => $species_id,
                "nbr" => 1,
            );
        }

        $this->species->createCards($species_deck, "deck");
        $this->species->shuffle("deck");

        for ($i = 1; $i <= 4; $i++) {
            $this->species->pickCardsForLocation(2, "deck", "shop_backup", $i);
            $this->species->pickCardForLocation("deck", "shop_visible", $i);
        }

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue("mainAction", 0);
        self::setGameStateInitialValue("freeAction", 0);
        self::setGameStateInitialValue("totalToReturn", 0);
        self::setGameStateInitialValue("previouslyReturned", 0);
        self::setGameStateInitialValue("selectedPosition", 0);
        self::setGameStateInitialValue("selectedSpecies", 0);
        self::setGameStateInitialValue("selectedBackup", 0);
        self::setGameStateInitialValue("secondStep", 0);
        self::setGameStateInitialValue("lastTurn", 0);

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $species_info = $this->species_info;
        $keepers_info = $this->keepers_info;

        $result["isRealTimeScoreTracking"] = $this->isRealTimeScoreTracking();
        $result["players"] = self::getCollectionFromDb($sql);
        $result["resourceCounters"] = $this->getResourceCounters();
        $result["bagCounters"] = $this->getBagCounters();
        $result["isBagEmpty"] = $this->isBagEmpty();
        $result["allKeepers"] = $keepers_info;
        $result["pileCounters"] = $this->getPileCounters();
        $result["pilesTops"] = $this->getPilesTops();
        $result["keepersOnBoards"] = $this->getKeepersOnBoards();
        $result["allSpecies"] = $species_info;
        $result["backupSpecies"] = $this->getBackupSpecies();
        $result["visibleSpecies"] = $this->getVisibleSpecies();
        $result["savableSpecies"] = $this->getSavableSpecies();
        $result["savableWithFund"] = $this->getSavableWithFund();
        $result["savableQuarantined"] = $this->getSavableQuarantined();
        $result["savableQuarantinedWithFund"] = $this->getSavableQuarantinedWithFund();
        $result["allQuarantines"] = $this->quarantines;
        $result["quarantinableSpecies"] = $this->getQuarantinableSpecies();
        $result["savedSpecies"] = $this->getSavedSpecies();
        $result["quarantinedSpecies"] = $this->getQuarantinedSpecies();
        $result["completedKeepers"] = $this->getCompletedKeepers();
        $result["speciesCounters"] = $this->getSpeciesCounters();
        $result["emptyColumnNbr"] = $this->getEmptyColumnNbr();

        $players = self::loadPlayersBasicInfos();

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }


    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    function isRealTimeScoreTracking()
    {
        return self::getGameStateValue("scoreTracking") == 2;
    }

    function filterByResourceType($resources, $type_arg)
    {
        $filtered_resources = array_filter($resources, function ($resource) use ($type_arg) {
            return $resource["type_arg"] == $type_arg;
        });

        return $filtered_resources;
    }

    function filterByLevel($items, $level, $exclusive = false)
    {
        $filtered_items = array_filter($items, function ($item) use ($level, $exclusive) {
            if ($exclusive) {
                return $item["level"] !== $level;
            }

            return $item["level"] == $level;
        });

        return $filtered_items;
    }

    function findCardByTypeArg($cards, $arg)
    {
        $found_card = null;
        foreach ($cards as $card) {
            if ($card["type_arg"] == $arg) {
                $found_card = $card;
                break;
            }
        }
        return $found_card;
    }

    function getPilesTops()
    {
        $tops = array();

        for ($pile = 1; $pile <= 4; $pile++) {
            $topCard = $this->keepers->getCardOnTop("deck:" . $pile);

            if ($topCard) {
                $keeper_info = $this->keepers_info[$topCard["type_arg"]];
                $tops[$pile] = $keeper_info["level"];
            } else {
                $top[$pile] = null;
            }
        }

        return $tops;
    }

    function getPileCounters()
    {
        $counters = array();

        for ($pile = 1; $pile <= 4; $pile++) {
            $counters[$pile] = $this->keepers->countCardsInLocation("deck:" . $pile);
        }

        return $counters;
    }

    function getResourceCounters()
    {
        $players = self::loadPlayersBasicInfos();

        $counters = array();

        foreach ($players as $player_id => $player) {
            $plants = $this->resources->getCardsOfTypeInLocation("plant", 1, "hand", $player_id);
            $plants_nbr = count($plants);

            $meat = $this->resources->getCardsOfTypeInLocation("meat", 2, "hand", $player_id);
            $meat_nbr = count($meat);

            $kits = $this->resources->getCardsOfTypeInLocation("kit", 3, "hand", $player_id);
            $kits_nbr = count($kits);

            $counters[$player_id] = array("plant" => $plants_nbr, "meat" => $meat_nbr, "kit" => $kits_nbr);
        }

        return $counters;
    }

    function getBagCounters()
    {
        $plants = $this->resources->getCardsOfTypeInLocation("plant", 1, "deck");
        $plants_nbr = count($plants);

        $meat = $this->resources->getCardsOfTypeInLocation("meat", 2, "deck");
        $meat_nbr = count($meat);

        $kits = $this->resources->getCardsOfTypeInLocation("kit", 3, "deck");
        $kits_nbr = count($kits);

        $counters = array("plant" => $plants_nbr, "meat" => $meat_nbr, "kit" => $kits_nbr);

        return $counters;
    }

    function isBagEmpty()
    {
        return $this->resources->countCardInLocation("deck") == 0;
    }

    function getKeepersOnBoards()
    {
        $players = self::loadPlayersBasicInfos();

        $keepers = array();
        foreach ($players as $player_id => $player) {
            for ($i = 1; $i <= 4; $i++) {
                $location = "board:" . $i;
                $sql = "SELECT pile, card_id, card_location, card_location_arg, card_type, card_type_arg FROM keeper WHERE card_location_arg='$player_id' AND card_location='$location'";
                $keepers[$player_id][$i] = self::getCollectionFromDb($sql);
            }
        }

        return $keepers;
    }

    function getVisibleSpecies()
    {
        $visible_species = array();
        for ($position = 1; $position <= 4; $position++) {
            $species = $this->species->getCardsInLocation("shop_visible", $position);
            $visible_species[$position] = array_shift($species);
        }

        return $visible_species;
    }

    function getSavableSpecies()
    {
        $savable_species = array();

        foreach ($this->species->getCardsInLocation("shop_visible") as $species_card) {
            $species_id = $species_card["type_arg"];

            $can_assign = count($this->getAssignableKeepers($species_id)) > 0;
            $can_pay_cost = $this->canPayCost($species_id);
            $can_pay_with_fund = $this->canPayWithFund($species_id);

            if ($can_assign && ($can_pay_cost || $can_pay_with_fund)) {
                $savable_species[$species_id] = $species_card;
            }
        }

        return $savable_species;
    }

    function getSavableWithFund()
    {
        $savable_with_fund = array();

        foreach ($this->species->getCardsInLocation("shop_visible") as $species_card) {
            $species_id = $species_card["type_arg"];

            $can_assign = count($this->getAssignableKeepers($species_id)) > 0;
            $can_pay_cost = $this->canPayCost($species_id);
            $can_pay_with_fund = $this->canPayWithFund($species_id);

            if ($can_assign && !$can_pay_cost && $can_pay_with_fund) {
                $savable_with_fund[$species_id] = $species_card;
            }
        }

        return $savable_with_fund;
    }

    function getSavableQuarantined()
    {
        $savable_quarantined = array();
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $savable_quarantined[$player_id] = array();
            foreach ($this->quarantines as $quarantine) {
                $cards_in_location = $this->species->getCardsInLocation("quarantine:" . $quarantine, $player_id);

                $species_card = array_shift($cards_in_location);

                if ($species_card === null) {
                    continue;
                }

                $species_id = $species_card["type_arg"];

                $can_assign = count($this->getAssignableKeepers($species_id)) > 0;
                $can_pay_cost = $this->canPayCost($species_id);
                $can_pay_with_fund = $this->canPayWithFund($species_id);

                if ($can_assign && ($can_pay_cost || $can_pay_with_fund)) {
                    $savable_quarantined[$player_id][$species_id] = $species_card;
                }
            }
        }

        return $savable_quarantined;
    }

    function getSavableQuarantinedWithFund()
    {
        $savable_with_fund = array();
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $savable_with_fund[$player_id] = array();
            foreach ($this->quarantines as $quarantine) {
                $cards_in_location = $this->species->getCardsInLocation("quarantine:" . $quarantine, $player_id);

                $species_card = array_shift($cards_in_location);

                if ($species_card === null) {
                    continue;
                }

                $species_id = $species_card["type_arg"];

                $can_assign = count($this->getAssignableKeepers($species_id)) > 0;
                $can_pay_cost = $this->canPayCost($species_id);
                $can_pay_with_fund = $this->canPayWithFund($species_id);

                if ($can_assign && !$can_pay_cost && $can_pay_with_fund) {
                    $savable_with_fund[$player_id][$species_id] = $species_card;
                }
            }
        }

        return $savable_with_fund;
    }

    function canPayCost($species_id)
    {
        $player_id = self::getActivePlayerId();
        $resource_counters = $this->getResourceCounters()[$player_id];

        $species_info = $this->species_info[$species_id];
        $cost = $species_info["cost"];

        $can_pay_cost = true;

        foreach ($resource_counters as $type => $counter) {
            $type_cost = $cost[$type];
            if ($type_cost > 0 && $counter < $type_cost) {
                $can_pay_cost = false;
                break;
            };
        }

        return $can_pay_cost;
    }

    function canPayWithFund($species_id)
    {
        $player_id = self::getActivePlayerId();
        $resource_counters = $this->getResourceCounters()[$player_id];

        $species_info = $this->species_info[$species_id];
        $cost = $species_info["cost"];

        $kit_nbr = $resource_counters["kit"];
        $kit_cost = $cost["kit"];
        $available_kits_nbr = $kit_nbr - $kit_cost;

        if ($available_kits_nbr == 0) {
            return null;
        }

        $conservation_fund = array();

        foreach ($cost as $type => $type_cost) {
            if ($type === "kit") {
                $conservation_fund["kit"] = null;
                continue;
            }

            $type_counter = $resource_counters[$type];

            if ($type_counter >= $type_cost) {
                $conservation_fund[$type] = null;
                continue;
            }

            $needed_kit_nbr = ceil(($type_cost - $type_counter) / 2);

            if ($type_counter < $type_cost && $available_kits_nbr < $needed_kit_nbr) {
                return null;
            }

            if ($available_kits_nbr >= $needed_kit_nbr) {
                $conservation_fund[$type]  = $needed_kit_nbr;
                $available_kits_nbr = $available_kits_nbr - $needed_kit_nbr;
            }
        }

        return $conservation_fund;
    }

    function getAssignableKeepers($species_id)
    {

        $player_id = self::getActivePlayerId();
        $species_info = $this->species_info[$species_id];

        $keepers_in_play = array();

        for ($i = 1; $i <= 4; $i++) {
            $keepers_in_play += $this->getKeepersOnBoards()[$player_id][$i];
        }

        $species_keys = array_keys($species_info);

        $assignable_keepers = array();

        foreach ($keepers_in_play as $keeper_card) {
            $keeper_id = $keeper_card["card_type_arg"];
            $keeper_location = $keeper_card["card_location"];

            $keeper_info = $this->keepers_info[$keeper_id];
            $operator = $keeper_info["operator"];

            if ($this->species->countCardsInLocation($keeper_location, $player_id) >= 3) {
                continue;
            }

            if ($operator === "any") {
                $assignable_keepers[$keeper_id] = $keeper_card;
                continue;
            }

            if ($operator === "single" || $operator === "or") {
                foreach ($keeper_info as $key => $keeper_value) {
                    if (
                        in_array($key, $species_keys, true)
                    ) {
                        $species_value = $species_info[$key];

                        if (is_array($keeper_value)) {
                            if (is_array($species_value) && count(array_intersect($keeper_value, $species_value)) > 0) {
                                $assignable_keepers[$keeper_id] = $keeper_card;
                                break;
                            }

                            if (!is_array($species_value) && in_array($species_value, $keeper_value, true)) {
                                $assignable_keepers[$keeper_id] = $keeper_card;
                                break;
                            }
                        }

                        if (!is_array($keeper_value) && $species_value == $keeper_value) {
                            $assignable_keepers[$keeper_id] = $keeper_card;
                            break;
                        }
                    }
                }
            }

            if ($operator === "and") {
                $conditions_met = 0;
                foreach ($keeper_info as $key => $keeper_value) {
                    if (
                        in_array($key, $species_keys, true)
                    ) {
                        $species_value = $species_info[$key];
                        if (is_array($keeper_value)) {
                            if (is_array($species_value) && count(array_intersect($keeper_value, $species_value)) > 0) {
                                $assignable_keepers[$keeper_id] = $keeper_card;
                                continue;
                            }

                            if (!is_array($species_value) && in_array($species_value, $keeper_value, true)) {
                                $assignable_keepers[$keeper_id] = $keeper_card;
                                continue;
                            }
                        }

                        if (!is_array($keeper_value) && $species_value == $keeper_value) {
                            $assignable_keepers[$keeper_id] = $keeper_card;
                            continue;
                        }
                    }
                }

                if ($conditions_met >= 2) {
                    $assignable_keepers[$keeper_id] = $keeper_card;
                }
            }
        }

        return $assignable_keepers;
    }

    function getSavedSpecies()
    {
        $players = self::loadPlayersBasicInfos();
        $saved_species = array();

        foreach ($players as $player_id => $player) {
            for ($position = 1; $position <= 4; $position++) {
                $cards_in_location = $this->species->getCardsInLocation("board:" . $position, $player_id);

                if (count($cards_in_location) > 0) {
                    foreach ($cards_in_location as $card) {
                        $saved_species[$player_id][$position][$card["type_arg"]] = $card;
                    }
                } else {
                    $saved_species[$player_id][$position] = null;
                }
            }
        }

        return $saved_species;
    }

    function returnCost($species_id)
    {
        $player_id = self::getActivePlayerId();
        $cost = $this->species_info[$species_id]["cost"];

        $returned_cost = array();

        foreach ($cost as $type => $type_cost) {
            $fund_cost = null;

            if ($this->canPayWithFund($species_id)) {
                $fund_cost = $this->canPayWithFund($species_id)[$type];
            }

            $resources_returned = array();

            if ($fund_cost) {
                $kits_in_hand = array_keys($this->resources->getCardsOfTypeInLocation("kit", null, "hand", $player_id));
                $kits_returned = array_slice($kits_in_hand, 0, $fund_cost);

                if (in_array("kit", array_keys($returned_cost))) {
                    $returned_cost["kit"] += $fund_cost;
                } else {
                    $returned_cost["kit"] = $fund_cost;
                }

                $returned_cost[$type] = $this->getResourceCounters()[$player_id][$type];
                $resources_in_hand = array_keys($this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id));
                $resources_returned = array_merge($kits_returned, array_slice($resources_in_hand, 0, $returned_cost[$type]));
            } else {
                $resources_in_hand = array_keys($this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id));
                $resources_returned = array_slice($resources_in_hand, 0, $type_cost);

                if ($type === "kit" && in_array("kit", array_keys($returned_cost))) {
                    $returned_cost[$type] += $type_cost;
                } else {
                    $returned_cost[$type] = $type_cost;
                }
            }


            $this->resources->moveCards($resources_returned, "deck");
        }

        $this->resources->shuffle("deck");

        return $returned_cost;
    }

    function revealSpecies()
    {
        for ($position = 1; $position <= 4; $position++) {
            if ($this->species->countCardsInLocation("shop_visible", $position) == 0) {
                $species_in_location = $this->species->getCardsInLocation("shop_backup", $position);
                $species = array_shift($species_in_location);

                if ($species) {
                    $this->species->moveCard($species["id"], "shop_visible", $position);
                    $this->notifyAllPlayers(
                        "revealSpecies",
                        clienttranslate('The ${species_name} is flipped over and revealed to all players'),
                        array(
                            "i18n" => array("species_name"),
                            "shop_position" => $position,
                            "species_name" => $species["type"],
                            "revealed_id" => $species["type_arg"],
                            "backup_species" => $this->getBackupSpecies(),
                            "visible_species" => $this->getVisibleSpecies(),
                        )
                    );
                }
            }
        }
    }

    function getQuarantinedSpecies()
    {
        $players = self::loadPlayersBasicInfos();
        $quarantined_species = array();

        foreach ($players as $player_id => $player) {
            foreach ($this->quarantines as $quarantine) {
                $cards_in_location = $this->species->getCardsInLocation("quarantine:" . $quarantine, $player_id);

                if (count($cards_in_location) > 0) {
                    foreach ($cards_in_location as $card) {
                        $quarantined_species[$player_id][$quarantine][$card["type_arg"]] = $card;
                    }
                } else {
                    $quarantined_species[$player_id][$quarantine] = null;
                }
            }
        }

        return $quarantined_species;
    }

    function getCompletedKeepers()
    {
        $completed_keepers = array();

        foreach (self::loadPlayersBasicInfos() as $player_id => $player) {
            for ($position = 1; $position <= 4; $position++) {
                $saved_species_nbr = $this->species->countCardsInLocation("board:" . $position, $player_id);
                $keepers = ($this->keepers->getCardsInLocation("board:" . $position, $player_id));
                $keeper = array_shift($keepers);

                if ($saved_species_nbr >= 3) {
                    $completed_keepers[$player_id][$position] = $keeper;
                } else {
                    $completed_keepers[$player_id][$position] = null;
                }
            }
        }

        return $completed_keepers;
    }

    function discardAllKeptSpecies($board_position, $keeper_name)
    {
        $player_id = self::getActivePlayerId();
        if ($board_position) {
            $discarded_species = $this->species->getCardsInLocation("board:" . $board_position, $player_id);

            if (count($discarded_species) > 0) {
                $this->species->moveAllCardsInLocation("board:" . $board_position, "deck", $player_id);
                $this->notifyAllPlayers(
                    "discardAllKeptSpecies",
                    clienttranslate('All species kept by ${keeper_name} are moved to the bottom of the deck'),
                    array(
                        "player_id" => $player_id,
                        "board_position" => $board_position,
                        "keeper_name" => $keeper_name,
                        "discarded_species" => $discarded_species,
                        "saved_species" => $this->getSavedSpecies(),
                        "species_counters" => $this->getSpeciesCounters(),
                    )
                );
            }

            $score = 0;
            foreach ($discarded_species as $species) {
                $score -= $this->species_info[$species["type_arg"]]["points"];
            }
            $this->updateScore($player_id, $score);
        }
    }

    function getBackupSpecies()
    {
        $backup_species = array();

        for ($position = 1; $position <= 4; $position++) {
            $backup_species[$position] = $this->species->countCardsInLocation("shop_backup", $position);
        }

        return $backup_species;
    }

    function getLookedBackup()
    {
        $backup_id = self::getGameStateValue("selectedBackup");
        $species_id = self::getGameStateValue("selectedSpecies");

        $backup_species = $this->species->getCardsInLocation("shop_backup");

        $species = $this->findCardByTypeArg($backup_species, $species_id);

        if ($species === null) {
            return null;
        }

        $species["backup_id"] = $backup_id;
        return $species;
    }

    function getOpenQuarantines()
    {
        $players = self::loadPlayersBasicInfos();
        $open_quarantines = array();

        foreach ($players as $player_id => $player) {
            $open_quarantines[$player_id] = array();
            foreach ($this->quarantines as $quarantine_id => $quarantine) {
                if ($this->species->countCardsInLocation("quarantine:" . $quarantine, $player_id) == 0) {
                    $open_quarantines[$player_id][$quarantine] = $quarantine;
                }
            }
        }

        return $open_quarantines;
    }

    function getQuarantinableSpecies()
    {
        $players = self::loadPlayersBasicInfos();
        $quarantinable_species = array();

        foreach ($players as $player_id => $player) {
            $quarantinable_species[$player_id] = array();

            $species = null;
            $species_id = null;
            foreach ($this->species->getCardsInLocation("shop_visible") as $card_id => $card) {
                $species = $card;
                $species_id = $card["type_arg"];

                $species_habitats = $this->species_info[$species_id]["habitat"];

                $open_quarantines = $this->getOpenQuarantines()[$player_id];

                if (in_array("ALL", $open_quarantines) || count(array_intersect($species_habitats, $open_quarantines)) > 0) {
                    $quarantinable_species[$player_id][$species_id] = $species;
                }
            }
        }

        return $quarantinable_species;
    }

    function canLiveInQuarantine($species_id, $quarantine)
    {
        $player_id = self::getActivePlayerId();
        $canLiveInQuarantine = false;

        $species_habitats = $this->species_info[$species_id]["habitat"];
        $open_quarantines = $this->getOpenQuarantines()[$player_id];

        if (($quarantine === "ALL" || in_array($quarantine, $species_habitats)) && in_array($quarantine, $open_quarantines)) {
            $canLiveInQuarantine = true;
        }

        return $canLiveInQuarantine;
    }

    function getSpeciesCounters()
    {
        $counters = array();

        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $species_nbr = 0;
            for ($position = 1; $position <= 4; $position++) {
                $species_nbr += $this->species->countCardsInLocation("board:" . $position, $player_id);
            }

            $counters[$player_id] = $species_nbr;
        }

        return $counters;
    }

    function updateHighestSaved($player_id)
    {
        $previous_highest = self::getGameStateValue("highestSaved");
        $saved_nbr = 0;

        for ($position = 1; $position <= 4; $position++) {
            $saved_nbr += $this->species->countCardsInLocation("board:" . $position, $player_id);
        }

        if ($saved_nbr > $previous_highest) {
            self::setGameStateValue("highestSaved", $saved_nbr);
        }
    }

    function updateScore($player_id, $score)
    {
        self::DbQuery("UPDATE player SET player_score=player_score+$score WHERE player_id='$player_id'");
        $new_scores = 0;
        $collection = self::getCollectionFromDb("SELECT player_score FROM player WHERE player_id='$player_id'");
        foreach ($collection as $player) {
            $new_scores = $player['player_score'];
        }

        self::notifyAllPlayers("newScores", '', array('player_id' => $player_id, 'new_scores' => $new_scores));
    }

    function getEmptyColumnNbr()
    {
        $empty_column_nbr = 0;

        for ($position = 1; $position <= 4; $position++) {
            $backup_species_nbr = $this->species->countCardsInLocation("shop_backup", $position);
            $visible_species_nbr = $this->species->countCardsInLocation("shop_visible", $position);

            if ($backup_species_nbr + $visible_species_nbr == 0) {
                $empty_column_nbr++;
            }
        }

        return $empty_column_nbr;
    }

    function drawNewSpecies($auto = false)
    {

        $empty_column_nbr = $this->getEmptyColumnNbr();
        if ($empty_column_nbr < 2) {
            throw new BgaVisibleSystemException("You can't draw new species until there are 2 or more empty species columns");
        }

        $backup_species = $this->species->getCardsInLocation("shop_backup");
        $visible_species = $this->species->getCardsInLocation("shop_visible");

        foreach ($backup_species as $card_id => $species) {
            $this->species->insertCardOnExtremePosition($card_id, "deck", false);
        }

        foreach ($visible_species as $card_id => $species) {
            $this->species->insertCardOnExtremePosition($card_id, "deck", false);
        }

        for ($position = 1; $position <= 4; $position++) {
            $this->species->pickCardsForLocation(2, "deck", "shop_backup", $position);
            $this->species->pickCardForLocation("deck", "shop_visible", $position);
        }

        $message = $auto ? clienttranslate('The grid is refilled with 12 new species') :
            clienttranslate('${player_name} moves all species from the grid to the bottom of the deck and draws 12 new species');

        $this->notifyAllPlayers(
            "newSpecies",
            clienttranslate($message),
            array(
                "player_name" => self::getActivePlayerName(),
                "visible_species" => $this->getVisibleSpecies(),
                "backup_species" => $this->getBackupSpecies(),
            )
        );

        for ($position = 1; $position <= 4; $position++) {
            foreach ($this->species->getCardsInLocation("shop_visible", $position) as $species) {
                $this->notifyAllPlayers(
                    "newVisibleSpecies",
                    "",
                    array("species_id" => $species["type_arg"], "shop_position" => $position)
                );
            }
        }
    }

    function autoDrawNewSpecies()
    {
        $backup_species_nbr = $this->species->countCardsInLocation("shop_backup");
        $visible_species_nbr = $this->species->countCardsInLocation("shop_visible");

        if ($backup_species_nbr + $visible_species_nbr == 0) {
            $this->drawNewSpecies(true);
        }
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in zookeepers.action.php)
    */

    function pass()
    {
        self::checkAction("pass");

        $this->gamestate->nextState("pass");
    }

    function hireKeeper()
    {
        self::checkAction("hireKeeper");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $keepers_on_board_nbr = 0;

        for ($i = 1; $i <= 4; $i++) {
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . $i, $player_id);
        }

        if ($keepers_on_board_nbr >= 4) {
            throw new BgaVisibleSystemException("You can't have more than 4 keepers in play");
        }

        $this->gamestate->nextState("selectHiredPile");
    }

    function selectHiredPile($pile)
    {
        self::checkAction("selectHiredPile");

        $player_id = self::getActivePlayerId();

        $board_position = 0;

        for ($position = 1; $position <= 4; $position++) {
            if ($this->keepers->countCardsInLocation("board:" . $position, $player_id) < 1) {
                $board_position = $position;
                break;
            }
        }

        if ($board_position === 0) {
            throw new BgaVisibleSystemException("You can't have more than 4 keepers in play");
        }

        $keeper = $this->keepers->pickCardForLocation("deck:" . $pile, "board:" . $board_position, $player_id);

        if ($keeper === null) {
            throw new BgaUserException(self::_("The selected pile is out of cards"));
        }
        $keeper_id = $keeper["id"];

        $pile_counters = $this->getPileCounters();

        $sql = "UPDATE keeper SET pile=$pile WHERE card_id=$keeper_id";
        self::DbQuery($sql);

        $this->notifyAllPlayers(
            "hireKeeper",
            clienttranslate('${player_name} hires ${keeper_name} from pile ${pile}. ${left_in_pile} keeper(s) remain in the pile'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "keeper_name" => $keeper["type"],
                "keeper_id" => $keeper["type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
                "left_in_pile" => $pile_counters[$pile]
            )
        );

        self::setGameStateValue("mainAction", 5);

        $this->gamestate->nextState("betweenActions");
    }

    function dismissKeeper($board_position)
    {
        self::checkAction("dismissKeeper");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $keepers_on_board_nbr = 0;

        for ($i = 1; $i <= 4; $i++) {
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . $i, $player_id);
        }

        if ($keepers_on_board_nbr === 0) {
            throw new BgaVisibleSystemException("You don't have any keeper to dismiss");
        }

        $pile = 0;
        $keepers_info = $this->keepers_info;
        $keeper = null;
        $keeper_id = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $pile = $card["pile"];
            $keeper_level = $keepers_info[$card["card_type_arg"]]["level"];
            $keeper = $card;
            $keeper_id = $card["card_type_arg"];
        }

        if ($keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        if ($pile == 0 && $keeper_level === 1) {
            self::setGameStateValue("selectedPosition", $board_position);
            $this->gamestate->nextState("selectDismissedPile");
            return;
        }

        if ($pile == 0) {
            throw new BgaVisibleSystemException("This keeper isn't hired by you");
        }

        if ($pile < 0 || $pile > 4) {
            throw new BgaVisibleSystemException("Invalid keeper pile");
        }

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper && $keeper_id == $completed_keeper["type_arg"]) {
            $score = $this->keepers_info[$keeper_id]["level"];
            $this->updateScore($player_id, -$score);
        }

        $this->keepers->insertCardOnExtremePosition($keeper["card_id"], "deck:" . $pile, false);

        $pile_counters = $this->getPileCounters();

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is returned to the bottom of pile ${pile}. ${left_in_pile} keeper(s) in the pile'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "keeper_name" => $keeper["card_type"],
                "keeper_id" => $keeper_id,
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
                "left_in_pile" => $pile_counters[$pile],
            )
        );

        $this->discardAllKeptSpecies($board_position, $keeper["card_type"]);

        self::setGameStateValue("selectedPosition", $board_position);

        $this->gamestate->nextState("betweenActions");
    }

    function selectDismissedPile($pile)
    {
        self::checkAction("selectDismissedPile");

        $board_position = self::getGameStateValue("selectedPosition");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        if ($pile < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid keeper pile");
        }

        $player_id = self::getActivePlayerId();

        $keeper = null;
        $keeper_id = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $keeper = $card;
            $keeper_id = $card["card_type_arg"];
        }

        if ($keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper && $keeper_id == $completed_keeper["type_arg"]) {
            $score = $this->keepers_info[$keeper_id]["level"];
            $this->updateScore($player_id, -$score);
        }

        $this->keepers->insertCardOnExtremePosition($keeper["card_id"], "pile:" . $pile, false);

        $pile_counters = $this->getPileCounters();

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is returned to pile ${pile}. ${left_in_pile} keeper(s) in the pile'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "keeper_name" => $keeper["card_type"],
                "keeper_id" => $keeper_id,
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
                "left_in_pile" => $pile_counters[$pile],
            )
        );

        $keeper_card_id = $keeper["card_id"];

        self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_id='$keeper_card_id'");

        $this->discardAllKeptSpecies($board_position, $keeper["card_type"]);

        self::setGameStateValue("mainAction", 6);

        $this->gamestate->nextState("betweenActions");
    }

    function replaceKeeper($board_position)
    {
        self::checkAction("replaceKeeper");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();
        $keepers_on_board_nbr = 0;

        for ($position = 1; $position <= 4; $position++) {
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . $position, $player_id);
        }

        if ($keepers_on_board_nbr === 0) {
            throw new BgaVisibleSystemException("You don't have any keeper to replace");
        }

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $keeper = $card;
        }

        if ($keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        self::setGameStateValue("selectedPosition", $board_position);

        $this->gamestate->nextState("selectReplacedPile");
    }

    function selectReplacedPile($pile)
    {
        self::checkAction("selectReplacedPile");

        $player_id = self::getActivePlayerId();

        $board_position = self::getGameStateValue("selectedPosition");

        $replaced_keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $replaced_keeper = $card;
        }

        if ($replaced_keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        $hired_keeper = $this->keepers->pickCardForLocation("deck:" . $pile, "board:" . $board_position, $player_id);

        if ($hired_keeper === null) {
            throw new BgaUserException(self::_("The selected pile is out of cards"));
        }

        $replaced_keeper_id = $replaced_keeper["card_type_arg"];

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper && $replaced_keeper_id == $completed_keeper["type_arg"]) {
            $score = $this->keepers_info[$replaced_keeper_id]["level"];
            $this->updateScore($player_id, -$score);
        }

        $replaced_card_id = $replaced_keeper["card_id"];
        self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_id=$replaced_card_id");
        $this->keepers->insertCardOnExtremePosition($replaced_card_id, "deck:" . $pile, false);;

        $this->notifyAllPlayers(
            "hireKeeper",
            "",
            array(
                "player_id" => self::getActivePlayerId(),
                "keeper_id" => $hired_keeper["type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $this->getPileCounters(),
                "piles_tops" => $this->getPilesTops(),
            )
        );

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} replaces ${replaced_keeper_name} by ${hired_keeper_name}, from pile ${pile}'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "replaced_keeper_name" => $replaced_keeper["card_type"],
                "hired_keeper_name" => $hired_keeper["type"],
                "keeper_id" => $replaced_keeper_id,
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $this->getPileCounters(),
                "piles_tops" => $this->getPilesTops(),
            )
        );

        $this->discardAllKeptSpecies($board_position, $replaced_keeper["card_type"]);

        self::setGameStateValue("mainAction", 7);

        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngKeepers()
    {
        self::checkAction("cancelMngKeepers");
        self::setGameStateValue("selectedPosition", 0);
        self::setGameStateValue("mainAction", 0);

        $this->gamestate->nextState("cancel");
    }

    function collectResources()
    {
        self::checkAction("collectResources");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        if ($this->isBagEmpty()) {
            throw new BgaUserException(self::_("The bag is out of resources"));
        }

        $player_id = self::getActivePlayerId();

        $species_nbr = $this->getSpeciesCounters()[$player_id];

        if ($species_nbr === 0) {
            throw new BgaUserException(self::_("You can't collect any resources until you save a species"));
        }

        $collected_resources = $this->resources->pickCards($species_nbr, "deck", $player_id);
        $collected_nbr = count($collected_resources);

        $collected_plant = $this->filterByResourceType($collected_resources, 1);
        $collected_plant_nbr = count($collected_plant);

        $collected_meat = $this->filterByResourceType($collected_resources, 2);
        $collected_meat_nbr = count($collected_meat);

        $collected_kit = $this->filterByResourceType($collected_resources, 3);
        $collected_kit_nbr = count($collected_kit);

        $this->notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} collects ${collected_nbr} resource(s): ${collected_plant_nbr} plant(s), 
           ${collected_meat_nbr} meat/fish, ${collected_kit_nbr} medical kit(s)'),
            array(
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "collected_nbr" => $collected_nbr,
                "collected_plant_nbr" => $collected_plant_nbr,
                "collected_meat_nbr" => $collected_meat_nbr,
                "collected_kit_nbr" => $collected_kit_nbr,
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );

        self::setGameStateValue("mainAction", 1);

        $this->gamestate->nextState("betweenActions");
    }

    function exchangeResources()
    {
        self::checkAction("exchangeResources");

        if (self::getGameStateValue("freeAction") || self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("The conservation fund can't be used after any other action"));
        }

        if ($this->isBagEmpty()) {
            throw new BgaUserException(self::_("The bag is out of resources"));
        }

        $this->gamestate->nextState("exchangeCollection");
    }

    function collectFromExchange($choosen_nbr)
    {
        self::checkAction("collectFromExchange");

        $player_id = self::getActivePlayerId();

        $collected_resources = $this->resources->pickCards($choosen_nbr, "deck", $player_id);
        $collected_nbr = count($collected_resources);

        $return_nbr = $collected_nbr * 2;
        self::setGameStateValue("totalToReturn", $return_nbr);

        $collected_plant = $this->filterByResourceType($collected_resources, 1);
        $collected_plant_nbr = count($collected_plant);

        $collected_meat = $this->filterByResourceType($collected_resources, 2);
        $collected_meat_nbr = count($collected_meat);

        $collected_kit = $this->filterByResourceType($collected_resources, 3);
        $collected_kit_nbr = count($collected_kit);

        $this->notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} activates the conservation fund and collects ${collected_nbr} resource(s): ${collected_plant_nbr} plant(s), 
            ${collected_meat_nbr} meat/fish, ${collected_kit_nbr} medical kit(s). ${return_nbr} resources must be returned to the bag'),
            array(
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "collected_nbr" => $collected_nbr,
                "collected_plant_nbr" => $collected_plant_nbr,
                "collected_meat_nbr" => $collected_meat_nbr,
                "collected_kit_nbr" => $collected_kit_nbr,
                "return_nbr" => $return_nbr,
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );
        self::setGameStateValue("freeAction", 3);

        $this->gamestate->nextState("exchangeReturn");
    }

    function cancelExchange()
    {
        self::checkAction("cancelExchange");
        self::setGameStateValue("freeAction", 0);

        $this->gamestate->nextState("cancel");
    }

    function returnFromExchange($lastly_returned_nbr, $type)
    {
        self::checkAction("returnFromExchange");
        $player_id = self::getActivePlayerId();

        $resources_in_hand = $this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id);
        $resources_returned = array_slice($resources_in_hand, 0, $lastly_returned_nbr, true);
        $keys = array_keys($resources_returned);

        $this->resources->moveCards($keys, "deck");
        $this->resources->shuffle("deck");

        $previously_returned_nbr = self::getGameStateValue("previouslyReturned");
        $returned_total = $previously_returned_nbr + $lastly_returned_nbr;
        self::setGameStateValue("previouslyReturned", $previously_returned_nbr + $lastly_returned_nbr);

        $to_return = self::getGameStateValue("totalToReturn") - $returned_total;

        $this->notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} ${type}(s)'),
            array(
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "returned_nbr" => $lastly_returned_nbr,
                "type" => $type,
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );

        if ($to_return === 0) {
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->gamestate->nextState("betweenExchangeReturns");
    }

    function returnExcess($lastly_returned_nbr, $type)
    {
        self::checkAction("returnExcess");
        $player_id = self::getActivePlayerId();

        $resources_in_hand = $this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id);
        $resources_returned = array_slice($resources_in_hand, 0, $lastly_returned_nbr, true);
        $keys = array_keys($resources_returned);

        $this->resources->moveCards($keys, "deck");
        $this->resources->shuffle("deck");

        $previously_returned_nbr = self::getGameStateValue("previouslyReturned");
        $returned_total = $previously_returned_nbr + $lastly_returned_nbr;
        self::setGameStateValue("previouslyReturned", $previously_returned_nbr + $lastly_returned_nbr);

        $to_return = self::getGameStateValue("totalToReturn") - $returned_total;

        $this->notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} ${type}(s) as excess'),
            array(
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "returned_nbr" => $lastly_returned_nbr,
                "type" => $type,
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );

        if ($to_return === 0) {
            $this->gamestate->nextState("betweenPlayers");
            return;
        }

        $this->gamestate->nextState("betweenExcessReturns");
    }

    function saveQuarantined($species_id)
    {
        self::checkAction("saveQuarantined");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $can_save = count($this->getSavableQuarantined()) > 0;

        if (!$can_save) {
            throw new BgaUserException(self::_("You can't save any of the available species"));
        }

        $species_card = null;

        foreach ($this->quarantines as $quarantine) {
            $cards_in_location = $this->species->getCardsInLocation("quarantine:" . $quarantine, $player_id);
            $species_card = $this->findCardByTypeArg($cards_in_location, $species_id);

            if ($species_card !==  null) {
                break;
            }
        }

        $savable_species = array_keys($this->getSavableQuarantined()[$player_id]);

        if ($species_card === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        if (!in_array($species_id, $savable_species)) {
            throw new BgaUserException(self::_("You don't have the required resources or keepers to save this species"));
        }

        self::setGameStateValue("selectedSpecies", $species_card["id"]);
        $this->gamestate->nextState("selectQuarantinedKeeper");
    }

    function selectQuarantinedKeeper($board_position)
    {
        self::checkAction("selectQuarantinedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $species = $this->species->getCard(self::getGameStateValue("selectedSpecies"));

        if (!$species) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $species_id = $species["type_arg"];

        $keeper = null;
        $keeper_id = null;
        foreach ($this->keepers->getCardsInLocation("board:" . $board_position, $player_id) as $card) {
            $keeper = $card;
            $keeper_id = $card["type_arg"];
        }

        $assignable_keepers = array_keys($this->getAssignableKeepers($species_id));

        if (!in_array($keeper_id, $assignable_keepers)) {
            throw new BgaUserException(self::_("You can't assign this species to this keeper"));
        }

        $this->species->moveCard(
            $species["id"],
            "board:" . $board_position,
            $player_id
        );

        $returned_cost = $this->returnCost($species_id);

        foreach ($returned_cost as $type => $cost) {
            if ($cost > 0) {
                $this->notifyAllPlayers(
                    "returnResources",
                    clienttranslate('${player_name} uses ${returned_nbr} ${type}(s) to save the ${species_name}'),
                    array(
                        "i18n" => array("species_name"),
                        "player_name" => $this->getActivePlayerName(),
                        "player_id" => $player_id,
                        "returned_nbr" => $cost,
                        "type" => $type,
                        "resource_counters" => $this->getResourceCounters(),
                        "species_name" => $species["type"]
                    )
                );
            }
        }

        $points = $this->species_info[$species_id]["points"] + 2;

        $quarantine = explode(":", $species["location"])[1];
        $quarantine_label = $quarantine === "ALL" ? "generic" : $quarantine;

        $this->notifyAllPlayers(
            "saveQuarantined",
            clienttranslate('${player_name} saves the ${species_name} from his ${quarantine_label} quarantine and assigns it to ${keeper_name}, 
            scoring ${species_points} point(s)'),
            array(
                "i18n" => array("species_name"),
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_name" => $species["type"],
                "species_id" => $species["type_arg"],
                "species_points" => $points,
                "shop_position" => $species["location_arg"],
                "keeper_name" => $keeper["type"],
                "board_position" => $board_position,
                "saved_species" => $this->getSavedSpecies(),
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "assignable_keepers" => $assignable_keepers,
                "species_counters" => $this->getSpeciesCounters(),
            )
        );

        $this->updateScore($player_id, $points);

        $completed_card = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_card !== null) {
            $completed_id = $completed_card["type_arg"];

            if ($keeper_id == $completed_id) {
                $keeper_level = $this->keepers_info[$keeper_id]["level"];

                $this->notifyAllPlayers("completeKeeper", clienttranslate('${player_name} completes ${keeper_name} and scores ${keeper_level} point(s)'),  array(
                    "player_name" => self::getActivePlayerName(),
                    "player_id" => $player_id,
                    "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                    "keeper_id" => $keeper_id,
                    "keeper_name" => $keeper["type"],
                    "keeper_level" => $keeper_level,
                    "board_position" => $board_position,
                    "completed_keepers" => $this->getCompletedKeepers(),
                ));

                $this->updateScore($player_id, $keeper_level);
            }
        }

        $this->updateHighestSaved($player_id);

        self::setGameStateValue("mainAction", 2);

        $this->gamestate->nextState("betweenActions");
    }

    function saveSpecies($shop_position)
    {
        self::checkAction("saveSpecies");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $can_save = count($this->getSavableSpecies()) > 0;

        if (!$can_save) {
            throw new BgaUserException(self::_("You can't save any of the available species"));
        }

        if ($shop_position < 1 || $shop_position > 4) {
            throw new BgaVisibleSystemException("Invalid shop position");
        }

        $species_id = null;
        $species_card_id = null;
        foreach ($this->species->getCardsInLocation("shop_visible", $shop_position) as $card) {
            $species_id = $card["type_arg"];
            $species_card_id = $card["id"];
        }

        $savable_species_ids = array_keys($this->getSavableSpecies());

        if ($species_id === null || $species_card_id === null) {
            throw new BgaVisibleSystemException("This species is not available to be saved");
        }

        if (!in_array($species_id, $savable_species_ids)) {
            throw new BgaUserException(self::_("You don't have the required resources or keepers to save this species"));
        }

        self::setGameStateValue("selectedSpecies", $species_card_id);
        $this->gamestate->nextState("selectAssignedKeeper");
    }

    function selectAssignedKeeper($board_position)
    {
        self::checkAction("selectAssignedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $species = $this->species->getCard(self::getGameStateValue("selectedSpecies"));

        if (!$species) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $species_id = $species["type_arg"];

        $keeper = null;
        $keeper_id = null;
        foreach ($this->keepers->getCardsInLocation("board:" . $board_position, $player_id) as $card) {
            $keeper = $card;
            $keeper_id = $card["type_arg"];
        }

        $assignable_keepers = $this->getAssignableKeepers($species_id);

        $assignable_keepers_ids = array_keys($assignable_keepers);

        if (!in_array($keeper_id, $assignable_keepers_ids)) {
            throw new BgaUserException(self::_("You can't assign this species to this keeper"));
        }

        $this->species->moveAllCardsInLocation(
            $species["location"],
            "board:" . $board_position,
            $species["location_arg"],
            $player_id
        );

        $returned_cost = $this->returnCost($species_id);

        foreach ($returned_cost as $type => $cost) {
            if ($cost > 0) {
                $this->notifyAllPlayers(
                    "returnResources",
                    clienttranslate('${player_name} uses ${returned_nbr} ${type}(s) to save the ${species_name}'),
                    array(
                        "i18n" => array("species_name"),
                        "player_name" => $this->getActivePlayerName(),
                        "player_id" => $player_id,
                        "returned_nbr" => $cost,
                        "type" => $type,
                        "resource_counters" => $this->getResourceCounters(),
                        "species_name" => $species["type"]
                    )
                );
            }
        }

        $points = $this->species_info[$species_id]["points"];

        $this->notifyAllPlayers(
            "saveSpecies",
            clienttranslate('${player_name} saves the ${species_name} and assigns it to ${keeper_name}, scoring ${species_points} point(s)'),
            array(
                "i18n" => array("species_name"),
                "player_name" => self::getActivePlayerName(),
                "player_id" => $player_id,
                "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_name" => $species["type"],
                "species_id" => $species["type_arg"],
                "species_points" => $points,
                "shop_position" => $species["location_arg"],
                "keeper_name" => $keeper["type"],
                "board_position" => $board_position,
                "visible_species" => $this->getVisibleSpecies(),
                "savable_species" => $this->getSavableSpecies(),
                "saved_species" => $this->getSavedSpecies(),
                "assignable_keepers" => $assignable_keepers,
                "species_counters" => $this->getSpeciesCounters(),
            )
        );

        $this->updateScore($player_id, $points);

        $completed_card = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_card !== null) {
            $completed_id = $completed_card["type_arg"];

            if ($keeper_id == $completed_id) {
                $keeper_level = $this->keepers_info[$keeper_id]["level"];

                $this->notifyAllPlayers("completeKeeper", clienttranslate('${player_name} completes ${keeper_name} and scores ${keeper_level} point(s)'),  array(
                    "player_name" => self::getActivePlayerName(),
                    "player_id" => $player_id,
                    "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                    "keeper_id" => $keeper_id,
                    "keeper_name" => $keeper["type"],
                    "keeper_level" => $keeper_level,
                    "board_position" => $board_position,
                    "completed_keepers" => $this->getCompletedKeepers(),
                ));

                $this->updateScore($player_id, $keeper_level);
            }
        }

        $this->updateHighestSaved($player_id);

        self::setGameStateValue("mainAction", 2);

        $this->gamestate->nextState("betweenActions");
    }

    function lookAtBackup(
        $shop_position,
        $backup_id
    ) {
        self::checkAction("lookAtBackup");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        if ($shop_position < 0 || $shop_position > 4) {
            throw new BgaVisibleSystemException("Invalid shop position");
        }

        $species_in_location = $this->species->getCardsInLocation("shop_backup", $shop_position);
        $species = array_shift($species_in_location);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $species_id = $species["type_arg"];

        $this->notifyPlayer(
            $player_id,
            "lookAtBackup",
            clienttranslate('You look at a face-down species and... It is the ${species_name}!'),
            array(
                "i18n" => array("species_name"),
                "player_id" => $player_id,
                "species_id" => $species_id,
                "species_name" => $species["type"],
                "shop_position" => $shop_position,
                "backup_id" => $backup_id,
                "backup_species" => $this->getBackupSpecies(),
            ),
        );

        self::setGameStateValue("selectedSpecies", $species_id);
        self::setGameStateValue("selectedBackup", $backup_id);
        self::setGameStateValue("selectedPosition", $shop_position);

        $this->gamestate->nextState("mngBackup");
    }

    function discardBackup()
    {
        self::checkAction("discardBackup");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $species_id = self::getGameStateValue("selectedSpecies");
        $backup_id = self::getGameStateValue("selectedBackup");
        $shop_position = self::getGameStateValue("selectedPosition");

        $species_in_location = $this->species->getCardsInLocation("shop_backup");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $this->species->insertCardOnExtremePosition($species["id"], "deck", false);

        $this->notifyPlayer(
            $player_id,
            "discardBackupPrivately",
            "",
            array(
                "player_id" => $player_id,
                "player_name" => self::getActivePlayerName(),
                "shop_position" => $shop_position,
                "backup_id" => $backup_id,
                "species_id" => $species_id,
                "backup_species" => $this->getBackupSpecies(),
            ),
        );

        $this->notifyAllPlayers(
            "discardBackup",
            clienttranslate('${player_name} moves a face-down species to the bottom of the deck'),
            array(
                "player_id" => $player_id,
                "player_name" => self::getActivePlayerName(),
                "shop_position" => $shop_position,
                "backup_id" => $backup_id,
                "backup_species" => $this->getBackupSpecies(),
            ),
        );

        if (self::getGameStateValue("secondStep") > 0) {
            self::setGameStateValue("mainAction", 4);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        self::setGameStateValue("selectedBackup", 0);
        self::setGameStateValue("selectedPosition", 0);
        self::setGameStateValue("secondStep", 1);

        $this->autoDrawNewSpecies();

        $this->gamestate->nextState("mngSecondSpecies");
    }

    function quarantineBackup()
    {
        self::checkAction("quarantineBackup");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $species_id = self::getGameStateValue("selectedSpecies");
        $player_id = self::getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_backup");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $is_quarantinable = false;

        foreach ($this->getOpenQuarantines()[$player_id] as $quarantine) {
            if ($this->canLiveInQuarantine($species_id, $quarantine)) {
                $is_quarantinable = true;
                break;
            }
        }

        if (!$is_quarantinable) {
            throw new BgaUserException(self::_("You can't quarantine this species"));
        }

        self::setGameStateValue("selectedSpecies", $species_id);

        $this->gamestate->nextState("selectBackupQuarantine");
    }

    function selectBackupQuarantine($quarantine)
    {
        self::checkAction("selectBackupQuarantine");

        $player_id = self::getActivePlayerId();
        $species_id = self::getGameStateValue("selectedSpecies");

        $species_in_location = $this->species->getCardsInLocation("shop_backup");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        if (!$this->canLiveInQuarantine($species_id, $quarantine)) {
            throw new BgaUserException(self::_("This species can't live in that quarantine"));
        }

        $this->species->moveCard($species["id"], "quarantine:" . $quarantine, $player_id);

        $quarantine_label = $quarantine;
        if ($quarantine_label === "ALL") {
            $quarantine_label = "generic";
        }

        $this->notifyAllPlayers(
            "quarantineBackup",
            clienttranslate('${player_name} moves ${species_name} to his ${quarantine_label} quarantine'),
            array(
                "i18n" => array("species_name"),
                "player_id" => $player_id,
                "player_name" => self::getActivePlayerName(),
                "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_id" => $species_id,
                "species_name" => $species["type"],
                "shop_position" => $species["location_arg"],
                "backup_id" => self::getGameStateValue("selectedBackup"),
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "quarantined_species" => $this->getQuarantinedSpecies(),
                "visible_species" => $this->getVisibleSpecies(),
            )
        );

        $this->updateScore($player_id, -2);

        if (self::getGameStateValue("secondStep") == 0) {
            $this->autoDrawNewSpecies();
            self::setGameStateValue("secondStep", 1);
            self::setGameStateValue("selectedSpecies", 0);
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        self::setGameStateValue("mainAction", 3);
        $this->gamestate->nextState("betweenActions");
    }

    function discardSpecies(
        $species_id
    ) {
        self::checkAction("discardSpecies");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $shop_position = $species["location_arg"];

        $this->species->insertCardOnExtremePosition($species["id"], "deck", false);

        $this->notifyAllPlayers(
            "discardSpecies",
            clienttranslate('${player_name} moves ${species_name} to the bottom of the deck'),
            array(
                "i18n" => array("species_name"),
                "player_id" => $player_id,
                "player_name" => self::getActivePlayerName(),
                "species_id" => $species_id,
                "species_name" => $species["type"],
                "shop_position" => $shop_position,
                "visible_species" => $this->getVisibleSpecies(),
            ),
        );

        if (self::getGameStateValue("secondStep") > 0) {
            self::setGameStateValue("mainAction", 4);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->autoDrawNewSpecies();
        self::setGameStateValue("secondStep", 1);
        $this->gamestate->nextState("mngSecondSpecies");
    }

    function quarantineSpecies($species_id)
    {
        self::checkAction("quarantineSpecies");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $quarantinable_species = array_keys($this->getQuarantinableSpecies()[$player_id]);

        if (!in_array($species_id, $quarantinable_species)) {
            throw new BgaUserException(self::_("You can't quarantine this species"));
        }

        self::setGameStateValue("selectedSpecies", $species_id);

        $this->gamestate->nextState("selectQuarantine");
    }

    function selectQuarantine($quarantine)
    {
        self::checkAction("selectQuarantine");

        $player_id = self::getActivePlayerId();
        $species_id = self::getGameStateValue("selectedSpecies");

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaSystemException("Species not found");
        }

        if (!$this->canLiveInQuarantine($species_id, $quarantine)) {
            throw new BgaUserException(self::_("This species can't live in that quarantine"));
        }

        $this->species->moveCard($species["id"], "quarantine:" . $quarantine, $player_id);

        $quarantine_label = $quarantine;
        if ($quarantine_label === "ALL") {
            $quarantine_label = "generic";
        }

        $this->notifyAllPlayers(
            "quarantineSpecies",
            clienttranslate('${player_name} moves ${species_name} to his ${quarantine_label} quarantine'),
            array(
                "i18n" => array("species_name"),
                "player_id" => $player_id,
                "player_name" => self::getActivePlayerName(),
                "player_color" => self::loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_id" => $species_id,
                "species_name" => $species["type"],
                "shop_position" => $species["location_arg"],
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "quarantined_species" => $this->getQuarantinedSpecies(),
                "visible_species" => $this->getVisibleSpecies(),
            )
        );

        $this->updateScore($player_id, -2);

        if (self::getGameStateValue("secondStep") == 0) {
            $this->autoDrawNewSpecies();
            self::setGameStateValue("secondStep", 1);
            self::setGameStateValue("selectedSpecies", 0);
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        self::setGameStateValue("mainAction", 3);
        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngSpecies()
    {
        self::checkAction("cancelMngSpecies");

        self::setGameStateValue("selectedSpecies", 0);
        self::setGameStateValue("selectedPosition", 0);
        self::setGameStateValue("selectedBackup", 0);

        if (
            self::getGameStateValue("secondStep") > 0
            && $this->gamestate->state()["name"] !== "mngSecondSpecies"
        ) {
            self::setGameStateValue("mainAction", 3);
            $this->gamestate->nextState("cancelSecond");
            return;
        }

        if ($this->gamestate->state()["name"] === "mngSecondSpecies") {
            self::setGameStateValue("mainAction", 3);
        }

        self::setGameStateValue("secondStep", 0);
        $this->gamestate->nextState("cancel");
    }

    function newSpecies()
    {
        self::checkAction("newSpecies");

        $this->drawNewSpecies();

        self::setGameStateValue("freeAction", 1);

        if ($this->gamestate->state()["name"] === "mngSecondSpecies") {
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        $this->gamestate->nextState("betweenActions");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argPlayerTurn()
    {
        return array(
            "mainAction" => self::getGameStateValue("mainAction"), "freeAction" => self::getGameStateValue("freeAction"),
            "isBagEmpty" => $this->isBagEmpty(),
            "keepers_on_boards" => $this->getKeepersOnBoards(),
            "savable_species" => $this->getSavableSpecies(),
            "savable_with_fund" => $this->getSavableWithFund(),
            "savable_quarantined" => $this->getSavableQuarantined(),
            "savable_quarantined_with_fund" => $this->getSavableQuarantinedWithFund(),
            "quarantinable_species" => $this->getQuarantinableSpecies(),
            "empty_column_nbr" => $this->getEmptyColumnNbr()
        );
    }

    function argExchangeCollection()
    {
        $player_id = self::getActivePlayerId();

        $resources_in_hand_nbr = $this->resources->countCardsInLocation("hand", $player_id);

        return array("resources_in_hand_nbr" => $resources_in_hand_nbr, "freeAction" => self::getGameStateValue("freeAction"));
    }

    function argExchangeReturn()
    {
        return array("to_return" => self::getGameStateValue("totalToReturn") - self::getGameStateValue("previouslyReturned"));
    }

    function argReturnExcess()
    {
        return array("to_return" => self::getGameStateValue("totalToReturn") - self::getGameStateValue("previouslyReturned"));
    }

    function argMngSecondSpecies()
    {
        return array("empty_column_nbr" => $this->getEmptyColumnNbr());
    }

    function argMngBackup()
    {
        return array("_private" => array("active" => array("looked_backup" => $this->getLookedBackup())));
    }

    function argBetweenActions()
    {
        $mainAction = self::getGameStateValue("mainAction");

        return array("mainAction" => $mainAction);
    }
    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */

    function stBetweenExchangeReturns()
    {
        $this->gamestate->nextState("nextReturn");
    }

    function stBetweenExcessReturns()
    {
        $this->gamestate->nextState("nextReturn");
    }

    function stBetweenActions()
    {
        self::setGameStateValue("totalToReturn", 0);
        self::setGameStateValue("previouslyReturned", 0);
        self::setGameStateValue("selectedPosition", 0);
        self::setGameStateValue("selectedSpecies", 0);
        self::setGameStateValue("selectedBackup", 0);
        self::setGameStateValue("secondStep", 0);

        $this->autoDrawNewSpecies();

        $this->gamestate->nextState("nextAction");
    }

    function stBetweenPlayers()
    {
        self::setGameStateValue("totalToReturn", 0);
        self::setGameStateValue("previouslyReturned", 0);
        self::setGameStateValue("mainAction", 0);
        self::setGameStateValue("freeAction", 0);

        $current_player_id = self::getActivePlayerId();

        $resources_nbr = $this->resources->countCardsInLocation("hand", $current_player_id);
        $kit_nbr = count($this->resources->getCardsOfTypeInLocation("kit", 3, "hand", $current_player_id));

        if ($resources_nbr > 12 || $kit_nbr > 5) {
            $this->gamestate->nextState("excessResources");
            return;
        }

        $this->notifyAllPlayers("pass", clienttranslate('${player_name} finishes his turn and passes'), array(
            "player_name" => self::getActivePlayerName(),
        ));


        if (self::getGameStateValue("highestSaved") >= 9) {
            $last_turn = self::getGameStateValue("lastTurn") + 1;
            self::setGameStateValue("lastTurn", $last_turn);
        }

        if (self::getGameStateValue("lastTurn") == count(self::loadPlayersBasicInfos())) {
            $this->gamestate->nextState("finalScoresCalc");
            return;
        }

        $this->revealSpecies();

        self::activeNextPlayer();
        $next_player_id = self::getActivePlayerId();
        $this->giveExtraTime($next_player_id);

        $this->gamestate->nextState("nextPlayer");
    }

    function stExcessResources()
    {
        $player_id = self::getActivePlayerId();
        $kit_nbr = count($this->resources->getCardsOfTypeInLocation("kit", 3, "hand", $player_id));

        if ($kit_nbr > 5) {
            $returned_nbr = $kit_nbr - 5;
            $kits = $this->resources->getCardsInLocation("hand", $player_id);
            $returned_kits = array_slice($kits, 0, $returned_nbr, true);
            $keys = array_keys($returned_kits);
            $this->resources->moveCards($keys, "deck");
            $this->resources->shuffle("deck");

            $this->notifyAllPlayers(
                "returnResources",
                clienttranslate('${player_name} returns ${returned_nbr} kit(s) as excess'),
                array(
                    "player_name" => $this->getActivePlayerName(),
                    "player_id" => $player_id,
                    "returned_nbr" => $returned_nbr,
                    "type" => "kit",
                    "resource_counters" => $this->getResourceCounters(),
                )
            );
        }

        $resources_nbr = $this->resources->countCardsInLocation("hand", $player_id);

        if ($resources_nbr > 12) {
            self::setGameStateValue("totalToReturn", $resources_nbr - 12);
            $this->gamestate->nextState("returnExcess");
            return;
        }

        $this->gamestate->nextState("betweenPlayers");
    }

    function stFinalScoresCalc()
    {
        $players = self::loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $collection = self::getCollectionFromDb("SELECT player_score FROM player WHERE player_id='$player_id'");

            $new_scores = 0;
            foreach ($collection as $player_data) {
                $new_scores = $player_data["player_score"];
            }

            $this->notifyAllPlayers("newScores", "", array(
                "player_id" => $player_id,
                "new_scores" => $new_scores, "final_scores_calc" => true
            ));
        }

        $this->gamestate->nextState("gameEnd");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState("zombiePass");
                    break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */

    function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
        //        if( $from_version <= 1404301345 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }
}
