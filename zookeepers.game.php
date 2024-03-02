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
        ));

        $this->resources = self::getNew("module.common.deck");
        $this->resources->init("resource");

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

        $this->resources->createCards($resources_deck, "deck");
        $this->resources->shuffle('deck');

        $species_deck = array();
        $species_info = $this->species_info;
        ksort($species_info);
        foreach ($species_info as $species_id => $species) {
            $habitat_string = "";
            foreach ($species["habitat"] as $habitat) {
                $habitat_string = $habitat_string . $habitat . ":";
            };

            $continent_string = "";
            foreach ($species["continent"] as $continent) {
                $continent_string = $continent_string . $continent . ":";
            };

            $species_deck[] = array(
                "type" => $species["name"],
                "type_arg" => $species["points"],
                "nbr" => 1,
            );
        }

        $this->species->createCards($species_deck, "deck");
        $this->species->shuffle("deck");

        for ($i = 1; $i <= 4; $i++) {
            $this->species->pickCardsForLocation(2, "deck", "shop_backup", $i);
            $this->species->pickCardForLocation("deck", "shop_visible", $i);
        }

        foreach ($players as $player_id => $player) {
            $this->resources->createCards($resources_to_players, "hand", $player_id);
        }

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue("mainAction", 0);
        self::setGameStateInitialValue("freeAction", 0);
        self::setGameStateInitialValue("totalToReturn", 0);
        self::setGameStateInitialValue("previouslyReturned", 0);

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
        $result["players"] = self::getCollectionFromDb($sql);
        $result["resourceCounters"] = $this->getResourceCounters();
        $result["bagCounters"] = $this->getBagCounters();
        $result["isBagEmpty"] = $this->isBagEmpty();
        $result["visible_species"] = $this->getVisibleSpecies();


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

            $counters[] = array($player_id => array("plant" => $plants_nbr, "meat" => $meat_nbr, "kit" => $kits_nbr));
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

    function getVisibleSpecies()
    {
        $visible_species = array();
        for ($i = 1; $i <= 4; $i++) {
            $species = $this->species->getCardsInLocation("shop_visible", $i);
            $visible_species[$i] = array_shift($species);
        }

        return $visible_species;
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

        self::notifyAllPlayers("pass", clienttranslate('${player_name} finishes their turn and passes'), array(
            "player_name" => self::getActivePlayerName(),
        ));

        $this->gamestate->nextState("pass");
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

        $this->gamestate->nextState("betweenReturns");
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
            "isBagEmpty" => $this->isBagEmpty()
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

    function stBetweenReturns()
    {
        $this->gamestate->nextState("nextReturn");
    }

    function stBetweenActions()
    {
        $this->gamestate->nextState("nextAction");
    }

    function stBetweenPlayers()
    {
        self::setGameStateValue("mainAction", 0);
        self::setGameStateValue("totalToReturn", 0);
        self::setGameStateValue("previouslyReturned", 0);
        self::setGameStateValue("freeAction", 0);

        self::activeNextPlayer();
        $this->gamestate->nextState("nextPlayer");
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
