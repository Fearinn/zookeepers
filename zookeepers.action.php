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

  private function checkVersion()
  {
    $clientVersion = (int) $this->getArg('gameVersion', AT_int, false);
    $this->game->checkVersion($clientVersion);
  }

  public function pass()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->pass();
    self::ajaxResponse();
  }

  public function collectResources()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->collectResources();
    self::ajaxResponse();
  }

  public function hireKeeper()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->hireKeeper();
    self::ajaxResponse();
  }

  public function selectHiredPile()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $pile = self::getArg("pile", AT_enum, true, null, range(1, 4));
    $this->game->selectHiredPile($pile);
    self::ajaxResponse();
  }

  public function dismissKeeper()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $board_position = self::getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->dismissKeeper($board_position);
    self::ajaxResponse();
  }

  public function selectDismissedPile()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $pile = self::getArg("pile", AT_enum, true, null, range(1, 4));
    $this->game->selectDismissedPile($pile);
    self::ajaxResponse();
  }

  public function replaceKeeper()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $board_position = self::getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->replaceKeeper($board_position);
    self::ajaxResponse();
  }

  public function selectReplacedPile()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $pile = self::getArg("pile", AT_enum, true,  null, range(1, 4));
    $this->game->selectReplacedPile($pile);
    self::ajaxResponse();
  }

  public function cancelMngKeepers()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->cancelMngKeepers();
    self::ajaxResponse();
  }

  public function exchangeResources()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->exchangeResources();
    self::ajaxResponse();
  }

  public function collectFromExchange()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $choosen_nbr = self::getArg("choosen_nbr", AT_enum, true, null, array(1, 2, 3, 4, 5));
    $this->game->collectFromExchange($choosen_nbr);
    self::ajaxResponse();
  }

  public function cancelExchange()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->cancelExchange();
    self::ajaxResponse();
  }

  public function returnFromExchange()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $lastly_returned_nbr = self::getArg("lastly_returned_nbr", AT_enum, true, null, range(1, 10));
    $lastly_returned_type = self::getArg("lastly_returned_type", AT_enum, true, null, array("plant", "meat", "kit"));
    $this->game->returnFromExchange($lastly_returned_nbr, $lastly_returned_type);
    self::ajaxResponse();
  }

  public function returnExcess()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $lastly_returned_nbr = self::getArg("lastly_returned_nbr", AT_enum, true, null, range(1, 8));
    $lastly_returned_type = self::getArg("lastly_returned_type", AT_enum, true, null, array("plant", "meat", "kit"));
    $this->game->returnExcess($lastly_returned_nbr, $lastly_returned_type);
    self::ajaxResponse();
  }

  public function saveSpecies()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $shop_position = self::getArg("shop_position", AT_enum, true, null, range(1, 4));
    $this->game->saveSpecies($shop_position);
    self::ajaxResponse();
  }

  public function selectAssignedKeeper()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $board_position = self::getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->selectAssignedKeeper($board_position);
    self::ajaxResponse();
  }

  public function saveQuarantined()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $species_id = self::getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->saveQuarantined($species_id);
    self::ajaxResponse();
  }

  public function selectQuarantinedKeeper()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $board_position = self::getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->selectQuarantinedKeeper($board_position);
    self::ajaxResponse();
  }

  public function discardSpecies()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $species_id = self::getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->discardSpecies($species_id);
    self::ajaxResponse();
  }

  public function quarantineSpecies()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $species_id = self::getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->quarantineSpecies($species_id);
    self::ajaxResponse();
  }

  public function selectQuarantine()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $quarantine = self::getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectQuarantine($quarantine);
    self::ajaxResponse();
  }

  public function lookAtBackup()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $shop_position = self::getArg("shop_position", AT_enum, true, null, range(1, 4));
    $backup_id = self::getArg("backup_id", AT_enum, true, null, range(1, 4));
    $this->game->lookAtBackup($shop_position, $backup_id);
    self::ajaxResponse();
  }

  public function discardBackup()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->discardBackup();
    self::ajaxResponse();
  }

  public function quarantineBackup()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->quarantineBackup();
    self::ajaxResponse();
  }

  public function selectBackupQuarantine()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $quarantine = self::getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectBackupQuarantine($quarantine);
    self::ajaxResponse();
  }

  public function cancelMngSpecies()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->cancelMngSpecies();
    self::ajaxResponse();
  }

  public function newSpecies()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $this->game->newSpecies();
    self::ajaxResponse();
  }

  public function zooHelp()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $species_id = self::getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->zooHelp($species_id);
    self::ajaxResponse();
  }

  public function selectZoo()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $player_id = self::getArg("player_id", AT_posint, true);
    $this->game->selectZoo($player_id);
    self::ajaxResponse();
  }

  public function selectHelpQuarantine()
  {
    self::setAjaxMode();
    $this->checkVersion();
    $quarantine = self::getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectHelpQuarantine($quarantine);
    self::ajaxResponse();
  }

  public function replaceObjective()
  {
    $this->setAjaxMode();
    $this->game->replaceObjective();
    $this->ajaxResponse();
  }
}
