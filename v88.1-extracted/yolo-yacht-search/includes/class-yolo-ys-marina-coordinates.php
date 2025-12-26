<?php
/**
 * Marina Coordinates Helper
 * 
 * Provides GPS coordinates for Greek Ionian marinas
 * Used for Google Maps embed on yacht details page
 * 
 * @since 86.4
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get GPS coordinates for a marina/home base
 * 
 * @param string $home_base The yacht's home base name
 * @return array|null Array with ['lat' => float, 'lng' => float] or null
 */
function yolo_ys_get_marina_coordinates($home_base) {
    if (empty($home_base)) {
        return null;
    }
    
    $base_lower = strtolower(trim($home_base));
    
    // Marina coordinates mapping for Greek Ionian
    // Format: 'search_term' => ['lat' => latitude, 'lng' => longitude, 'name' => 'Full Marina Name']
    $marina_coords = array(
        // Lefkada marinas
        'marina perigiali' => array('lat' => 38.7075, 'lng' => 20.6783, 'name' => 'Marina Perigiali, Lefkada'),
        'perigiali' => array('lat' => 38.7075, 'lng' => 20.6783, 'name' => 'Marina Perigiali, Lefkada'),
        'nydri' => array('lat' => 38.7239, 'lng' => 20.7433, 'name' => 'Nydri, Lefkada'),
        'vliho' => array('lat' => 38.7000, 'lng' => 20.7200, 'name' => 'Vliho Bay, Lefkada'),
        'lefkas marina' => array('lat' => 38.8333, 'lng' => 20.7050, 'name' => 'Lefkas Marina'),
        'lefkada marina' => array('lat' => 38.8333, 'lng' => 20.7050, 'name' => 'Lefkas Marina'),
        'vasiliki' => array('lat' => 38.6258, 'lng' => 20.5850, 'name' => 'Vasiliki, Lefkada'),
        'nikiana' => array('lat' => 38.7583, 'lng' => 20.7117, 'name' => 'Nikiana, Lefkada'),
        'lygia' => array('lat' => 38.7867, 'lng' => 20.7000, 'name' => 'Lygia, Lefkada'),
        'ligia' => array('lat' => 38.7867, 'lng' => 20.7000, 'name' => 'Lygia, Lefkada'),
        
        // Preveza area
        'preveza' => array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Preveza Marina'),
        'preveza main port' => array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Preveza Main Port'),
        'cleopatra marina' => array('lat' => 38.9600, 'lng' => 20.7600, 'name' => 'Cleopatra Marina, Preveza'),
        'cleopatra' => array('lat' => 38.9600, 'lng' => 20.7600, 'name' => 'Cleopatra Marina, Preveza'),
        
        // Corfu
        'gouvia' => array('lat' => 39.6517, 'lng' => 19.8467, 'name' => 'Gouvia Marina, Corfu'),
        'gouvia marina' => array('lat' => 39.6517, 'lng' => 19.8467, 'name' => 'Gouvia Marina, Corfu'),
        'corfu' => array('lat' => 39.6243, 'lng' => 19.9217, 'name' => 'Corfu Marina'),
        'kerkyra' => array('lat' => 39.6243, 'lng' => 19.9217, 'name' => 'Kerkyra (Corfu)'),
        
        // Kefalonia
        'argostoli' => array('lat' => 38.1750, 'lng' => 20.4883, 'name' => 'Argostoli, Kefalonia'),
        'fiskardo' => array('lat' => 38.4517, 'lng' => 20.5750, 'name' => 'Fiskardo, Kefalonia'),
        'sami' => array('lat' => 38.2667, 'lng' => 20.6450, 'name' => 'Sami, Kefalonia'),
        'agia effimia' => array('lat' => 38.3417, 'lng' => 20.5217, 'name' => 'Agia Effimia, Kefalonia'),
        
        // Zakynthos
        'zakynthos' => array('lat' => 37.7867, 'lng' => 20.8983, 'name' => 'Zakynthos Marina'),
        'zante' => array('lat' => 37.7867, 'lng' => 20.8983, 'name' => 'Zante Marina'),
        
        // Other Ionian locations
        'ithaca' => array('lat' => 38.3650, 'lng' => 20.7183, 'name' => 'Ithaca'),
        'ithaki' => array('lat' => 38.3650, 'lng' => 20.7183, 'name' => 'Ithaca'),
        'vathy' => array('lat' => 38.3650, 'lng' => 20.7183, 'name' => 'Vathy, Ithaca'),
        'paxos' => array('lat' => 39.2000, 'lng' => 20.1833, 'name' => 'Paxos'),
        'gaios' => array('lat' => 39.1950, 'lng' => 20.1817, 'name' => 'Gaios, Paxos'),
        'syvota' => array('lat' => 39.4083, 'lng' => 20.2350, 'name' => 'Syvota'),
        'sivota' => array('lat' => 39.4083, 'lng' => 20.2350, 'name' => 'Syvota'),
        'vonitsa' => array('lat' => 38.9200, 'lng' => 20.8833, 'name' => 'Vonitsa'),
        'palairos' => array('lat' => 38.7833, 'lng' => 20.8550, 'name' => 'Palairos'),
        'paleros' => array('lat' => 38.7833, 'lng' => 20.8550, 'name' => 'Palairos'),
        'vounaki' => array('lat' => 38.7900, 'lng' => 20.8600, 'name' => 'Vounaki'),
        'plataria' => array('lat' => 39.4567, 'lng' => 20.2700, 'name' => 'Plataria'),
        'astakos' => array('lat' => 38.5317, 'lng' => 21.0800, 'name' => 'Astakos'),
        'meganisi' => array('lat' => 38.6667, 'lng' => 20.7667, 'name' => 'Meganisi'),
        
        // Athens area (for some yachts)
        'athens' => array('lat' => 37.9417, 'lng' => 23.6500, 'name' => 'Athens Marina'),
        'alimos' => array('lat' => 37.9117, 'lng' => 23.7117, 'name' => 'Alimos Marina, Athens'),
        'lavrion' => array('lat' => 37.7133, 'lng' => 24.0550, 'name' => 'Lavrion, Athens'),
    );
    
    // First try exact match
    if (isset($marina_coords[$base_lower])) {
        return $marina_coords[$base_lower];
    }
    
    // Try partial match (marina name contains key)
    foreach ($marina_coords as $key => $coords) {
        if (strpos($base_lower, $key) !== false) {
            return $coords;
        }
    }
    
    // Try if key is contained in base name
    foreach ($marina_coords as $key => $coords) {
        if (strpos($key, $base_lower) !== false) {
            return $coords;
        }
    }
    
    // Default to Preveza for unknown marinas (central Ionian location)
    error_log("YOLO Marina Coords: Unknown marina '$home_base' - using Preveza fallback");
    return array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Greek Ionian');
}
