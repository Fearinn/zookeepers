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

  public function selectKeeperPile()
  {
    self::setAjaxMode();
    $pile = self::getArg("pile", AT_posint, true);
    $this->game->selectKeeperPile($pile);
    self::ajaxResponse();
  }

  public function dismissKeeper()
  {
    self::setAjaxMode();
    $this->game->dismissKeeper();
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
}
