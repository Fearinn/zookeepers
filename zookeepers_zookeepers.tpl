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
      <h3 id="zkp_bag_title" class="zkp_bag_title">Resources</h3>
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

  <div id="zkp_playmats" class="zkp_playmats"></div>
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
