/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Zookeepers implementation : © <Matheus Gomes> <matheusgomesforwork@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * zookeepers.js
 *
 * Zookeepers user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
], function (dojo, declare) {
  return declare("bgagame.zookeepers", ebg.core.gamegui, {
    constructor: function () {
      console.log("zookeepers constructor");

      // Here, you can init the global variables of your user interface

      this.resourceCounters = {};
      this.mainAction = 0;
    },

    /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

    setup: function (gamedatas) {
      console.log("Starting game setup");

      for (const player_id in gamedatas.players) {
        const player = gamedatas.players[player_id];

        const player_board_div = $("player_board_" + player_id);
        dojo.place(
          this.format_block("jstpl_player_board", player),
          player_board_div
        );

        const plantCounter = new ebg.counter();
        plantCounter.create("plant_count_p" + player_id);

        const meatCounter = new ebg.counter();
        meatCounter.create("meat_count_p" + player_id);

        const kitCounter = new ebg.counter();
        kitCounter.create("kit_count_p" + player_id);

        this.resourceCounters = {
          ...this.resourceCounters,
          [player_id]: {
            plant: plantCounter,
            meat: meatCounter,
            kit: kitCounter,
          },
        };

        gamedatas.resourceCounters.forEach((object) => {
          if (object[player_id]) {
            this.updateResourceCounters(object[player_id], player_id);
          }
        });
      }

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    // onEnteringState: this method is called each time we are entering into a new game state.
    //                  You can use this method to perform some user interface changes at this moment.
    //
    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      if (stateName === "playerTurn") {
        this.mainAction = args.args.mainAction;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      switch (stateName) {
        /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */

        case "dummmy":
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      console.log("onUpdateActionButtons: " + stateName);

      if (this.isCurrentPlayerActive()) {
        if (stateName === "playerTurn") {
          this.addActionButton(
            "collect_resources_btn",
            _("Collect Resources"),
            "onCollectResources"
          );
          if (!this.mainAction) {
            dojo.addClass("collect_resources_btn", "disabled");
          }

          this.addActionButton("pass_btn", _("Pass Turn"), "onPass");
        }
      }
    },

    ///////////////////////////////////////////////////
    //// Utility methods

    updateResourceCounters: function (counters, player_id) {
      for (const counter_container_id in counters) {
        const counter_value = counters[counter_container_id];

        this.resourceCounters[player_id][counter_container_id].toValue(
          counter_value
        );
      }
    },

    ///////////////////////////////////////////////////
    //// Player's action

    /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
    onPass: function () {
      const action = "pass";

      if (this.checkAction(action, true)) {
        if (!this.mainAction) {
          this.confirmationDialog(
            _(
              "You didn't use any main action yet. Are you sure you want to pass?"
            ),
            () => {
              this.ajaxcall(
                "/" +
                  this.game_name +
                  "/" +
                  this.game_name +
                  "/" +
                  action +
                  ".html",
                {
                  lock: true,
                },
                this,
                function (result) {},
                function (is_error) {}
              );
            }
          );
          return;
        }

        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onCollectResources: function () {
      const action = "collectResources";
      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    ///////////////////////////////////////////////////
    //// Reaction to cometD notifications

    /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your zookeepers.game.php file.
        
        */
    setupNotifications: function () {
      console.log("notifications subscriptions setup");

      dojo.subscribe("collectResources", this, "notif_collectResources");
      dojo.subscribe("pass", this, "notif_pass");
    },

    notif_collectResources: function (notif) {
      currentPlayerCounters = notif.args.counters.find((object) => {
        return notif.args.player_id === Object.keys(object)[0];
      })[notif.args.player_id];

      this.updateResourceCounters(currentPlayerCounters, notif.args.player_id);
    },

    notif_pass: function (notif) {},
  });
});
