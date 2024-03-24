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

      this.isRealTimeScoreTracking = false;

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
      this.backupSpecies = {};
      this.visibleSpecies = {};
      this.savableSpecies = {};
      this.savableWithFund = {};
      this.savedSpecies = {};
      this.completedKeepers = {};
      this.speciesCounters = {};
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

      this.isRealTimeScoreTracking = gamedatas.isRealTimeScoreTracking;

      this.allKeepers = gamedatas.allKeepers;
      this.keepersOnBoards = this.formatKeepersOnBoards(
        gamedatas.keepersOnBoards
      );
      this.completedKeepers = gamedatas.completedKeepers;

      this.allSpecies = gamedatas.allSpecies;
      this.backupSpecies = gamedatas.backupSpecies;
      this.visibleSpecies = gamedatas.visibleSpecies;
      this.savableSpecies = this.formatSavableSpecies(gamedatas.savableSpecies);
      this.savableWithFund = this.formatSavableSpecies(
        gamedatas.savableWithFund
      );
      this.savedSpecies = gamedatas.savedSpecies;
      this.allQuarantines = gamedatas.allQuarantines;

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

          this[stockKey].setSelectionMode(1);
          dojo.connect(this[stockKey], "onChangeSelection", this, () => {
            this.onSelectKeeper(this[stockKey]);
          });

          this[stockKey].extraClasses = "zkp_card zkp_hired_keeper";
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

          this[stockKey].container_div.width = "120px";
          this[stockKey].autowidth = false;
          this[stockKey].use_vertical_overlap_as_offset = false;
          this[stockKey].vertical_overlap = 95;
          this[stockKey].horizontal_overlap = -1;
          this[stockKey].item_margin = 0;
          this[stockKey].updateDisplay();

          this[stockKey].image_items_per_row = 10;

          for (const species_id in this.allSpecies) {
            this[stockKey].addItemType(
              `species_${species_id}`,
              0,
              g_gamethemeurl + "img/species.png",
              species_id - 1
            );
          }

          const savedSpecies = this.savedSpecies[player_id][position];
          if (savedSpecies) {
            for (const speciesId in savedSpecies) {
              this[stockKey].addToStockWithId(
                `species_${speciesId}`,
                `species_${speciesId}`
              );
            }
          }

          const completedKeeper = this.completedKeepers[player_id][position];

          if (completedKeeper) {
            const keeperId = completedKeeper.type_arg;
            const element = `zkp_keeper_${player_id}:${position}_item_${keeperId}`;
            dojo.addClass(element, "zkp_completed_keeper");

            const level = this.allKeepers[keeperId].level;
            const backgroundPosition = this.topsPositions[level];

            dojo.setStyle(element, {
              backgroundPosition: backgroundPosition,
            });
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

      for (let column = 1; column <= 4; column++) {
        const stockKey = `visibleShop_${column}`;
        this[stockKey] = new ebg.stock();
        this[stockKey].create(
          this,
          $("zkp_visible_species_" + column),
          this.cardWidth,
          this.cardHeight
        );

        this[stockKey].setSelectionMode(1);
        dojo.connect(this[stockKey], "onChangeSelection", this, () => {
          this.onSelectSpecies(this[stockKey]);
        });

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
        const species_id = this.visibleSpecies[column]?.type_arg;

        if (species_id) {
          this[stockKey].addToStockWithId(
            species_id,
            species_id,
            "zkp_species_deck"
          );
        }
      }

      for (let column = 1; column <= 4; column++) {
        const stockKey = `backupShop_${column}`;
        this[stockKey] = new ebg.stock();
        this[stockKey].create(
          this,
          $(`zkp_backup_column:${column}`),
          this.cardWidth,
          this.cardHeight
        );

        this[stockKey].setSelectionMode(1);
        dojo.connect(this[stockKey], "onChangeSelection", this, (target) => {
          this.onSelectBackup(target, this[stockKey]);
        });

        this[stockKey].extraClasses = "zkp_card zkp_background_contain";

        this[stockKey].addItemType(
          0,
          0,
          g_gamethemeurl + "img/species_back.png",
          0
        );

        const species_nbr = this.backupSpecies[column];

        for (let i = 1; i <= species_nbr; i++) {
          this[stockKey].addToStockWithId(0, i, "zkp_species_deck");
        }
      }

      for (const player_id in gamedatas.players) {
        this.speciesCounters[player_id] = new ebg.counter();
        this.speciesCounters[player_id].create(
          `zkp_species_count_${player_id}`
        );
      }
      this.updateSpeciesCounters(gamedatas.speciesCounters);

      // event connections

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectKeeperPile(event);
      });

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectDismissedPile(event);
      });

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectReplacedPile(event);
      });

      for (const player_id in gamedatas.players) {
        dojo
          .query(`.zkp_keeper-${player_id}`)
          .connect("onclick", this, (event) => {
            this.onSelectAssignedKeeper(event);
          });

        dojo
          .query(`.zkp_quarantine_${player_id}`)
          .connect("onclick", this, (event) => {
            this.onSelectQuarantine(event);
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
      const playerId = this.getActivePlayerId();

      if (stateName === "playerTurn") {
        this.mainAction = args.args.mainAction;
        this.freeAction = args.args.freeAction;
        this.isBagEmpty = args.args.isBagEmpty;
        this.savableSpecies = this.formatSavableSpecies(
          args.args.savable_species
        );
        this.savableWithFund = this.formatSavableSpecies(
          args.args.savable_with_fund
        );

        this.keepersOnBoards = this.formatKeepersOnBoards(
          args.args.keepers_on_boards
        );

        this.addPlayerTurnButtons();
      }

      if (stateName === "selectHiredPile") {
        if (this.isCurrentPlayerActive()) {
          dojo.query(".zkp_keeper_pile").forEach((element) => {
            if (!dojo.hasClass(element, "zkp_empty_pile")) {
              this.addSelectableStyle(".zkp_keeper_pile");
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

          this.addSelectableStyle(".zkp_keeper_pile");
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

          this.addSelectableStyle(".zkp_keeper_pile");
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

      if (stateName === "selectAssignedKeeper") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          this.addSelectableStyle(`.zkp_keeper-${playerId}`, ".stockitem");
        }
      }

      if (stateName === "selectQuarantine") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          this.addSelectableStyle(`.zkp_quarantine_${playerId}`);
        }
      }

      if (stateName === "mngBackup" && this.isCurrentPlayerActive()) {
        this.addActionButton("discard_backup_btn", _("Discard"), () => {
          this.onDiscardBackup();
        });

        // this.addActionButton("quarantine_backup_btn", _("Quarantine"), () => {
        //   stock.unselectAll();
        //   this.onQuarantineBackup(item);
        // });
      }

      if (stateName === "mngSecondSpecies" && this.isCurrentPlayerActive()) {
        this.addActionButton(
          "cancel_btn",
          _("Cancel"),
          () => {
            this.onCancelMngSpecies();
          },
          null,
          null,
          "red"
        );
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
        this.removeSelectableStyle(".zkp_keeper_pile");
      }

      if (stateName === "selectDismissedKeeper") {
        const playerId = this.getActivePlayerId();
        this.removeSelectableStyle(`.zkp_keeper-${playerId}`, ".stockitem");
      }

      if (stateName === "selectDismissedPile") {
        this.removeSelectableStyle(".zkp_keeper_pile");
      }

      if (stateName === "selectReplacedKeeper") {
        const playerId = this.getActivePlayerId();
        this.removeSelectableStyle(`.zkp_keeper-${playerId}`, ".stockitem");
      }

      if (stateName === "selectReplacedPile") {
        this.removeSelectableStyle(".zkp_keeper_pile");
      }

      if (stateName === "selectAssignedKeeper") {
        this.removeSelectableStyle(`.zkp_keeper-${playerId}`, ".stockitem");
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

    addPlayerTurnButtons: function (args) {
      const playerId = this.getActivePlayerId();

      let openBoardPosition = 0;
      for (const position in this.keepersOnBoards[playerId]) {
        const keeperOnPosition = this.keepersOnBoards[playerId][position];
        if (!keeperOnPosition) {
          openBoardPosition = position;
          break;
        }
      }

      if (this.isCurrentPlayerActive()) {
        if (this.mainAction < 1) {
          if (openBoardPosition > 0) {
            this.addActionButton(
              "hire_keeper_btn",
              _("Hire Keeper"),
              "onHireKeeper"
            );
          }

          if (
            !this.isBagEmpty &&
            this.speciesCounters[playerId].getValue() > 0
          ) {
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
    },

    sendAjaxCall: function (action, args = {}) {
      args.lock = true;

      if (this.checkAction(action)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          args,
          this,
          (result) => {},
          (isError) => {}
        );
      }
    },

    checkKeeperOwner: function (keeperId, event = null) {
      const playerId = this.getActivePlayerId();

      let result = { isOwner: false, position: 0 };

      if (keeperId) {
        const ownedKeepers = this.keepersOnBoards[playerId];

        for (const position in ownedKeepers) {
          if (
            ownedKeepers[position] &&
            ownedKeepers[position].card_type_arg == keeperId
          ) {
            result = { isOwner: true, position: position };
            break;
          }
        }

        if (!result.isOwner) {
          this.showMessage("You don't own this keeper", "error");
        }

        return result;
      }

      if (event) {
        const targetPlayerId = event.currentTarget.id
          .split("keeper_")[1]
          .split(":")[0];

        if (targetPlayerId != playerId) {
          this.showMessage("You don't own this keeper");
          result = { isOwner: false, position: 0 };
        } else {
          result = { isOwner: true, position: 0 };
        }
      }

      return result;
    },

    addSelectableStyle: function (containerSelector, itemSelector = null) {
      const border = "3px solid green";

      dojo.query(containerSelector).style({
        border: itemSelector ? "none" : border,
        cursor: "pointer",
      });

      if (itemSelector) {
        const query = dojo.query(`${containerSelector} > ${itemSelector}`);
        query.style({
          ["pointer-events"]: "none",
        });
        query.removeClass("stockitem_unselectable");

        dojo
          .query(`${containerSelector} > ${itemSelector}:first-child`)
          .style({ border: border });
      }
    },

    removeSelectableStyle: function (containerSelector, itemSelector) {
      dojo.query(containerSelector).style({
        border: "none",
        cursor: "initial",
      });

      if (itemSelector) {
        const query = dojo.query(`${containerSelector} > ${itemSelector}`);
        query.style({
          border: "none",
          ["pointer-events"]: "all",
        });
      }
    },

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

    updateSpeciesCounters: function (counters) {
      for (const player_id in counters) {
        const newValue = counters[player_id];
        this.speciesCounters[player_id].toValue(newValue);
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
              this.sendAjaxCall(action);
            }
          );
          return;
        }

        this.sendAjaxCall(action);
      }
    },

    onCollectResources: function () {
      const action = "collectResources";
      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onHireKeeper: function () {
      const action = "hireKeeper";
      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onSelectKeeperPile: function (event) {
      const action = "selectHiredPile";

      const pile = event.target.id.split(":")[1];
      dojo.stopEvent(event);

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { pile: parseInt(pile) });
      }
    },

    onSelectKeeper: function (stock) {
      if (
        this.gamedatas.gamestate.name === "playerTurn" &&
        this.isCurrentPlayerActive()
      ) {
        this.removeActionButtons();

        if (stock.getSelectedItems().length > 0) {
          const itemId = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} may select an action with this keeper"
          );
          this.updatePageTitle();

          if (this.checkKeeperOwner(itemId).isOwner) {
            this.addActionButton("replace_keeper_btn", _("Replace"), () => {
              stock.unselectAll();
              this.onReplaceKeeper(itemId);
            });

            this.addActionButton("dismiss_keeper_btn", _("Dismiss"), () => {
              stock.unselectAll();
              this.onDismissKeeper(itemId);
            });
          } else {
            stock.unselectAll();
          }

          return;
        }

        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} can do any free actions and one of the four main actions"
        );
        this.updatePageTitle();
        this.addPlayerTurnButtons();
      }
    },

    onDismissKeeper: function (keeperId) {
      const action = "dismissKeeper";

      if (this.checkAction(action, true)) {
        const { isOwner, position } = this.checkKeeperOwner(keeperId);
        if (isOwner) {
          this.sendAjaxCall(action, { board_position: parseInt(position) });
        }
      }
    },

    onSelectDismissedPile: function (event) {
      const action = "selectDismissedPile";

      const pile = event.target.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { pile: parseInt(pile) });
      }
    },

    onReplaceKeeper(keeperId) {
      const action = "replaceKeeper";

      if (this.checkAction(action, true)) {
        const { isOwner, position } = this.checkKeeperOwner(keeperId);
        if (isOwner) {
          this.sendAjaxCall(action, { board_position: parseInt(position) });
        }
      }
    },

    onSelectReplacedPile: function (event) {
      const action = "selectReplacedPile";

      const pile = event.target.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { pile: parseInt(pile) });
      }
    },

    onCancelMngKeepers: function () {
      const action = "cancelMngKeepers";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onExchangeResources: function () {
      const action = "exchangeResources";
      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onCollectFromExchange: function (choosen_nbr) {
      const action = "collectFromExchange";
      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { choosen_nbr: parseInt(choosen_nbr) });
      }
    },

    onCancelExchange: function () {
      const action = "cancelExchange";

      this.freeAction = 0;

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onReturnFromExchange: function (choosen_nbr, resource_type) {
      const action = "returnFromExchange";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, {
          lastly_returned_nbr: parseInt(choosen_nbr),
          lastly_returned_type: resource_type,
        });
      }
    },

    onReturnExcess: function (choosen_nbr, resource_type) {
      const action = "returnExcess";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, {
          lastly_returned_nbr: parseInt(choosen_nbr),
          lastly_returned_type: resource_type,
        });
      }
    },

    onSelectSpecies: function (stock) {
      const stateName = this.gamedatas.gamestate.name;

      if (stateName === "playerTurn" && this.isCurrentPlayerActive()) {
        this.removeActionButtons();

        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} may select an action with this species"
          );
          this.updatePageTitle();

          if (this.savableWithFund && this.savableWithFund[item]) {
            this.addActionButton(
              "save_species_btn",
              _("Save (with conservation fund)"),
              () => {
                stock.unselectAll();
                this.onSaveSpecies(item);
              }
            );
          } else if (this.savableSpecies && this.savableSpecies[item]) {
            this.addActionButton("save_species_btn", _("Save"), () => {
              stock.unselectAll();
              this.onSaveSpecies(item);
            });
          }

          this.addActionButton("discard_species_btn", _("Discard"), () => {
            stock.unselectAll();
            this.onDiscardSpecies(item);
          });

          this.addActionButton(
            "quarantine_species_btn",
            _("Quarantine"),
            () => {
              stock.unselectAll();
              this.onQuarantineSpecies(item);
            }
          );
          return;
        }
        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} can do any free actions and one of the four main actions"
        );
        this.updatePageTitle();
        this.addPlayerTurnButtons();
        return;
      }

      if (stateName === "mngSecondSpecies" && this.isCurrentPlayerActive()) {
        this.removeActionButtons();
        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.addActionButton("discard_species_btn", _("Discard"), () => {
            stock.unselectAll();
            this.onDiscardSpecies(item);
          });

          this.addActionButton(
            "quarantine_species_btn",
            _("Quarantine"),
            () => {
              stock.unselectAll();
              this.onQuarantineSpecies(item);
            }
          );
          return;
        }

        this.removeActionButtons();
      }
    },

    onSelectBackup: function (target, stock) {
      const stateName = this.gamedatas.gamestate.name;

      const column = target.split(":")[1];

      if (stateName === "playerTurn" && this.isCurrentPlayerActive()) {
        this.removeActionButtons();

        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} may select an action with this species"
          );
          this.updatePageTitle();

          this.addActionButton(
            "manage_backup_species_btn",
            _("Look at and discard/quarantine"),
            () => {
              stock.unselectAll();
              this.onLookAtBackup(column, item);
            }
          );
          return;
        }
        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} can do any free actions and one of the four main actions"
        );
        this.updatePageTitle();
        this.addPlayerTurnButtons();
        return;
      }

      if (stateName === "mngSecondSpecies" && this.isCurrentPlayerActive()) {
        this.removeActionButtons();
        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.addActionButton(
            "manage_backup_species_btn",
            _("Look at and discard/quarantine"),
            () => {
              stock.unselectAll();
              this.onLookAtBackup(column, item);
            }
          );
          return;
        }

        this.removeActionButtons();
      }
    },

    onSaveSpecies: function (speciesId) {
      const action = "saveSpecies";

      let position = null;

      for (let i = 1; i <= 4; i++) {
        if (
          this.visibleSpecies[i] &&
          this.visibleSpecies[i].type_arg == speciesId
        ) {
          position = i;
          break;
        }
      }

      if (!position) {
        this.showMessage(
          _("This species is not available to be saved"),
          "error"
        );
        return;
      }

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { shop_position: parseInt(position) });
      }
    },

    onDiscardSpecies: function (speciesId) {
      const action = "discardSpecies";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { species_id: parseInt(speciesId) });
      }
    },

    onQuarantineSpecies: function (speciesId) {
      const action = "quarantineSpecies";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { species_id: parseInt(speciesId) });
      }
    },

    onSelectQuarantine: function (event) {
      const action = "selectQuarantine";
      console.log(action, event);

      const quarantine = event.currentTarget.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { quarantine: quarantine });
      }
    },

    onLookAtBackup: function (column, backupId) {
      const action = "lookAtBackup";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, {
          shop_position: parseInt(column),
          backup_id: parseInt(backupId),
        });
      }
    },

    onDiscardBackup: function () {
      const action = "discardBackup";
      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
      }
    },

    onSelectAssignedKeeper: function (event) {
      const action = "selectAssignedKeeper";

      const position = event.currentTarget.id.split(":")[1];

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action, { board_position: parseInt(position) });
      }
    },

    onCancelMngSpecies: function () {
      const action = "cancelMngSpecies";

      if (this.checkAction(action, true)) {
        this.sendAjaxCall(action);
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
      dojo.subscribe("saveSpecies", this, "notif_saveSpecies");
      dojo.subscribe("discardSpecies", this, "notif_discardSpecies");
      this.notifqueue.setSynchronous("discardSpecies", 1000);
      dojo.subscribe("lookAtBackup", this, "notif_lookAtBackup");
      dojo.subscribe("discardBackup", this, "notif_discardBackup");
      dojo.subscribe(
        "discardBackupPrivately",
        this,
        "notif_discardBackupPrivately"
      );
      this.notifqueue.setSynchronous("discardBackupPrivately", 1000);
      dojo.subscribe("completeKeeper", this, "notif_completeKeeper");
      dojo.subscribe("revealSpecies", this, "notif_revealSpecies");
      dojo.subscribe(
        "discardAllKeptSpecies",
        this,
        "notif_discardAllKeptSpecies"
      );
      dojo.subscribe("newScores", this, "notif_newScores");
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

    notif_saveSpecies: function (notif) {
      this.savedSpecies = notif.args.saved_species;

      const player_id = notif.args.player_id;
      const board_position = notif.args.board_position;
      const shop_position = notif.args.shop_position;
      const species_id = notif.args.species_id;

      const originKey = `visibleShop_${shop_position}`;
      const destinationKey = `board_${player_id}:${board_position}`;
      const destinationElement = `zkp_visible_species_${shop_position}_item_${species_id}`;

      this.displayScoring(
        `zkp_visible_shop`,
        notif.args.player_color,
        notif.args.species_points
      );

      this[destinationKey].addToStockWithId(
        `species_${species_id}`,
        `species_${species_id}`,
        destinationElement
      );

      this.updateSpeciesCounters(notif.args.species_counters);
      this[originKey].removeFromStockById(species_id);
    },

    notif_discardSpecies: function (notif) {
      const column = notif.args.shop_position;
      const species_id = notif.args.species_id;

      const stockKey = `visibleShop_${column}`;
      const deckElement = "zkp_species_deck";
      const container = `zkp_visible_species_${column}`;
      const template = `zkp_discarded_species_${species_id}`;
      const item = `${container}_item_${species_id}`;

      dojo.place(
        this.format_block("jstpl_discarded_species", {
          species: species_id,
        }),
        container
      );

      this.placeOnObject(template, item);

      const animation = this.slideToObject(template, deckElement);

      dojo.connect(animation, "onEnd", () => {
        this[stockKey].removeFromStockById(species_id, deckElement);
        dojo.destroy(template);
      });

      animation.play();
    },

    notif_lookAtBackup: function (notif) {
      const column = notif.args.shop_position;
      const backup_id = notif.args.backup_id;
      const species_id = notif.args.species_id;

      const stockKey = `backupShop_${column}`;

      this[stockKey].removeFromStockById(backup_id);

      this[stockKey].image_items_per_row = 10;

      this[stockKey].addItemType(
        species_id,
        backup_id == 1 ? -1 : 1,
        g_gamethemeurl + "img/species.png",
        species_id - 1
      );

      this[stockKey].addToStockWithId(species_id, `species_${species_id}`);

      dojo.removeClass(
        `zkp_backup_column:${column}_item_species_${species_id}`,
        "zkp_background_contain"
      );

      this[stockKey].image_items_per_row = 1;
    },

    notif_discardBackup: function (notif) {
      if (this.isCurrentPlayerActive()) {
        return;
      }

      const column = notif.args.shop_position;
      const backup_id = notif.args.backup_id;

      const stockKey = `backupShop_${column}`;
      const deckElement = "zkp_species_deck";
      const container = `zkp_backup_column:${column}`;
      const template = `zkp_discarded_species_${backup_id}`;
      const item = `${container}_item_${backup_id}`;

      dojo.place(
        this.format_block("jstpl_discarded_species", {
          species: backup_id,
        }),
        container
      );

      this.placeOnObject(template, item);

      const animation = this.slideToObject(template, deckElement);

      dojo.connect(animation, "onEnd", () => {
        this[stockKey].removeFromStockById(backup_id, deckElement, true);
        dojo.destroy(template);

        if (this[stockKey].count() > 0 && backup_id == 2) {
          setTimeout(() => this[stockKey].updateDisplay(), 1000);
        }
      });

      animation.play();

      this.backupSpecies = notif.args.backup_species;
    },

    notif_discardBackupPrivately(notif) {
      const column = notif.args.shop_position;
      const species_id = notif.args.species_id;
      const backup_id = notif.args.backup_id;

      const stockKey = `backupShop_${column}`;
      const deckElement = "zkp_species_deck";
      const container = `zkp_backup_column:${column}`;
      const template = `zkp_discarded_species_${species_id}`;
      const item = `${container}_item_species_${species_id}`;

      dojo.place(
        this.format_block("jstpl_discarded_species", {
          species: species_id,
        }),
        container
      );

      this.placeOnObject(template, item);

      const animation = this.slideToObject(template, deckElement);

      dojo.connect(animation, "onEnd", () => {
        this[stockKey].removeFromStockById(
          `species_${species_id}`,
          deckElement,
          true
        );
        dojo.destroy(template);

        if (this[stockKey].count() > 0 && backup_id == 2) {
          setTimeout(() => this[stockKey].updateDisplay(), 1000);
        }
      });

      animation.play();

      this.backupSpecies = notif.args.backup_species;
    },

    notif_revealSpecies: function (notif) {
      const column = notif.args.shop_position;
      const revealed_id = notif.args.revealed_id;

      const originKey = `backupShop_${column}`;
      const lastInColumn = this[originKey].count();
      const destinationKey = `visibleShop_${column}`;
      const destinationElement = `zkp_visible_species_${column}`;

      this[destinationKey].addToStockWithId(
        revealed_id,
        revealed_id,
        destinationElement
      );

      if (lastInColumn > 0) {
        this[originKey].removeFromStockById(lastInColumn);
      }

      this.backupSpecies = notif.args.backup_species;
      this.visibleSpecies = notif.args.visible_species;
    },

    notif_discardAllKeptSpecies: function (notif) {
      const player_id = notif.args.player_id;
      const position = notif.args.board_position;
      const discarded_species = notif.args.discarded_species;

      const stockKey = `board_${player_id}:${position}`;
      const deckElement = `zkp_species_deck`;
      const container = `zkp_keeper_${player_id}:${position}`;

      for (const card_id in discarded_species) {
        const species_id = discarded_species[card_id].type_arg;
        const item = `${container}_item_species_${species_id}`;

        dojo.place(
          this.format_block("jstpl_discarded_species", {
            species: species_id,
          }),
          container
        );

        this.placeOnObject(`zkp_discarded_species_${species_id}`, item);

        const animation = this.slideToObject(
          `zkp_discarded_species_${species_id}`,
          deckElement
        );

        dojo.connect(animation, "onEnd", () => {
          this[stockKey].removeFromStockById(
            `species_${species_id}`,
            deckElement
          );
        });

        animation.play();
      }

      this.updateSpeciesCounters(notif.args.species_counters);
      this.savedSpecies = notif.args.saved_species;
    },

    notif_completeKeeper: function (notif) {
      const position = notif.args.board_position;
      const player_id = notif.args.player_id;
      const keeper_id = notif.args.keeper_id;
      const level = notif.args.keeper_level;

      const element = `zkp_keeper_${player_id}:${position}_item_${keeper_id}`;
      const backgroundPosition = this.topsPositions[level];

      if (!dojo.hasClass(element, "zkp_completed_keeper")) {
        dojo.setStyle(element, {
          backgroundImage: "url('img/keepers.png')",
          backgroundPosition: backgroundPosition,
        });

        dojo.addClass(element, "zkp_completed_keeper");

        this.displayScoring(element, notif.args.player_color, level);

        this.completedKeepers = notif.args.completed_keepers;
      }
    },

    notif_newScores: function (notif) {
      if (this.isRealTimeScoreTracking || notif.args.final_scores_calc) {
        this.scoreCtrl[notif.args.player_id].toValue(notif.args.new_scores);
      }
    },

    notif_pass: function (notif) {},
  });
});
