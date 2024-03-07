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

<div id="zkp_common_area" class="zkp_common_area">
  <div id="zkp_bag" class="zkp_bag whiteblock">
    <h3>{BAG OF RESOURCES}</h3>
    <div id="zkp_bag_img" class="zkp_bag_img"></div>
    <div class="zkp_bag_counters">
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
        <div id="zkp_bag_kit_icon" class="zkp_resource_icon zkp_kit_icon"></div>
        <span id="zkp_bag_counter_kit">0</span>
      </div>
    </div>
  </div>
  <div id="zkp_keeper_piles" class="zkp_keeper_piles">
    <div id="zkp_keeper_pile:1" class="zkp_keeper_pile zkp_card"></div>
    <div id="zkp_keeper_pile:2" class="zkp_keeper_pile zkp_card"></div>
    <div id="zkp_keeper_pile:3" class="zkp_keeper_pile zkp_card"></div>
    <div id="zkp_keeper_pile:4" class="zkp_keeper_pile zkp_card"></div>
  </div>
  <div id="zkp_species_deck" class="zkp_species_deck zkp_card"></div>
  <div id="zkp_species_shop" class="zkp_species_shop">
    <div id="zkp_backup_shop" class="zkp_backup_shop">
      <div id="zkp_backup_column_1" class="zkp_backup_column">
        <div
          id="zkp_backup_species_1"
          class="zkp_backup_species zkp_card"
        ></div>
        <div
          id="zkp_backup_species_1"
          class="zkp_backup_species zkp_card"
        ></div>
      </div>
      <div id="zkp_backup_column_2" class="zkp_backup_column">
        <div
          id="zkp_backup_species_2"
          class="zkp_backup_species zkp_card"
        ></div>
        <div
          id="zkp_backup_species_2"
          class="zkp_backup_species zkp_card"
        ></div>
      </div>
      <div id="zkp_backup_column_3" class="zkp_backup_column">
        <div
          id="zkp_backup_species_3"
          class="zkp_backup_species zkp_card"
        ></div>
        <div
          id="zkp_backup_species_3"
          class="zkp_backup_species zkp_card"
        ></div>
      </div>
      <div id="zkp_backup_column_4" class="zkp_backup_column">
        <div
          id="zkp_backup_species_4"
          class="zkp_backup_species zkp_card"
        ></div>
        <div
          id="zkp_backup_species_4"
          class="zkp_backup_species zkp_card"
        ></div>
      </div>
    </div>
    <div id="zkp_visible_shop" class="zkp_visible_shop">
      <div id="zkp_visible_species_1"></div>
      <div id="zkp_visible_species_2"></div>
      <div id="zkp_visible_species_3"></div>
      <div id="zkp_visible_species_4"></div>
    </div>
  </div>
</div>

<div id="zkp_playmats" class="zkp_playmats">
  <!-- BEGIN playmatblock -->
  <div class="zkp_playmat_container whiteblock">
    <h3 style="color: #{PLAYER_COLOR}">{PLAYER_NAME}</h3>
    <div id="zkp_playmat_{PLAYER_ID}" class="zkp_playmat">
      <div id="zkp_keepers_{PLAYER_ID}" class="zkp_keepers">
        <div id="zkp_keeper_1_{PLAYER_ID}" class="zkp_keeper_1"></div>
        <div id="zkp_keeper_2_{PLAYER_ID}" class="zkp_keeper_2"></div>
        <div id="zkp_keeper_3_{PLAYER_ID}" class="zkp_keeper_3"></div>
        <div id="zkp_keeper_4_{PLAYER_ID}" class="zkp_keeper_4"></div>
      </div>

      <div id="zkp_quarantines_{PLAYER_ID}" class="zkp_quarantines">
        <div
          id="zkp_quarantine_TEM_{PLAYER_ID}"
          class="zkp_quarantine_TEM"
        ></div>
        <div
          id="zkp_quarantine_SAV_{PLAYER_ID}"
          class="zkp_quarantine_SAV"
        ></div>
        <div
          id="zkp_quarantine_PRA_{PLAYER_ID}"
          class="zkp_quarantine_PRA"
        ></div>
        <div
          id="zkp_quarantine_DES_{PLAYER_ID}"
          class="zkp_quarantine_DES"
        ></div>
        <div
          id="zkp_quarantine_AQU_{PLAYER_ID}"
          class="zkp_quarantine_AQU"
        ></div>
        <div
          id="zkp_quarantine_TRO_{PLAYER_ID}"
          class="zkp_quarantine_TRO"
        ></div>
        <div
          id="zkp_quarantine_ALL_{PLAYER_ID}"
          class="zkp_quarantine_ALL"
        ></div>
      </div>
    </div>
  </div>
  <!-- END playmatblock -->
</div>

<script type="text/javascript">
  // Javascript HTML templates

  var jstpl_player_board =
    '<div class="zkp_board">\
    <div class="zkp_resource"><div id="plant_icon_p${id}" class="zkp_resource_icon zkp_plant_icon"></div><span id="plant_count_p${id}">0</span></div>\
    <div class="zkp_resource"><div id="meat_icon_p${id}" class="zkp_resource_icon zkp_meat_icon"></div><span id="meat_count_p${id}">0</span></div>\
    <div class="zkp_resource"><div id="kit_icon_p${id}" class="zkp_resource_icon zkp_kit_icon"></div><span id="kit_count_p${id}">0</span></div>\
</div>';

  var jstpl_resource_cube =
    '<div id="zkp_${type}_cube_${nbr}" class="zkp_resource_cube zkp_resource_icon zkp_${type}_icon"></div>';
</script>

{OVERALL_GAME_FOOTER}
