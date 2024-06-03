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
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->pass();
    $this->ajaxResponse();
  }

  public function collectResources()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->collectResources();
    $this->ajaxResponse();
  }

  public function hireKeeper()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $pile = $this->getArg("pile", AT_enum, true, null, range(1, 4));
    $this->game->hireKeeper($pile);
    $this->ajaxResponse();
  }

  public function dismissKeeper()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $board_position = $this->getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->dismissKeeper($board_position);
    $this->ajaxResponse();
  }

  public function selectDismissedPile()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $pile = $this->getArg("pile", AT_enum, true, null, range(1, 4));
    $this->game->selectDismissedPile($pile);
    $this->ajaxResponse();
  }

  public function replaceKeeper()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $board_position = $this->getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->replaceKeeper($board_position);
    $this->ajaxResponse();
  }

  public function selectReplacedPile()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $pile = $this->getArg("pile", AT_enum, true,  null, range(1, 4));
    $this->game->selectReplacedPile($pile);
    $this->ajaxResponse();
  }

  public function cancelMngKeepers()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->cancelMngKeepers();
    $this->ajaxResponse();
  }

  public function exchangeResources()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->exchangeResources();
    $this->ajaxResponse();
  }

  public function collectFromExchange()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $choosen_nbr = $this->getArg("choosen_nbr", AT_enum, true, null, range(1, 5));
    $this->game->collectFromExchange($choosen_nbr);
    $this->ajaxResponse();
  }

  public function cancelExchange()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->cancelExchange();
    $this->ajaxResponse();
  }

  public function returnFromExchange()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $lastly_returned_nbr = $this->getArg("lastly_returned_nbr", AT_enum, true, null, range(1, 10));
    $lastly_returned_type = $this->getArg("lastly_returned_type", AT_enum, true, null, array("plant", "meat", "kit"));
    $this->game->returnFromExchange($lastly_returned_nbr, $lastly_returned_type);
    $this->ajaxResponse();
  }

  public function returnExcess()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $lastly_returned_nbr = $this->getArg("lastly_returned_nbr", AT_enum, true, null, range(1, 6));
    $lastly_returned_type = $this->getArg("lastly_returned_type", AT_enum, true, null, array("plant", "meat", "kit"));
    $this->game->returnExcess($lastly_returned_nbr, $lastly_returned_type);
    $this->ajaxResponse();
  }

  public function saveSpecies()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $shop_position = $this->getArg("shop_position", AT_enum, true, null, range(1, 4));
    $this->game->saveSpecies($shop_position);
    $this->ajaxResponse();
  }

  public function selectAssignedKeeper()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $board_position = $this->getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->selectAssignedKeeper($board_position);
    $this->ajaxResponse();
  }

  public function saveQuarantined()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $species_id = $this->getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->saveQuarantined($species_id);
    $this->ajaxResponse();
  }

  public function selectQuarantinedKeeper()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $board_position = $this->getArg("board_position", AT_enum, true, null, range(1, 4));
    $this->game->selectQuarantinedKeeper($board_position);
    $this->ajaxResponse();
  }

  public function discardSpecies()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $species_id = $this->getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->discardSpecies($species_id);
    $this->ajaxResponse();
  }

  public function quarantineSpecies()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $species_id = $this->getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->quarantineSpecies($species_id);
    $this->ajaxResponse();
  }

  public function selectQuarantine()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $quarantine = $this->getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectQuarantine($quarantine);
    $this->ajaxResponse();
  }

  public function lookAtBackup()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $shop_position = $this->getArg("shop_position", AT_enum, true, null, range(1, 4));
    $backup_id = $this->getArg("backup_id", AT_enum, true, null, range(1, 4));
    $this->game->lookAtBackup($shop_position, $backup_id);
    $this->ajaxResponse();
  }

  public function discardBackup()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->discardBackup();
    $this->ajaxResponse();
  }

  public function quarantineBackup()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->quarantineBackup();
    $this->ajaxResponse();
  }

  public function selectBackupQuarantine()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $quarantine = $this->getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectBackupQuarantine($quarantine);
    $this->ajaxResponse();
  }

  public function cancelMngSpecies()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->cancelMngSpecies();
    $this->ajaxResponse();
  }

  public function newSpecies()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $this->game->newSpecies();
    $this->ajaxResponse();
  }

  public function zooHelp()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $species_id = $this->getArg("species_id", AT_enum, true, null, range(1, 70));
    $this->game->zooHelp($species_id);
    $this->ajaxResponse();
  }

  public function selectZoo()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $player_id = $this->getArg("player_id", AT_posint, true);
    $this->game->selectZoo($player_id);
    $this->ajaxResponse();
  }

  public function selectHelpQuarantine()
  {
    $this->setAjaxMode();
    $this->checkVersion();
    $quarantine = $this->getArg(
      "quarantine",
      AT_enum,
      true,
      null,
      array("ALL", "TEM", "SAV", "PRA", "DES", "AQU", "TRO")
    );
    $this->game->selectHelpQuarantine($quarantine);
    $this->ajaxResponse();
  }

  public function replaceObjective()
  {
    $this->setAjaxMode();
    $this->game->replaceObjective();
    $this->ajaxResponse();
  }
}
