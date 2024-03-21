<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Zookeepers implementation : © <Matheus Gomes> <matheusgomesforwork@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * zookeepers.action.php
 *
 * Zookeepers main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/zookeepers/zookeepers/myAction.html", ...)
 *
 */


class action_zookeepers extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = "common_notifwindow";
      $this->viewArgs['table'] = self::getArg("table", AT_posint, true);
    } else {
      $this->view = "zookeepers_zookeepers";
      self::trace("Complete reinitialization of board game");
    }
  }

  public function pass()
  {
    self::setAjaxMode();
    $this->game->pass();
    self::ajaxResponse();
  }

  public function collectResources()
  {
    self::setAjaxMode();
    $this->game->collectResources();
    self::ajaxResponse();
  }

  public function hireKeeper()
  {
    self::setAjaxMode();
    $this->game->hireKeeper();
    self::ajaxResponse();
  }

  public function selectHiredPile()
  {
    self::setAjaxMode();
    $pile = self::getArg("pile", AT_posint, true);
    $this->game->selectHiredPile($pile);
    self::ajaxResponse();
  }

  public function dismissKeeper()
  {
    self::setAjaxMode();
    $board_position = self::getArg("board_position", AT_posint, true);
    $this->game->dismissKeeper($board_position);
    self::ajaxResponse();
  }

  public function selectDismissedPile()
  {
    self::setAjaxMode();
    $pile = self::getArg("pile", AT_posint, true);
    $this->game->selectDismissedPile($pile);
    self::ajaxResponse();
  }

  public function replaceKeeper()
  {
    self::setAjaxMode();
    $board_position = self::getArg("board_position", AT_posint, true);
    $this->game->replaceKeeper($board_position);
    self::ajaxResponse();
  }

  public function selectReplacedPile()
  {
    self::setAjaxMode();
    $pile = self::getArg("pile", AT_posint, true);
    $this->game->selectReplacedPile($pile);
    self::ajaxResponse();
  }

  public function cancelMngKeepers()
  {
    self::setAjaxMode();
    $this->game->cancelMngKeepers();
    self::ajaxResponse();
  }

  public function exchangeResources()
  {
    self::setAjaxMode();
    $this->game->exchangeResources();
    self::ajaxResponse();
  }

  public function collectFromExchange()
  {
    self::setAjaxMode();
    $choosen_nbr = self::getArg("choosen_nbr", AT_posint, true);
    $this->game->collectFromExchange($choosen_nbr);
    self::ajaxResponse();
  }

  public function cancelExchange()
  {
    self::setAjaxMode();
    $this->game->cancelExchange();
    self::ajaxResponse();
  }

  public function returnFromExchange()
  {
    self::setAjaxMode();
    $lastly_returned_nbr = self::getArg("lastly_returned_nbr", AT_posint, true);
    $lastly_returned_type = self::getArg("lastly_returned_type", AT_alphanum, true);
    $this->game->returnFromExchange($lastly_returned_nbr, $lastly_returned_type);
    self::ajaxResponse();
  }

  public function returnExcess()
  {
    self::setAjaxMode();
    $lastly_returned_nbr = self::getArg("lastly_returned_nbr", AT_posint, true);
    $lastly_returned_type = self::getArg("lastly_returned_type", AT_alphanum, true);
    $this->game->returnExcess($lastly_returned_nbr, $lastly_returned_type);
    self::ajaxResponse();
  }

  public function saveSpecies()
  {
    self::setAjaxMode();
    $shop_position = self::getArg("shop_position", AT_posint, true);
    $this->game->saveSpecies($shop_position);
    self::ajaxResponse();
  }

  public function discardSpecies()
  {
    self::setAjaxMode();
    $species_id = self::getArg("species_id", AT_posint, true);
    $this->game->discardSpecies($species_id);
    self::ajaxResponse();
  }

  public function lookAtBackup()
  {
    self::setAjaxMode();
    $shop_position = self::getArg("shop_position", AT_posint, true);
    $backup_id = self::getArg("backup_id", AT_posint, true);
    $this->game->lookAtBackup($shop_position, $backup_id);
    self::ajaxResponse();
  }

  public function discardBackup()
  {
    self::setAjaxMode();
    $shop_position = self::getArg("shop_position", AT_posint, true);
    $backup_id = self::getArg("backup_id", AT_posint, true);
    $this->game->discardBackup($shop_position, $backup_id);
    self::ajaxResponse();
  }

  public function quarantineSpecies()
  {
    self::setAjaxMode();
    $species_id = self::getArg("species_id", AT_posint, true);
    $this->game->quarantineSpecies($species_id);
    self::ajaxResponse();
  }

  public function selectAssignedKeeper()
  {
    self::setAjaxMode();
    $board_position = self::getArg("board_position", AT_posint, true);
    $this->game->selectAssignedKeeper($board_position);
    self::ajaxResponse();
  }

  public function cancelMngSpecies()
  {
    self::setAjaxMode();
    $this->game->cancelMngSpecies();
    self::ajaxResponse();
  }
}
