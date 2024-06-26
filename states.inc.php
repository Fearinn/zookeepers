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
 * states.inc.php
 *
 * Zookeepers game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: $this->checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!


$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array("" => 2)
    ),

    2 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} can select a card and/or do any available actions, limited to one of the four main ones'),
        "descriptionmyturn" => clienttranslate('${you} can select a card and/or do any available actions, limited to one of the four main ones'),
        "type" => "activeplayer",
        "possibleactions" => array(
            "saveSpecies",
            "saveQuarantined",
            "collectResources",
            "exchangeResources",
            "collectFromExchange",
            "returnFromExchange",
            "quarantineSpecies",
            "discardSpecies",
            "lookAtBackup",
            "replaceKeeper",
            "dismissKeeper",
            "hireKeeper",
            "zooHelp",
            "newSpecies",
            "replaceObjective",
            "pass"
        ),
        "args" => "argPlayerTurn",
        "transitions" => array(
            "betweenActions" => 7,
            "pass" => 8,
            "zombiePass" => 8,
            "exchangeCollection" => 21,
            "selectDismissedPile" => 25,
            "selectReplacedPile" => 26,
            "selectAssignedKeeper" => 27,
            "selectQuarantine" => 28,
            "mngSecondSpecies" => 29,
            "mngBackup" => 30,
            "selectQuarantinedKeeper" => 32,
            "selectZoo" => 33,
            "returnFromNewSpecies" => 37
        )
    ),

    21 => array(
        "name" => "exchangeCollection",
        "description" => clienttranslate('${actplayer} can choose the number of resources to get from the conservation fund'),
        "descriptionmyturn" => clienttranslate('${you} can choose the number of resources to get from the conservation fund'),
        "type" => "activeplayer",
        "args" => "argExchangeCollection",
        "possibleactions" => array("collectFromExchange", "cancelExchange"),
        "transitions" => array("exchangeReturn" => 22, "cancel" => 2, "zombiePass" => 8,)
    ),

    22 => array(
        "name" => "exchangeReturn",
        "description" => clienttranslate('${actplayer} must return ${to_return} resource(s) to the bag'),
        "descriptionmyturn" => clienttranslate('${you} must return ${to_return} resource(s) to the bag'),
        "type" => "activeplayer",
        "args" => "argExchangeReturn",
        "possibleactions" => array("returnFromExchange"),
        "transitions" => array("betweenExchangeReturns" => 23, "betweenActions" => 7, "zombiePass" => 8,)
    ),

    23 => array(
        "name" => "betweenExchangeReturns",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "activeplayer",
        "action" => "stBetweenExchangeReturns",
        "transitions" => array("nextReturn" => 22, "betweenActions" => 7, "zombiePass" => 8,)
    ),

    25 => array(
        "name" => "selectDismissedPile",
        "description" => clienttranslate('${actplayer} can pick a pile to send ${keeper_name} to'),
        "descriptionmyturn" => clienttranslate('${you} can pick a pile to send ${keeper_name} to'),
        "type" => "activeplayer",
        "args" => "argSelectDismissedPile",
        "possibleactions" => array("selectDismissedPile", "cancelMngKeepers"),
        "transitions" => array("betweenActions" => 7, "cancel" => 2, "zombiePass" => 8,)
    ),

    26 => array(
        "name" => "selectReplacedPile",
        "description" => clienttranslate('${actplayer} can pick a pile to replace ${keeper_name}'),
        "descriptionmyturn" => clienttranslate('${you} can pick a pile to replace ${keeper_name}'),
        "type" => "activeplayer",
        "args" => "argSelectReplacedPile",
        "possibleactions" => array("selectReplacedPile", "cancelMngKeepers"),
        "transitions" => array("betweenActions" => 7, "cancel" => 2, "zombiePass" => 8)
    ),

    27 => array(
        "name" => "selectAssignedKeeper",
        "description" => clienttranslate('${actplayer} can pick a keeper to keep the ${species_name}'),
        "descriptionmyturn" => clienttranslate('${you} can pick a keeper to keep the ${species_name}'),
        "type" => "activeplayer",
        "args" => "argSelectAssignedKeeper",
        "possibleactions" => array("selectAssignedKeeper", "cancelMngSpecies"),
        "transitions" => array("betweenActions" => 7, "cancel" => 2, "zombiePass" => 8)
    ),

    28 => array(
        "name" => "selectQuarantine",
        "description" => clienttranslate('${actplayer} can pick a quarantine to put the ${species_name} in'),
        "descriptionmyturn" => clienttranslate('${you} can pick a quarantine to put the ${species_name} in'),
        "type" => "activeplayer",
        "args" => "argSelectQuarantine",
        "possibleactions" => array("selectQuarantine", "cancelMngSpecies"),
        "transitions" => array(
            "mngSecondSpecies" => 29,
            "betweenActions" => 7,
            "cancel" => 2,
            "cancelQuarantine" => 29, 
            "zombiePass" => 8
        )
    ),

    29 => array(
        "name" => "mngSecondSpecies",
        "description" => clienttranslate('${actplayer} can discard or quarantine other species'),
        "descriptionmyturn" => clienttranslate('${you} can discard or quarantine other species'),
        "type" => "activeplayer",
        "args" => "argMngSecondSpecies",
        "possibleactions" => array("discardSpecies", "quarantineSpecies", "lookAtBackup", "newSpecies", "cancelMngSpecies"),
        "transitions" => array(
            "selectQuarantine" => 28,
            "mngSecondSpecies" => 29, "mngBackup" => 30,
            "betweenActions" => 7,
            "returnFromNewSpecies" => 37,
            "cancel" => 2,
            "zombiePass" => 8
        )
    ),

    30 => array(
        "name" => "mngBackup",
        "description" => clienttranslate('${actplayer} looked at a face-down species and must choose to discard or quarantine it'),
        "descriptionmyturn" => clienttranslate('${you} must choose to discard or quarantine the ${_private.species_name}'),
        "type" => "activeplayer",
        "args" => "argMngBackup",
        "possibleactions" => array("discardBackup", "quarantineBackup"),
        "transitions" => array("selectBackupQuarantine" => 31, "mngSecondSpecies" => 29, "betweenActions" => 7, "zombiePass" => 8)
    ),

    31 => array(
        "name" => "selectBackupQuarantine",
        "description" => clienttranslate('${actplayer} can pick a quarantine to put a face-down species in'),
        "descriptionmyturn" => clienttranslate('${you} can pick a quarantine to put the ${_private.species_name} in'),
        "type" => "activeplayer",
        "args" => "argSelectBackupQuarantine",
        "possibleactions" => array("selectBackupQuarantine", "cancelMngSpecies"),
        "transitions" => array("mngSecondSpecies" => 29, 
        "betweenActions" => 7, 
        "cancel" => 30, 
        "cancelQuarantine" => 30, 
        "zombiePass" => 8)
    ),

    32 => array(
        "name" => "selectQuarantinedKeeper",
        "description" => clienttranslate('${actplayer} can pick a keeper to keep the ${species_name}'),
        "descriptionmyturn" => clienttranslate('${you} can pick a keeper to keep the ${species_name}'),
        "type" => "activeplayer",
        "args" => "argSelectQuarantinedKeeper",
        "possibleactions" => array("selectQuarantinedKeeper", "cancelMngSpecies"),
        "transitions" => array("betweenActions" => 7, "cancel" => 2, "zombiePass" => 8)
    ),

    33 => array(
        "name" => "selectZoo",
        "description" => clienttranslate('${actplayer} can pick a zoo to ask help for with the ${species_name}'),
        "descriptionmyturn" => clienttranslate('${you} can pick a zoo to ask help for with the ${species_name}'),
        "type" => "activeplayer",
        "args" => "argSelectZoo",
        "possibleactions" => array("selectZoo", "cancelMngSpecies"),
        "transitions" => array("activateZoo" => 34, "betweenActions" => 7, "cancel" => 2, "zombiePass" => 8),
    ),

    34 => array(
        "name" => "activateZoo",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stActivateZoo",
        "transitions" => array("selectHelpQuarantine" => 35),
    ),

    35 => array(
        "name" => "selectHelpQuarantine",
        "description" => clienttranslate('${actplayer} must select a quarantine to put the ${species_name} in'),
        "descriptionmyturn" => clienttranslate('${you} must select a quarantine to put the ${species_name} in'),
        "type" => "activeplayer",
        "args" => "argSelectHelpQuarantine",
        "possibleactions" => array("selectHelpQuarantine"),
        "transitions" => array("activatePrevZoo" => 36, "zombiePass" => 8),
    ),

    36 => array(
        "name" => "activatePrevZoo",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stActivatePrevZoo",
        "transitions" => array("betweenActions" => 7),
    ),

    37 => array(
        "name" => "newSpeciesReturn",
        "description" => clienttranslate('${actplayer} must return 1 resource to the bag to refill the grid of species'),
        "descriptionmyturn" => clienttranslate('${you} must return 1 resource to the bag to refill the grid of species'),
        "type" => "activeplayer",
        "possibleactions" => array("returnFromNewSpecies", "cancelNewSpecies"),
        "transitions" => array("betweenActions" => 7, "mngSecondSpecies" => 29, "cancel" => 2)
    ),

    7 => array(
        "name" => "betweenActions",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBetweenActions",
        "args" => "argBetweenActions",
        "transitions" => array("nextAction" => 2, "betweenPlayers" => 8)
    ),

    8 => array(
        "name" => "betweenPlayers",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stBetweenPlayers",
        "transitions" => array("nextPlayer" => 2, "excessResources" => 81, "finalScoresCalc" => 98),
        "updateGameProgression" => true
    ),

    81 => array(
        "name" => "excessResources",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "game",
        "action" => "stExcessResources",
        "transitions" => array("betweenPlayers" => 8, "returnExcess" => 82)
    ),

    82 => array(
        "name" => "returnExcess",
        "description" => clienttranslate('${actplayer} exceeded the limit of resources and must return ${to_return} to the bag'),
        "descriptionmyturn" => clienttranslate('${you} exceeded the limit of resources and must return  ${to_return} to the bag'),
        "type" => "activeplayer",
        "args" => "argReturnExcess",
        "possibleactions" => array("returnExcess"),
        "transitions" => array("betweenExcessReturns" => 83, "betweenPlayers" => 8, "zombiePass" => 8)
    ),

    83 => array(
        "name" => "betweenExcessReturns",
        "type" => "game",
        "action" => "stBetweenExcessReturns",
        "transitions" => array("nextReturn" => 82)
    ),

    98 => array(
        "name" => "finalScoresCalc",
        "type" => "game",
        "action" => "stFinalScoresCalc",
        "transitions" => array("gameEnd" => 99)
    ),

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);
