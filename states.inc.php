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
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
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
        "description" => clienttranslate('${actplayer} can do any free actions and one of the four main actions'),
        "descriptionmyturn" => clienttranslate('${you} can do any free actions and one of the four main actions'),
        "type" => "activeplayer",
        "possibleactions" => array(
            "saveSpecies", "collectResources", "exchangeResources", "collectFromExchange", "returnFromExchange", "quarentineSpecies", "discardSpecies", "swapKeepers", "dismissKeeper",
            "hireKeeper", "zooHelp", "newSpecies", "pass"
        ),
        "args" => "argPlayerTurn",
        "transitions" => array("betweenActions" => 3, "pass" => 4, "exchangeCollecting" => 21)
    ),

    21 => array(
        "name" => "exchangeCollecting",
        "description" => clienttranslate('${actplayer} can choose how many resources they want from the conservartion fund'),
        "descriptionmyturn" => clienttranslate('${you} can choose how many resources you want from the conservartion fund'),
        "type" => "activeplayer",
        "args" => "argExchangeCollecting",
        "possibleactions" => array("collectFromExchange", "cancelExchange"),
        "transitions" => array("exchangeReturn" => 22, "cancel" => 2)
    ),

    22 => array(
        "name" => "exchangeReturn",
        "description" => clienttranslate('${actplayer} must return ${to_return} resources to the bag'),
        "descriptionmyturn" => clienttranslate('${you} must return ${to_return} resources to the bag'),
        "type" => "activeplayer",
        "args" => "argExchangeReturn",
        "possibleactions" => array("returnFromExchange"),
        "transitions" => array("betweenReturns" => 23, "betweenActions" => 3)
    ),

    23 => array(
        "name" => "betweenReturns",
        "description" => "",
        "descriptionmyturn" => "",
        "type" => "activeplayer",
        "args" => "argExchangeReturn",
        "action" => "stBetweenReturns",
        "transitions" => array("nextReturn" => 22, "betweenActions" => 3)
    ),

    3 => array(
        "name" => "betweenActions",
        "type" => "game",
        "action" => "stBetweenActions",
        "args" => "argBetweenActions",
        "transitions" => array("nextAction" => 2, "gameEnd" => 99)
    ),

    4 => array(
        "name" => "betweenPlayers",
        "type" => "game",
        "action" => "stBetweenPlayers",
        "transitions" => array("nextPlayer" => 2, "gameEnd" => 99)
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
