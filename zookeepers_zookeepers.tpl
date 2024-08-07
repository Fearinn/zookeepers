{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- Zookeepers implementation : © <Matheus Gomes> <matheusgomesforwork@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<audio
  id="audiosrc_zookeepers_carnivore"
  src="{GAMETHEMEURL}img/zookeepers_carnivore.mp3"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_o_zookeepers_carnivore"
  src="{GAMETHEMEURL}img/zookeepers_carnivore.ogg"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_zookeepers_herbivore"
  src="{GAMETHEMEURL}img/zookeepers_herbivore.mp3"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_o_zookeepers_herbivore"
  src="{GAMETHEMEURL}img/zookeepers_herbivore.ogg"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_zookeepers_bird"
  src="{GAMETHEMEURL}img/zookeepers_bird.mp3"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_o_zookeepers_bird"
  src="{GAMETHEMEURL}img/zookeepers_bird.ogg"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_zookeepers_reptile"
  src="{GAMETHEMEURL}img/zookeepers_reptile.mp3"
  preload="none"
  autobuffer
></audio>
<audio
  id="audiosrc_o_zookeepers_reptile"
  src="{GAMETHEMEURL}img/zookeepers_reptile.ogg"
  preload="none"
  autobuffer
></audio>
<div id="zkp_gameplay_area" class="zkp_gameplay_area">
  <div id="zkp_common_area" class="zkp_common_area">
    <div id="zkp_species_shop" class="zkp_species_shop">
      <div id="zkp_backup_shop" class="zkp_backup_shop">
        <div id="zkp_backup_column:1" class="zkp_backup_column"></div>
        <div id="zkp_backup_column:2" class="zkp_backup_column"></div>
        <div id="zkp_backup_column:3" class="zkp_backup_column"></div>
        <div id="zkp_backup_column:4" class="zkp_backup_column"></div>
      </div>
      <div id="zkp_visible_shop" class="zkp_visible_shop">
        <div id="zkp_visible_species_1" class="zkp_visible_species"></div>
        <div id="zkp_visible_species_2" class="zkp_visible_species"></div>
        <div id="zkp_visible_species_3" class="zkp_visible_species"></div>
        <div id="zkp_visible_species_4" class="zkp_visible_species"></div>
        <div id="zkp_visible_species_5" class="zkp_visible_species"></div>
        <div id="zkp_visible_species_6" class="zkp_visible_species"></div>
      </div>
    </div>
    <div id="zkp_keeper_piles" class="zkp_keeper_piles">
      <div id="zkp_keeper_pile:1" class="zkp_keeper_pile"></div>
      <div id="zkp_keeper_pile:2" class="zkp_keeper_pile"></div>
      <div id="zkp_keeper_pile:3" class="zkp_keeper_pile"></div>
      <div id="zkp_keeper_pile:4" class="zkp_keeper_pile"></div>
    </div>
    <div id="zkp_bag" class="zkp_bag zkp_column_container whiteblock">
      <h3>{RESOURCES}</h3>
      <div id="zkp_bag_stock" style="width: 150px"></div>
      <div id="zkp_bag_counters" class="zkp_bag_counters">
        <div id="zkp_bag_plant" class="zkp_resource">
          <div
            id="zkp_bag_plant_icon"
            class="zkp_resource_icon zkp_plant_icon"
          ></div>
          <span id="zkp_bag_counter_plant">0</span>
        </div>
        <div id="zkp_bag_meat" class="zkp_resource">
          <div
            id="zkp_bag_meat_icon"
            class="zkp_resource_icon zkp_meat_icon"
          ></div>
          <span id="zkp_bag_counter_meat">0</span>
        </div>
        <div id="zkp_bag_meat" class="zkp_resource">
          <div
            id="zkp_bag_kit_icon"
            class="zkp_resource_icon zkp_kit_icon"
          ></div>
          <span id="zkp_bag_counter_kit">0</span>
        </div>
      </div>
    </div>
    <div id="zkp_species_deck" class="zkp_species_deck zkp_card"></div>
    <div id="zkp_objectives_deck" class="zkp_objectives_deck zkp_card"></div>
  </div>

  <div id="zkp_playmats" class="zkp_playmats">
    <!-- BEGIN myzooblock -->
    <div
      id="zkp_playmat_container:{PLAYER_ID}"
      class="zkp_playmat_container:{PLAYER_ID} zkp_playmat_container whiteblock"
    >
      <h3 id="zkp_my_zoo_title" style="color: #{PLAYER_COLOR}">{YOUR ZOO}</h3>
      <div id="zkp_playmat:{PLAYER_ID}" class="zkp_playmat">
        <button
          id="zkp_expand_house_{PLAYER_ID}:1"
          class="zkp_expand_house zkp_expand_house_1"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:2"
          class="zkp_expand_house zkp_expand_house_2"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:3"
          class="zkp_expand_house zkp_expand_house_3"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:4"
          class="zkp_expand_house zkp_expand_house_4"
        ></button>
        <div id="zkp_houses_{PLAYER_ID}" class="zkp_houses">
          <div
            id="zkp_keeper_{PLAYER_ID}:1"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:2"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:3"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:4"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
        </div>
        <div id="zkp_playmat_counters_{PLAYER_ID}" class="zkp_playmat_counters">
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:0"
            class="zkp_playmat_counter_0 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:1"
            class="zkp_playmat_counter_1 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:2"
            class="zkp_playmat_counter_2 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:3"
            class="zkp_playmat_counter_3 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:4"
            class="zkp_playmat_counter_4 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:5"
            class="zkp_playmat_counter_5 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:6"
            class="zkp_playmat_counter_6 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:7"
            class="zkp_playmat_counter_7 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:8"
            class="zkp_playmat_counter_8 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:9"
            class="zkp_playmat_counter_9 zkp_playmat_counter"
          ></div>
        </div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:ALL"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_ALL"
          data-quarantine="ALL"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:TEM"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_TEM"
          data-quarantine="TEM"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:SAV"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_SAV"
          data-quarantine="SAV"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:PRA"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_PRA"
          data-quarantine="PRA"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:DES"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_DES"
          data-quarantine="DES"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:AQU"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_AQU"
          data-quarantine="AQU"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:TRO"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_TRO"
          data-quarantine="TRO"
        ></div>
      </div>
    </div>
    <!-- END myzooblock -->
    <!-- BEGIN zooblock -->
    <div
      id="zkp_playmat_container:{PLAYER_ID}"
      class="zkp_playmat_container:{PLAYER_ID} zkp_playmat_container whiteblock"
    >
      <h3 id="zkp_zoo_title:{PLAYER_ID}" style="color: #{PLAYER_COLOR}">
        {PLAYER_NAME}
      </h3>
      <div id="zkp_playmat:{PLAYER_ID}" class="zkp_playmat">
        <button
          id="zkp_expand_house_{PLAYER_ID}:1"
          class="zkp_expand_house zkp_expand_house_1"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:2"
          class="zkp_expand_house zkp_expand_house_2"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:3"
          class="zkp_expand_house zkp_expand_house_3"
        ></button>
        <button
          id="zkp_expand_house_{PLAYER_ID}:4"
          class="zkp_expand_house zkp_expand_house_4"
        ></button>
        <div id="zkp_houses_{PLAYER_ID}" class="zkp_houses">
          <div
            id="zkp_keeper_{PLAYER_ID}:1"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:2"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:3"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
          <div
            id="zkp_keeper_{PLAYER_ID}:4"
            class="zkp_house zkp_keeper_{PLAYER_ID}"
          ></div>
        </div>
        <div id="zkp_playmat_counters_{PLAYER_ID}" class="zkp_playmat_counters">
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:0"
            class="zkp_playmat_counter_0 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:1"
            class="zkp_playmat_counter_1 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:2"
            class="zkp_playmat_counter_2 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:3"
            class="zkp_playmat_counter_3 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:4"
            class="zkp_playmat_counter_4 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:5"
            class="zkp_playmat_counter_5 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:6"
            class="zkp_playmat_counter_6 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:7"
            class="zkp_playmat_counter_7 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:8"
            class="zkp_playmat_counter_8 zkp_playmat_counter"
          ></div>
          <div
            id="zkp_playmat_counter_{PLAYER_ID}:9"
            class="zkp_playmat_counter_9 zkp_playmat_counter"
          ></div>
        </div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:ALL"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_ALL"
          data-quarantine="ALL"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:TEM"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_TEM"
          data-quarantine="TEM"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:SAV"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_SAV"
          data-quarantine="SAV"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:PRA"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_PRA"
          data-quarantine="PRA"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:DES"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_DES"
          data-quarantine="DES"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:AQU"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_AQU"
          data-quarantine="AQU"
        ></div>
        <div
          id="zkp_quarantine_{PLAYER_ID}:TRO"
          class="zkp_quarantine zkp_quarantine_{PLAYER_ID} zkp_quarantine_TRO"
          data-quarantine="TRO"
        ></div>
      </div>
    </div>
    <!-- END zooblock -->
  </div>
</div>

<script type="text/javascript">
  // Javascript HTML templates

  var jstpl_player_board =
    '<div id="zkp_board" class="zkp_board zkp_column_container">\
      <div class="zkp_board_resources">\
        <div class="zkp_resource"><div id="zkp_plant_icon_${id}" class="zkp_resource_icon zkp_plant_icon"></div><span id="zkp_plant_count_${id}">0</span></div>\
        <div class="zkp_resource"><div id="zkp_meat_icon_${id}" class="zkp_resource_icon zkp_meat_icon"></div><span id="zkp_meat_count_${id}">0</span></div>\
        <div class="zkp_resource"><div id="zkp_kit_icon_${id}" class="zkp_resource_icon zkp_kit_icon"></div><span id="zkp_kit_count_${id}">0</span></div>\
        <div class="zkp_resource"><div id="zkp_species_icon_${id}" class="zkp_species_icon"></div><span id="zkp_species_count_${id}"></span></div>\
      </div>\
      <div id="zkp_objective:${id}" class="zkp_objective"></div>\
    </div>';

  var jstpl_resource_cube =
    '<div id="zkp_${type}_cube_${nbr}" class="zkp_resource_cube zkp_resource_icon zkp_${type}_icon"></div>';

  var jstpl_dismissed_keeper = '<div id="zkp_dismissed_keeper"></div>';

  var jstpl_discarded_species =
    '<div id="zkp_discarded_species_${species}"></div>';
</script>

{OVERALL_GAME_FOOTER}
