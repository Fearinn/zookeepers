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
  g_gamethemeurl + "modules/bga-zoom.js",
], function (dojo, declare) {
  return declare("bgagame.zookeepers", ebg.core.gamegui, {
    constructor: function () {
      console.log("zookeepers constructor");

      //version
      this.gameVersion = 0;

      //game mode constants
      this.shopPositions = 4;
      this.keeperPiles = 4;
      this.keeperHouses = 4;
      this.speciesGoal = 9;

      //styles
      this.cardWidth = 180;
      this.cardHeight = 249;

      this._registeredCustomTooltips = {};
      this._attachedTooltips = {};

      this.topsPositions = {
        1: "",
        2: "-100% 0",
        3: "-200% 0",
        4: "-100% -100%",
        5: "0 -100%",
      };
      this.topsSpritePositions = {
        1: 0,
        2: 1,
        3: 2,
        4: 4,
        5: 3,
      };

      this.filters = {
        ["ff0000"]:
          "invert(20%) sepia(59%) saturate(7414%) hue-rotate(355deg) brightness(98%) contrast(120%)" /* Red */,
        ["008000"]:
          "invert(24%) sepia(100%) saturate(6316%) hue-rotate(117deg) brightness(98%) contrast(103%)" /* Green */,
        "0000ff":
          "invert(9%) sepia(100%) saturate(5946%) hue-rotate(246deg) brightness(106%) contrast(145%)" /* Blue */,
        ["ffa500"]:
          "invert(58%) sepia(89%) saturate(1108%) hue-rotate(360deg) brightness(103%) contrast(105%)" /* Yellow */,
        ["000000"]: "" /* Black */,
        ["ffffff"]:
          "invert(100%) sepia(0%) saturate(1%) hue-rotate(201deg) brightness(108%) contrast(101%)" /* White */,
        ["e94190"]:
          "invert(46%) sepia(70%) saturate(3890%) hue-rotate(307deg) brightness(93%) contrast(95%)" /* Pink */,
        ["982fff"]:
          "invert(39%) sepia(84%) saturate(7500%) hue-rotate(264deg) brightness(102%) contrast(101%)" /* Purple */,
        ["72c3b1"]:
          "invert(80%) sepia(54%) saturate(275%) hue-rotate(111deg) brightness(81%) contrast(88%)" /* Cyan */,
        ["f07f16"]:
          "invert(49%) sepia(93%) saturate(1196%) hue-rotate(356deg) brightness(100%) contrast(89%)" /* Orange */,
        ["bdd002"]:
          "invert(77%) sepia(55%) saturate(2858%) hue-rotate(21deg) brightness(101%) contrast(104%)" /* Khaki green */,
        ["7b7b7b"]:
          "invert(49%) sepia(10%) saturate(14%) hue-rotate(17deg) brightness(96%) contrast(84%)" /* Gray */,
      };

      //gameoptions
      this.fastMode = false;
      this.isRealTimeScoreTracking = false;
      this.isBagHidden = false;
      this.hasSecretObjectives = false;

      this.isSetup = true;
      this.mainAction = 0;
      this.freeAction = 0;
      this.resourceTypes = {};
      this.resourceCounters = {};
      this.bagCounters = {};
      this.allKeepers = {};
      this.pileCounters = {};
      this.pilesTops = {};
      this.keepersAtHouses = {};
      this.openHouse = 0;
      this.allSpecies = {};
      this.backupSpecies = {};
      this.visibleSpecies = {};
      this.savableSpecies = {};
      this.savableWithFund = {};
      this.savableQuarantined = {};
      this.savableQuarantinedWithFund = {};
      this.savedSpecies = {};
      this.allQuarantines = {};
      this.openQuarantines = {};
      this.quarantinedSpecies = {};
      this.completedKeepers = {};
      this.speciesCounters = {};
      this.possibleZoos = {};
      this.emptyColumnNbr = 0;
      this.resourcesInHandNbr = 0;
      this.allObjectives = {};
      this.secretObjective = {};
      this.isBagEmpty = false;
      this.canZooHelp = false;
      this.isLastTurn = false;
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

      this.gameVersion = gamedatas.gameVersion;

      this.fastMode = gamedatas.fastMode;
      this.isRealTimeScoreTracking = gamedatas.isRealTimeScoreTracking;
      this.isBagHidden = gamedatas.isBagHidden;
      this.hasSecretObjectives = gamedatas.hasSecretObjectives;

      this.dontPreloadImage("bigger_keepers.png");
      this.dontPreloadImage("bigger_species.png");
      this.dontPreloadImage("bigger_objectives.png");
      this.dontPreloadImage("objectives.png");
      this.dontPreloadImage("playmat_fm.jpg");
      this.dontPreloadImage("playmat.png");

      const loadedImages = [];

      if (this.getGameUserPreference(200) == 0) {
        loadedImages.push("bigger_keepers.png");
        loadedImages.push("bigger_species.png");
      }

      if (this.fastMode) {
        loadedImages.push("playmat_fm.jpg");
      } else {
        loadedImages.push("playmat.png");
      }

      if (this.hasSecretObjectives) {
        loadedImages.push("objectives.png");

        if (this.getGameUserPreference(200) == 0) {
          loadedImages.push("bigger_objectives.png");
        }
      }
      this.ensureSpecificGameImageLoading(loadedImages);

      this.zoomManager = new ZoomManager({
        element: document.getElementById("zkp_gameplay_area"),
        localStorageZoomKey: "zookeepers-zoom",
        zoomControls: {
          color: "black",
        },
      });

      this.resourceTypes = gamedatas.resourceTypes;

      this.allKeepers = gamedatas.allKeepers;
      this.keepersAtHouses = this.formatKeepersAtHouses(
        gamedatas.keepersAtHouses
      );
      this.completedKeepers = gamedatas.completedKeepers;

      this.allSpecies = gamedatas.allSpecies;
      this.backupSpecies = gamedatas.backupSpecies;
      this.visibleSpecies = gamedatas.visibleSpecies;
      this.savableSpecies = this.formatSavableSpecies(gamedatas.savableSpecies);
      this.savableWithFund = this.formatSavableSpecies(
        gamedatas.savableWithFund
      );
      this.savableQuarantined = gamedatas.savableQuarantined;
      this.savableQuarantinedWithFund = gamedatas.savableQuarantinedWithFund;
      this.savedSpecies = gamedatas.savedSpecies;
      this.allQuarantines = gamedatas.allQuarantines;
      this.openQuarantines = gamedatas.openQuarantines;
      this.quarantinedSpecies = gamedatas.quarantinedSpecies;

      this.allObjectives = gamedatas.allObjectives;
      this.secretObjective = gamedatas.secretObjective;

      this.isBagEmpty = gamedatas.isBagEmpty;
      this.emptyColumnNbr = gamedatas.emptyColumnNbr;
      this.isLastTurn = gamedatas.isLastTurn;

      //fast mode constants
      if (this.fastMode) {
        this.shopPositions = 6;
        this.keeperPiles = 1;
        this.keeperHouses = 2;
        this.speciesGoal = 6;
      }

      for (const player_id in this.gamedatas.players) {
        document.getElementById("zkp_playmats").insertAdjacentHTML(
          "beforeend",
          `<div
      id="zkp_playmat_container:${player_id}"
      class="zkp_playmat_container:${player_id} zkp_playmat_container whiteblock"
    >
      <h3 id="zkp_zoo_title:${player_id}" class="zkp_zoo_title whiteblock">
        {PLAYER_NAME}
      </h3>
      <div id="zkp_playmat:${player_id}" class="zkp_playmat">
        <button
          id="zkp_expand_house_${player_id}:1"
          class="zkp_expand_house zkp_expand_house_1"
        ></button>
        <button
          id="zkp_expand_house_${player_id}:2"
          class="zkp_expand_house zkp_expand_house_2"
        ></button>
        <button
          id="zkp_expand_house_${player_id}:3"
          class="zkp_expand_house zkp_expand_house_3"
        ></button>
        <button
          id="zkp_expand_house_${player_id}:4"
          class="zkp_expand_house zkp_expand_house_4"
        ></button>
        <div id="zkp_houses_${player_id}" class="zkp_houses">
          <div
            id="zkp_keeper_${player_id}:1"
            class="zkp_house zkp_keeper_${player_id}"
          ></div>
          <div
            id="zkp_keeper_${player_id}:2"
            class="zkp_house zkp_keeper_${player_id}"
          ></div>
          <div
            id="zkp_keeper_${player_id}:3"
            class="zkp_house zkp_keeper_${player_id}"
          ></div>
          <div
            id="zkp_keeper_${player_id}:4"
            class="zkp_house zkp_keeper_${player_id}"
          ></div>
        </div>
        <div id="zkp_playmat_counters_${player_id}" class="zkp_playmat_counters">
          <div
            id="zkp_playmat_counter_${player_id}:0"
            class="zkp_playmat_counter_0 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:1"
            class="zkp_playmat_counter_1 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:2"
            class="zkp_playmat_counter_2 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:3"
            class="zkp_playmat_counter_3 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:4"
            class="zkp_playmat_counter_4 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:5"
            class="zkp_playmat_counter_5 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:6"
            class="zkp_playmat_counter_6 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:7"
            class="zkp_playmat_counter_7 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:8"
            class="zkp_playmat_counter_8 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_${player_id}:9"
            class="zkp_playmat_counter_9 zkp_playmat_counter"
          ></div>
        </div>
        <div
          id="zkp_quarantine_${player_id}:ALL"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_ALL"
          data-quarantine="ALL"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:TEM"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_TEM"
          data-quarantine="TEM"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:SAV"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_SAV"
          data-quarantine="SAV"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:PRA"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_PRA"
          data-quarantine="PRA"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:DES"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_DES"
          data-quarantine="DES"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:AQU"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_AQU"
          data-quarantine="AQU"
        ></div>
        <div
          id="zkp_quarantine_${player_id}:TRO"
          class="zkp_quarantine zkp_quarantine_${player_id} zkp_quarantine_TRO"
          data-quarantine="TRO"
        ></div>
      </div>
    </div>`
        );

        const titleElement = document.getElementById(
          `zkp_zoo_title:${player_id}`
        );
        const playerColor = `#${this.gamedatas.players[player_id].color}`;
        titleElement.style.color = playerColor;

        const playmatContainerElement = document.getElementById(
          `zkp_playmat_container:${player_id}`
        );
        playmatContainerElement.style.backgroundColor = `${playerColor}44`;

        const player_name = this.gamedatas.players[player_id].name;
        if (player_id == this.player_id) {
          titleElement.textContent = this.format_string_recursive(
            _("Your zoo (${player_name})"),
            { player_name }
          );
          playmatContainerElement.style.order = -1;
        } else {
          titleElement.textContent = this.format_string_recursive(
            _("${player_name}'s zoo"),
            {
              player_name,
            }
          );
        }
      }

      //resources
      for (const player_id in gamedatas.players) {
        const player = gamedatas.players[player_id];

        const player_board_div = $("player_board_" + player_id);
        dojo.place(
          this.format_block("jstpl_player_board", player),
          player_board_div
        );

        const plantCounter = new ebg.counter();
        plantCounter.create(`zkp_plant_count_${player_id}`);

        const meatCounter = new ebg.counter();
        meatCounter.create(`zkp_meat_count_${player_id}`);

        const kitCounter = new ebg.counter();
        kitCounter.create(`zkp_kit_count_${player_id}`);

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

        this.addTooltip(`zkp_plant_icon_${player_id}`, _("plant"), "");
        this.addTooltip(`zkp_meat_icon_${player_id}`, _("meat/fish"), "");
        this.addTooltip(`zkp_kit_icon_${player_id}`, _("medical kit"), "");
      }

      document.getElementById("zkp_bag_title").textContent = _("Resources");
      const bagKey = "bag";
      this[bagKey] = new ebg.stock();
      this[bagKey].create(this, $("zkp_bag_stock"), 150, 150);

      this[bagKey].image_items_per_row = 1;
      this[bagKey].extraClasses = "zkp_bag_img";
      this[bagKey].setSelectionMode(1);

      this[bagKey].addItemType(0, 0, g_gamethemeurl + "img/bag.png", 0);
      this[bagKey].addToStockWithId(0, 0);

      dojo.connect(this[bagKey], "onChangeSelection", this, () => {
        this.onSelectBag(this[bagKey]);
      });

      if (!this.isBagHidden) {
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

        this.addTooltip("zkp_bag_plant_icon", _("plant"), "");
        this.addTooltip("zkp_bag_meat_icon", _("meat/fish"), "");
        this.addTooltip("zkp_bag_kit_icon", _("medical kit"), "");
      }

      if (this.isBagHidden) {
        dojo.destroy("zkp_bag_counters");
      }

      // keepers
      for (const player_id in gamedatas.players) {
        for (let position = 1; position <= this.keeperHouses; position++) {
          const stockKey = `board_${player_id}:${position}`;
          this[stockKey] = new ebg.stock();
          this[stockKey].create(
            this,
            $(`zkp_keeper_${player_id}:${position}`),
            this.cardWidth,
            this.cardHeight
          );

          if (player_id == this.player_id) {
            this[stockKey].setSelectionMode(1);
            dojo.connect(
              this[stockKey],
              "onChangeSelection",
              this,
              (target) => {
                this.onSelectKeeper(this[stockKey]);
              }
            );
          } else {
            this[stockKey].setSelectionMode(0);
          }

          this[stockKey].extraClasses = "zkp_card";
          this[stockKey].image_items_per_row = 6;

          for (const keeper_id in this.allKeepers) {
            this[stockKey].addItemType(
              keeper_id,
              1000,
              g_gamethemeurl + "img/keepers.png",
              keeper_id - 1
            );
          }

          const addedKeeper = this.keepersAtHouses[player_id][position];

          if (addedKeeper && addedKeeper.type_arg) {
            const pile = addedKeeper.pile;
            if (pile > 0) {
              this[stockKey].addToStockWithId(
                addedKeeper.type_arg,
                addedKeeper.type_arg,
                `zkp_keeper_pile:${pile}`
              );
            } else {
              this[stockKey].addToStockWithId(
                addedKeeper.type_arg,
                addedKeeper.type_arg
              );
            }
          }

          this[stockKey].container_div.width = "180px";
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
            for (const species_id in savedSpecies) {
              this[stockKey].addToStockWithId(
                `species_${species_id}`,
                `species_${species_id}`
              );

              this.addSpeciesTooltipHtml(
                `zkp_keeper_${player_id}:${position}_item_species_${species_id}`,
                species_id
              );
            }
          }
          this[stockKey].image_items_per_row = 6;

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

      for (const pile in this.pileCounters) {
        const element = $(`zkp_keeper_pile:${pile}`);
        const className = "zkp_empty_pile";
        const top = this.pilesTops[pile];

        const stockKey = `keeper_pile:${pile}`;
        this[stockKey] = new ebg.stock();
        this[stockKey].create(this, element, this.cardWidth, this.cardHeight);

        dojo.connect(this[stockKey], "onChangeSelection", this, () => {
          this.onSelectKeeperPile(this[stockKey], pile);
        });

        this[stockKey].image_items_per_row = 3;
        this[stockKey].extraClasses = "zkp_card";
        this[stockKey].setSelectionMode(1);

        for (let level = 1; level <= 5; level++) {
          this[stockKey].addItemType(
            level,
            0,
            g_gamethemeurl + "img/keepers_backs.png",
            this.topsSpritePositions[level]
          );
        }

        if (this.pileCounters[pile] <= 0) {
          dojo.addClass(element, className);
        } else {
          this[stockKey].addToStockWithId(top, top);
          dojo.removeClass(element, className);
        }
      }

      //species
      for (let column = 1; column <= this.shopPositions; column++) {
        const stockKey = `visibleShop_${column}`;
        const container = `zkp_visible_species_${column}`;
        this[stockKey] = new ebg.stock();
        this[stockKey].create(
          this,
          $(container),
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

        const species_id = this.visibleSpecies[column]?.type_arg;

        if (species_id) {
          this[stockKey].addToStockWithId(
            species_id,
            species_id,
            "zkp_species_deck"
          );

          this.addSpeciesTooltipHtml(
            `${container}_item_${species_id}`,
            species_id
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
        for (const quarantine_id in this.allQuarantines) {
          const quarantine = this.allQuarantines[quarantine_id];
          const stockKey = `quarantine_${player_id}:${quarantine}`;

          this[stockKey] = new ebg.stock();
          this[stockKey].create(
            this,
            $(`zkp_quarantine_${player_id}:${quarantine}`),
            this.cardWidth,
            this.cardHeight
          );

          if (player_id == this.player_id) {
            this[stockKey].setSelectionMode(1);
            dojo.connect(this[stockKey], "onChangeSelection", () => {
              this.onSelectQuarantined(this[stockKey]);
            });
          } else {
            this[stockKey].setSelectionMode(0);
          }

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

          const speciesInQuarantine =
            this.quarantinedSpecies[player_id][quarantine];

          for (const species_id in speciesInQuarantine) {
            this[stockKey].addToStockWithId(species_id, species_id);

            this.addSpeciesTooltipHtml(
              `zkp_quarantine_${player_id}:${quarantine}_item_${species_id}`,
              species_id
            );
          }
        }

        this.speciesCounters[player_id] = new ebg.counter();
        this.speciesCounters[player_id].create(
          `zkp_species_count_${player_id}`
        );
        this.addTooltip(
          `zkp_species_icon_${player_id}`,
          _("saved species"),
          ""
        );

        for (let count = 0; count <= this.speciesGoal; count++) {
          const stockKey = `playmatCounter_${player_id}_${count}`;
          const element = $(`zkp_playmat_counter_${player_id}:${count}`);

          this[stockKey] = new ebg.stock();
          this[stockKey].create(this, element, 30, 30);

          this[stockKey].setSelectionMode(0);
          this[stockKey].image_items_per_row = 10;
          this[stockKey].addItemType(
            0,
            0,
            g_gamethemeurl + "img/tokens.png",
            5
          );

          const player_color = gamedatas.players[player_id].color.toLowerCase();
          const filter = this.filters[player_color] ?? "";

          dojo.style(element, "filter", filter);
        }
        this[`playmatCounter_${player_id}_0`].addToStockWithId(0, 0);
      }

      this.updateSpeciesCounters(gamedatas.speciesCounters);

      //event connections
      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectDismissedPile(event);
      });

      dojo.query(".zkp_keeper_pile").connect("onclick", this, (event) => {
        this.onSelectReplacedPile(event);
      });

      dojo.query(`.zkp_playmat_container`).connect("onclick", this, (event) => {
        this.onSelectZoo(event);
      });

      this.setupExpandHouses();

      for (const player_id in gamedatas.players) {
        if (player_id == this.player_id) {
          dojo
            .query(`.zkp_keeper_${player_id}`)
            .connect("onclick", this, (event) => {
              this.onSelectAssignedKeeper(event);
            });

          dojo
            .query(`.zkp_keeper_${player_id}`)
            .connect("onclick", this, (event) => {
              this.onSelectQuarantinedKeeper(event);
            });

          dojo
            .query(`.zkp_quarantine_${player_id}`)
            .connect("onclick", this, (event) => {
              this.onSelectQuarantine(event);
            });

          dojo
            .query(`.zkp_quarantine_${player_id}`)
            .connect("onclick", this, (event) => {
              this.onSelectBackupQuarantine(event);
            });

          dojo
            .query(`.zkp_quarantine_${player_id}`)
            .connect("onclick", this, (event) => {
              this.onSelectHelpQuarantine(event);
            });
        }
      }

      //secret objectives
      if (!this.hasSecretObjectives) {
        dojo.destroy("zkp_objectives_deck");
        dojo.query(".zkp_objective").forEach(dojo.destroy);
      }

      if (this.hasSecretObjectives) {
        for (const player_id in gamedatas.players) {
          const stockKey = `objective_${player_id}`;
          this[stockKey] = new ebg.stock();
          this[stockKey].create(
            this,
            $(`zkp_objective:${player_id}`),
            this.cardWidth,
            this.cardHeight
          );

          this[stockKey].addItemType(
            0,
            0,
            g_gamethemeurl + "img/objectives_back.png",
            0
          );

          for (const objective_id in this.allObjectives) {
            const sprite_pos = this.allObjectives[objective_id].sprite_pos;
            this[stockKey].addItemType(
              objective_id,
              0,
              g_gamethemeurl + "img/objectives.png",
              sprite_pos
            );
          }

          if (player_id == this.player_id) {
            const objective_id = this.secretObjective.type_arg;

            this[stockKey].extraClasses = "zkp_card";
            this[stockKey].image_items_per_row = 4;
            this[stockKey].setSelectionMode(1);

            this[stockKey].addToStockWithId(
              objective_id,
              objective_id,
              "zkp_objectives_deck"
            );

            dojo.connect(this[stockKey], "onChangeSelection", this, () => {
              this.onSelectObjective(this[stockKey]);
            });

            const backgroundPosition = this.calcBackgroundPosition(
              this.allObjectives[objective_id].sprite_pos,
              4
            );

            this.addTooltipHtml(
              `zkp_objective:${player_id}_item_${objective_id}`,
              `<div class="zkp_bigger_objective zkp_card" style="background-position: ${backgroundPosition}"></div>`
            );
          } else {
            this[stockKey].extraClasses = "zkp_card zkp_background_contain";
            this[stockKey].image_items_per_row = 1;
            this[stockKey].setSelectionMode(0);

            this[stockKey].addToStockWithId(0, 0, "zkp_objectives_deck");
          }
        }
      }

      //fast mode
      if (this.fastMode) {
        dojo.destroy($("zkp_backup_shop"));

        const restyledElements = [];

        for (let pile = 4; pile > this.keeperPiles; pile--) {
          dojo.destroy($(`zkp_keeper_pile:${pile}`));
        }

        for (const player_id in gamedatas.players) {
          for (let house = 4; house > this.keeperHouses; house--) {
            dojo.destroy($(`zkp_keeper_${player_id}:${house}`));
            dojo.destroy($(`zkp_expand_house_${player_id}:${house}`));
          }

          dojo.query("[data-quarantine]").forEach((element) => {
            const possibleQuarantines = [];
            const quarantine = element.dataset.quarantine;

            for (const quarantine_id in this.allQuarantines) {
              possibleQuarantines.push(this.allQuarantines[quarantine_id]);
            }

            if (!possibleQuarantines.includes(quarantine)) {
              dojo.destroy(element);
            }
          });

          restyledElements.push(`zkp_houses_${player_id}`);
          restyledElements.push(`zkp_playmat:${player_id}`);
          restyledElements.push(`zkp_quarantine_${player_id}:SAV`);
          restyledElements.push(`zkp_playmat_counters_${player_id}`);

          dojo.query(".zkp_playmat_counter").addClass("zkp_fast_mode");
          dojo.query(".zkp_expand_house").addClass("zkp_fast_mode");
        }

        restyledElements.push("zkp_species_shop");
        restyledElements.push("zkp_visible_shop");
        restyledElements.push("zkp_keeper_piles");
        restyledElements.push("zkp_common_area");

        restyledElements.forEach((elementId) => {
          dojo.addClass($(elementId), "zkp_fast_mode");
        });
      } else {
        for (let position = 6; position > this.shopPositions; position--) {
          dojo.destroy($(`zkp_visible_species_${position}`));
        }
      }

      // Setup game notifications to handle (see "setupNotifications" method below)
      this.setupNotifications();
      this.initObserver();

      console.log("Ending game setup");
    },

    ///////////////////////////////////////////////////
    //// Game & client states

    onEnteringState: function (stateName, args) {
      console.log("Entering state: " + stateName);

      this.attachRegisteredTooltips();

      const playerId = this.getActivePlayerId();

      if (this.isSetup) {
        this.isSetup = false;

        if (!this.isRealTimeScoreTracking) {
          for (const player_id in this.scoreCtrl) {
            this.scoreCtrl[player_id].setValue(0);
          }
        }
      }

      if (stateName === "playerTurn") {
        this.mainAction = args.args.mainAction;
        this.freeAction = args.args.freeAction;
        this.isBagEmpty = args.args.isBagEmpty;
        this.savableSpecies = this.formatSavableSpecies(
          args.args.savableSpecies
        );
        this.savableWithFund = this.formatSavableSpecies(
          args.args.savableWithFund
        );
        this.savableQuarantined = args.args.savableQuarantined;
        this.savableQuarantinedWithFund = args.args.savableQuarantinedWithFund;
        this.keepersAtHouses = this.formatKeepersAtHouses(
          args.args.keepersAtHouses
        );
        this.emptyColumnNbr = args.args.emptyColumnNbr;
        this.resourcesInHandNbr = args.args.resourcesInHandNbr;
        this.canZooHelp = args.args.canZooHelp;
        this.isLastTurn = args.args.isLastTurn;
        this.possibleZoos = args.args.possibleZoos;

        this.setupExpandHouses();
        this.addPlayerTurnButtons();
      }

      if (stateName === "selectHiredPile") {
        if (this.isCurrentPlayerActive()) {
          this.addSelectableStyle(".zkp_keeper_pile", ".stockitem");

          this.addActionButton(
            "zkp_cancel_btn",
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
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          this.addSelectableStyle(".zkp_keeper_pile", ".stockitem");
          this.addSelectableStyle(".zkp_empty_pile");

          const keeper_id = args.args.keeper_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_keeper_${playerId}:${position}_item_${keeper_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "selectReplacedPile") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngKeepers",
            null,
            null,
            "red"
          );

          this.addSelectableStyle(".zkp_keeper_pile", ".stockitem");

          const keeper_id = args.args.keeper_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_keeper_${playerId}:${position}_item_${keeper_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "exchangeCollection") {
        this.freeAction = args.args.freeAction;

        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            "Cancel",
            "onCancelExchange",
            null,
            null,
            "red"
          );
          for (let i = 1; i <= 5 && i < args.args.resources_in_hand_nbr; i++) {
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
                "zkp_image_btn_" + type,
                `<div class="zkp_resource_icon zkp_${type}_icon"></div>`,
                () => {},
                null,
                null,
                "gray"
              );
              dojo.addClass("zkp_image_btn_" + type, "bgaimagebutton");

              for (
                let option = 1;
                option <= activePlayerCounters[type].getValue() &&
                option <= args.args.to_return;
                option++
              ) {
                this.addActionButton(
                  "exchange_resources_option_" + type + "_" + option,
                  option.toString(),
                  () => this.onReturnFromExchange(option, type)
                );
              }
            }
          }
        }
        return;
      }

      if (stateName === "newSpeciesReturn") {
        const playerId = this.getActivePlayerId();
        const activePlayerCounters = this.resourceCounters[playerId];

        if (this.isCurrentPlayerActive()) {
          for (const type in activePlayerCounters) {
            if (activePlayerCounters[type].getValue() > 0) {
              this.addActionButton(
                "zkp_image_btn_" + type,
                `<div class="zkp_resource_icon zkp_${type}_icon"></div>`,
                () => {},
                null,
                null,
                "gray"
              );
              dojo.addClass("zkp_image_btn_" + type, "bgaimagebutton");

              const label = this.resourceTypes[type].label;

              this.addActionButton("zkp_type_btn_" + type, label, () => {
                this.onReturnFromNewSpecies(type);
              });
            }
          }

          this.addActionButton(
            "zkp_cancel_btn",
            _("Cancel"),
            () => {
              this.onCancelNewSpecies();
            },
            null,
            null,
            "red"
          );
        }
      }

      if (stateName === "returnExcess") {
        const playerId = this.getActivePlayerId();
        const activePlayerCounters = this.resourceCounters[playerId];

        if (this.isCurrentPlayerActive()) {
          for (const type in activePlayerCounters) {
            if (activePlayerCounters[type].getValue() > 0) {
              this.addActionButton(
                "zkp_image_btn_" + type,
                `<div class="zkp_resource_icon zkp_${type}_icon"></div>`,
                () => {},
                null,
                null,
                "gray"
              );
              dojo.addClass("zkp_image_btn_" + type, "bgaimagebutton");

              for (
                let option = 1;
                option <= activePlayerCounters[type].getValue() &&
                option <= args.args.to_return;
                option++
              ) {
                this.addActionButton(
                  "exchange_resources_option_" + type + "_" + option,
                  option.toString(),
                  () => this.onReturnExcess(option, type)
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
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          const assignableKeepers = args.args.assignable_keepers;

          this.addSelectableStyle(
            `.zkp_keeper_${playerId}`,
            ".stockitem",
            null,
            (element) => {
              const keeperId = element.id.split("item_")[1];
              return !!assignableKeepers[keeperId];
            }
          );

          const species_id = args.args.species_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_visible_species_${position}_item_${species_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "selectQuarantinedKeeper") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          const assignableKeepers = args.args.assignable_keepers;
          this.addSelectableStyle(
            `.zkp_keeper_${playerId}`,
            ".stockitem",
            null,
            (element) => {
              const keeperId = element.id.split("item_")[1];
              return !!assignableKeepers[keeperId];
            }
          );

          const species_id = args.args.species_id;
          const quarantine = args.args.quarantine;

          dojo.addClass(
            `zkp_quarantine_${playerId}:${quarantine}_item_${species_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "selectQuarantine") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          const possibleQuarantines = args.args.possible_quarantines;

          this.addSelectableStyle(
            `.zkp_quarantine_${playerId}`,
            null,
            (element) => {
              return !!possibleQuarantines[element.id.split(":")[1]];
            }
          );

          const species_id = args.args.species_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_visible_species_${position}_item_${species_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "selectBackupQuarantine") {
        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            "Cancel",
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          const looked_backup = args.args._private?.looked_backup;
          const possibleQuarantines = args.args._private?.possible_quarantines;

          if (looked_backup && possibleQuarantines) {
            const species_id = looked_backup.type_arg;
            const column = looked_backup.location_arg;
            const backup_id = looked_backup.backup_id;

            this.lookAtBackup({
              column: column,
              backup_id: backup_id,
              species_id: species_id,
            });

            this.addSelectableStyle(
              `.zkp_quarantine_${playerId}`,
              null,
              (element) => {
                return !!possibleQuarantines[element.id.split(":")[1]];
              }
            );
          }
        }
      }

      if (stateName === "selectHelpQuarantine") {
        if (this.isCurrentPlayerActive()) {
          const possibleQuarantines = args.args.possible_quarantines;

          this.addSelectableStyle(
            `.zkp_quarantine_${playerId}`,
            null,
            (element) => {
              return !!possibleQuarantines[element.id.split(":")[1]];
            }
          );

          const species_id = args.args.species_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_visible_species_${position}_item_${species_id}`,
            "zkp_highlight"
          );
        }
      }

      if (stateName === "mngBackup") {
        const looked_backup = args.args._private?.looked_backup;

        if (this.isCurrentPlayerActive())
          if (looked_backup) {
            const species_id = looked_backup.type_arg;
            const column = looked_backup.location_arg;

            const backup_id = looked_backup.backup_id;

            this.lookAtBackup({
              column: column,
              backup_id: backup_id,
              species_id: species_id,
            });

            this.addActionButton("zkp_discard_backup_btn", _("Discard"), () => {
              this.onDiscardBackup();
            });

            if (this.isQuarantinable(species_id)) {
              this.addActionButton(
                "zkp_quarantine_backup_btn",
                _("Quarantine"),
                () => {
                  this.onQuarantineBackup();
                }
              );
            }
          }
      }

      if (stateName === "mngSecondSpecies") {
        this.emptyColumnNbr = args.args.empty_column_nbr;
        this.freeAction = args.args.freeAction;

        const canNewSpecies =
          this.freeAction != 1 &&
          ((this.fastMode && this.resourcesInHandNbr > 0) ||
            (!this.fastMode && this.emptyColumnNbr >= 2));

        if (this.isCurrentPlayerActive()) {
          if (canNewSpecies) {
            this.addActionButton(
              "zkp_new_species_btn",
              _("New Species"),
              "onNewSpecies"
            );
          }

          this.addActionButton(
            "zkp_cancel_btn",
            _("Skip"),
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );
        }
      }

      if (stateName === "selectZoo") {
        this.possibleZoos = args.args.possible_zoos;

        if (this.isCurrentPlayerActive()) {
          this.addActionButton(
            "zkp_cancel_btn",
            _("Cancel"),
            "onCancelMngSpecies",
            null,
            null,
            "red"
          );

          const species_id = args.args.species_id;
          const position = args.args.position;

          dojo.addClass(
            `zkp_visible_species_${position}_item_${species_id}`,
            "zkp_highlight"
          );

          this.addSelectableStyle(".zkp_playmat_container", null, (element) => {
            return !!this.possibleZoos[element.id.split(":")[1]];
          });
        }
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
        this.removeSelectableStyle(".zkp_keeper_pile", ".stockitem");
        return;
      }

      if (stateName === "selectDismissedKeeper") {
        const playerId = this.getActivePlayerId();
        this.removeSelectableStyle(`.zkp_keeper_${playerId}`, ".stockitem");
        return;
      }

      if (stateName === "selectDismissedPile") {
        this.removeSelectableStyle(".zkp_keeper_pile", ".stockitem");
        this.removeSelectableStyle(".zkp_empty_pile");
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectReplacedKeeper") {
        const playerId = this.getActivePlayerId();
        this.removeSelectableStyle(`.zkp_keeper_${playerId}`, ".stockitem");
        return;
      }

      if (stateName === "selectReplacedPile") {
        this.removeSelectableStyle(".zkp_keeper_pile", ".stockitem");
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectAssignedKeeper") {
        this.removeSelectableStyle(`.zkp_keeper_${playerId}`, ".stockitem");
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectQuarantinedKeeper") {
        this.removeSelectableStyle(`.zkp_keeper_${playerId}`, ".stockitem");
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectQuarantine") {
        this.removeSelectableStyle(`.zkp_quarantine_${playerId}`);
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectBackupQuarantine") {
        this.removeSelectableStyle(`.zkp_quarantine_${playerId}`);
        return;
      }

      if (stateName === "selectHelpQuarantine") {
        this.removeSelectableStyle(`.zkp_quarantine_${playerId}`);
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
      }

      if (stateName === "selectZoo") {
        this.removeSelectableStyle(".zkp_playmat_container");
        dojo.query(".stockitem").removeClass("zkp_highlight");
        return;
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

    unselectOtherStocks: function (stock) {
      if (stock.getSelectedItems().length === 0) {
        return;
      }
      for (field in this) {
        if (
          this[field] &&
          this[field].unselectAll &&
          this[field].getSelectedItems().length > 0 &&
          this[field].control_name != stock.control_name
        ) {
          this[field].unselectAll();
        }
      }
    },

    setupExpandHouses: function () {
      for (const player_id in this.gamedatas.players) {
        for (let position = 1; position <= this.keeperHouses; position++) {
          const button = $(`zkp_expand_house_${player_id}:${position}`);
          const house = $(`zkp_keeper_${player_id}:${position}`);
          const className = "zkp_expanded";

          if (this.isSetup) {
            dojo.connect(button, "onclick", this, () => {
              if (dojo.hasClass(house, className)) {
                dojo.removeClass(house, className);
                return;
              }
              dojo.addClass(house, className);
            });
          }

          const hasSpecies = !!this.savedSpecies[player_id][position];

          if (hasSpecies) {
            dojo.removeClass(button, "zkp_hide");
          } else {
            dojo.addClass(button, "zkp_hide");
          }
        }
      }
    },

    calcBackgroundPosition: function (spritePosition, itemsPerRow = 10) {
      const xAxis = (spritePosition % itemsPerRow) * 100;
      const yAxis = Math.floor(spritePosition / itemsPerRow) * 100;
      return `-${xAxis}% -${yAxis}%`;
    },

    playSpeciesSound: function (species_id) {
      const species = this.allSpecies[species_id];

      if (species.class === "mammal") {
        playSound(`zookeepers_${species.diet}`);
        this.disableNextMoveSound();
        return;
      }

      playSound(`zookeepers_${species.class}`);
      this.disableNextMoveSound();
    },

    getTooltipForSpecies: function (species_id, isLog = false) {
      const species = this.allSpecies[species_id];

      if (!species) {
        return;
      }

      const speciesName = _(species.name);

      const backgroundPosition = this.calcBackgroundPosition(species_id - 1);

      const html = `${
        isLog
          ? `<div class="zkp_bigger_species zkp_card" style="background-position: ${backgroundPosition}"></div>`
          : ""
      }<span style="display: block; text-align: center; text-transform: capitalize">${speciesName}</span>`;

      return html;
    },

    getTooltipForKeeper: function (keeper_id) {
      const keeper = this.allKeepers[keeper_id];

      if (!keeper) {
        return;
      }

      const backgroundPosition = this.calcBackgroundPosition(keeper_id - 1, 6);

      const html = `<div class="zkp_bigger_keeper zkp_card" style="background-position: ${backgroundPosition}"></div>`;

      return html;
    },

    addSpeciesTooltipHtml: function (container, species_id) {
      const html = this.getTooltipForSpecies(species_id);
      this.addTooltipHtml(container, html);
    },

    addCustomTooltip: function (container, html) {
      this.addTooltipHtml(container, html, 1000);
    },

    setLoader: function (image_progress, logs_progress) {
      this.inherited(arguments); // required, this is "super()" call, do not remove
      if (!this.isLoadingLogsComplete && logs_progress >= 100) {
        this.isLoadingLogsComplete = true; // this is to prevent from calling this more then once
        this.onLoadingLogsComplete();
      }
    },

    onLoadingLogsComplete: function () {
      console.log("Loading logs complete");

      this.attachRegisteredTooltips();
    },

    registerCustomTooltip(html, id) {
      this._registeredCustomTooltips[id] = html;
      return id;
    },

    attachRegisteredTooltips() {
      console.log("Attaching toolips");

      for (const id in this._registeredCustomTooltips) {
        this.addCustomTooltip(id, this._registeredCustomTooltips[id]);
        this._attachedTooltips[id] = this._registeredCustomTooltips[id];
      }

      this._registeredCustomTooltips = {};
    },

    addPlayerTurnButtons: function () {
      if (this.mainAction > 0) {
        this.gamedatas.gamestate.description = _(
          "${actplayer} has already used a main action, but can still do any available free actions"
        );
        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} have already used a main action, but can still do any available free actions"
        );

        if (this.isLastTurn) {
          this.gamedatas.gamestate.description = _(
            "It's ${actplayer}'s last turn! ${actplayer} has already used a main action, but can still do any available free actions"
          );
          this.gamedatas.gamestate.descriptionmyturn = _(
            "It's your last turn! ${you} have already used a main action, but can still do any available free actions"
          );
        }
      }

      if (this.mainAction == 2 && this.canZooHelp) {
        this.gamedatas.gamestate.description = _(
          "${actplayer} saved a species and can now use the bonus action (ask a zoo for help) or any available free actions"
        );
        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} saved a species and can now use the bonus action (ask a zoo for help) or any available free actions"
        );

        if (this.isLastTurn) {
          this.gamedatas.gamestate.description = _(
            "It's ${actplayer}'s last turn! ${actplayer} saved a species and can now use the bonus action (ask a zoo for help with a species) or any available free actions"
          );
          this.gamedatas.gamestate.descriptionmyturn = _(
            "It's your last turn! ${you} saved a species and can now use the bonus action (ask a zoo for help with a species) or any available free actions"
          );
        }
      }

      if (this.mainAction == 0) {
        this.gamedatas.gamestate.description = _(
          "${actplayer} can select a card and/or do any available actions, limited to one of the main ones"
        );
        this.gamedatas.gamestate.descriptionmyturn = _(
          "${you} can select a card and/or do any available actions, limited to one of the main ones"
        );

        if (this.isLastTurn) {
          this.gamedatas.gamestate.description = _(
            "It's ${actplayer}'s last turn! ${actplayer} can select a card and/or do any available actions, limited to one of the main ones"
          );
          this.gamedatas.gamestate.descriptionmyturn = _(
            "It's your last turn! ${you} can select a card and/or do any available actions, limited to one of the main ones"
          );
        }
      }

      this.updatePageTitle();

      const playerId = this.getActivePlayerId();

      for (const position in this.keepersAtHouses[playerId]) {
        const keepersAtHouse = this.keepersAtHouses[playerId][position];
        if (!keepersAtHouse) {
          this.openHouse = position;
          break;
        }
      }

      if (this.isCurrentPlayerActive()) {
        const canNewSpecies =
          this.freeAction != 1 &&
          ((this.fastMode && this.resourcesInHandNbr > 0) ||
            (!this.fastMode && this.emptyColumnNbr >= 2));

        if (canNewSpecies) {
          this.addActionButton(
            "zkp_new_species_btn",
            _("New Species"),
            "onNewSpecies"
          );
        }

        this.addActionButton(
          "zkp_pass_btn",
          _("Pass Turn"),
          "onPass",
          null,
          null,
          "red"
        );
      }
    },

    performAction: function (action, args = {}) {
      args.gameVersion = this.gameVersion;

      if (this.checkAction(action, true)) {
        this.bgaPerformAction(action, args);
      }
    },

    checkKeeperOwner: function (keeperId, event = null) {
      const playerId = this.getCurrentPlayerId();

      let result = { isOwner: false, position: 0 };

      if (keeperId) {
        const ownedKeepers = this.keepersAtHouses[playerId];

        for (const position in ownedKeepers) {
          if (
            ownedKeepers[position] &&
            ownedKeepers[position].type_arg == keeperId
          ) {
            result = { isOwner: true, position: position };
            break;
          }
        }

        if (!result.isOwner) {
          this.showMessage(_("This keeper is not yours"), "error");
        }

        return result;
      }

      if (event) {
        const targetPlayerId = event.currentTarget.id
          .split("keeper_")[1]
          .split(":")[0];

        const position = event.currentTarget.id.split(":")[1];

        if (targetPlayerId != playerId) {
          this.showMessage(_("This keeper is not yours"), "error");
          result = { isOwner: false, position: 0 };
        } else {
          result = { isOwner: true, position: position };
        }
      }

      return result;
    },

    checkQuarantinedOwner: function (quarantined_id) {
      const playerId = this.getCurrentPlayerId();

      let isOwner = false;

      for (const quarantine_id in this.allQuarantines) {
        const quarantine = this.allQuarantines[quarantine_id];
        const ownedQuarantine = this.quarantinedSpecies[playerId][quarantine];

        if (ownedQuarantine && ownedQuarantine[quarantined_id]) {
          isOwner = true;
          break;
        }
      }

      if (!isOwner) {
        // this.showMessage(_("This quarantine is not yours"), "error");
      }

      return isOwner;
    },

    isQuarantinable(speciesId) {
      const habitats = this.allSpecies[speciesId].habitat;
      const playerId = this.getActivePlayerId();
      let isQuarantinable = false;

      habitats.forEach((habitat) => {
        if (
          !!this.openQuarantines[playerId]["ALL"] ||
          !!this.openQuarantines[playerId][habitat]
        ) {
          isQuarantinable = true;
        }
      });

      return isQuarantinable;
    },

    addSelectableStyle: function (
      containerSelector,
      itemSelector = null,
      condition = null,
      itemCondition = null
    ) {
      const border = "3px solid green";

      if (condition) {
        dojo.query(containerSelector).forEach((element) => {
          if (condition(element)) {
            dojo.setStyle(element, {
              border: itemSelector ? "none" : border,
              cursor: "pointer",
            });
          }
        });
      } else {
        dojo.query(containerSelector).style({
          border: itemSelector ? "none" : border,
          cursor: "pointer",
        });
      }

      if (itemSelector) {
        if (itemCondition) {
          dojo
            .query(`${containerSelector} > ${itemSelector}:first-child`)
            .forEach((element) => {
              if (itemCondition(element)) {
                dojo.removeClass(element, "stockitem_unselectable");
                dojo.setStyle(element, {
                  border: border,
                  ["pointer-events"]: "none",
                });
              }
            });
        } else {
          const query = dojo.query(
            `${containerSelector} > ${itemSelector}:first-child`
          );

          query.removeClass("stockitem_unselectable");
          query.style({ border: border, ["pointer-events"]: "none" });
        }
      }
    },

    removeSelectableStyle: function (containerSelector, itemSelector) {
      dojo.query(containerSelector).style({
        border: "none",
        cursor: "initial",
      });

      if (itemSelector) {
        dojo.query(`${containerSelector} > ${itemSelector}:first-child`).style({
          border: "0 solid red",
          cursor: "pointer",
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
      if (this.isBagHidden) {
        return;
      }

      for (const type in counters) {
        const newValue = counters[type];
        this.bagCounters[type].toValue(newValue);
      }
    },

    updateSpeciesCounters: function (counters) {
      for (const playerId in counters) {
        const counterHandler = this.speciesCounters[playerId];
        const prevValue = counterHandler.getValue();
        const newValue = counters[playerId];

        if (prevValue != newValue) {
          counterHandler.toValue(newValue);

          const originKey = `playmatCounter_${playerId}_${prevValue}`;
          const destinationKey = `playmatCounter_${playerId}_${newValue}`;

          this[destinationKey].addToStockWithId(
            0,
            0,
            `zkp_playmat_counter_${playerId}:${prevValue}`
          );
          this[originKey].removeFromStockById(0);
        }
      }
    },

    formatKeepersAtHouses: function (keepersAtHouses) {
      for (const playerId in keepersAtHouses) {
        for (const position in keepersAtHouses[playerId]) {
          const keeperOnPosition = keepersAtHouses[playerId][position];

          if (Array.isArray(keeperOnPosition)) {
            if (keeperOnPosition.length < 1) {
              keepersAtHouses[playerId][position] = null;
            } else {
              keepersAtHouses[playerId][position] = keeperOnPosition[0];
            }
          } else if (!keeperOnPosition.id) {
            for (const keeper in keeperOnPosition)
              keepersAtHouses[playerId][position] = keeperOnPosition[keeper];
          }
        }
      }
      return keepersAtHouses;
    },

    formatSavableSpecies: function (savableSpecies) {
      const species = savableSpecies;
      if (Array.isArray(savableSpecies)) {
        return null;
      }
      return species;
    },

    lookAtBackup: function ({ column, backup_id, species_id }) {
      const itemId = `species_${species_id}`;
      const stockKey = `backupShop_${column}`;

      if (
        this[stockKey].getAllItems().find((item) => {
          return item.id === itemId;
        })
      ) {
        return;
      }

      this[stockKey].removeFromStockById(backup_id);

      this[stockKey].image_items_per_row = 10;
      this[stockKey].addItemType(
        itemId,
        backup_id == 1 ? -1 : 1,
        g_gamethemeurl + "img/species.png",
        species_id - 1
      );

      this[stockKey].addToStockWithId(itemId, itemId);
      this[stockKey].image_items_per_row = 1;

      this.addSpeciesTooltipHtml(
        `zkp_backup_column:${column}_item_${itemId}`,
        species_id
      );

      dojo.removeClass(
        `zkp_backup_column:${column}_item_${itemId}`,
        "zkp_background_contain"
      );
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

    //stock selections
    onSelectBag: function (stock) {
      this.unselectOtherStocks(stock);

      if (this.gamedatas.gamestate.name === "playerTurn") {
        const stockItemsNbr = stock.getSelectedItems().length;

        if (stockItemsNbr > 0) {
          if (!this.isCurrentPlayerActive()) {
            // this.showMessage(_("It's not your turn"), "error");
            stock.unselectAll();
            return;
          }
        }

        if (this.isBagEmpty) {
          // this.showMessage(_("The bag is empty"), "error");
          stock.unselectAll();
          return;
        }

        this.removeActionButtons();

        const playerId = this.getActivePlayerId();

        const canCollect = this.speciesCounters[playerId].getValue() > 0;
        const canConservationFund =
          !this.fastMode && this.freeAction < 1 && this.resourcesInHandNbr > 1;

        if (stockItemsNbr > 0) {
          if (this.mainAction < 1) {
            if (canCollect && !canConservationFund) {
              this.gamedatas.gamestate.descriptionmyturn = _(
                "${you} can collect resources"
              );
              this.updatePageTitle();

              this.statusBar.addActionButton(
                _("Cancel"),
                () => {
                  stock.unselectAll();
                },
                {
                  color: "alert",
                }
              );

              this.addActionButton(
                "zkp_collect_resources_btn",
                _("Collect"),
                () => {
                  stock.unselectAll();
                  this.onCollectResources();
                }
              );
              return;
            }

            if (canConservationFund && !canCollect) {
              this.gamedatas.gamestate.descriptionmyturn = _(
                "${you} can use the conservation fund"
              );
              this.updatePageTitle();

              this.statusBar.addActionButton(
                _("Cancel"),
                () => {
                  stock.unselectAll();
                },
                {
                  color: "alert",
                }
              );

              this.addActionButton("zkp_collect_fund_btn", _("Fund"), () => {
                stock.unselectAll();
                this.onExchangeResources();
              });
              return;
            }

            if (canCollect && canConservationFund) {
              this.gamedatas.gamestate.descriptionmyturn = _(
                "${you} can collect resources or use the conservation fund"
              );
              this.updatePageTitle();

              this.statusBar.addActionButton(
                _("Cancel"),
                () => {
                  stock.unselectAll();
                },
                {
                  color: "alert",
                }
              );

              this.addActionButton(
                "zkp_collect_resources_btn",
                _("Collect"),
                () => {
                  stock.unselectAll();
                  this.onCollectResources();
                }
              );

              this.addActionButton("zkp_collect_fund_btn", _("Fund"), () => {
                stock.unselectAll();
                this.onExchangeResources();
              });

              return;
            }

            // this.showMessage(_("You can't collect resources now"), "error");
            stock.unselectAll();
            return;
          }

          // this.showMessage(_("You can't collect resources now"), "error");
          stock.unselectAll();
          return;
        }

        this.addPlayerTurnButtons();
      }
    },

    onSelectKeeperPile: function (stock, pile) {
      this.unselectOtherStocks(stock);
      const stockItemsNbr = stock.getSelectedItems().length;

      if (stockItemsNbr > 0) {
        if (!this.isCurrentPlayerActive()) {
          // this.showMessage(_("It's not your turn"), "error");
          stock.unselectAll();
          return;
        }

        if (this.mainAction > 0 || !this.openHouse) {
          // this.showMessage(_("You can't hire a keeper now"), "error");
          stock.unselectAll();
          return;
        }
      }

      if (this.gamedatas.gamestate.name === "playerTurn") {
        this.removeActionButtons();

        if (stockItemsNbr > 0) {
          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can hire a keeper from this pile"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          this.addActionButton("zkp_hire_keeper_btn", _("Hire"), () => {
            stock.unselectAll();
            this.onHireKeeper(pile);
          });

          return;
        }

        this.addPlayerTurnButtons();
      }
    },

    onSelectKeeper: function (stock) {
      this.unselectOtherStocks(stock);

      const stockItemsNbr = stock.getSelectedItems().length;
      const playerId = this.getActivePlayerId();

      if (stockItemsNbr > 0) {
        const itemId = stock.getSelectedItems()[0].id;

        if (!this.checkKeeperOwner(itemId).isOwner) {
          stock.unselectAll();
          return;
        }

        if (!this.isCurrentPlayerActive()) {
          // this.showMessage(_("It's not your turn"), "error");
          stock.unselectAll();
          return;
        }

        const position = this.checkKeeperOwner(itemId).position;

        if (
          this.resourcesInHandNbr < 1 &&
          Object.keys(this.savedSpecies[playerId][position]).length ==
            this.speciesCounters[playerId].getValue()
        ) {
          // this.showMessage(
          //   "You'd be unable to continue this match if you dismissed or replaced this keeper",
          //   "error"
          // );
          stock.unselectAll();
          return;
        }

        if (this.mainAction > 0) {
          // this.showMessage(
          //   "You can't do anything with this keeper now",
          //   "error"
          // );
          stock.unselectAll();
          return;
        }
      }

      if (this.gamedatas.gamestate.name === "playerTurn") {
        this.removeActionButtons();

        if (stockItemsNbr > 0) {
          const itemId = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can dismiss or replace this keeper. If you do, all species kept by them will be discarded"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          this.addActionButton("zkp_replace_keeper_btn", _("Replace"), () => {
            stock.unselectAll();
            this.onReplaceKeeper(itemId);
          });

          this.addActionButton("zkp_dismiss_keeper_btn", _("Dismiss"), () => {
            stock.unselectAll();
            this.onDismissKeeper(itemId);
          });

          return;
        }

        this.addPlayerTurnButtons();
      }
    },

    onSelectSpecies: function (stock) {
      this.unselectOtherStocks(stock);

      const stateName = this.gamedatas.gamestate.name;

      const stockItemsNbr = stock.getSelectedItems().length;

      if (stockItemsNbr > 0) {
        if (!this.isCurrentPlayerActive()) {
          // this.showMessage(_("It's not your turn"), "error");
          stock.unselectAll();
          return;
        }

        if (this.mainAction > 0 && this.mainAction != 2) {
          // this.showMessage(
          //   _("You can't do anything with this species now"),
          //   "error"
          // );
          stock.unselectAll();
          return;
        }
      }

      if (stateName === "playerTurn") {
        this.removeActionButtons();

        if (stockItemsNbr > 0) {
          const itemId = stock.getSelectedItems()[0].id;

          if (this.canZooHelp) {
            this.gamedatas.gamestate.descriptionmyturn = _(
              "${you} can ask a zoo for help with this species"
            );
            this.updatePageTitle();

            this.statusBar.addActionButton(
              _("Cancel"),
              () => {
                stock.unselectAll();
              },
              {
                color: "alert",
              }
            );

            this.addActionButton("zkp_zoo_help_btn", _("Zoo Help"), () => {
              stock.unselectAll();
              this.onZooHelp(itemId);
            });
            return;
          }

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can select an action with this species"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          if (this.savableWithFund && this.savableWithFund[itemId]) {
            this.addActionButton(
              "zkp_save_species_btn",
              _("Save (with conservation fund)"),
              () => {
                stock.unselectAll();
                this.onSaveSpecies(itemId);
              }
            );
          } else if (this.savableSpecies && this.savableSpecies[itemId]) {
            this.addActionButton("zkp_save_species_btn", _("Save"), () => {
              stock.unselectAll();
              this.onSaveSpecies(itemId);
            });
          }

          this.addActionButton("zkp_discard_species_btn", _("Discard"), () => {
            stock.unselectAll();
            this.onDiscardSpecies(itemId);
          });

          if (this.isQuarantinable(itemId)) {
            this.addActionButton(
              "zkp_quarantine_species_btn",
              _("Quarantine"),
              () => {
                stock.unselectAll();
                this.onQuarantineSpecies(itemId);
              }
            );
          }
          return;
        }
        this.addPlayerTurnButtons();
        return;
      }

      if (stateName === "mngSecondSpecies") {
        this.removeActionButtons();
        if (stock.getSelectedItems().length > 0) {
          const itemId = stock.getSelectedItems()[0].id;

          this.addActionButton("zkp_discard_species_btn", _("Discard"), () => {
            stock.unselectAll();
            this.onDiscardSpecies(itemId);
          });

          if (this.isQuarantinable(itemId)) {
            this.addActionButton(
              "zkp_quarantine_species_btn",
              _("Quarantine"),
              () => {
                stock.unselectAll();
                this.onQuarantineSpecies(itemId);
              }
            );
          }
          return;
        }

        this.removeActionButtons();

        const canNewSpecies =
          this.freeAction != 1 &&
          ((this.fastMode && this.resourcesInHandNbr > 0) ||
            (!this.fastMode && this.emptyColumnNbr >= 2));

        if (canNewSpecies) {
          this.addActionButton(
            "zkp_new_species_btn",
            _("New Species"),
            "onNewSpecies"
          );
        }

        this.addActionButton(
          "zkp_cancel_btn",
          _("Skip"),
          () => {
            stock.unselectAll();
            this.onCancelMngSpecies();
          },
          null,
          null,
          "red"
        );
      }
    },

    onSelectBackup: function (target, stock) {
      this.unselectOtherStocks(stock);

      const stockItemsNbr = stock.getSelectedItems().length;

      if (stockItemsNbr > 0) {
        if (!this.isCurrentPlayerActive()) {
          // this.showMessage(_("It's not your turn"), "error");
          stock.unselectAll();
          return;
        }

        if (this.mainAction > 0) {
          // this.showMessage(
          //   _("You can't do anything with this species now"),
          //   "error"
          // );
          stock.unselectAll();
          return;
        }
      }

      const stateName = this.gamedatas.gamestate.name;
      const column = target.split(":")[1];

      if (stateName === "playerTurn") {
        this.removeActionButtons();

        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can select an action with this species"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          this.addActionButton(
            "zkp_manage_backup_species_btn",
            _("Look at and discard/quarantine"),
            () => {
              stock.unselectAll();
              this.onLookAtBackup(column, item);
            }
          );
          return;
        }
        this.addPlayerTurnButtons();
        return;
      }

      if (stateName === "mngSecondSpecies") {
        this.removeActionButtons();
        if (stock.getSelectedItems().length > 0) {
          const item = stock.getSelectedItems()[0].id;

          this.addActionButton(
            "zkp_manage_backup_species_btn",
            _("Look at and discard/quarantine"),
            () => {
              stock.unselectAll();
              this.onLookAtBackup(column, item);
            }
          );
          return;
        }

        this.removeActionButtons();

        const canNewSpecies =
          this.freeAction != 1 &&
          ((this.fastMode && this.resourcesInHandNbr > 0) ||
            (!this.fastMode && this.emptyColumnNbr >= 2));

        if (canNewSpecies) {
          this.addActionButton(
            "zkp_new_species_btn",
            _("New Species"),
            "onNewSpecies"
          );
        }

        this.addActionButton(
          "zkp_cancel_btn",
          _("Cancel"),
          () => {
            stock.unselectAll();
            this.onCancelMngSpecies();
          },
          null,
          null,
          "red"
        );
        return;
      }
    },

    onSelectQuarantined: function (stock) {
      this.unselectOtherStocks(stock);

      const stockItemsNbr = stock.getSelectedItems().length;
      const playerId = this.getActivePlayerId();

      const savableQuarantined = this.savableQuarantined[playerId];

      if (stockItemsNbr > 0) {
        const itemId = stock.getSelectedItems()[0].id;

        if (!this.checkQuarantinedOwner(itemId)) {
          stock.unselectAll();
          return;
        }

        if (!this.isCurrentPlayerActive()) {
          // this.showMessage(_("It's not your turn"), "error");
          stock.unselectAll();
          return;
        }

        if (
          this.mainAction > 0 ||
          !savableQuarantined ||
          !savableQuarantined[itemId]
        ) {
          // this.showMessage(
          //   _("You can't do anything with this species now"),
          //   "error"
          // );
          stock.unselectAll();
          return;
        }
      }

      const stateName = this.gamedatas.gamestate.name;

      if (stateName === "playerTurn") {
        this.removeActionButtons();

        if (stock.getSelectedItems().length > 0) {
          const itemId = stock.getSelectedItems()[0].id;

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can save this quarantined species"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          if (this.savableQuarantinedWithFund[playerId][itemId]) {
            this.addActionButton(
              "zkp_save_species_btn",
              _("Save (with conservation fund)"),
              () => {
                stock.unselectAll();
                this.onSaveQuarantined(itemId);
              }
            );
          } else {
            this.addActionButton("zkp_save_species_btn", _("Save"), () => {
              stock.unselectAll();
              this.onSaveQuarantined(itemId);
            });
          }

          return;
        }

        this.addPlayerTurnButtons();
      }
    },

    onSelectObjective(stock) {
      this.unselectOtherStocks(stock);

      const stockItemsNbr = stock.getSelectedItems().length;

      if (this.gamedatas.gamestate.name === "playerTurn") {
        if (stockItemsNbr > 0) {
          if (!this.isCurrentPlayerActive()) {
            // this.showMessage(_("It's not your turn"), "error");
            stock.unselectAll();
            return;
          }

          if (this.mainAction > 0) {
            // this.showMessage(
            //   _("You can't replace this objective now"),
            //   "error"
            // );
            stock.unselectAll();
            return;
          }

          this.removeActionButtons();

          this.gamedatas.gamestate.descriptionmyturn = _(
            "${you} can replace this objective"
          );
          this.updatePageTitle();

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          this.statusBar.addActionButton(
            _("Cancel"),
            () => {
              stock.unselectAll();
            },
            {
              color: "alert",
            }
          );

          this.addActionButton(
            "zkp_replace_objective_btn",
            _("Replace"),
            () => {
              stock.unselectAll();
              this.onReplaceObjective();
            }
          );

          return;
        }

        this.addPlayerTurnButtons();
      }
    },

    //actions
    onPass: function () {
      const action = "pass";

      if (this.mainAction < 1) {
        this.confirmationDialog(
          _(
            "You haven't used any main action yet. Are you sure you want to pass?"
          ),
          () => {
            this.performAction(action);
          }
        );
        return;
      }

      this.performAction(action);
    },

    onCollectResources: function () {
      const action = "collectResources";

      this.performAction(action);
    },

    onHireKeeper: function (pile) {
      const action = "hireKeeper";

      this.performAction(action, { pile: parseInt(pile) });
    },

    onDismissKeeper: function (keeperId) {
      const action = "dismissKeeper";

      const { isOwner, position } = this.checkKeeperOwner(keeperId);
      if (isOwner) {
        this.performAction(action, { board_position: parseInt(position) });
      }
    },

    onSelectDismissedPile: function (event) {
      const action = "selectDismissedPile";

      const pile = event.target.id.split(":")[1];

      this.performAction(action, { pile: parseInt(pile) });
    },

    onReplaceKeeper(keeperId) {
      const action = "replaceKeeper";

      const { isOwner, position } = this.checkKeeperOwner(keeperId);
      if (isOwner) {
        this.performAction(action, { board_position: parseInt(position) });
      }
    },

    onSelectReplacedPile: function (event) {
      const action = "selectReplacedPile";

      const pile = event.target.id.split(":")[1];

      this.performAction(action, { pile: parseInt(pile) });
    },

    onCancelMngKeepers: function () {
      const action = "cancelMngKeepers";

      this.performAction(action);
    },

    onExchangeResources: function () {
      const action = "exchangeResources";

      this.performAction(action);
    },

    onCollectFromExchange: function (choosen_nbr) {
      const action = "collectFromExchange";

      this.performAction(action, { choosen_nbr: parseInt(choosen_nbr) });
    },

    onCancelExchange: function () {
      const action = "cancelExchange";

      this.freeAction = 0;

      this.performAction(action);
    },

    onReturnFromExchange: function (choosen_nbr, resource_type) {
      const action = "returnFromExchange";

      this.performAction(action, {
        nbr: parseInt(choosen_nbr),
        type: resource_type,
      });
    },

    onReturnExcess: function (choosen_nbr, resource_type) {
      const action = "returnExcess";

      this.performAction(action, {
        nbr: parseInt(choosen_nbr),
        type: resource_type,
      });
    },

    onSaveSpecies: function (species_id) {
      const action = "saveSpecies";

      this.performAction(action, { species_id });
    },

    onSelectAssignedKeeper: function (event) {
      const action = "selectAssignedKeeper";

      const { isOwner, position } = this.checkKeeperOwner(null, event);
      if (isOwner) {
        this.performAction(action, { board_position: parseInt(position) });
      }
    },

    onSaveQuarantined: function (speciesId) {
      const action = "saveQuarantined";

      this.performAction(action, { species_id: parseInt(speciesId) });
    },

    onSelectQuarantinedKeeper: function (event) {
      const action = "selectQuarantinedKeeper";

      const position = event.currentTarget.id.split(":")[1];

      this.performAction(action, { board_position: parseInt(position) });
    },

    onDiscardSpecies: function (speciesId) {
      const action = "discardSpecies";

      this.performAction(action, { species_id: parseInt(speciesId) });
    },

    onQuarantineSpecies: function (speciesId) {
      const action = "quarantineSpecies";

      this.performAction(action, { species_id: parseInt(speciesId) });
    },

    onSelectQuarantine: function (event) {
      const action = "selectQuarantine";

      const quarantine = event.currentTarget.id.split(":")[1];

      this.performAction(action, { quarantine: quarantine });
    },

    onLookAtBackup: function (column, backupId) {
      const action = "lookAtBackup";

      this.performAction(action, {
        shop_position: parseInt(column),
        backup_id: parseInt(backupId),
      });
    },

    onDiscardBackup: function () {
      const action = "discardBackup";

      this.performAction(action);
    },

    onQuarantineBackup: function () {
      const action = "quarantineBackup";

      this.performAction(action);
    },

    onSelectBackupQuarantine: function (event) {
      const action = "selectBackupQuarantine";

      const quarantine = event.currentTarget.id.split(":")[1];

      this.performAction(action, { quarantine: quarantine });
    },

    onCancelMngSpecies: function () {
      const action = "cancelMngSpecies";

      this.performAction(action);
    },

    onNewSpecies: function () {
      const action = "newSpecies";

      this.performAction(action);
    },

    onReturnFromNewSpecies: function (resource_type) {
      const action = "returnFromNewSpecies";

      this.performAction(action, {
        type: resource_type,
      });
    },

    onCancelNewSpecies: function () {
      const action = "cancelNewSpecies";

      this.performAction(action);
    },

    onZooHelp: function (speciesId) {
      const action = "zooHelp";

      this.performAction(action, { species_id: parseInt(speciesId) });
    },

    onSelectZoo: function (event) {
      const action = "selectZoo";

      const playerId = event.currentTarget.id.split(":")[1];

      this.performAction(action, { player_id: parseInt(playerId) });
    },

    onSelectHelpQuarantine: function (event) {
      const action = "selectHelpQuarantine";

      const quarantine = event.currentTarget.id.split(":")[1];

      this.performAction(action, { quarantine: quarantine });
    },

    onReplaceObjective: function () {
      const action = "replaceObjective";

      this.performAction(action);
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
      dojo.subscribe("saveQuarantined", this, "notif_saveQuarantined");
      dojo.subscribe("discardSpecies", this, "notif_discardSpecies");
      this.notifqueue.setSynchronous("discardSpecies", 1000);
      dojo.subscribe("quarantineSpecies", this, "notif_quarantineSpecies");
      dojo.subscribe("lookAtBackup", this, "notif_lookAtBackup");
      dojo.subscribe("discardBackup", this, "notif_discardBackup");
      dojo.subscribe(
        "discardBackupPrivately",
        this,
        "notif_discardBackupPrivately"
      );
      this.notifqueue.setSynchronous("discardBackupPrivately", 1000);
      dojo.subscribe("quarantineBackup", this, "notif_quarantineBackup");
      dojo.subscribe("completeKeeper", this, "notif_completeKeeper");
      dojo.subscribe("revealSpecies", this, "notif_revealSpecies");
      dojo.subscribe(
        "discardAllKeptSpecies",
        this,
        "notif_discardAllKeptSpecies"
      );
      dojo.subscribe("zooHelp", this, "notif_zooHelp");
      dojo.subscribe("newSpecies", this, "notif_newSpecies");
      dojo.subscribe("drawSingleSpecies", this, "notif_drawSingleSpecies");
      dojo.subscribe("newVisibleSpecies", this, "notif_newVisibleSpecies");
      dojo.subscribe("replaceObjective", this, "notif_replaceObjective");
      dojo.subscribe(
        "replaceObjectivePrivately",
        this,
        "notif_replaceObjectivePrivately"
      );
      dojo.subscribe("outOfActions", this, "notif_outOfActions");
      dojo.subscribe("newScores", this, "notif_newScores");
      dojo.subscribe("pass", this, "notif_pass");
      dojo.subscribe("lastTurn", this, "notif_lastTurn");

      //game end animations
      dojo.subscribe("regularPoints", this, "notif_regularPoints");
      this.notifqueue.setSynchronous("regularPoints", 500);
      dojo.subscribe("quarantinePenalties", this, "notif_quarantinePenalties");
      this.notifqueue.setSynchronous("quarantinePenalties", 500);
      dojo.subscribe("objectiveBonus", this, "notif_objectiveBonus");
      this.notifqueue.setSynchronous("objectiveBonus", 500);
    },

    notif_hireKeeper: function (notif) {
      this.pileCounters = notif.args.pile_counters;
      this.pilesTops = notif.args.piles_tops;

      const player_id = notif.args.player_id;
      const keeper_id = notif.args.keeper_id;
      const position = notif.args.board_position;
      const pile = notif.args.pile;
      const pileKey = `keeper_pile:${pile}`;
      const houseKey = `board_${player_id}:${position}`;

      this.image_items_per_row = 6;
      this[houseKey].addToStockWithId(
        keeper_id,
        keeper_id,
        `zkp_keeper_pile:${notif.args.pile}`
      );
      this[pileKey].removeAll();

      const pileElement = $(`zkp_keeper_pile:${pile}`);
      const className = "zkp_empty_pile";
      const top = this.pilesTops[pile];

      if (this.pileCounters[pile] <= 0) {
        dojo.addClass(pileElement, className);
      } else {
        this[pileKey].addToStockWithId(top, top);
      }
    },

    notif_dismissKeeper: function (notif) {
      const player_id = notif.args.player_id;
      const keeper_id = notif.args.keeper_id;
      const position = notif.args.board_position;
      const pile = notif.args.pile;
      const level = this.allKeepers[keeper_id].level;

      const houseKey = `board_${player_id}:${position}`;
      const pileKey = `keeper_pile:${pile}`;
      const pileElement = $(`zkp_${pileKey}`);
      const className = "zkp_empty_pile";

      const houseElement = $(`zkp_keeper_${player_id}:${position}`);
      dojo.removeClass(houseElement, "zkp_expanded");

      this[houseKey].removeFromStockById(keeper_id, pileElement);

      if (dojo.hasClass(pileElement, className)) {
        this[pileKey].addToStockWithId(level);
      }

      dojo.removeClass(pileElement, className);

      this.pileCounters = notif.args.pile_counters;
      this.pilesTops = notif.args.piles_tops;
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
              $("zkp_bag_stock")
            );

            this.placeOnObject(`zkp_${type}_cube_${i}`, $("zkp_bag_stock"));

            const animation = this.slideToObject(
              `zkp_${type}_cube_${i}`,
              "overall_player_board_" + player_id,
              500,
              (loopIterator - 1) * 100
            );

            dojo.connect(animation, "onEnd", () => {
              dojo.destroy(`zkp_${type}_cube_${i}`);
            });

            animation.play();
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

          const animation = this.slideToObject(
            `zkp_${type}_cube_${i}`,
            $("zkp_bag_stock"),
            500,
            (i - 1) * 100
          );

          dojo.connect(animation, "onEnd", () => {
            dojo.destroy(`zkp_${type}_cube_${i}`);
          });

          animation.play();
        }
      }
      this.updateResourceCounters(newResourcesCounter, notif.args.player_id);
      this.updateBagCounters(notif.args.bag_counters, notif.args.player_id);
    },

    notif_saveSpecies: function (notif) {
      const player_id = notif.args.player_id;
      const board_position = notif.args.board_position;
      const shop_position = notif.args.shop_position;
      const species_id = notif.args.species_id;

      this.playSpeciesSound(species_id);

      const originKey = `visibleShop_${shop_position}`;
      const destinationKey = `board_${player_id}:${board_position}`;
      const originElement = `zkp_visible_species_${shop_position}_item_${species_id}`;

      this.displayScoring(
        `zkp_visible_shop`,
        notif.args.player_color,
        notif.args.species_points
      );

      this[destinationKey].image_items_per_row = 10;
      this[destinationKey].addToStockWithId(
        `species_${species_id}`,
        `species_${species_id}`,
        originElement
      );
      this[destinationKey].image_items_per_row = 6;

      this.addSpeciesTooltipHtml(
        `zkp_keeper_${player_id}:${board_position}_item_species_${species_id}`,
        species_id
      );

      this.updateSpeciesCounters(notif.args.species_counters);
      this[originKey].removeFromStockById(species_id);

      this.canZooHelp = notif.args.can_zoo_help;
      this.possibleZoos = notif.args.possible_zoos;
      this.savedSpecies = notif.args.saved_species;
      this.openQuarantines = notif.args.open_quarantines;
    },

    notif_saveQuarantined: function (notif) {
      this.savedSpecies = notif.args.saved_species;

      const player_id = notif.args.player_id;
      const board_position = notif.args.board_position;
      const quarantine = notif.args.quarantine;
      const species_id = notif.args.species_id;

      this.playSpeciesSound(species_id);

      const originKey = `quarantine_${player_id}:${quarantine}`;
      const destinationKey = `board_${player_id}:${board_position}`;
      const originElement = `zkp_${originKey}_item_${species_id}`;

      this.displayScoring(
        `zkp_${originKey}`,
        notif.args.player_color,
        notif.args.species_points
      );

      this[destinationKey].image_items_per_row = 10;
      this[destinationKey].addToStockWithId(
        `species_${species_id}`,
        `species_${species_id}`,
        originElement
      );
      this[destinationKey].image_items_per_row = 6;

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

    notif_quarantineSpecies: function (notif) {
      const column = notif.args.shop_position;
      const species_id = notif.args.species_id;
      const player_id = notif.args.player_id;
      const quarantine = notif.args.quarantine;

      const originKey = `visibleShop_${column}`;
      const destinationKey = `quarantine_${player_id}:${quarantine}`;
      const originElement = `zkp_visible_species_${column}_item_${species_id}`;

      this[destinationKey].addToStockWithId(
        species_id,
        species_id,
        originElement
      );
      this[originKey].removeFromStockById(species_id);

      this.displayScoring(`zkp_${destinationKey}`, notif.args.player_color, -2);

      this.addSpeciesTooltipHtml(
        `zkp_quarantine_${player_id}:${quarantine}_item_${species_id}`,
        species_id
      );

      this.quarantinedSpecies = notif.args.quarantined_species;
      this.openQuarantines = notif.args.open_quarantines;
    },

    notif_quarantineBackup: function (notif) {
      const column = notif.args.shop_position;
      const species_id = notif.args.species_id;
      const player_id = notif.args.player_id;
      const backup_id = notif.args.backup_id;
      const quarantine = notif.args.quarantine;

      const originKey = `backupShop_${column}`;
      const destinationKey = `quarantine_${player_id}:${quarantine}`;

      let originElement = `zkp_backup_column:${column}_item_${backup_id}`;

      if (this.isCurrentPlayerActive()) {
        originElement = `zkp_backup_column:${column}_item_species_${species_id}`;
      }

      this[destinationKey].addToStockWithId(
        species_id,
        species_id,
        originElement
      );

      this[originKey].removeFromStockById(`species_${species_id}`);
      this[originKey].removeFromStockById(backup_id);

      this.displayScoring(`zkp_${destinationKey}`, notif.args.player_color, -2);

      this.addSpeciesTooltipHtml(
        `zkp_quarantine_${player_id}:${quarantine}_item_${species_id}`,
        species_id
      );

      this.quarantinedSpecies = notif.args.quarantined_species;
      this.openQuarantines = notif.args.open_quarantines;
    },

    notif_lookAtBackup: function (notif) {
      const column = notif.args.shop_position;
      const backup_id = notif.args.backup_id;
      const species_id = notif.args.species_id;

      this.lookAtBackup({
        column: column,
        backup_id: backup_id,
        species_id: species_id,
      });
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
      const species_id = notif.args.species_id;

      const originKey = `backupShop_${column}`;
      const stockItems = this[originKey].getAllItems();
      const lastInColumn = stockItems.length - 1;
      const backupId = stockItems[lastInColumn].id;
      const destinationKey = `visibleShop_${column}`;
      const originElement = `zkp_backup_column:${column}_item_${backupId}`;

      this[destinationKey].addToStockWithId(
        species_id,
        species_id,
        originElement
      );

      this.addSpeciesTooltipHtml(
        `zkp_visible_species_${column}_item_${species_id}`,
        species_id
      );

      this[originKey].removeFromStockById(backupId);

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

        const discardedElement = $(`zkp_discarded_species_${species_id}`);

        this.placeOnObject(discardedElement, item);

        const animation = this.slideToObject(discardedElement, deckElement);

        dojo.connect(animation, "onEnd", () => {
          this[stockKey].removeFromStockById(
            `species_${species_id}`,
            deckElement
          );

          dojo.destroy(discardedElement);
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

    notif_zooHelp(notif) {},

    notif_newSpecies(notif) {
      this.backupSpecies = notif.args.backup_species;
      this.visibleSpecies = notif.args.visible_species;

      const deckElement = "zkp_species_deck";
      for (let column = 1; column <= this.shopPositions; column++) {
        const backupKey = `backupShop_${column}`;
        const visibleKey = `visibleShop_${column}`;

        if (!this.fastMode) {
          this[backupKey].removeAllTo(deckElement);

          for (let backup = 1; backup <= 2; backup++) {
            this[backupKey].addToStockWithId(0, backup, deckElement);
          }
        }

        this[visibleKey].removeAllTo(deckElement);
        const speciesId = this.visibleSpecies[column]?.type_arg;

        if (speciesId) {
          this[visibleKey].addToStockWithId(speciesId, speciesId, deckElement);
        }
      }
    },

    notif_newVisibleSpecies(notif) {
      const species_id = notif.args.species_id;
      const column = notif.args.shop_position;

      this.addSpeciesTooltipHtml(
        `zkp_visible_species_${column}_item_${species_id}`,
        species_id
      );
    },

    notif_drawSingleSpecies(notif) {
      const player_id = notif.args.player_id;
      const position = notif.args.shop_position;
      const species_id = notif.args.species_id;

      const deckElement = $(`zkp_species_deck`);
      const visibleKey = `visibleShop_${position}`;

      this[visibleKey].addToStockWithId(species_id, species_id, deckElement);
    },

    notif_replaceObjective(notif) {
      const current_player_id = this.getCurrentPlayerId();
      const player_id = notif.args.player_id;

      if (current_player_id == player_id) {
        return;
      }

      const stockKey = `objective_${player_id}`;
      const deckElement = `zkp_objectives_deck`;

      this[stockKey].removeFromStockById(0, deckElement);
      this[stockKey].addToStockWithId(0, 0, deckElement);
    },

    notif_replaceObjectivePrivately(notif) {
      const player_id = notif.args.player_id;
      const replaced_objective_id = notif.args.replaced_objective_id;
      const new_objective_id = notif.args.new_objective_id;

      const stockKey = `objective_${player_id}`;
      const deckElement = `zkp_objectives_deck`;

      this[stockKey].removeFromStockById(replaced_objective_id, deckElement);

      this[stockKey].addToStockWithId(
        new_objective_id,
        new_objective_id,
        deckElement
      );

      const backgroundPosition = this.calcBackgroundPosition(
        this.allObjectives[new_objective_id].sprite_pos,
        4
      );
      this.addTooltipHtml(
        `zkp_objective:${player_id}_item_${new_objective_id}`,
        `<div class="zkp_bigger_objective zkp_card" style="background-position: ${backgroundPosition}"></div>`
      );
    },

    notif_newScores: function (notif) {
      this.scoreCtrl[notif.args.player_id].toValue(notif.args.new_scores);
    },

    notif_outOfActions: function (notif) {},

    notif_pass: function (notif) {},

    notif_lastTurn: function (notif) {
      this.showMessage(
        this.format_string_recursive(
          _(
            "${player_name} reaches ${species_goal} saved species. Each of the other players must play their last turn before the game ends"
          ),
          {
            player_name: notif.args.player_name,
            species_goal: notif.args.species_goal,
          }
        ),
        "warning"
      );
    },

    notif_regularPoints: function (notif) {
      const player_id = notif.args.player_id;
      const position = notif.args.board_position;
      const keeper_id = notif.args.keeper_id;

      this.displayScoring(
        `zkp_keeper_${player_id}:${position}_item_${keeper_id}`,
        notif.args.player_color,
        notif.args.points
      );
    },

    notif_quarantinePenalties: function (notif) {
      const player_id = notif.args.player_id;
      const quarantine = notif.args.quarantine;
      const species_id = notif.args.species_id;

      this.displayScoring(
        `zkp_quarantine_${player_id}:${quarantine}_item_${species_id}`,
        notif.args.player_color,
        -2
      );
    },

    notif_objectiveBonus: function (notif) {
      const player_id = notif.args.player_id;
      const objective_id = notif.args.objective_id;
      const stockKey = `objective_${player_id}`;

      if (player_id !== this.getCurrentPlayerId()) {
        this[stockKey].removeFromStockById(0);
        this[stockKey].image_items_per_row = 4;
        this[stockKey].extraClasses = "zkp_card";
        this[stockKey].addToStockWithId(objective_id, objective_id);
      }

      this.displayScoring(
        `zkp_objective:${player_id}_item_${objective_id}`,
        notif.args.player_color,
        notif.args.bonus
      );
    },

    // Add tooltips to logs
    // @Override
    format_string_recursive(log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;

          if (args.species_id && args.species_name) {
            const html = this.getTooltipForSpecies(args.species_id, true);
            const uid = Date.now() + args.species_id;
            args.species_name = `<span class="zkp_log_highlight" id="zkp_species_log:${uid}">${_(
              args.species_name
            )}</span>`;
            this.registerCustomTooltip(html, `zkp_species_log:${uid}`);
          }

          if (args.keeper_id) {
            if (args.keeper_name) {
              const html = this.getTooltipForKeeper(args.keeper_id);
              const uid = Date.now() + args.keeper_id;
              args.keeper_name = `<span class="zkp_log_highlight" id="zkp_keeper_log:${uid}">${args.keeper_name}</span>`;
              this.registerCustomTooltip(html, `zkp_keeper_log:${uid}`);
            }

            if (args.hired_keeper_name && args.hired_keeper_id) {
              const html = this.getTooltipForKeeper(args.hired_keeper_id);
              const uid = Date.now() + args.hired_keeper_id;
              args.hired_keeper_name = `<span class="zkp_log_highlight" id="zkp_keeper_log:${uid}">${args.hired_keeper_name}</span>`;
              this.registerCustomTooltip(html, `zkp_keeper_log:${uid}`);
            }
          }
        }
      } catch (e) {
        console.error(log, args, "Exception thrown", e.stack);
      }

      return this.inherited(arguments);
    },

    initObserver: function () {
      const observer = new MutationObserver((mutationList) => {
        console.log(mutationList);
        mutationList.forEach((mutation) => {
          const target = mutation.target;

          console.log(mutation, target);

          if (!target.classList.contains("zkp_card")) {
            return;
          }

          console.log(target.style, target.style.borderWidth);

          if (target.style.borderWidth === "1px") {
            target.style.borderWidth = "3px";
          }
        });
      });

      observer.observe(document.getElementById("zkp_gameplay_area"), {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ["style"],
      });
    },
  });
});
