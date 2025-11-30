<?php
/**
 * Equipment Icon Mapping
 * Maps equipment IDs and names to FontAwesome 7 icons (including duotones)
 * Updated: v2.4.1 - Added FontAwesome 7 duotone icons with colors
 */

function yolo_get_equipment_icon($equipment_id, $equipment_name = '') {
    // Primary mapping by ID
    $icon_map = array(
        1  => 'fa-duotone fa-compass',              // Autopilot
        2  => 'fa-duotone fa-volume-high',          // Cockpit speakers
        3  => 'fa-duotone fa-compact-disc',         // DVD player
        4  => 'fa-duotone fa-ship',                 // Dinghy
        5  => 'fa-duotone fa-map-location-dot',     // Chart plotter
        6  => 'fa-duotone fa-gears',                // Electric winches
        7  => 'fa-duotone fa-umbrella',             // Bimini
        8  => 'fa-duotone fa-bridge',               // Flybridge
        9  => 'fa-duotone fa-jet-fighter-up',       // Bow thruster
        10 => 'fa-duotone fa-radar',                // Radar
        11 => 'fa-duotone fa-fire',                 // Heating
        12 => 'fa-duotone fa-bolt',                 // Generator
        13 => 'fa-duotone fa-anchor',               // Lazy jack
        14 => 'fa-duotone fa-radio',                // Radio-CD player
        16 => 'fa-duotone fa-shield',               // Sprayhood
        17 => 'fa-duotone fa-map',                  // Chart plotter in cockpit
        18 => 'fa-duotone fa-engine',               // Outboard engine
        19 => 'fa-duotone fa-tree',                 // Teak deck
        21 => 'fa-duotone fa-wifi',                 // Wi-Fi & Internet
        22 => 'fa-duotone fa-gamepad',              // Game console
        23 => 'fa-duotone fa-person-swimming',      // Stand up paddle
        25 => 'fa-duotone fa-mug-hot',              // Coffee maker
        26 => 'fa-duotone fa-mask-snorkel',         // Diving equipment
        27 => 'fa-duotone fa-toilet',               // Electric toilet
        29 => 'fa-duotone fa-snowflake',            // Air condition
        30 => 'fa-duotone fa-tank-water',           // Holding tank
        31 => 'fa-duotone fa-stairs',               // Hydraulic gangway
        32 => 'fa-duotone fa-flag',                 // Spinnaker
        33 => 'fa-duotone fa-plug',                 // Inverter
        34 => 'fa-duotone fa-ice-cream',            // Ice maker
        35 => 'fa-duotone fa-bag-shopping',         // Lazy bag
        36 => 'fa-duotone fa-border-all',           // Railing net
        37 => 'fa-duotone fa-kitchen-set',          // Refrigerator
        40 => 'fa-duotone fa-mask-snorkel',         // Snorkeling equipment
        41 => 'fa-duotone fa-flag-checkered',       // Gennaker
        42 => 'fa-duotone fa-tv',                   // TV
        43 => 'fa-duotone fa-sailboat',             // Racing sails
        44 => 'fa-duotone fa-droplet',              // Water maker
        45 => 'fa-duotone fa-couch',                // Cockpit cushions
        46 => 'fa-duotone fa-solar-panel',          // Solar Panels
        47 => 'fa-duotone fa-tree',                 // Teak Cockpit
        48 => 'fa-duotone fa-fire-burner',          // Barbecue grill in cockpit
        49 => 'fa-duotone fa-steering-wheel',       // Outside Steering Position
        50 => 'fa-duotone fa-person-swimming',      // Swimming platform
        51 => 'fa-duotone fa-water',                // Swimming pool
        52 => 'fa-duotone fa-kitchen-set',          // Dishwasher
        53 => 'fa-duotone fa-shirt',                // Washer/Dryer
        54 => 'fa-duotone fa-lightbulb',            // Underwater lights
        55 => 'fa-duotone fa-elevator',             // Tenderlift platform
        56 => 'fa-duotone fa-warehouse',            // Tender garage
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
            return 'fa-duotone fa-engine';
        }
        if (strpos($name_lower, 'snorkel') !== false || strpos($name_lower, 'diving') !== false) {
            return 'fa-duotone fa-mask-snorkel';
        }
        if (strpos($name_lower, 'wifi') !== false || strpos($name_lower, 'internet') !== false) {
            return 'fa-duotone fa-wifi';
        }
        if (strpos($name_lower, 'toilet') !== false) {
            return 'fa-duotone fa-toilet';
        }
        if (strpos($name_lower, 'refrigerator') !== false || strpos($name_lower, 'fridge') !== false) {
            return 'fa-duotone fa-kitchen-set';
        }
        if (strpos($name_lower, 'air') !== false || strpos($name_lower, 'condition') !== false) {
            return 'fa-duotone fa-snowflake';
        }
        if (strpos($name_lower, 'solar') !== false) {
            return 'fa-duotone fa-solar-panel';
        }
        if (strpos($name_lower, 'tv') !== false || strpos($name_lower, 'television') !== false) {
            return 'fa-duotone fa-tv';
        }
        if (strpos($name_lower, 'radio') !== false || strpos($name_lower, 'cd') !== false) {
            return 'fa-duotone fa-radio';
        }
        if (strpos($name_lower, 'anchor') !== false) {
            return 'fa-duotone fa-anchor';
        }
        if (strpos($name_lower, 'compass') !== false || strpos($name_lower, 'autopilot') !== false) {
            return 'fa-duotone fa-compass';
        }
        if (strpos($name_lower, 'dinghy') !== false || strpos($name_lower, 'tender') !== false) {
            return 'fa-duotone fa-ship';
        }
        if (strpos($name_lower, 'bimini') !== false) {
            return 'fa-duotone fa-umbrella';
        }
        if (strpos($name_lower, 'coffee') !== false) {
            return 'fa-duotone fa-mug-hot';
        }
        if (strpos($name_lower, 'paddle') !== false || strpos($name_lower, 'sup') !== false) {
            return 'fa-duotone fa-person-swimming';
        }
    }
    
    // Default fallback icon
    return 'fa-duotone fa-circle-check';
}
