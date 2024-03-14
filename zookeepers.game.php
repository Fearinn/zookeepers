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
            "selectedBoardPosition" => 14,
            "selectedSpecies" => 15,
            "selectedCard" => 16,
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

        foreach ($this->filterByPoints($keepers_info, 1) as $keeper_id => $keeper) {
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
        foreach ($this->filterByPoints($keepers_info, 1, true) as $keeper_id => $keeper) {
            $other_keepers[] = array("type" => $keeper["name"], "type_arg" => $keeper_id, "nbr" => 1);
        }
        $this->keepers->createCards($other_keepers, "deck");
        $this->keepers->shuffle("deck");
        for ($pile = 1; $pile <= 4; $pile++) {
            $location = "deck:" . strval($pile);
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
        self::setGameStateInitialValue("selectedBoardPosition", 0);
        self::setGameStateInitialValue("selectedSpecies", 0);
        self::setGameStateInitialValue("selectedCard", 0);

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

        $result["players"] = self::getCollectionFromDb($sql);
        $result["resourceCounters"] = $this->getResourceCounters();
        $result["bagCounters"] = $this->getBagCounters();
        $result["isBagEmpty"] = $this->isBagEmpty();
        $result["allKeepers"] = $keepers_info;
        $result["pileCounters"] = $this->getPileCounters();
        $result["pilesTops"] = $this->getPilesTops();
        $result["keepersOnBoards"] = $this->getKeepersOnBoards();
        $result["allSpecies"] = $species_info;
        $result["visibleSpecies"] = $this->getVisibleSpecies();
        $result["savableSpecies"] = $this->getSavableSpecies();

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

    function filterByResourceType($resources, $type_arg)
    {
        $filtered_resources = array_filter($resources, function ($resource) use ($type_arg) {
            return $resource["type_arg"] == $type_arg;
        });

        return $filtered_resources;
    }

    function filterByPoints($items, $points, $exclusive = false)
    {
        $filtered_items = array_filter($items, function ($item) use ($points, $exclusive) {
            if ($exclusive) {
                return $item["points"] !== $points;
            }

            return $item["points"] == $points;
        });

        return $filtered_items;
    }

    function getPilesTops()
    {
        $tops = array();

        for ($pile = 1; $pile <= 4; $pile++) {
            $topCard = $this->keepers->getCardOnTop("deck:" . strval($pile));

            if ($topCard) {
                $keeper_info = $this->keepers_info[$topCard["type_arg"]];
                $tops[$pile] = $keeper_info["points"];
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
            $counters[$pile] = $this->keepers->countCardsInLocation("deck:" . strval($pile));
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
                $location = "board:" . strval($i);
                $sql = "SELECT pile, card_id, card_location, card_location_arg, card_type, card_type_arg FROM keeper WHERE card_location_arg='$player_id' AND card_location='$location'";
                $keepers[$player_id][$i] = self::getCollectionFromDb($sql);
            }
        }

        return $keepers;
    }

    function getVisibleSpecies()
    {
        $visible_species = array();
        for ($i = 1; $i <= 4; $i++) {
            $species = $this->species->getCardsInLocation("shop_visible", $i);
            $visible_species[$i] = array_shift($species);
        }

        return $visible_species;
    }

    function getSavableSpecies()
    {
        $savable_species = array();
        $player_id = self::getActivePlayerId();
        $resource_counters = $this->getResourceCounters()[$player_id];

        foreach ($this->species->getCardsInLocation("shop_visible") as $species_card) {
            $species_id = $species_card["type_arg"];
            $species_info = $this->species_info[$species_id];
            $cost = $species_info["cost"];

            $can_assign = count($this->getAssignableKeepers($species_id)) > 0;
            $can_pay_cost = true;

            foreach ($resource_counters as $type => $counter) {
                $type_cost = $cost[$type];
                if ($type_cost > 0 && $counter < $type_cost) {
                    $can_pay_cost = false;
                    break;
                };
            }

            $keepers_in_play = array();

            for ($i = 1; $i <= 4; $i++) {
                $keepers_in_play += $this->getKeepersOnBoards()[$player_id][$i];
            }

            if ($can_pay_cost && $can_assign) {
                $savable_species[$species_id] = $species_card;
            }
        }

        return $savable_species;
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
            $keeper_position = $keeper_card["card_location"];

            $keeper_info = $this->keepers_info[$keeper_id];
            $operator = $keeper_info["operator"];

            if ($this->species->countCardsInLocation("board:" . $keeper_position, $player_id) >= 3) {
                continue;
            }

            if ($operator === "any") {
                $assignable_keepers[$keeper_id] = $keeper_card;
                continue;
            }

            if ($operator === "single") {
                foreach ($keeper_info as $key => $keeper_value) {
                    if (
                        $key !== "points"
                        && in_array($key, $species_keys, true)
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

            if ($operator === "or") {
                foreach ($keeper_info as $key => $keeper_value) {
                    if (
                        $key !== "points"
                        && in_array($key, $species_keys, true)
                    ) {
                        $species_value = $species_info[$key];

                        if (is_array($species_value) && count(array_intersect($keeper_value, $species_value)) > 0) {
                            $assignable_keepers[$keeper_id] = $keeper_card;
                            break;
                        }

                        if (!is_array($species_value) && in_array($species_value, $keeper_value, true)) {
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
                        $key !== "points"
                        && in_array($key, $species_keys, true)
                    ) {
                        $species_value = $species_info[$key];

                        if (is_array($species_value) && in_array($keeper_value, $species_value, true)) {
                            $conditions_met++;
                            continue;
                        }

                        if (!is_array($species_value) && $species_value == $keeper_value) {
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
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . strval($i), $player_id);
        }

        if ($keepers_on_board_nbr >= 4) {
            throw new BgaUserException("You can't have more than 4 keepers in play");
        }

        $this->gamestate->nextState("selectHiredPile");
    }

    function selectHiredPile($pile)
    {
        self::checkAction("selectHiredPile");

        $player_id = self::getActivePlayerId();

        $board_position = 0;

        for ($position = 1; $position <= 4; $position++) {
            if ($this->keepers->countCardsInLocation("board:" . strval($position), $player_id) < 1) {
                $board_position = $position;
                break;
            }
        }

        if ($board_position === 0) {
            throw new BgaUserException(self::_("You can't have more than 4 keepers in play"));
        }

        $keeper = $this->keepers->pickCardForLocation("deck:" . $pile, "board:" . strval($board_position), $player_id);

        if ($keeper === null) {
            throw new BgaUserException(self::_("The selected pile is out of cards"));
        }
        $keeper_id = $keeper["id"];

        $pile_counters = $this->getPileCounters();

        $sql = "UPDATE keeper SET pile=$pile WHERE card_id=$keeper_id";
        self::DbQuery($sql);

        self::notifyAllPlayers(
            "hireKeeper",
            clienttranslate('${player_name} hires ${keeper_name} from pile ${pile}. Keepers left in the deck: ${left_in_pile}'),
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

    function dismissKeeper()
    {
        self::checkAction("dismissKeeper");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $keepers_on_board_nbr = 0;

        for ($i = 1; $i <= 4; $i++) {
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . strval($i), $player_id);
        }

        if ($keepers_on_board_nbr === 0) {
            throw new BgaUserException("You don't have any keeper to dismiss");
        }

        $this->gamestate->nextState("selectDismissedKeeper");
    }

    function selectDismissedKeeper($board_position)
    {
        self::checkAction("selectDismissedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaUserException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $pile = 0;

        $keepers_info = $this->keepers_info;
        $keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $pile = $card["pile"];
            $keeper_points = $keepers_info[$card["card_type_arg"]]["points"];
            $keeper = $card;
        }

        if ($keeper === null) {
            throw new BgaUserException("Keeper not found");
        }

        if ($pile == 0 && $keeper_points === 1) {
            self::setGameStateValue("selectedBoardPosition", $board_position);
            $this->gamestate->nextState("selectDismissedPile");
            return;
        }

        if ($pile == 0) {
            throw new BgaUserException("This keeper isn't hired by you");
        }

        if ($pile < 0 || $pile > 4) {
            throw new BgaUserException("Invalid keeper pile");
        }

        $keeper_id = $keeper["card_id"];
        $this->keepers->insertCardOnExtremePosition($keeper["card_id"], "deck:" . $pile, false);

        $pile_counters = $this->getPileCounters();

        self::notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is returned to the bottom of pile ${pile}. Number of keepers in the pile: ${left_in_pile}'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "keeper_name" => $keeper["card_type"],
                "keeper_id" => $keeper["card_type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
                "left_in_pile" => $pile_counters[$pile]
            )
        );

        self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_id='$keeper_id'");

        self::setGameStateValue("selectedBoardPosition", $board_position);
        self::setGameStateValue("mainAction", 6);

        $this->gamestate->nextState("betweenActions");
    }

    function selectDismissedPile($pile)
    {
        self::checkAction("selectDismissedPile");

        $board_position = self::getGameStateValue("selectedBoardPosition");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaUserException("Invalid board position");
        }

        if ($pile < 1 || $board_position > 4) {
            throw new BgaUserException("Invalid keeper pile");
        }

        $player_id = self::getActivePlayerId();

        $keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $keeper = $card;
        }

        if ($keeper === null) {
            throw new BgaUserException("Keeper not found");
        }

        $keeper_id = $keeper["card_id"];

        $this->keepers->insertCardOnExtremePosition($keeper["card_id"], "pile:" . $pile, false);

        $pile_counters = $this->getPileCounters();

        self::notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} dismiss ${keeper_name}, who is returned to pile ${pile}. Number of keepers in the pile: ${left_in_pile}'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "keeper_name" => $keeper["card_type"],
                "keeper_id" => $keeper["card_type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $pile_counters,
                "piles_tops" => $this->getPilesTops(),
                "left_in_pile" => $pile_counters[$pile]
            )
        );

        self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_id='$keeper_id'");

        self::setGameStateValue("mainAction", 6);

        $this->gamestate->nextState("betweenActions");
    }

    function replaceKeeper()
    {
        self::checkAction("replaceKeeper");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $player_id = self::getActivePlayerId();

        $keepers_on_board_nbr = 0;

        for ($position = 1; $position <= 4; $position++) {
            $keepers_on_board_nbr += $this->keepers->countCardsInLocation("board:" . strval($position), $player_id);
        }

        if ($keepers_on_board_nbr === 0) {
            throw new BgaUserException("You don't have any keeper to replace");
        }

        $this->gamestate->nextState("selectReplacedKeeper");
    }

    function selectReplacedKeeper($board_position)
    {
        self::checkAction("selectReplacedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaUserException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $keeper = $card;
        }

        if ($keeper === null) {
            throw new BgaUserException("Keeper not found");
        }

        self::setGameStateValue("selectedBoardPosition", $board_position);

        $this->gamestate->nextState("selectReplacedPile");
    }

    function selectReplacedPile($pile)
    {
        self::checkAction("selectReplacedPile");

        $player_id = self::getActivePlayerId();

        $board_position = self::getGameStateValue("selectedBoardPosition");

        $replaced_keeper = null;

        foreach ($this->getKeepersOnBoards()[$player_id][$board_position] as $card) {
            $replaced_keeper = $card;
        }

        if ($replaced_keeper === null) {
            throw new BgaUserException("Keeper not found");
        }

        $hired_keeper = $this->keepers->pickCardForLocation("deck:" . strval($pile), "board:" . strval($board_position), $player_id);

        if ($hired_keeper === null) {
            throw new BgaUserException(self::_("The selected pile is out of cards"));
        }

        $replaced_keeper_id = $replaced_keeper["card_id"];

        self::DbQuery("UPDATE keeper SET pile=$pile WHERE card_id=$replaced_keeper_id");
        $this->keepers->insertCardOnExtremePosition($replaced_keeper_id, "deck:" . strval($pile), false);

        self::notifyAllPlayers(
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

        self::notifyAllPlayers(
            "dismissKeeper",
            clienttranslate('${player_name} replaces ${replaced_keeper_name} by ${hired_keeper_name}, from pile ${pile}'),
            array(
                "player_id" => self::getActivePlayerId(),
                "player_name" => self::getActivePlayerName(),
                "replaced_keeper_name" => $replaced_keeper["card_type"],
                "hired_keeper_name" => $hired_keeper["type"],
                "keeper_id" => $replaced_keeper["card_type_arg"],
                "board_position" => $board_position,
                "pile" => $pile,
                "pile_counters" => $this->getPileCounters(),
                "piles_tops" => $this->getPilesTops(),
            )
        );

        self::setGameStateValue("mainAction", 7);

        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngKeepers()
    {
        self::checkAction("cancelMngKeepers");
        self::setGameStateValue("selectedBoardPosition", 0);
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

        // temporary, for tests only
        $species_nbr = 3;

        $collected_resources = $this->resources->pickCards($species_nbr, "deck", $player_id);
        $collected_nbr = count($collected_resources);

        $collected_plant = $this->filterByResourceType($collected_resources, 1);
        $collected_plant_nbr = count($collected_plant);

        $collected_meat = $this->filterByResourceType($collected_resources, 2);
        $collected_meat_nbr = count($collected_meat);

        $collected_kit = $this->filterByResourceType($collected_resources, 3);
        $collected_kit_nbr = count($collected_kit);

        self::notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} collects ${collected_nbr} resources. Plant: ${collected_plant_nbr}; 
            meat/fish: ${collected_meat_nbr}; medical kit: ${collected_kit_nbr}'),
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

        self::notifyAllPlayers(
            "collectResources",
            clienttranslate('${player_name} activates the conservation fund and collects ${collected_nbr} resources. Plant: ${collected_plant_nbr}; 
            meat/fish: ${collected_meat_nbr}; medical kit: ${collected_kit_nbr}. They must return ${return_nbr} resources to the bag'),
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

        self::notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} resources of ${type}'),
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

        self::notifyAllPlayers(
            "returnResources",
            clienttranslate('${player_name} returns ${returned_nbr} resources of ${type} as excess'),
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

    function saveSpecies()
    {
        self::checkAction("saveSpecies");

        if (self::getGameStateValue("mainAction")) {
            throw new BgaUserException(self::_("You already used a main action this turn"));
        }

        $can_save = count($this->getSavableSpecies()) > 0;

        if (!$can_save) {
            throw new BgaUserException(self::_("You can't save any of the available species"));
        }

        $this->gamestate->nextState("selectSavedSpecies");
    }

    function selectSavedSpecies($shop_position)
    {
        self::checkAction("selectSavedSpecies");

        if ($shop_position < 1 || $shop_position > 4) {
            throw new BgaUserException("Invalid shop position");
        }

        $species_id = null;
        $species_card_id = null;
        foreach ($this->species->getCardsInLocation("shop_visible", $shop_position) as $card_id => $card) {
            $species_id = $card["type_arg"];
            $species_card_id = $card_id;
        }

        $savable_species_ids = array_keys($this->getSavableSpecies());

        if ($species_id === null || !$species_card_id === null) {
            throw new BgaUserException(self::_("This species is not available to be saved"));
        }

        if (!in_array($species_id, $savable_species_ids)) {
            throw new BgaUserException(self::_("You don't have the required resources or keepers to save this species"));
        }

        self::setGameStateValue("selectedCard", $species_card_id);
        self::setGameStateValue("selectedSpecies", $species_id);
        $this->gamestate->nextState("selectAssignedKeeper");
    }

    function selectAssignedKeeper($board_position)
    {
        self::checkAction("selectAssignedKeeper");

        if ($board_position < 1 || $board_position > 4) {
            throw new BgaUserException("Invalid board position");
        }

        $player_id = self::getActivePlayerId();

        $species_id = self::getGameStateValue("selectedSpecies");
        $species_card_id = self::getGameStateValue("selectedCard");

        $assigned_keeper_id = null;

        foreach ($this->keepers->getCardsInLocation("board:" . $board_position, $player_id) as $card_id => $card) {
            $assigned_keeper_id = $card["type_arg"];
        }

        $assignable_keepers = $this->getAssignableKeepers($species_id);

        $assignable_keepers_ids = array_keys($assignable_keepers);

        if (!in_array($assigned_keeper_id, $assignable_keepers_ids)) {
            throw new BgaUserException(self::_("You can't assign this species to this keeper"));
        }

        $this->species->moveCard($species_card_id, "board:" . $board_position);

        $this->gamestate->nextState("betweenActions");
    }

    function cancelMngSpecies()
    {
        self::checkAction("cancelMngSpecies");
        self::setGameStateValue("selectedSpecies", 0);

        $this->gamestate->nextState("cancel");
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
            "keepers_on_boards" => $this->getKeepersOnBoards()
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
        self::setGameStateValue("selectedBoardPosition", 0);

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

        self::notifyAllPlayers("pass", clienttranslate('${player_name} finishes their turn and passes'), array(
            "player_name" => self::getActivePlayerName(),
        ));

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

            self::notifyAllPlayers(
                "returnResources",
                clienttranslate('${player_name} returns ${returned_nbr} of kits as excess'),
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
