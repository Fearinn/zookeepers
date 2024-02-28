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
 * zookeepers.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in zookeepers_zookeepers.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_zookeepers_zookeepers extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "zookeepers";
    }

    function build_page($viewArgs)
    {
        // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count($players);

        /*********** Place your code below:  ************/

        $template = self::getGameName() . "_" . self::getGameName();

        $this->page->begin_block($template, "playmatblock");
        foreach ($players as $player_id => $player) {
            $this->page->insert_block("playmatblock", array(
                "PLAYER_NAME" => $player["player_name"],
                "PLAYER_ID" => $player_id,
                "PLAYER_COLOR" => $player["player_color"],
            ));
        }

        $this->tpl["BAG OF RESOURCES"] = self::_("Bag of resources");

        /*********** Do not change anything below this line  ************/
    }
}
