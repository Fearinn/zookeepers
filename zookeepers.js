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
  "ebg/stock",
], function (dojo, declare) {
  return declare("bgagame.zookeepers", ebg.core.gamegui, {
    constructor: function () {
      console.log("zookeepers constructor");

      // Here, you can init the global variables of your user interface
      this.cardWidth = 120;
      this.cardHeight = 165;
      this.topsPositions = {
        1: "-126px -171px",
        2: "-126px -3px",
        3: "-3px -171px",
        4: "-3px -3px",
        5: "-249px -3px",
      };

      this.mainAction = 0;
      this.freeAction = 0;
      this.resourceCounters = {};
      this.bagCounters = {};
      this.isBagEmpty = false;
      this.allKeepers = {};
      this.pileCounters = {};
      this.pilesTops = {};
      this.keepersOnBoards = {};
      this.allSpecies = {};
      this.visibleSpecies = {};
      this.savableSpecies = {};
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

      this.isBagEmpty = gamedatas.isBagEmpty;

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

        this.updateResourceCounters(
          gamedatas.resourceCounters[player_id],
          player_id
        );
      }

      const plantBagCounter = new ebg.counter();
      plantBagCounter.create("zkp_bag_counter_plant");

      const meatBagCounter = new ebg.counter();
      meatBagCounter.create("zkp_bag_counter_meat");

      const kitBagCounter = new ebg.counter();
      kitBagCounter.create("zkp_bag_counter_kit");

      this.bagCounters = {
        plant: plantBagCounter,
        meat: meatBagCounter,
        kit: kitBagCounter,
      };

      this.updateBagCounters(gamedatas.bagCounters);

      // keepers
      this.allKeepers = gamedatas.allKeepers;
      this.keepersOnBoards = this.formatKeepersOnBoards(
        gamedatas.keepersOnBoards
      );

      for (const player_id in gamedatas.players) {
        for (let position = 1; position <= 4; position++) {
          const stockKey = `board_${player_id}:${position}`;
          this[stockKey] = new ebg.stock();
          this[stockKey].create(
            this,
            $(`zkp_keeper_${player_id}:${position}`),
            this.cardWidth,
            this.cardHeight
          );
          this[stockKey].setSelectionMode(0);

          this[
            stockKey
          ].extraClasses = `zkp_card zkp_hired_keeper-${player_id}`;

          this[stockKey].image_items_per_row = 7;

          for (const keeper_id in this.allKeepers) {
            this[stockKey].addItemType(
              keeper_id,
              1000,
              // wrong image, tests only
              g_gamethemeurl + "img/keepers.png",
              keeper_id - 1
            );
          }

          const addedKeeper = this.keepersOnBoards[player_id][position];

          if (addedKeeper && addedKeeper.card_type_arg) {
            const pile = addedKeeper.pile;
            if (pile > 0) {
              this[stockKey].addToStockWithId(
                addedKeeper.card_type_arg,
                addedKeeper.card_type_arg,
                `zkp_keeper_pile:${pile}`
              );
            } else {
              this[stockKey].addToStockWithId(
                addedKeeper.card_type_arg,
                addedKeeper.card_type_arg
              );
            }
          }
        }
      }

      this.pileCounters = gamedatas.pileCounters;
      this.pilesTops = gamedatas.pilesTops;

      for (pile in this.pileCounters) {
        const element = `zkp_keeper_pile:${pile}`;
        const className = "zkp_empty_pile";
        const top = this.pilesTops[pile];

        dojo.style(element, "backgroundPosition", this.topsPositions[top]);

        if (this.pileCounters[pile] < 1 && !dojo.hasClass(element, className)) {
          dojo.addClass(element, className);
        }

        if (this.pileCounters > 0 && dojo.hasClass(element, className)) {
          dojo.removeClass(element, className);
        }
      }

      // species
      this.allSpecies = gamedatas.allSpecies;
      this.visibleSpecies = gamedatas.visibleSpecies;
      this.savableSpecies = this.formatSavableSpecies(gamedatas.savableSpecies);

      console.log(this.savableSpecies);

      for (let column = 1; column <= 4; column++) {
        const stockKey = `visibleShop_${column}`;
        this[stockKey] = new ebg.stock();
        this[stockKey].create(
          this,
          $("zkp_visible_species_" + column),
          this.cardWidth,
          this.cardHeight
        );

        this[stockKey].setSelectionMode(0);
        this[stockKey].extraClasses = "zkp_card";
        this[stockKey].image_items_per_row = 10;

        for (const species_id in this.allSpecies) {
          this[stockKey].addItemType(
            species_id,
            species_id,
            g_gamethemeurl + "img/species.png",
            species_id - 1
          );
        }
      }

      for (const column in this.visibleSpecies) {
        const stockKey = `visibleShop_${column}`;
        const species_id = this.visibleSpecies[column].type_arg;

        this[stockKey].addToStockWithId(
          species_id,
          species_id,
          "zkp_species_deck"
        );
      }

      // event connections

      const playerId = this.getActivePlayerId();

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectKeeperPile(event);
      });

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectDismissedPile(event);
      });

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectReplacedPile(event);
      });

      dojo
        .query(`.zkp_keeper-${playerId}`)
        .connect("onclick", this, (event) => {
          this.onSelectDismissedKeeper(event);
        });

      dojo
        .query(`.zkp_keeper-${playerId}`)
        .connect("onclick", this, (event) => {
          this.onSelectReplacedKeeper(event);
        });

      dojo.query(".zkp_visible_species").connect("onclick", this, (event) => {
        this.onSelectSavedSpecies(event);
      });

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
      const playerId = this.getActivePlayerId();

      if (stateName === "playerTurn") {
        this.mainAction = args.args.mainAction;
        this.freeAction = args.args.freeAction;
        this.isBagEmpty = args.args.isBagEmpty;

        this.keepersOnBoards = this.formatKeepersOnBoards(
          args.args.keepers_on_boards
        );

        let openBoardPosition = 0;
        let boardEmpty = true;

        for (const position in this.keepersOnBoards[playerId]) {
          const keeperOnPosition = this.keepersOnBoards[playerId][position];
          if (!keeperOnPosition) {
            openBoardPosition = position;
            break;
          }
        }

        for (const position in this.keepersOnBoards[playerId]) {
          const keeperOnPosition = this.keepersOnBoards[playerId][position];
          if (keeperOnPosition && keeperOnPosition.card_type_arg) {
            boardEmpty = false;
            break;
          }
        }

        if (this.isCurrentPlayerActive()) {
          if (this.mainAction < 1) {
            if (this.savableSpecies) {
              this.addActionButton(
                "save_species_btn",
                _("Save Species"),
                "onSaveSpecies"
              );
            }

            if (openBoardPosition > 0) {
              this.addActionButton(
                "hire_keeper_btn",
                _("Hire Keeper"),
                "onHireKeeper"
              );
            }

            if (!boardEmpty) {
              this.addActionButton(
                "replace_keeper_btn",
                _("Replace Keeper"),
                "onReplaceKeeper"
              );

              this.addActionButton(
                "dismiss_keeper_btn",
                _("Dismiss Keeper"),
                "onDismissKeeper"
              );
            }

            if (!this.isBagEmpty) {
              this.addActionButton(
                "collect_resources_btn",
                _("Collect Resources"),
                "onCollectResources"
              );
            }

            if (this.freeAction < 1 && !this.isBagEmpty) {
              this.addActionButton(
                "exchange_resources_btn",
                _("Conservation Fund"),
                "onExchangeResources"
              );
            }
          }

          this.addActionButton(
            "pass_btn",
            _("Pass Turn"),
            "onPass",
            null,
            null,
            "red"
          );
        }
        return;
      }

      if (stateName === "selectHiredPile") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".zkp_keeper_pile").forEach((element) => {
            if (!dojo.hasClass(element, "zkp_empty_pile")) {
              dojo.setStyle(element, {
                border: "3px solid green",
                cursor: "pointer",
              });
            }
          });

          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );
        }
      }
      if (stateName === "selectDismissedKeeper") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          const query = dojo.query(`.zkp_hired_keeper-${playerId}`);

          query.removeClass("stockitem_unselectable");
          query.style({
            border: "3px solid green",
          });

          dojo.query(`.zkp_keeper-${playerId}`).style({ cursor: "pointer" });
        }
      }

      if (stateName === "selectDismissedPile") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          dojo.query(`.zkp_keeper_pile`).style({
            border: "3px solid green",
            cursor: "pointer",
          });
        }
      }

      if (stateName === "selectReplacedKeeper") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          const query = dojo.query(`.zkp_hired_keeper-${playerId}`);

          query.removeClass("stockitem_unselectable");
          query.style({
            border: "3px solid green",
          });

          dojo.query(`.zkp_keeper-${playerId}`).style({ cursor: "pointer" });
        }
      }

      if (stateName === "selectReplacedPile") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          dojo.query(`.zkp_keeper_pile`).style({
            border: "3px solid green",
            cursor: "pointer",
          });
        }
      }

      if (stateName === "exchangeCollection") {
        this.freeAction = args.args.freeAction;

        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelExchange",
            null,
            null,
            "red"
          );
          for (let i = 1; i < args.args.resources_in_hand_nbr; i++) {
            this.addActionButton(
              "exchange_resources_option_" + i,
              i.toString(),
              () => this.onCollectFromExchange(i)
            );
          }
          return;
        }
      }

      if (stateName === "exchangeReturn") {
        const playerId = this.getActivePlayerId();
        const activePlayerCounters = this.resourceCounters[playerId];

        if (this.isCurrentPlayerActive()) {
          for (const type in activePlayerCounters) {
            if (activePlayerCounters[type].getValue() > 0) {
              this.addActionButton(
                "image_btn_" + type,
                `<div class="zkp_resource_icon zkp_${type}_icon"></div>`,
                () => {},
                null,
                null,
                "gray"
              );
              dojo.addClass("image_btn_" + type, "bgaimagebutton");

              for (
                let i = 1;
                i <= activePlayerCounters[type].getValue() &&
                i <= args.args.to_return;
                i++
              ) {
                this.addActionButton(
                  "exchange_resources_option_" + type + "_" + i,
                  i.toString(),
                  () => this.onReturnFromExchange(i, type)
                );
              }
            }
          }
        }
        return;
      }

      if (stateName === "returnExcess") {
        const playerId = this.getActivePlayerId();
        const activePlayerCounters = this.resourceCounters[playerId];

        if (this.isCurrentPlayerActive()) {
          for (const type in activePlayerCounters) {
            if (activePlayerCounters[type].getValue() > 0) {
              this.addActionButton(
                "image_btn_" + type,
                `<div class="zkp_resource_icon zkp_${type}_icon"></div>`,
                () => {},
                null,
                null,
                "gray"
              );
              dojo.addClass("image_btn_" + type, "bgaimagebutton");

              for (
                let i = 1;
                i <= activePlayerCounters[type].getValue() &&
                i <= args.args.to_return;
                i++
              ) {
                this.addActionButton(
                  "exchange_resources_option_" + type + "_" + i,
                  i.toString(),
                  () => this.onReturnExcess(i, type)
                );
              }
            }
          }
        }
        return;
      }

      if (stateName === "selectSavedSpecies") {
        this.addActionButton(
          "cancel_btn",
          "Cancel",
          "onCancelMngSpecies",
          null,
          null,
          "red"
        );

        const query = dojo.query(".zkp_visible_species > .stockitem");

        query.removeClass("stockitem_unselectable");
        query.style({
          border: "3px solid green",
        });

        dojo.query(".zkp_visible_species").style({ cursor: "pointer" });
      }

      if (stateName === "selectAssignedKeeper") {
        this.addActionButton(
          "cancel_btn",
          "Cancel",
          "onCancelMngSpecies",
          null,
          null,
          "red"
        );

        const query = dojo.query(`.zkp_keeper-${playerId} > .stockitem`);

        query.removeClass("stockitem_unselectable");
        query.style({
          border: "3px solid green",
        });

        dojo.query(`.zkp_keeper-${playerId}`).style({ cursor: "pointer" });
      }

      if (stateName === "betweenActions") {
        this.mainAction = args.args.mainAction;
        return;
      }

      if (stateName === "betweenPlayers") {
        this.mainAction = 0;
        this.freeAction = 0;
        return;
      }
    },

    // onLeavingState: this method is called each time we are leaving a game state.
    //                 You can use this method to perform some user interface changes at this moment.
    //
    onLeavingState: function (stateName) {
      console.log("Leaving state: " + stateName);

      const playerId = this.getActivePlayerId();

      if (stateName === "selectHiredPile") {
        dojo.query(".zkp_keeper_pile").style({
          border: "none",
          cursor: "initial",
        });
      }

      if (stateName === "selectDismissedKeeper") {
        const playerId = this.getActivePlayerId();
        dojo.query(`.zkp_hired_keeper-${playerId}`).style({
          border: "none",
          cursor: "initial",
        });
      }

      if (stateName === "selectDismissedPile") {
        dojo.query(".zkp_keeper_pile").style({
          border: "none",
          cursor: "initial",
        });
      }

      if (stateName === "selectReplacedKeeper") {
        const playerId = this.getActivePlayerId();
        dojo.query(`.zkp_hired_keeper-${playerId}`).style({
          border: "none",
          cursor: "initial",
        });
      }

      if (stateName === "selectReplacedPile") {
        dojo.query(".zkp_keeper_pile").style({
          border: "none",
          cursor: "initial",
        });
      }
      if (stateName === "selectSavedSpecies") {
        dojo.query(".zkp_visible_species").style({
          cursor: "initial",
        });

        dojo
          .query(".zkp_visible_species > .stockitem")
          .style({ border: "none" });
      }

      if (stateName === "selectAssignedKeeper") {
        dojo.query(`.zkp_keeper-${playerId}`).style({
          cursor: "initial",
        });

        dojo
          .query(`.zkp_keeper-${playerId} > .stockitem`)
          .style({ border: "none" });
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      // if (this.isCurrentPlayerActive()) {
      // }
    },

    ///////////////////////////////////////////////////
    //// Utility methods

    updateResourceCounters: function (counters, playerId) {
      for (const type in counters) {
        const newValue = counters[type];

        this.resourceCounters[playerId][type].toValue(newValue);
      }
    },

    updateBagCounters: function (counters) {
      for (const type in counters) {
        const newValue = counters[type];
        this.bagCounters[type].toValue(newValue);
      }
    },

    formatKeepersOnBoards: function (keepersOnBoards) {
      for (const playerId in keepersOnBoards) {
        for (const position in keepersOnBoards[playerId]) {
          const keeperOnPosition = keepersOnBoards[playerId][position];

          if (Array.isArray(keeperOnPosition)) {
            if (keeperOnPosition.length < 1) {
              keepersOnBoards[playerId][position] = null;
            } else {
              keepersOnBoards[playerId][position] = keeperOnPosition[0];
            }
          } else if (!keeperOnPosition.card_id) {
            for (const keeper in keeperOnPosition)
              keepersOnBoards[playerId][position] = keeperOnPosition[keeper];
          }
        }
      }
      return keepersOnBoards;
    },

    formatSavableSpecies: function (savableSpecies) {
      const species = savableSpecies;
      if (Array.isArray(savableSpecies)) {
        return null;
      }
      return species;
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
        if (this.mainAction < 1) {
          this.confirmationDialog(
            _(
              "You haven't used any main action yet. Are you sure you want to pass?"
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

    onHireKeeper: function () {
      const action = "hireKeeper";
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

    onSelectKeeperPile: function (event) {
      const action = "selectHiredPile";

      const pile = event.target.id.split(":")[1];
      dojo.stopEvent(event);

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            pile: parseInt(pile),
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onDismissKeeper: function () {
      const action = "dismissKeeper";

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

    onSelectDismissedKeeper: function (event) {
      const action = "selectDismissedKeeper";

      const position = event.target.id.split(":")[1].split("_")[0];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            board_position: position,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onSelectDismissedPile: function (event) {
      const action = "selectDismissedPile";

      const pile = event.target.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            pile: pile,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onReplaceKeeper() {
      const action = "replaceKeeper";

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

    onSelectReplacedKeeper: function (event) {
      const action = "selectReplacedKeeper";

      const position = event.target.id.split(":")[1].split("_")[0];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            board_position: position,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onSelectReplacedPile: function (event) {
      const action = "selectReplacedPile";

      const pile = event.target.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            pile: pile,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onCancelMngKeepers: function () {
      const action = "cancelMngKeepers";

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

    onExchangeResources: function () {
      const action = "exchangeResources";
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

    onCollectFromExchange: function (choosen_nbr) {
      const action = "collectFromExchange";
      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            choosen_nbr: choosen_nbr,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onCancelExchange: function () {
      const action = "cancelExchange";

      this.freeAction = 0;

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

    onReturnFromExchange: function (choosen_nbr, resource_type) {
      const action = "returnFromExchange";

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            lastly_returned_nbr: choosen_nbr,
            lastly_returned_type: resource_type,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onReturnExcess: function (choosen_nbr, resource_type) {
      const action = "returnExcess";

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            lastly_returned_nbr: choosen_nbr,
            lastly_returned_type: resource_type,
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onSaveSpecies: function () {
      const action = "saveSpecies";

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

    onSelectSavedSpecies: function (event) {
      const action = "selectSavedSpecies";

      const position = event.currentTarget.id.split("species_")[1];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            shop_position: parseInt(position),
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onSelectAssignedKeeper: function (event) {
      const action = "selectAssignedKeeper";

      const position = event.currentTarget.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            board_position: parseInt(position),
          },
          this,
          function (result) {},
          function (is_error) {}
        );
      }
    },

    onCancelMngSpecies: function (event) {
      const action = "cancelMngSpecies";

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

      dojo.subscribe("hireKeeper", this, "notif_hireKeeper");
      dojo.subscribe("dismissKeeper", this, "notif_dismissKeeper");
      dojo.subscribe("collectResources", this, "notif_collectResources");
      dojo.subscribe("returnResources", this, "notif_returnResources");
      dojo.subscribe("pass", this, "notif_pass");
    },

    notif_hireKeeper: function (notif) {
      this.pileCounters = notif.args.pile_counters;
      this.pilesTops = notif.args.piles_tops;

      const player_id = notif.args.player_id;
      const keeper_id = notif.args.keeper_id;
      const position = notif.args.board_position;
      const stockKey = `board_${player_id}:${position}`;

      this[stockKey].addToStockWithId(
        keeper_id,
        keeper_id,
        `zkp_keeper_pile:${notif.args.pile}`
      );

      for (const pile in this.pileCounters) {
        const element = `zkp_keeper_pile:${pile}`;
        const className = "zkp_empty_pile";

        const top = this.pilesTops[pile];
        dojo.style(element, "backgroundPosition", this.topsPositions[top]);

        if (this.pileCounters[pile] < 1 && !dojo.hasClass(element, className)) {
          dojo.addClass(element, className);
        }
      }
    },

    notif_dismissKeeper: function (notif) {
      this.pileCounters = notif.args.pile_counters;
      this.pilesTops = notif.args.piles_tops;

      const player_id = notif.args.player_id;
      const keeper_id = notif.args.keeper_id;
      const position = notif.args.board_position;
      const pile = notif.args.pile;

      const stockKey = `board_${player_id}:${position}`;
      const pileElement = `zkp_keeper_pile:${pile}`;
      const className = "zkp_empty_pile";
      const container = `zkp_keeper_${player_id}:${position}`;
      const item = `${container}_item_${keeper_id}`;

      dojo.place(this.format_block("jstpl_dismissed_keeper"), container);

      this.placeOnObject("zkp_dismissed_keeper", item);

      const animation = this.slideToObject("zkp_dismissed_keeper", pileElement);

      dojo.connect(animation, "onEnd", () => {
        this[stockKey].removeFromStockById(keeper_id, pileElement);
        const top = this.pilesTops[pile];
        dojo.style(pileElement, "backgroundPosition", this.topsPositions[top]);

        if (
          this.pileCounters[pile] > 0 &&
          dojo.hasClass(pileElement, className)
        ) {
          dojo.removeClass(pileElement, className);
        }
      });

      animation.play();
    },

    notif_collectResources: function (notif) {
      const player_id = notif.args.player_id;

      const newResourceCounters = notif.args.resource_counters[player_id];
      console.log(newResourceCounters);

      const collectedArgs = {
        plant: notif.args["collected_plant_nbr"],
        meat: notif.args["collected_meat_nbr"],
        kit: notif.args["collected_kit_nbr"],
      };

      let loopIterator = 0;

      for (const type in collectedArgs) {
        const collected_nbr = collectedArgs[type];

        if (collected_nbr > 0) {
          for (let i = 1; i <= collected_nbr; i++) {
            loopIterator++;
            dojo.place(
              this.format_block("jstpl_resource_cube", {
                type: type,
                nbr: i,
              }),
              "zkp_bag_img"
            );

            this.placeOnObject(`zkp_${type}_cube_${i}`, "zkp_bag_img");

            this.slideToObjectAndDestroy(
              `zkp_${type}_cube_${i}`,
              "overall_player_board_" + player_id,
              500,
              (loopIterator - 1) * 100
            );
          }
        }
      }
      this.updateResourceCounters(newResourceCounters, player_id);
      this.updateBagCounters(notif.args.bag_counters, player_id);
    },

    notif_returnResources: function (notif) {
      const player_id = notif.args.player_id;

      const newResourcesCounter = notif.args.resource_counters[player_id];

      const returned_nbr = notif.args.returned_nbr;
      const type = notif.args.type;

      if (returned_nbr > 0) {
        for (let i = 1; i <= returned_nbr; i++) {
          dojo.place(
            this.format_block("jstpl_resource_cube", {
              type: type,
              nbr: i,
            }),
            "overall_player_board_" + player_id
          );

          this.placeOnObject(
            `zkp_${type}_cube_${i}`,
            "overall_player_board_" + player_id
          );

          this.slideToObjectAndDestroy(
            `zkp_${type}_cube_${i}`,
            "zkp_bag_img",
            500,
            (i - 1) * 100
          );
        }
      }
      this.updateResourceCounters(newResourcesCounter, notif.args.player_id);
      this.updateBagCounters(notif.args.bag_counters, notif.args.player_id);
    },

    notif_pass: function (notif) {},
  });
});
