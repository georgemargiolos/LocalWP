<?php
/**
 * Equipment Icon Mapping
 * Maps equipment IDs and names to FontAwesome 7 icons (including duotones)
 * Updated: v2.4.1 - Added FontAwesome 7 duotone icons with colors
 */

function yolo_get_equipment_icon($equipment_id, $equipment_name = '') {
    // Primary mapping by ID
    $icon_map = array(
        1  => 'fa-solid fa-compass',              // Autopilot
        2  => 'fa-solid fa-volume-high',          // Cockpit speakers
        3  => 'fa-solid fa-compact-disc',         // DVD player
        4  => 'fa-solid fa-ship',                 // Dinghy
        5  => 'fa-solid fa-map-location-dot',     // Chart plotter
        6  => 'fa-solid fa-gears',                // Electric winches
        7  => 'fa-solid fa-umbrella',             // Bimini
        8  => 'fa-solid fa-bridge',               // Flybridge
        9  => 'fa-solid fa-jet-fighter-up',       // Bow thruster
        10 => 'fa-solid fa-radar',                // Radar
        11 => 'fa-solid fa-fire',                 // Heating
        12 => 'fa-solid fa-bolt',                 // Generator
        13 => 'fa-solid fa-anchor',               // Lazy jack
        14 => 'fa-solid fa-radio',                // Radio-CD player
        16 => 'fa-solid fa-shield',               // Sprayhood
        17 => 'fa-solid fa-map',                  // Chart plotter in cockpit
        18 => 'fa-solid fa-cog',                  // Outboard engine
        19 => 'fa-solid fa-tree',                 // Teak deck
        21 => 'fa-solid fa-wifi',                 // Wi-Fi & Internet
        22 => 'fa-solid fa-gamepad',              // Game console
        23 => 'fa-solid fa-person-swimming',      // Stand up paddle
        25 => 'fa-solid fa-mug-hot',              // Coffee maker
        26 => 'fa-solid fa-person-swimming',      // Diving equipment
        27 => 'fa-solid fa-toilet',               // Electric toilet
        29 => 'fa-solid fa-snowflake',            // Air condition
        30 => 'fa-solid fa-tank-water',           // Holding tank
        31 => 'fa-solid fa-stairs',               // Hydraulic gangway
        32 => 'fa-solid fa-flag',                 // Spinnaker
        33 => 'fa-solid fa-plug',                 // Inverter
        34 => 'fa-solid fa-ice-cream',            // Ice maker
        35 => 'fa-solid fa-bag-shopping',         // Lazy bag
        36 => 'fa-solid fa-border-all',           // Railing net
        37 => 'fa-solid fa-kitchen-set',          // Refrigerator
        40 => 'fa-solid fa-person-swimming',      // Snorkeling equipment
        41 => 'fa-solid fa-flag-checkered',       // Gennaker
        42 => 'fa-solid fa-tv',                   // TV
        43 => 'fa-solid fa-sailboat',             // Racing sails
        44 => 'fa-solid fa-droplet',              // Water maker
        45 => 'fa-solid fa-couch',                // Cockpit cushions
        46 => 'fa-solid fa-solar-panel',          // Solar Panels
        47 => 'fa-solid fa-tree',                 // Teak Cockpit
        48 => 'fa-solid fa-fire-burner',          // Barbecue grill in cockpit
        49 => 'fa-solid fa-steering-wheel',       // Outside Steering Position
        50 => 'fa-solid fa-person-swimming',      // Swimming platform
        51 => 'fa-solid fa-water',                // Swimming pool
        52 => 'fa-solid fa-kitchen-set',          // Dishwasher
        53 => 'fa-solid fa-shirt',                // Washer/Dryer
        54 => 'fa-solid fa-lightbulb',            // Underwater lights
        55 => 'fa-solid fa-elevator',             // Tenderlift platform
        56 => 'fa-solid fa-warehouse',            // Tender garage
    );
    
    // If ID found, return it
    if (isset($icon_map[$equipment_id])) {
        return $icon_map[$equipment_id];
    }
    
    // Fallback: Try to match by name if provided
    if (!empty($equipment_name)) {
        $name_lower = strtolower($equipment_name);
        
        // Name-based mapping for equipment not in ID map
        if (strpos($name_lower, 'outboard') !== false || strpos($name_lower, 'engine') !== false) {
            return 'fa-solid fa-cog';
        }
        if (strpos($name_lower, 'snorkel') !== false || strpos($name_lower, 'diving') !== false) {
            return 'fa-solid fa-person-swimming';
        }
        if (strpos($name_lower, 'wifi') !== false || strpos($name_lower, 'internet') !== false) {
            return 'fa-solid fa-wifi';
        }
        if (strpos($name_lower, 'toilet') !== false) {
            return 'fa-solid fa-toilet';
        }
        if (strpos($name_lower, 'refrigerator') !== false || strpos($name_lower, 'fridge') !== false) {
            return 'fa-solid fa-kitchen-set';
        }
        if (strpos($name_lower, 'air') !== false || strpos($name_lower, 'condition') !== false) {
            return 'fa-solid fa-snowflake';
        }
        if (strpos($name_lower, 'solar') !== false) {
            return 'fa-solid fa-solar-panel';
        }
        if (strpos($name_lower, 'tv') !== false || strpos($name_lower, 'television') !== false) {
            return 'fa-solid fa-tv';
        }
        if (strpos($name_lower, 'radio') !== false || strpos($name_lower, 'cd') !== false) {
            return 'fa-solid fa-radio';
        }
        if (strpos($name_lower, 'anchor') !== false) {
            return 'fa-solid fa-anchor';
        }
        if (strpos($name_lower, 'compass') !== false || strpos($name_lower, 'autopilot') !== false) {
            return 'fa-solid fa-compass';
        }
        if (strpos($name_lower, 'dinghy') !== false || strpos($name_lower, 'tender') !== false) {
            return 'fa-solid fa-ship';
        }
        if (strpos($name_lower, 'bimini') !== false) {
            return 'fa-solid fa-umbrella';
        }
        if (strpos($name_lower, 'coffee') !== false) {
            return 'fa-solid fa-mug-hot';
        }
        if (strpos($name_lower, 'paddle') !== false || strpos($name_lower, 'sup') !== false) {
            return 'fa-solid fa-person-swimming';
        }
    }
    
    // Default fallback icon
    return 'fa-solid fa-circle-check';
}
