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

        $this->initGameStateLabels(array(
            "mainAction" => 10,
            "totalToReturn" => 11,
            "previouslyReturned" => 12,
            "freeAction" => 13,
            "selectedPosition" => 14,
            "selectedSpecies" => 15,
            "selectedBackup" => 16,
            "selectedZoo" => 17,
            "secondStep" => 18,
            "zooHelp" => 19,

            "highestSaved" => 80,
            "lastTurn" => 81,

            "scoreTracking" => 100,
            "bagHidden" => 101,
            "secretObjectives" => 102
        ));

        $this->resources = $this->getNew("module.common.deck");
        $this->resources->init("resource");

        $this->keepers = $this->getNew("module.common.deck");
        $this->keepers->init("keeper");

        $this->species = $this->getNew("module.common.deck");
        $this->species->init("species");


        $this->objectives = $this->getNew("module.common.deck");
        $this->objectives->init("objective");


        // experimental flag to prevent deadlocks
        $this->bSelectGlobalsForUpdate = true;
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
        $gameinfos = $this->getGameinfos();
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
        $this->DbQuery($sql);
        $this->reattributeColorsBasedOnPreferences($players, $gameinfos["player_colors"]);
        $this->reloadPlayersBasicInfos();

        $players = $this->loadPlayersBasicInfos();

        $resources_deck = array();
        foreach ($this->resource_types as $code => $resource) {
            $resources_deck[] = array("type" => $code, "type_arg" => 0, "nbr" => ($resource["total"] - $resource["per_player"] * count($players)));
        }

        $resources_to_players = array();
        foreach ($this->resource_types as $code => $resource) {
            $resources_to_players[] = array("type" => $code, "type_arg" => 0, "nbr" => $resource["per_player"]);
        }

        foreach ($players as $player_id => $player) {
            $this->resources->createCards($resources_to_players, "hand", $player_id);
        }

        $this->resources->createCards($resources_deck, "deck");
        $this->resources->shuffle("deck");

        $keepers_info = $this->keepers_info;
        $starting_keepers = array();

        foreach ($this->filterByLevel($keepers_info, 1) as $keeper_id => $keeper) {
            $starting_keepers[] = array("type" => $keeper["keeper_name"], "type_arg" => $keeper_id, "nbr" => 1);
        }
        $this->keepers->createCards($starting_keepers, "deck");
        $this->keepers->shuffle("deck");
        foreach ($players as $player_id => $player) {
            $this->DbQuery("UPDATE keeper SET pile=0 WHERE card_location='deck'");
            $this->keepers->pickCardForLocation("deck", "board:1", $player_id);
        }

        $this->keepers->moveAllCardsInLocation("deck", "box");

        $other_keepers = array();
        foreach ($this->filterByLevel($keepers_info, 1, true) as $keeper_id => $keeper) {
            $other_keepers[] = array("type" => $keeper["keeper_name"], "type_arg" => $keeper_id, "nbr" => 1);
        }
        $this->keepers->createCards($other_keepers, "deck");
        $this->keepers->shuffle("deck");
        for ($pile = 1; $pile <= 4; $pile++) {
            $location = "deck:" . $pile;
            $this->keepers->pickCardsForLocation(5, "deck", $location);
            $this->DbQuery("UPDATE keeper SET pile=$pile WHERE card_location='$location'");
            $this->keepers->shuffle($location);
        }

        $species_deck = array();
        $species_info = $this->species_info;

        foreach ($species_info as $species_id => $species) {
            $species_deck[] = array(
                "type" => $species["scientific_name"],
                "type_arg" => $species_id,
                "nbr" => 1,
            );
        }

        $this->species->createCards($species_deck, "deck");
        $this->species->shuffle("deck");

        for ($position = 1; $position <= 4; $position++) {
            $this->species->pickCardsForLocation(2, "deck", "shop_backup", $position);
            $this->species->pickCardForLocation("deck", "shop_visible", $position);
        }

        //secret objectives 

        if ($this->hasSecretObjectives()) {
            $objectives_deck = array();
            foreach ($this->objectives_info as $objective_id => $objective) {
                $objectives_deck[] = array(
                    "type" => strval($objective["sprite_pos"]),
                    "type_arg" => $objective_id,
                    "nbr" => 2
                );
            }

            $this->objectives->createCards($objectives_deck, "deck");
            $this->objectives->shuffle("deck");

            foreach ($players as $player_id => $player) {
                $this->objectives->pickCard("deck", $player_id);
            }
        }

        /************ Start the game initialization *****/

        // Init global values with their initial values
        $this->setGameStateInitialValue("mainAction", 0);
        $this->setGameStateInitialValue("freeAction", 0);
        $this->setGameStateInitialValue("totalToReturn", 0);
        $this->setGameStateInitialValue("previouslyReturned", 0);
        $this->setGameStateInitialValue("selectedPosition", 0);
        $this->setGameStateInitialValue("selectedSpecies", 0);
        $this->setGameStateInitialValue("selectedBackup", 0);
        $this->setGameStateInitialValue("selectedZoo", 0);
        $this->setGameStateInitialValue("zooHelp", 0);
        $this->setGameStateInitialValue("secondStep", 0);
        $this->setGameStateInitialValue("lastTurn", 0);

        // Init game statistics
        $this->initStat("player", "species_saved", 0);
        $this->initStat("player", "points_species_saved", 0);
        $this->initStat("player", "cr_species_saved", 0);
        $this->initStat("player", "quarantined_species", 0);
        $this->initStat("player", "discarded_species", 0);
        $this->initStat("player", "discarded_saved_species", 0);
        $this->initStat("player", "keepers_completed", 0);
        $this->initStat("player", "points_keepers_completed", 0);
        $this->initStat("player", "points_objectives", 0);
        $this->initStat("player", "keepers_dismissed", 0);
        $this->initStat("player", "resources_consumed", 0);
        $this->initStat("player", "zoo_help_asked", 0);
        $this->initStat("player", "zoo_help_given", 0);

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = $this->getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $species_info = $this->species_info;
        $keepers_info = $this->keepers_info;
        $objectives_info = $this->objectives_info;
        $players = $this->loadPlayersBasicInfos();

        $result["gameVersion"] = intval($this->gamestate->table_globals[300]);

        $result["isRealTimeScoreTracking"] = $this->isRealTimeScoreTracking();
        $result["isBagHidden"] = $this->isBagHidden();
        $result["hasSecretObjectives"] = $this->hasSecretObjectives();

        $result["players"] = $this->getCollectionFromDb($sql);
        $result["resourceCounters"] = $this->getResourceCounters();
        $result["bagCounters"] = $this->getBagCounters();
        $result["isBagEmpty"] = $this->isBagEmpty();
        $result["allKeepers"] = $keepers_info;
        $result["pileCounters"] = $this->getPileCounters();
        $result["pilesTops"] = $this->getPilesTops();
        $result["keepersAtHouses"] = $this->getKeepersAtHouses();
        $result["allSpecies"] = $species_info;
        $result["backupSpecies"] = $this->getBackupSpecies();
        $result["visibleSpecies"] = $this->getVisibleSpecies();
        $result["savableSpecies"] = $this->getSavableSpecies();
        $result["savableWithFund"] = $this->getSavableWithFund();
        $result["savableQuarantined"] = $this->getSavableQuarantined();
        $result["savableQuarantinedWithFund"] = $this->getSavableQuarantinedWithFund();
        $result["allQuarantines"] = $this->quarantines;
        $result["openQuarantines"] = $this->getOpenQuarantines();
        $result["quarantinableSpecies"] = $this->getQuarantinableSpecies();
        $result["savedSpecies"] = $this->getSavedSpecies();
        $result["quarantinedSpecies"] = $this->getQuarantinedSpecies();
        $result["completedKeepers"] = $this->getCompletedKeepers();
        $result["speciesCounters"] = $this->getSpeciesCounters();
        $result["emptyColumnNbr"] = $this->getEmptyColumnNbr();
        $result["allObjectives"] = $objectives_info;
        $result["isLastTurn"] = $this->isLastTurn();

        if (in_array($current_player_id, array_keys($players))) {
            $result["secretObjective"] = $this->getObjectives()[$current_player_id];
        }

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
        return 10 * $this->getGameStateValue("highestSaved");
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Utility functions
    ////////////    

    public function checkVersion(int $clientVersion): void
    {
        if ($clientVersion != intval($this->gamestate->table_globals[300])) {
            throw new BgaVisibleSystemException($this->_("A new version of this game is now available. Please reload the page (F5)."));
        }
    }

    function isRealTimeScoreTracking()
    {
        return $this->getGameStateValue("scoreTracking") == 1;
    }

    function isBagHidden()
    {
        return $this->getGameStateValue("bagHidden") == 1;
    }

    function hasSecretObjectives()
    {
        return $this->getGameStateValue("secretObjectives") == 1;
    }

    function styledSpeciesName()
    {
        return '<span style="font-weight: bold; text-transform: capitalize">${species_name_tr}</span>';
    }

    function filterByResourceType($type, $resources)
    {
        $filtered_resources = array_filter($resources, function ($resource) use ($type) {
            return $resource["type"] == $type;
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
        $players = $this->loadPlayersBasicInfos();

        $counters = array();

        foreach ($players as $player_id => $player) {
            $plants = $this->resources->getCardsOfTypeInLocation("plant", null, "hand", $player_id);
            $plants_nbr = count($plants);

            $meat = $this->resources->getCardsOfTypeInLocation("meat", null, "hand", $player_id);
            $meat_nbr = count($meat);

            $kits = $this->resources->getCardsOfTypeInLocation("kit", null, "hand", $player_id);
            $kits_nbr = count($kits);

            $counters[$player_id] = array("plant" => $plants_nbr, "meat" => $meat_nbr, "kit" => $kits_nbr);
        }

        return $counters;
    }

    function getBagCounters()
    {
        if ($this->isBagHidden()) {
            return null;
        }

        $plants = $this->resources->getCardsOfTypeInLocation("plant", null, "deck");
        $plants_nbr = count($plants);

        $meat = $this->resources->getCardsOfTypeInLocation("meat", null, "deck");
        $meat_nbr = count($meat);

        $kits = $this->resources->getCardsOfTypeInLocation("kit", null, "deck");
        $kits_nbr = count($kits);

        $counters = array("plant" => $plants_nbr, "meat" => $meat_nbr, "kit" => $kits_nbr);

        return $counters;
    }

    function isBagEmpty()
    {
        return $this->resources->countCardInLocation("deck") == 0;
    }

    function getKeepersAtHouses()
    {
        $players = $this->loadPlayersBasicInfos();

        $keepers = array();
        foreach ($players as $player_id => $player) {
            for ($position = 1; $position <= 4; $position++) {
                $location = "board:" . $position;
                $sql = "SELECT pile, card_id, card_location, card_location_arg, card_type, card_type_arg FROM keeper WHERE card_location_arg='$player_id' AND card_location='$location'";
                $keepers[$player_id][$position] = $this->getCollectionFromDb($sql);
            }
        }

        return $keepers;
    }

    function getObjectives()
    {
        $objectives = array();

        $players = $this->loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $objectives_in_hand = $this->objectives->getCardsInLocation("hand", $player_id);
            $objective = array_shift($objectives_in_hand);

            $objectives[$player_id] = $objective;
        }

        return $objectives;
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
        $players = $this->loadPlayersBasicInfos();

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
        $players = $this->loadPlayersBasicInfos();

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
        $player_id = $this->getActivePlayerId();
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
        $player_id = $this->getActivePlayerId();
        $resource_counters = $this->getResourceCounters()[$player_id];

        $species_info = $this->species_info[$species_id];
        $cost = $species_info["cost"];

        $kit_nbr = $resource_counters["kit"];
        $kit_cost = $cost["kit"];
        $available_kits_nbr = $kit_nbr - $kit_cost;

        if ($available_kits_nbr <= 0) {
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
                $conservation_fund[$type] = $needed_kit_nbr;
                $available_kits_nbr = $available_kits_nbr - $needed_kit_nbr;
            }
        }

        return $conservation_fund;
    }

    function getAssignableKeepers($species_id)
    {
        $player_id = $this->getActivePlayerId();
        $species_info = $this->species_info[$species_id];

        $keepers_in_play = array();

        for ($position = 1; $position <= 4; $position++) {
            $keepers_in_play[$position] = $this->getKeepersAtHouses()[$player_id][$position];
        }

        $species_keys = array_keys($species_info);

        $assignable_keepers = array();

        foreach ($keepers_in_play as $card_container) {
            $keeper_card = array_shift($card_container);

            if (!$keeper_card) {
                continue;
            }

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
                        in_array($key, $species_keys)
                    ) {
                        $species_value = $species_info[$key];

                        if (is_array($species_value) && count(array_intersect($keeper_value, $species_value)) > 0) {
                            $assignable_keepers[$keeper_id] = $keeper_card;
                            break;
                        }

                        if (!is_array($species_value) && in_array($species_value, $keeper_value)) {
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
                        in_array($key, $species_keys)
                    ) {
                        $species_value = $species_info[$key];

                        if (is_array($species_value) && count(array_intersect($keeper_value, $species_value)) > 0) {
                            $conditions_met++;
                            continue;
                        }

                        if (!is_array($species_value) && in_array($species_value, $keeper_value)) {
                            $conditions_met++;
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
        $players = $this->loadPlayersBasicInfos();
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
        $player_id = $this->getActivePlayerId();
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

                $returned_cost[$type] = $type_cost - $fund_cost * 2;

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

                    $species_id = $species["type_arg"];

                    $this->notifyAllPlayers(
                        "revealSpecies",
                        clienttranslate('The ${species_name} is flipped over and revealed to all players'),
                        array(
                            "shop_position" => $position,
                            "species_name" => array(
                                "log" => $this->styledSpeciesName(),
                                "args" => array(
                                    "i18n" => array("species_name_tr"),
                                    "species_name_tr" => $this->species_info[$species_id]["name"]
                                )
                            ),
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
        $players = $this->loadPlayersBasicInfos();
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

        foreach ($this->loadPlayersBasicInfos() as $player_id => $player) {
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

    function canDismissKeeper($board_position, $player_id)
    {
        $can_dismiss = true;

        $total_species_nbr = $this->getSpeciesCounters()[$player_id];

        if (
            $this->species->countCardsInLocation("board:" . $board_position, $player_id) == $total_species_nbr
            && $this->resources->countCardsInLocation("hand", $player_id) == 0
        ) {
            $can_dismiss = false;
        }

        return $can_dismiss;
    }

    function discardAllKeptSpecies($board_position, $keeper_name)
    {
        $player_id = $this->getActivePlayerId();

        $discarded_species = $this->species->getCardsInLocation("board:" . $board_position, $player_id);
        $discarded_species_nbr = count($discarded_species);

        if ($discarded_species_nbr > 0) {
            $this->species->moveAllCardsInLocation("board:" . $board_position, "deck", $player_id);
            $this->incStat($discarded_species_nbr, "discarded_saved_species", $player_id);

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


            $score = 0;
            foreach ($discarded_species as $species) {
                $score -= $this->species_info[$species["type_arg"]]["points"];
            }
            $this->updateScore($player_id, $score);
            $this->updateHighestSaved();
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
        $backup_id = $this->getGameStateValue("selectedBackup");
        $species_id = $this->getGameStateValue("selectedSpecies");

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
        $players = $this->loadPlayersBasicInfos();
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
        $players = $this->loadPlayersBasicInfos();
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

    function canLiveInQuarantine($species_id, $quarantine, $player_id)
    {
        $canLiveInQuarantine = false;

        $species_habitats = $this->species_info[$species_id]["habitat"];
        $open_quarantines = $this->getOpenQuarantines()[$player_id];

        if (($quarantine === "ALL" || in_array($quarantine, $species_habitats)) && in_array($quarantine, $open_quarantines)) {
            $canLiveInQuarantine = true;
        }

        return $canLiveInQuarantine;
    }

    function getPossibleQuarantines($species_id, $player_id)
    {
        $possible_quarantines = array();

        foreach ($this->quarantines as $quarantine) {
            if ($this->canLiveInQuarantine($species_id, $quarantine, $player_id)) {
                $possible_quarantines[$quarantine] = $quarantine;
            }
        }

        return $possible_quarantines;
    }

    function getSpeciesCounters()
    {
        $counters = array();

        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $species_nbr = 0;
            for ($position = 1; $position <= 4; $position++) {
                $species_nbr += $this->species->countCardsInLocation("board:" . $position, $player_id);
            }

            $counters[$player_id] = $species_nbr;
        }

        return $counters;
    }

    function updateHighestSaved()
    {
        $highest = 0;
        foreach ($this->getSpeciesCounters() as $counter) {
            if ($counter > $highest) {
                $highest = $counter;
            }
        }

        $this->setGameStateValue("highestSaved", $highest);
        return $highest;
    }

    function updateScore($player_id, $score)
    {
        $this->DbQuery("UPDATE player SET player_score=player_score+$score WHERE player_id='$player_id'");
        $new_scores = 0;
        $collection = $this->getCollectionFromDb("SELECT player_score FROM player WHERE player_id='$player_id'");
        foreach ($collection as $player) {
            $new_scores = $player['player_score'];
        }

        if ($this->isRealTimeScoreTracking()) {
            $this->notifyAllPlayers("newScores", '', array('player_id' => $player_id, 'new_scores' => $new_scores));
        }
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
            $message,
            array(
                "player_name" => $this->getActivePlayerName(),
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

    function isOutOfActions($notify = true)
    {
        $player_id = $this->getActivePlayerId();

        $used_main_action = $this->getGameStateValue("mainAction") > 0;
        $can_new_species = $this->getEmptyColumnNbr() >= 2;
        $used_free_action = $this->getGameStateValue("freeAction") > 0;
        $can_conservation_fund = $this->resources->countCardsInLocation("hand", $player_id) > 0 && !$used_main_action && !$used_free_action;
        $can_zoo_help = $this->canZooHelp();

        if ($used_main_action && !$can_conservation_fund && !$can_new_species && !$can_zoo_help) {
            if ($notify) {
                $this->notifyPlayer(
                    $player_id,
                    "outOfActions",
                    clienttranslate('You are out of actions. The turn is automatically finished'),
                    array()
                );
            }
            return true;
        }

        return false;
    }

    function getPossibleZoos()
    {
        $possible_zoos = array();
        $species_counters = $this->getSpeciesCounters();

        $active_player_id = $this->getActivePlayerId();
        $active_counter = $species_counters[$active_player_id];

        foreach ($species_counters as $player_id => $counter) {
            if ($active_counter <= $counter && $active_player_id != $player_id) {
                $possible_zoos[$player_id] = $player_id;
            }
        }

        return $possible_zoos;
    }

    function canZooHelp()
    {
        return count($this->getPossibleZoos()) > 0 && $this->getGameStateValue("mainAction") == 2 && $this->getGameStateValue("zooHelp") == 0;
    }

    function moveToHelpZoo($card, $quarantine, $selected_zoo, $automatic = false)
    {
        $this->species->moveCard($card["id"], "quarantine:" . $quarantine, $selected_zoo);
        $this->updateScore($selected_zoo, -2);

        $quarantine_label = $quarantine === "ALL" ? "generic" : $quarantine;

        $selected_zoo_info = $this->loadPlayersBasicInfos()[$selected_zoo];

        $species_id = $card["type_arg"];

        $message = $automatic ? clienttranslate('The ${species_name} is automatically moved to ${player_name}&apos;s ${quarantine_label} quarantine')
            : clienttranslate('${player_name} moves the ${species_name} to his ${quarantine_label} quarantine');

        $this->notifyAllPlayers(
            "quarantineSpecies",
            $message,
            array(
                "player_id" => $selected_zoo,
                "player_name" => $selected_zoo_info["player_name"],
                "player_color" => $selected_zoo_info["player_color"],
                "species_id" => $species_id,
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "shop_position" => $card["location_arg"],
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "quarantined_species" => $this->getQuarantinedSpecies(),
                "visible_species" => $this->getVisibleSpecies(),
                "open_quarantines" => $this->getOpenQuarantines(),
            )
        );
    }

    function isLastTurn()
    {
        return $this->getGameStateValue("lastTurn") >= 1;
    }

    function calcRegularPoints($player_id)
    {
        $regular_points = array();

        for ($position = 1; $position <= 4; $position++) {
            $regular_points[$position] = null;

            $keepers_in_location = $this->keepers->getCardsInLocation("board:" . $position, $player_id);
            $keeper = array_shift($keepers_in_location);

            if ($keeper !== null) {
                $keeper_id = $keeper["type_arg"];
                $regular_points[$position][$keeper_id] = 0;

                $species_in_location = $this->species->getCardsInLocation("board:" . $position, $player_id);

                if (count($species_in_location) == 3) {
                    $keeper_points = $this->keepers_info[$keeper_id]["level"];

                    $regular_points[$position][$keeper_id] += $keeper_points;
                    $this->incStat(1, "keepers_completed", $player_id);
                    $this->incStat($keeper_points, "points_keepers_completed", $player_id);
                }

                foreach ($species_in_location as $species) {
                    $species_id = $species["type_arg"];
                    $species_points =  $this->species_info[$species_id]["points"];

                    $regular_points[$position][$keeper_id] += $species_points;
                    $this->incStat($species_points, "points_species_saved", $player_id);
                }
            }
        }

        return $regular_points;
    }

    function calcQuarantinePenalties($player_id)
    {
        $quarantine_penalties = array();
        foreach ($this->quarantines as $quarantine) {
            $quarantine_penalties[$quarantine] = null;
            $species_in_quarantine = $this->species->getCardsInLocation("quarantine:" . $quarantine, $player_id);
            $species = array_shift($species_in_quarantine);

            if ($species !== null) {
                $species_id = $species["type_arg"];

                $quarantine_penalties[$quarantine] = $species_id;
                $this->incStat(1, "quarantined_species", $player_id);
            }
        }
        return $quarantine_penalties;
    }

    function sumKeepersLevels($player_id)
    {
        $sum = 0;
        foreach ($this->getKeepersAtHouses()[$player_id] as $location) {
            foreach ($location as $keeper) {
                if (is_array($keeper) && count($keeper) > 0) {
                    $keeper_id = $keeper["card_type_arg"];
                    $sum += $this->keepers_info[$keeper_id]["level"];
                }
            }
        }

        return $sum;
    }

    function calcObjectiveBonus($player_id)
    {
        $keepers_levels = $this->sumKeepersLevels($player_id);

        if (!$this->hasSecretObjectives() || $keepers_levels < 7) {
            return null;
        }

        $location = $this->objectives->getCardsInLocation("hand", $player_id);
        $objective = array_shift($location);
        $objective_id = $objective["type_arg"];
        $objective_info = $this->objectives_info[$objective_id];

        $target = $keepers_levels <= 13 ? $objective_info["targets"][$keepers_levels] : $objective_info["targets"][14];
        $bonus = $target["bonus"];
        $condition = $target["condition"];

        $saved_species = $this->getSavedSpecies()[$player_id];
        $total_bonus = 0;
        foreach ($saved_species as $species_in_location) {
            if ($species_in_location !== null) {
                foreach ($species_in_location as $species_id => $species) {
                    $species_info = $this->species_info[$species_id];
                    $species_fields = array_keys($species_info);

                    foreach ($condition as $condition_field => $condition_value) {
                        if (in_array($condition_field, $species_fields)) {
                            $species_value = $species_info[$condition_field];

                            if (
                                (is_array($species_value) && count(array_intersect($species_value, $condition_value)) > 0) ||
                                (!is_array($species_value) && in_array($species_value, $condition_value))
                            ) {
                                $total_bonus += $bonus;
                            }
                            break;
                        }
                    }
                }
            }
        }

        $this->updateScore($player_id, $total_bonus);
        $this->setStat($total_bonus, "points_objectives", $player_id);

        $objective["bonus"] = $total_bonus;

        return $objective;
    }

    function countSpeciesByStatus($player_id)
    {
        $species_by_status = array();

        foreach ($this->status as $status) {
            $species_by_status[$status] = 0;
        }

        for ($position = 1; $position <= 4; $position++) {
            foreach ($this->species->getCardsInLocation("board:" . $position, $player_id) as $species) {
                $species_id = $species["type_arg"];
                $status = $this->species_info[$species_id]["status"];

                $species_by_status[$status]++;
            }
        }

        return $species_by_status;
    }

    function calcTieBreakers($player_id)
    {
        $species_by_status = $this->countSpeciesByStatus($player_id);

        $tie_breakers = 0;

        foreach ($this->status as $weight => $status) {
            $tie_breakers += $species_by_status[$status] * $weight;
        }

        $this->DbQuery("UPDATE player SET player_score_aux=$tie_breakers WHERE player_id='$player_id'");

        return $tie_breakers;
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
        $this->checkAction("pass");

        $this->gamestate->nextState("pass");
    }

    function hireKeeper($pile)
    {
        $this->checkAction("hireKeeper");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        $keepers_hired_nbr = 0;

        for ($position = 1; $position <= 4; $position++) {
            $keepers_hired_nbr += $this->keepers->countCardsInLocation("board:" . $position, $player_id);
        }

        if ($keepers_hired_nbr >= 4) {
            throw new BgaVisibleSystemException("You can't have more than 4 keepers in play");
        }

        $board_position = 0;
        for ($position = 1; $position <= 4; $position++) {
            if ($this->keepers->countCardsInLocation("board:" . $position, $player_id) < 1) {
                $board_position = $position;
                break;
            }
        }

        $keeper = $this->keepers->pickCardForLocation("deck:" . $pile, "board:" . $board_position, $player_id);

        if ($keeper === null) {
            throw new BgaVisibleSystemException("The selected pile is out of cards");
        }

        $card_id = $keeper["id"];

        $pile_counters = $this->getPileCounters();

        $this->DbQuery("UPDATE keeper SET pile=$pile WHERE card_id=$card_id");

        $this->notifyAllPlayers(
            "hireKeeper",
            clienttranslate('${player_name} hires ${keeper_name} from pile ${pile}'),
            array(
                "player_id" => $this->getActivePlayerId(),
                "player_name" => $this->getActivePlayerName(),
                "keeper_name" => $keeper["type"],
                "keeper_id" => $keeper["type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops()
            )
        );

        $this->setGameStateValue("mainAction", 5);

        $this->gamestate->nextState("betweenActions");
    }

    function dismissKeeper($board_position)
    {
        $this->checkAction("dismissKeeper");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = $this->getActivePlayerId();

        if (!$this->canDismissKeeper($board_position, $player_id)) {
            throw new BgaVisibleSystemException("Dismissing this keeper would make you unable to continue this match");
        }

        $keepers_at_houses_nbr = 0;
        for ($position = 1; $position <= 4; $position++) {
            $keepers_at_houses_nbr += $this->keepers->countCardsInLocation("board:" . $position, $player_id);
        }

        if ($keepers_at_houses_nbr === 0) {
            throw new BgaVisibleSystemException("You don't have any keeper to dismiss");
        }

        $pile = 0;
        $keepers_info = $this->keepers_info;
        $keeper = null;
        $keeper_id = null;

        foreach ($this->getKeepersAtHouses()[$player_id][$board_position] as $card) {
            $pile = $card["pile"];
            $keeper_level = $keepers_info[$card["card_type_arg"]]["level"];
            $keeper = $card;
            $keeper_id = $card["card_type_arg"];
        }

        if ($keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        if ($pile == 0 && $keeper_level === 1) {
            $this->setGameStateValue("selectedPosition", $board_position);
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
        $this->incStat(1, "keepers_dismissed", $player_id);

        $pile_counters = $this->getPileCounters();

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is returned to the bottom of pile ${pile}'),
            array(
                "player_id" => $this->getActivePlayerId(),
                "player_name" => $this->getActivePlayerName(),
                "keeper_name" => $keeper["card_type"],
                "keeper_id" => $keeper_id,
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
            )
        );

        $this->discardAllKeptSpecies($board_position, $keeper["card_type"]);

        $this->setGameStateValue("mainAction", 6);

        $this->gamestate->nextState("betweenActions");
    }

    function selectDismissedPile($pile)
    {
        $this->checkAction("selectDismissedPile");

        $board_position = $this->getGameStateValue("selectedPosition");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        if ($pile < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid keeper pile");
        }

        $player_id = $this->getActivePlayerId();

        $keeper = null;
        $keeper_id = null;

        foreach ($this->getKeepersAtHouses()[$player_id][$board_position] as $card) {
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
        $this->incStat(1, "keepers_dismissed", $player_id);

        $pile_counters = $this->getPileCounters();

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is sent the bottom of pile ${pile}. ${left_in_pile} keeper(s) in the pile'),
            array(
                "player_id" => $this->getActivePlayerId(),
                "player_name" => $this->getActivePlayerName(),
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

        $this->DbQuery("UPDATE keeper SET pile=$pile WHERE card_id='$keeper_card_id'");

        $this->discardAllKeptSpecies($board_position, $keeper["card_type"]);

        $this->setGameStateValue("mainAction", 6);

        $this->gamestate->nextState("betweenActions");
    }

    function replaceKeeper($board_position)
    {
        $this->checkAction("replaceKeeper");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        if (!$this->canDismissKeeper($board_position, $player_id)) {
            throw new BgaVisibleSystemException("Replacing this keeper would make you unable to continue this match");
        }

        $keepers_at_houses_nbr = 0;
        for ($position = 1; $position <= 4; $position++) {
            $keepers_at_houses_nbr += $this->keepers->countCardsInLocation("board:" . $position, $player_id);
        }

        if ($keepers_at_houses_nbr === 0) {
            throw new BgaVisibleSystemException("You don't have any keeper to replace");
        }

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $keeper = null;

        foreach ($this->getKeepersAtHouses()[$player_id][$board_position] as $card) {
            $keeper = $card;
        }

        if ($keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        $this->setGameStateValue("selectedPosition", $board_position);

        $this->gamestate->nextState("selectReplacedPile");
    }

    function selectReplacedPile($pile)
    {
        $this->checkAction("selectReplacedPile");

        $player_id = $this->getActivePlayerId();

        $board_position = $this->getGameStateValue("selectedPosition");

        $replaced_keeper = null;

        foreach ($this->getKeepersAtHouses()[$player_id][$board_position] as $card) {
            $replaced_keeper = $card;
        }

        if ($replaced_keeper === null) {
            throw new BgaVisibleSystemException("Keeper not found");
        }

        $hired_keeper = $this->keepers->pickCardForLocation("deck:" . $pile, "board:" . $board_position, $player_id);

        if ($hired_keeper === null) {
            throw new BgaUserException($this->_("The selected pile is out of cards"));
        }

        $replaced_keeper_id = $replaced_keeper["card_type_arg"];

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper && $replaced_keeper_id == $completed_keeper["type_arg"]) {
            $score = $this->keepers_info[$replaced_keeper_id]["level"];
            $this->updateScore($player_id, -$score);
        }

        $replaced_card_id = $replaced_keeper["card_id"];
        $this->DbQuery("UPDATE keeper SET pile=$pile WHERE card_id=$replaced_card_id");
        $this->keepers->insertCardOnExtremePosition($replaced_card_id, "deck:" . $pile, false);;

        $this->notifyAllPlayers(
            "hireKeeper",
            "",
            array(
                "player_id" => $this->getActivePlayerId(),
                "keeper_id" => $hired_keeper["type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $this->getPileCounters(),
                "piles_tops" => $this->getPilesTops(),
            )
        );

        $this->incStat(1, "keepers_dismissed", $player_id);

        $this->notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} replaces ${replaced_keeper_name} by ${hired_keeper_name}, from pile ${pile}'),
            array(
                "player_id" => $this->getActivePlayerId(),
                "player_name" => $this->getActivePlayerName(),
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

        $this->setGameStateValue("mainAction", 7);

        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngKeepers()
    {
        $this->checkAction("cancelMngKeepers");
        $this->setGameStateValue("selectedPosition", 0);
        $this->setGameStateValue("mainAction", 0);

        $this->gamestate->nextState("cancel");
    }

    function collectResources()
    {
        $this->checkAction("collectResources");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        if ($this->isBagEmpty()) {
            throw new BgaUserException($this->_("The bag is out of resources"));
        }

        $player_id = $this->getActivePlayerId();

        $species_nbr = $this->getSpeciesCounters()[$player_id];
        $collected_nbr = $species_nbr <= 6 ? $species_nbr : 6;

        if ($collected_nbr === 0) {
            throw new BgaUserException($this->_("You must have at least one saved species to collect resources"));
        }

        $this->resources->shuffle("deck");
        $collected_resources = $this->resources->pickCards($collected_nbr, "deck", $player_id);
        $collected_nbr = count($collected_resources);

        $collected_plant = $this->filterByResourceType("plant", $collected_resources);
        $collected_plant_nbr = count($collected_plant);

        $collected_meat = $this->filterByResourceType("meat", $collected_resources);
        $collected_meat_nbr = count($collected_meat);

        $collected_kit = $this->filterByResourceType("kit", $collected_resources);
        $collected_kit_nbr = count($collected_kit);

        $this->notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} collects ${collected_nbr} resource(s): ${collected_plant_nbr} plant(s), ${collected_meat_nbr} meat/fish, ${collected_kit_nbr} medical kit(s)'),
            array(
                "player_name" => $this->getActivePlayerName(),
                "player_id" => $player_id,
                "collected_nbr" => $collected_nbr,
                "collected_plant_nbr" => $collected_plant_nbr,
                "collected_meat_nbr" => $collected_meat_nbr,
                "collected_kit_nbr" => $collected_kit_nbr,
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );

        $this->setGameStateValue("mainAction", 1);

        $this->gamestate->nextState("betweenActions");
    }

    function exchangeResources()
    {
        $this->checkAction("exchangeResources");

        if ($this->getGameStateValue("freeAction") || $this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("The conservation fund can't be used after any other action"));
        }

        if ($this->isBagEmpty()) {
            throw new BgaUserException($this->_("The bag is out of resources"));
        }

        $this->gamestate->nextState("exchangeCollection");
    }

    function collectFromExchange($choosen_nbr)
    {
        $this->checkAction("collectFromExchange");

        $player_id = $this->getActivePlayerId();

        if ($choosen_nbr >= $this->resources->countCardsInLocation("hand", $player_id)) {
            throw new BgaVisibleSystemException("You can't collect more resources from the fund than what you have in hand");
        }

        if ($choosen_nbr > 5) {
            throw new BgaVisibleSystemException("You can't collect more than 5 resources from the fund");
        }

        $this->resources->shuffle("deck");
        $collected_resources = $this->resources->pickCards($choosen_nbr, "deck", $player_id);
        $collected_nbr = count($collected_resources);

        $return_nbr = $collected_nbr * 2;
        $this->setGameStateValue("totalToReturn", $return_nbr);

        $collected_plant = $this->filterByResourceType("plant", $collected_resources);
        $collected_plant_nbr = count($collected_plant);

        $collected_meat = $this->filterByResourceType("meat", $collected_resources);
        $collected_meat_nbr = count($collected_meat);

        $collected_kit = $this->filterByResourceType("kit", $collected_resources);
        $collected_kit_nbr = count($collected_kit);

        $this->notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} activates the conservation fund and collects ${collected_nbr} resource(s): ${collected_plant_nbr} plant(s), ${collected_meat_nbr} meat/fish, ${collected_kit_nbr} medical kit(s). ${return_nbr} resource(s) must be returned to the bag'),
            array(
                "player_name" => $this->getActivePlayerName(),
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
        $this->setGameStateValue("freeAction", 3);

        $this->gamestate->nextState("exchangeReturn");
    }

    function cancelExchange()
    {
        $this->checkAction("cancelExchange");
        $this->setGameStateValue("freeAction", 0);

        $this->gamestate->nextState("cancel");
    }

    function returnFromExchange($lastly_returned_nbr, $type)
    {
        $this->checkAction("returnFromExchange");
        $player_id = $this->getActivePlayerId();

        $resources_in_hand = $this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id);
        $resources_returned = array_slice($resources_in_hand, 0, $lastly_returned_nbr, true);
        $keys = array_keys($resources_returned);

        $this->resources->moveCards($keys, "deck");

        $previously_returned_nbr = $this->getGameStateValue("previouslyReturned");
        $returned_total = $previously_returned_nbr + $lastly_returned_nbr;
        $this->setGameStateValue("previouslyReturned", $previously_returned_nbr + $lastly_returned_nbr);

        $to_return = $this->getGameStateValue("totalToReturn") - $returned_total;

        $this->notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} ${type_label}(s) to the bag'),
            array(
                "i18n" => array("type_label"),
                "player_name" => $this->getActivePlayerName(),
                "player_id" => $player_id,
                "returned_nbr" => $lastly_returned_nbr,
                "type" => $type,
                "type_label" => $this->resource_types[$type]["label"],
                "resource_counters" => $this->getResourceCounters(),
                "bag_counters" => $this->getBagCounters(),
            )
        );

        $resources_by_type = array(
            "plant" => $this->resources->getCardsOfTypeInLocation("plant", null, "hand", $player_id),
            "meat" => $this->resources->getCardsOfTypeInLocation("meat", null, "hand", $player_id),
            "kit" => $this->resources->getCardsOfTypeInLocation("kit", null, "hand", $player_id),
        );

        $types_with_resources = array();
        foreach ($resources_by_type as $type => $type_resources) {
            if (count($type_resources) > 0) {
                $types_with_resources[$type] = $type_resources;
            }
        }

        if (count($types_with_resources) == 1) {
            foreach ($types_with_resources as $type => $type_resources) {
                $keys = array_keys($type_resources);
                $needed_resources = array_slice($keys, 0, $to_return);
                $this->resources->moveCards($needed_resources, "deck");

                $this->notifyAllPlayers(
                    "returnResources",
                    clienttranslate('${player_name} automatically returns ${returned_nbr} ${type_label}(s) to the bag'),
                    array(
                        "i18n" => array("type_label"),
                        "player_name" => $this->getActivePlayerName(),
                        "player_id" => $player_id,
                        "returned_nbr" => $to_return,
                        "type" => $type,
                        "type_label" => $this->resource_types[$type]["label"],
                        "resource_counters" => $this->getResourceCounters(),
                        "bag_counters" => $this->getBagCounters(),
                    )
                );

                $to_return = 0;
            }
        }

        if ($to_return === 0) {
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->gamestate->nextState("betweenExchangeReturns");
    }

    function returnExcess($lastly_returned_nbr, $type)
    {
        $this->checkAction("returnExcess");
        $player_id = $this->getActivePlayerId();

        $resources_in_hand = $this->resources->getCardsOfTypeInLocation($type, null, "hand", $player_id);
        $resources_returned = array_slice($resources_in_hand, 0, $lastly_returned_nbr, true);
        $keys = array_keys($resources_returned);

        $this->resources->moveCards($keys, "deck");

        $previously_returned_nbr = $this->getGameStateValue("previouslyReturned");
        $returned_total = $previously_returned_nbr + $lastly_returned_nbr;
        $this->setGameStateValue("previouslyReturned", $previously_returned_nbr + $lastly_returned_nbr);

        $to_return = $this->getGameStateValue("totalToReturn") - $returned_total;

        $this->notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} ${type_label}(s) to the bag as excess'),
            array(
                "i18n" => array("type_label"),
                "player_name" => $this->getActivePlayerName(),
                "player_id" => $player_id,
                "returned_nbr" => $lastly_returned_nbr,
                "type" => $type,
                "type_label" => $this->resource_types[$type]["label"],
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

    function zombieReturnResources()
    {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            if ($player["player_zombie"] == 1) {
                $this->resources->moveAllCardsInLocation("hand", "deck", $player_id);

                foreach ($this->getResourceCounters()[$player_id] as $type => $counter) {
                    $this->notifyAllPlayers(
                        "returnResources",
                        "",
                        array(
                            "player_name" => $this->getActivePlayerName(),
                            "player_id" => $player_id,
                            "returned_nbr" => $counter,
                            "type" => $type,
                            "resource_counters" => $this->getResourceCounters(),
                            "bag_counters" => $this->getBagCounters(),
                        )
                    );
                }
            }
        }
    }

    function saveQuarantined($species_id)
    {
        $this->checkAction("saveQuarantined");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        $can_save = count($this->getSavableQuarantined()) > 0;

        if (!$can_save) {
            throw new BgaUserException($this->_("You can't save any of the available species"));
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
            throw new BgaUserException($this->_("You don't have the required resources or keepers to save this species"));
        }

        $this->setGameStateValue("selectedSpecies", $species_card["id"]);
        $this->gamestate->nextState("selectQuarantinedKeeper");
    }

    function selectQuarantinedKeeper($board_position)
    {
        $this->checkAction("selectQuarantinedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = $this->getActivePlayerId();

        $species = $this->species->getCard($this->getGameStateValue("selectedSpecies"));

        if ($species === null) {
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
            throw new BgaUserException($this->_("You can't assign this species to this keeper"));
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
                    "",
                    array(
                        // "i18n" => array("type_label"),
                        // "player_name" => $this->getActivePlayerName(),
                        // "species_name" => array(
                        //     "log" => $this->styledSpeciesName(),
                        //     "args" => array(
                        //         "i18n" => array("species_name_tr"),
                        //         "species_name_tr" => $this->species_info[$species_id]["name"]
                        //     )
                        // ),
                        // "type_label" => $this->resource_types[$type]["label"],
                        "player_id" => $player_id,
                        "returned_nbr" => $cost,
                        "type" => $type,
                        "resource_counters" => $this->getResourceCounters(),
                        "bag_counters" => $this->getBagCounters(),
                    )
                );
            }
        }

        $points = $this->species_info[$species_id]["points"];

        $quarantine = explode(":", $species["location"])[1];
        $quarantine_label = $quarantine === "ALL" ? "generic" : $quarantine;

        $this->notifyAllPlayers(
            "saveQuarantined",
            clienttranslate('${player_name} saves the ${species_name} from his ${quarantine_label} quarantine and assigns it to ${keeper_name}. ${species_points} point(s) scored'),
            array(

                "player_name" => $this->getActivePlayerName(),
                "player_id" => $player_id,
                "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "species_id" => $species_id,
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

        $this->updateScore($player_id, $points + 2);
        $this->incStat(1, "species_saved", $player_id);

        if ($this->species_info[$species_id]["status"] === "CR") {
            $this->incStat(1, "cr_species_saved", $player_id);
        }

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper !== null) {
            $completed_id = $completed_keeper["type_arg"];

            if ($keeper_id == $completed_id) {
                $keeper_level = $this->keepers_info[$keeper_id]["level"];

                $this->notifyAllPlayers("completeKeeper", clienttranslate('${player_name} completes ${keeper_name} and scores ${keeper_level} point(s)'),  array(
                    "player_name" => $this->getActivePlayerName(),
                    "player_id" => $player_id,
                    "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                    "keeper_id" => $keeper_id,
                    "keeper_name" => $keeper["type"],
                    "keeper_level" => $keeper_level,
                    "board_position" => $board_position,
                    "completed_keepers" => $this->getCompletedKeepers(),
                ));

                $this->updateScore($player_id, $keeper_level);
            }
        }

        $this->updateHighestSaved();

        $this->setGameStateValue("mainAction", 2);

        $this->gamestate->nextState("betweenActions");
    }

    function saveSpecies($shop_position)
    {
        $this->checkAction("saveSpecies");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $can_save = count($this->getSavableSpecies()) > 0;

        if (!$can_save) {
            throw new BgaUserException($this->_("You can't save any of the available species"));
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
            throw new BgaUserException($this->_("You don't have the required resources or keepers to save this species"));
        }

        $this->setGameStateValue("selectedSpecies", $species_card_id);
        $this->gamestate->nextState("selectAssignedKeeper");
    }

    function selectAssignedKeeper($board_position)
    {
        $this->checkAction("selectAssignedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaVisibleSystemException("Invalid board position");
        }

        $player_id = $this->getActivePlayerId();

        $species = $this->species->getCard($this->getGameStateValue("selectedSpecies"));

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
            throw new BgaUserException($this->_("You can't assign this species to this keeper"));
        }

        $this->setGameStateValue("mainAction", 2);

        $this->species->moveAllCardsInLocation(
            $species["location"],
            "board:" . $board_position,
            $species["location_arg"],
            $player_id
        );

        $returned_cost = $this->returnCost($species_id);

        foreach ($returned_cost as $type => $cost) {
            if ($cost > 0) {
                $this->incStat($cost, "resources_consumed", $player_id);

                $this->notifyAllPlayers(
                    "returnResources",
                    "",
                    array(
                        // "i18n" => array("type_label"),
                        // "player_name" => $this->getActivePlayerName(),
                        // "type_label" => $this->resource_types[$type]["label"],
                        // "species_name" => array(
                        //     "log" => $this->styledSpeciesName(),
                        //     "args" => array(
                        //         "i18n" => array("species_name_tr"),
                        //         "species_name_tr" => $this->species_info[$species_id]["name"]
                        //     )
                        // ),
                        "player_id" => $player_id,
                        "returned_nbr" => $cost,
                        "type" => $type,
                        "resource_counters" => $this->getResourceCounters(),
                        "bag_counters" => $this->getBagCounters(),
                    )
                );
            }
        }

        $points = $this->species_info[$species_id]["points"];

        $this->notifyAllPlayers(
            "saveSpecies",
            clienttranslate('${player_name} saves the ${species_name} and assigns it to ${keeper_name}. ${species_points} point(s) scored'),
            array(

                "player_name" => $this->getActivePlayerName(),
                "player_id" => $player_id,
                "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
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
                "can_zoo_help" => $this->canZooHelp(),
                "open_quarantines" => $this->getOpenQuarantines()
            )
        );

        $this->updateScore($player_id, $points);
        $this->incStat(1, "species_saved", $player_id);

        if ($this->species_info[$species_id]["status"] === "CR") {
            $this->incStat(1, "cr_species_saved", $player_id);
        }

        $completed_keeper = $this->getCompletedKeepers()[$player_id][$board_position];

        if ($completed_keeper !== null) {
            $completed_id = $completed_keeper["type_arg"];

            if ($keeper_id == $completed_id) {
                $keeper_level = $this->keepers_info[$keeper_id]["level"];

                $this->notifyAllPlayers("completeKeeper", clienttranslate('${player_name} completes ${keeper_name} and scores ${keeper_level} point(s)'),  array(
                    "player_name" => $this->getActivePlayerName(),
                    "player_id" => $player_id,
                    "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                    "keeper_id" => $keeper_id,
                    "keeper_name" => $keeper["type"],
                    "keeper_level" => $keeper_level,
                    "board_position" => $board_position,
                    "completed_keepers" => $this->getCompletedKeepers(),
                ));

                $this->updateScore($player_id, $keeper_level);
            }
        }

        $this->updateHighestSaved();

        $this->gamestate->nextState("betweenActions");
    }

    function lookAtBackup(
        $shop_position,
        $backup_id
    ) {
        $this->checkAction("lookAtBackup");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

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
                "player_id" => $player_id,
                "species_id" => $species_id,
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "shop_position" => $shop_position,
                "backup_id" => $backup_id,
                "backup_species" => $this->getBackupSpecies(),
            ),
        );

        $this->setGameStateValue("selectedSpecies", $species_id);
        $this->setGameStateValue("selectedBackup", $backup_id);
        $this->setGameStateValue("selectedPosition", $shop_position);

        $this->gamestate->nextState("mngBackup");
    }

    function discardBackup()
    {
        $this->checkAction("discardBackup");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        $species_id = $this->getGameStateValue("selectedSpecies");
        $backup_id = $this->getGameStateValue("selectedBackup");
        $shop_position = $this->getGameStateValue("selectedPosition");

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
                "player_name" => $this->getActivePlayerName(),
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
                "player_name" => $this->getActivePlayerName(),
                "shop_position" => $shop_position,
                "backup_id" => $backup_id,
                "backup_species" => $this->getBackupSpecies(),
            ),
        );

        if ($this->getGameStateValue("secondStep") > 0) {
            $this->setGameStateValue("mainAction", 4);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->setGameStateValue("selectedBackup", 0);
        $this->setGameStateValue("selectedPosition", 0);
        $this->setGameStateValue("secondStep", 1);

        $this->autoDrawNewSpecies();

        $this->gamestate->nextState("mngSecondSpecies");
    }

    function quarantineBackup()
    {
        $this->checkAction("quarantineBackup");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $species_id = $this->getGameStateValue("selectedSpecies");
        $player_id = $this->getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_backup");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $is_quarantinable = false;

        foreach ($this->getOpenQuarantines()[$player_id] as $quarantine) {
            if ($this->canLiveInQuarantine($species_id, $quarantine, $player_id)) {
                $is_quarantinable = true;
                break;
            }
        }

        if (!$is_quarantinable) {
            throw new BgaUserException($this->_("You can't quarantine this species"));
        }

        $this->setGameStateValue("selectedSpecies", $species_id);

        $this->gamestate->nextState("selectBackupQuarantine");
    }

    function selectBackupQuarantine($quarantine)
    {
        $this->checkAction("selectBackupQuarantine");

        $player_id = $this->getActivePlayerId();
        $species_id = $this->getGameStateValue("selectedSpecies");

        $species_in_location = $this->species->getCardsInLocation("shop_backup");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        if (!$this->canLiveInQuarantine($species_id, $quarantine, $player_id)) {
            throw new BgaUserException($this->_("This species can't live in that quarantine"));
        }

        $this->species->moveCard($species["id"], "quarantine:" . $quarantine, $player_id);

        $quarantine_label = $quarantine;
        if ($quarantine_label === "ALL") {
            $quarantine_label = "generic";
        }

        $this->notifyAllPlayers(
            "quarantineBackup",
            clienttranslate('${player_name} moves the ${species_name} to his ${quarantine_label} quarantine'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getActivePlayerName(),
                "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_id" => $species_id,
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "shop_position" => $species["location_arg"],
                "backup_id" => $this->getGameStateValue("selectedBackup"),
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "quarantined_species" => $this->getQuarantinedSpecies(),
                "visible_species" => $this->getVisibleSpecies(),
                "open_quarantines" => $this->getOpenQuarantines(),
            )
        );

        $this->updateScore($player_id, -2);

        if ($this->getGameStateValue("secondStep") == 0) {
            $this->autoDrawNewSpecies();
            $this->setGameStateValue("secondStep", 1);
            $this->setGameStateValue("selectedSpecies", 0);
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        $this->setGameStateValue("mainAction", 3);
        $this->gamestate->nextState("betweenActions");
    }

    function discardSpecies(
        $species_id
    ) {
        $this->checkAction("discardSpecies");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $shop_position = $species["location_arg"];

        $this->species->insertCardOnExtremePosition($species["id"], "deck", false);
        $this->incStat(1, "discarded_species", $player_id);

        $this->notifyAllPlayers(
            "discardSpecies",
            clienttranslate('${player_name} moves the ${species_name} to the bottom of the deck'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getActivePlayerName(),
                "species_id" => $species_id,
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "shop_position" => $shop_position,
                "visible_species" => $this->getVisibleSpecies(),
            ),
        );

        if ($this->getGameStateValue("secondStep") > 0) {
            $this->setGameStateValue("mainAction", 4);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->autoDrawNewSpecies();
        $this->setGameStateValue("secondStep", 1);
        $this->gamestate->nextState("mngSecondSpecies");
    }

    function quarantineSpecies($species_id)
    {
        $this->checkAction("quarantineSpecies");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        $player_id = $this->getActivePlayerId();

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $quarantinable_species = array_keys($this->getQuarantinableSpecies()[$player_id]);

        if (!in_array($species_id, $quarantinable_species)) {
            throw new BgaUserException($this->_("You can't quarantine this species"));
        }

        $this->setGameStateValue("selectedSpecies", $species_id);

        $this->gamestate->nextState("selectQuarantine");
    }

    function selectQuarantine($quarantine)
    {
        $this->checkAction("selectQuarantine");

        $player_id = $this->getActivePlayerId();
        $species_id = $this->getGameStateValue("selectedSpecies");

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaSystemException("Species not found");
        }

        if (!$this->canLiveInQuarantine($species_id, $quarantine, $player_id)) {
            throw new BgaUserException($this->_("This species can't live in that quarantine"));
        }

        $this->species->moveCard($species["id"], "quarantine:" . $quarantine, $player_id);

        $quarantine_label = $quarantine;
        if ($quarantine_label === "ALL") {
            $quarantine_label = "generic";
        }

        $this->notifyAllPlayers(
            "quarantineSpecies",
            clienttranslate('${player_name} moves the ${species_name} to his ${quarantine_label} quarantine'),
            array(
                "player_id" => $player_id,
                "player_name" => $this->getActivePlayerName(),
                "player_color" => $this->loadPlayersBasicInfos()[$player_id]["player_color"],
                "species_id" => $species_id,
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                ),
                "shop_position" => $species["location_arg"],
                "quarantine" => $quarantine,
                "quarantine_label" => $quarantine_label,
                "quarantined_species" => $this->getQuarantinedSpecies(),
                "visible_species" => $this->getVisibleSpecies(),
                "open_quarantines" => $this->getOpenQuarantines()
            )
        );

        $this->updateScore($player_id, -2);

        if ($this->getGameStateValue("secondStep") == 0) {
            $this->autoDrawNewSpecies();
            $this->setGameStateValue("secondStep", 1);
            $this->setGameStateValue("selectedSpecies", 0);
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        $this->setGameStateValue("mainAction", 3);
        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngSpecies()
    {
        $this->checkAction("cancelMngSpecies");
        $this->setGameStateValue("selectedPosition", 0);

        $state_name = $this->gamestate->state()["name"];

        if ($state_name !== "selectBackupQuarantine") {
            $this->setGameStateValue("selectedSpecies", 0);
            $this->setGameStateValue("selectedBackup", 0);
        }

        if (
            $this->getGameStateValue("secondStep") > 0
            &&
            ($state_name === "selectQuarantine" || $state_name === "selectBackupQuarantine")
        ) {
            $this->gamestate->nextState("cancelQuarantine");
            return;
        }

        if ($state_name === "mngSecondSpecies") {
            $this->setGameStateValue("mainAction", 3);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->setGameStateValue("secondStep", 0);
        $this->gamestate->nextState("cancel");
    }

    function newSpecies()
    {
        $this->checkAction("newSpecies");

        $this->drawNewSpecies();

        $this->setGameStateValue("freeAction", 1);

        if ($this->gamestate->state()["name"] === "mngSecondSpecies") {
            $this->gamestate->nextState("mngSecondSpecies");
            return;
        }

        $this->gamestate->nextState("betweenActions");
    }

    function zooHelp($species_id)
    {
        $this->checkAction("zooHelp");

        if ($this->getGameStateValue("mainAction") != 2) {
            throw new BgaVisibleSystemException("The bonus action is only available after you save a species in the turn");
        }

        if ($this->getGameStateValue("zooHelp")) {
            throw new BgaVisibleSystemException("You already asked a zoo for help this turn");
        }

        if (!$this->canZooHelp()) {
            throw new BgaVisibleSystemException("No zoo can help with this species now");
        }

        $players = $this->loadPlayersBasicInfos();

        $this->setGameStateValue("selectedSpecies", $species_id);

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        $can_quarantine = false;
        foreach ($players as $player_id => $player) {
            $quarantinable_species = array_keys($this->getQuarantinableSpecies()[$player_id]);
            if (in_array($species_id, $quarantinable_species) && !$this->gamestate->isPlayerActive($player_id)) {
                $can_quarantine = true;
                break;
            }
        }

        if (!$can_quarantine) {
            throw new BgaUserException($this->_("No zoo can help with this species"));
        }

        $this->gamestate->nextState("selectZoo");
    }

    function selectZoo($selected_zoo)
    {
        $this->checkAction("selectZoo");

        $players_ids = array_keys($this->loadPlayersBasicInfos());

        if (!in_array($selected_zoo, $players_ids)) {
            throw new BgaVisibleSystemException("This player is not in the table");
        }

        if ($this->gamestate->isPlayerActive($selected_zoo)) {
            throw new BgaUserException($this->_("You can't select your own zoo"));
        }

        $species_id = $this->getGameStateValue("selectedSpecies");
        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        if (!in_array($selected_zoo, $this->getPossibleZoos())) {
            throw new BgaUserException($this->_("You can't ask this zoo for help"));
        }

        $player_id = $this->getActivePlayerId();

        $this->incStat(1, "zoo_help_asked", $player_id);
        $this->incStat(1, "zoo_help_given", $selected_zoo);

        $this->notifyAllPlayers(
            "zooHelp",
            clienttranslate('${selected_zoo_name} asks ${active_zoo_name} for help with the ${species_name}'),
            array(
                "selected_zoo_name" => $this->loadPlayersBasicInfos()[$selected_zoo]["player_name"],
                "active_zoo_name" => $this->getActivePlayerName(),
                "species_name" => array(
                    "log" => $this->styledSpeciesName(),
                    "args" => array(
                        "i18n" => array("species_name_tr"),
                        "species_name_tr" => $this->species_info[$species_id]["name"]
                    )
                )
            )
        );

        $this->setGameStateValue("selectedZoo", $selected_zoo);
        $this->setGameStateValue("zooHelp", 1);

        $possible_quarantines = $this->getPossibleQuarantines($species_id, $selected_zoo);

        if (count($possible_quarantines) == 1) {
            $quarantine = array_shift($possible_quarantines);
            $this->moveToHelpZoo($species, $quarantine, $selected_zoo, true);
            $this->gamestate->nextState("betweenActions");
            return;
        }

        $this->gamestate->nextState("activateZoo");
    }

    function selectHelpQuarantine($quarantine)
    {
        $this->checkAction("selectHelpQuarantine");

        $player_id = $this->getActivePlayerId();
        $species_id = $this->getGameStateValue("selectedSpecies");

        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        if ($species === null) {
            throw new BgaVisibleSystemException("Species not found");
        }

        if (!$this->canLiveInQuarantine($species_id, $quarantine, $player_id)) {
            throw new BgaUserException($this->_("This species can't live in that quarantine"));
        }

        $this->moveToHelpZoo($species, $quarantine, $player_id);

        $this->gamestate->nextState("activatePrevZoo");
    }

    function replaceObjective()
    {
        $this->checkAction("replaceObjective");

        if ($this->getGameStateValue("mainAction")) {
            throw new BgaUserException($this->_("You already used a main action this turn"));
        }

        if (!$this->hasSecretObjectives()) {
            throw new BgaVisibleSystemException("This action is not supported in this game mode");
        }

        $player_id = $this->getActivePlayerId();

        $location = $this->objectives->getCardsInLocation("hand", $player_id);
        $replaced_objective = array_shift($location);

        $this->objectives->insertCardOnExtremePosition($replaced_objective["id"], "deck", false);
        $new_objective = $this->objectives->pickCard("deck", $player_id);

        $this->notifyAllPlayers(
            "replaceObjective",
            clienttranslate('${player_name} moves his secret objective to the bottom of the deck and draws a new one'),
            array(
                "player_name" => $this->getActivePlayerName(),
                "player_id" => $this->getActivePlayerId(),
            )
        );

        $this->notifyPlayer(
            $player_id,
            "replaceObjectivePrivately",
            "",
            array(
                "player_name" => $this->getActivePlayerName(),
                "player_id" => $this->getActivePlayerId(),
                "replaced_objective_id" => $replaced_objective["type_arg"],
                "new_objective_id" => $new_objective["type_arg"]
            )
        );

        $this->setGameStateValue("mainAction", 9);

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
        $player_id = $this->getActivePlayerId();

        return array(
            "mainAction" => $this->getGameStateValue("mainAction"),
            "freeAction" => $this->getGameStateValue("freeAction"),
            "isBagEmpty" => $this->isBagEmpty(),
            "canZooHelp" => $this->canZooHelp(),
            "keepersAtHouses" => $this->getKeepersAtHouses(),
            "savableSpecies" => $this->getSavableSpecies(),
            "savableWithFund" => $this->getSavableWithFund(),
            "savableQuarantined" => $this->getSavableQuarantined(),
            "savableQuarantinedWithFund" => $this->getSavableQuarantinedWithFund(),
            "quarantinableSpecies" => $this->getQuarantinableSpecies(),
            "emptyColumnNbr" => $this->getEmptyColumnNbr(),
            "possibleZoos" => $this->getPossibleZoos(),
            "resourcesInHandNbr" => $this->resources->countCardsInLocation("hand", $player_id),
            "isLastTurn" => $this->isLastTurn(),
        );
    }

    function argExchangeCollection()
    {
        $player_id = $this->getActivePlayerId();

        $resources_in_hand_nbr = $this->resources->countCardsInLocation("hand", $player_id);

        return array("resources_in_hand_nbr" => $resources_in_hand_nbr, "freeAction" => $this->getGameStateValue("freeAction"));
    }

    function argExchangeReturn()
    {
        return array("to_return" => $this->getGameStateValue("totalToReturn") - $this->getGameStateValue("previouslyReturned"));
    }

    function argReturnExcess()
    {
        return array("to_return" => $this->getGameStateValue("totalToReturn") - $this->getGameStateValue("previouslyReturned"));
    }

    function argMngSecondSpecies()
    {
        return array("empty_column_nbr" => $this->getEmptyColumnNbr());
    }

    function argMngBackup()
    {
        $species = $this->getLookedBackup();
        $species_id = $species["type_arg"];
        return array(
            "_private" => array(
                "active" => array(
                    "i18n" => array("species_name_tr"),
                    "looked_backup" => $species,
                    "species_name" =>  $this->species_info[$species_id]["name"]
                )
            )
        );
    }

    function argSelectDismissedPile()
    {
        $player_id = $this->getActivePlayerId();
        $position = $this->getGameStateValue("selectedPosition");

        $keepers_in_location = $this->keepers->getCardsInLocation("board:" . $position, $player_id);
        $keeper = array_shift($keepers_in_location);

        return array(
            "keeper_name" => $keeper["type"], "keeper_id" => $keeper["type_arg"],
            "position" => explode(":", $keeper["location"])[1]
        );
    }

    function argSelectReplacedPile()
    {
        $player_id = $this->getActivePlayerId();
        $position = $this->getGameStateValue("selectedPosition");

        $keepers_in_location = $this->keepers->getCardsInLocation("board:" . $position, $player_id);
        $keeper = array_shift($keepers_in_location);

        return array(

            "keeper_name" => $keeper["type"],
            "keeper_id" => $keeper["type_arg"],
            "position" => explode(":", $keeper["location"])[1]
        );
    }
    function argSelectAssignedKeeper()
    {
        $species_card_id = $this->getGameStateValue("selectedSpecies");
        $species = $this->species->getCard($species_card_id);
        $species_id = $species["type_arg"];
        return array(
            "i18n" => array("species_name"),
            "species_name" =>  $this->species_info[$species_id]["name"],
            "species_id" => $species_id,
            "position" => $species["location_arg"],
            "assignable_keepers" => $this->getAssignableKeepers($species_id),
        );
    }

    function argSelectQuarantinedKeeper()
    {
        $species_card_id = $this->getGameStateValue("selectedSpecies");
        $species = $this->species->getCard($species_card_id);
        $species_id = $species["type_arg"];

        return array(
            "i18n" => array("species_name"),
            "species_name" => $this->species_info[$species_id]["name"],
            "species_id" => $species_id,
            "quarantine" => explode(":", $species["location"])[1],
            "assignable_keepers" => $this->getAssignableKeepers($species_id)
        );
    }

    function argSelectQuarantine()
    {
        $player_id = $this->getActivePlayerId();
        $species_id = $this->getGameStateValue("selectedSpecies");
        $species = $this->findCardByTypeArg($this->species->getCardsInLocation("shop_visible"), $species_id);

        return array(
            "i18n" => array("species_name"),
            "species_name" => $this->species_info[$species_id]["name"],
            "species_id" => $species_id,
            "position" => $species["location_arg"],
            "possible_quarantines" => $this->getPossibleQuarantines($species_id, $player_id),
        );
    }

    function argSelectBackupQuarantine()
    {
        $player_id = $this->getActivePlayerId();
        $species = $this->getLookedBackup();
        $species_id = $species["type_arg"];

        return array(
            "_private" => array(
                "active" => array(
                    "i18n" => array("species_name"),
                    "species_name" => $this->species_info[$species_id]["name"],
                    "looked_backup" => $species,
                    "possible_quarantines" => $this->getPossibleQuarantines($species_id, $player_id),
                )
            )
        );
    }

    function argSelectHelpQuarantine()
    {
        $player_id = $this->getActivePlayerId();
        $species_id = $this->getGameStateValue("selectedSpecies");
        $species = $this->findCardByTypeArg($this->species->getCardsInLocation("shop_visible"), $species_id);
        return array(

            "i18n" => array("species_name"),
            "species_name" => $this->species_info[$species_id]["name"],
            "species_id" => $species_id,
            "position" => $species["location_arg"],
            "possible_quarantines" => $this->getPossibleQuarantines($species_id, $player_id),
        );
    }

    function argSelectZoo()
    {
        $species_id = $this->getGameStateValue("selectedSpecies");
        $species_in_location = $this->species->getCardsInLocation("shop_visible");
        $species = $this->findCardByTypeArg($species_in_location, $species_id);

        return array(
            "i18n" => array("species_name"),
            "species_name" => $this->species_info[$species_id]["name"],
            "species_id" => $species_id,
            "position" => $species["location_arg"],
            "possible_zoos" => $this->getPossibleZoos(),
        );
    }

    function argBetweenActions()
    {
        $mainAction = $this->getGameStateValue("mainAction");

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

    function stActivateZoo()
    {
        $prev_zoo = $this->getActivePlayerId();
        $selected_zoo = $this->getGameStateValue("selectedZoo");

        $this->gamestate->changeActivePlayer($selected_zoo);
        $this->setGameStateValue("selectedZoo", $prev_zoo);

        $this->gamestate->nextState("selectHelpQuarantine");
    }

    function stActivatePrevZoo()
    {
        $prev_zoo = $this->getGameStateValue("selectedZoo");

        $this->gamestate->changeActivePlayer($prev_zoo);
        $this->gamestate->nextState("betweenActions");
    }

    function stBetweenActions()
    {
        $this->setGameStateValue("totalToReturn", 0);
        $this->setGameStateValue("previouslyReturned", 0);
        $this->setGameStateValue("selectedPosition", 0);
        $this->setGameStateValue("selectedSpecies", 0);
        $this->setGameStateValue("selectedBackup", 0);
        $this->setGameStateValue("selectedZoo", 0);
        $this->setGameStateValue("secondStep", 0);

        $this->autoDrawNewSpecies();

        // if ($this->hasSecretObjectives()) {
        //     $this->calcObjectiveBonus($player_id);
        // }

        if ($this->isOutOfActions()) {
            $this->gamestate->nextState("betweenPlayers");
            return;
        }

        $this->gamestate->nextState("nextAction");
    }

    function stBetweenPlayers()
    {
        $this->setGameStateValue("totalToReturn", 0);
        $this->setGameStateValue("previouslyReturned", 0);
        $this->setGameStateValue("mainAction", 0);
        $this->setGameStateValue("freeAction", 0);
        $this->setGameStateValue("zooHelp", 0);

        $active_player_id = $this->getActivePlayerId();

        $this->zombieReturnResources();
        $resources_nbr = $this->resources->countCardsInLocation("hand", $active_player_id);
        $kit_nbr = count($this->resources->getCardsOfTypeInLocation("kit", null, "hand", $active_player_id));

        if ($resources_nbr > 12 || $kit_nbr > 5) {
            $this->gamestate->nextState("excessResources");
            return;
        }

        $this->notifyAllPlayers("pass", clienttranslate('${player_name} finishes his turn and passes'), array(
            "player_name" => $this->getActivePlayerName(),
        ));

        //game end condition
        if ($this->getGameStateValue("highestSaved") >= 9) {
            $last_turn = $this->getGameStateValue("lastTurn") + 1;

            if ($last_turn == 1) {
                $this->notifyAllPlayers(
                    "lastTurn",
                    clienttranslate('${player_name} reaches 9 saved species. Each of the other players must play their last turn before the game ends'),
                    array("player_name" => $this->getActivePlayerName())
                );
            }

            $this->setGameStateValue("lastTurn", $last_turn);
        }

        if ($this->getGameStateValue("lastTurn") == count($this->loadPlayersBasicInfos())) {
            $this->gamestate->nextState("finalScoresCalc");
            return;
        }

        $this->revealSpecies();

        $this->activeNextPlayer();
        $next_player_id = $this->getActivePlayerId();
        $this->giveExtraTime($next_player_id);

        $this->gamestate->nextState("nextPlayer");
    }

    function stExcessResources()
    {
        $player_id = $this->getActivePlayerId();
        $kits = $this->resources->getCardsOfTypeInLocation("kit", null, "hand", $player_id);
        $kit_nbr = count($kits);

        if ($kit_nbr > 5) {
            $returned_nbr = $kit_nbr - 5;

            $returned_kits = array_slice($kits, 0, $returned_nbr, true);

            $keys = array_keys($returned_kits);
            $this->resources->moveCards($keys, "deck");

            $this->notifyAllPlayers(
                "returnResources",
                clienttranslate('${player_name} returns ${returned_nbr} medical kit(s) to the bag as excess'),
                array(
                    "player_name" => $this->getActivePlayerName(),
                    "player_id" => $player_id,
                    "returned_nbr" => count($keys),
                    "type" => "kit",
                    "resource_counters" => $this->getResourceCounters(),
                )
            );
        }

        $resources_nbr = $this->resources->countCardsInLocation("hand", $player_id);

        if ($resources_nbr > 12) {
            $this->setGameStateValue("totalToReturn", $resources_nbr - 12);
            $this->gamestate->nextState("returnExcess");
            return;
        }

        $this->gamestate->nextState("betweenPlayers");
    }

    function stFinalScoresCalc()
    {
        $players = $this->loadPlayersBasicInfos();

        foreach ($players as $player_id => $player) {
            $regular_points = $this->calcRegularPoints($player_id);
            foreach ($regular_points as $position => $position_info) {
                if ($position_info !== null) {
                    foreach ($position_info as $keeper_id => $points) {
                        if ($points > 0) {
                            $this->notifyAllPlayers(
                                "regularPoints",
                                clienttranslate('${player_name} scores ${points} with ${keeper_name} and their kept species'),
                                array(
                                    "player_name" => $player["player_name"],
                                    "player_id" => $player_id,
                                    "player_color" => $player["player_color"],
                                    "board_position" => $position,
                                    "keeper_id" => $keeper_id,
                                    "keeper_name" => $this->keepers_info[$keeper_id]["keeper_name"],
                                    "points" => $points
                                )
                            );
                        }
                    }
                }
            }

            $quarantine_penalties = $this->calcQuarantinePenalties($player_id);
            foreach ($quarantine_penalties as $quarantine => $species_id) {
                if ($species_id !== null) {
                    $this->notifyAllPlayers(
                        "quarantinePenalties",
                        clienttranslate('${player_name} loses 2 points due to having a species in his ${quarantine_label} quarantine'),
                        array(
                            "player_name" => $player["player_name"],
                            "player_id" => $player_id,
                            "player_color" => $player["player_color"],
                            "quarantine" => $quarantine,
                            "quarantine_label" => $quarantine === "ALL" ? "generic" : $quarantine,
                            "species_id" => $species_id
                        )
                    );
                }
            }

            if ($this->hasSecretObjectives()) {
                $objective = $this->calcObjectiveBonus($player_id);

                if ($objective !== null) {
                    $objective_bonus = $objective["bonus"];

                    if ($objective_bonus > 0) {
                        $this->notifyAllPlayers(
                            "objectiveBonus",
                            clienttranslate('${player_name} scores ${bonus} points by completing a secret objective'),
                            array(
                                "player_id" => $player_id,
                                "player_name" => $player["player_name"],
                                "player_color" => $player["player_color"],
                                "objective_id" => $objective["type_arg"],
                                "bonus" => $objective_bonus
                            )
                        );
                    }
                }
            }

            $collection = $this->getCollectionFromDb("SELECT player_score FROM player WHERE player_id='$player_id'");

            $final_scores = 0;
            foreach ($collection as $player_data) {
                $final_scores = $player_data["player_score"];
            }

            $this->notifyAllPlayers("newScores", "", array(
                "player_id" => $player_id,
                "new_scores" => $final_scores,
            ));

            $this->calcTieBreakers($player_id);
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
        $stateName = $state['name'];

        if ($state['type'] === "activeplayer") {
            $this->gamestate->nextState("zombiePass");
            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException("Zombie mode not supported at this game state: " . $stateName);
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
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        if( $from_version <= 1405061421 )
        //        {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
        //            $this->applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //


    }
}
