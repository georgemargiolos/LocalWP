<?php
/**
 * Airport Distance Helper
 * Maps Greek Ionian marinas to nearest airports
 * 
 * @package YOLO_Yacht_Search
 * @since 81.18
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get nearest airport info for a home base
 * 
 * @param string $home_base The yacht's home base name
 * @return array|null Array with [airport_name, code, distance_km] or null
 */
function yolo_ys_get_nearest_airport($home_base) {
    if (empty($home_base)) {
        return null;
    }
    
    $base_lower = strtolower(trim($home_base));
    
    // Airport mapping: 'search_term' => ['airport_name', 'code', 'distance_km']
    $airport_map = array(
        // PREVEZA / LEFKADA AREA (PVK - Aktion Airport)
        'preveza marina' => array('Aktion Airport', 'PVK', 5),
        'preveza main port' => array('Aktion Airport', 'PVK', 7),
        'cleopatra marina' => array('Aktion Airport', 'PVK', 6),
        'd-marin marina lefkas' => array('Aktion Airport', 'PVK', 25),
        'd-marin lefkas' => array('Aktion Airport', 'PVK', 25),
        'port of lefkas' => array('Aktion Airport', 'PVK', 25),
        'lefkada' => array('Aktion Airport', 'PVK', 25),
        'nydri marina' => array('Aktion Airport', 'PVK', 40),
        'nydri port' => array('Aktion Airport', 'PVK', 40),
        'nydri' => array('Aktion Airport', 'PVK', 40),
        'vliho' => array('Aktion Airport', 'PVK', 38),
        'vasiliki' => array('Aktion Airport', 'PVK', 55),
        'sivota' => array('Aktion Airport', 'PVK', 35),
        'nikiana' => array('Aktion Airport', 'PVK', 32),
        'lygia' => array('Aktion Airport', 'PVK', 28),
        'ligia' => array('Aktion Airport', 'PVK', 28),
        'marina paleros' => array('Aktion Airport', 'PVK', 45),
        'palairos' => array('Aktion Airport', 'PVK', 45),
        'vounaki' => array('Aktion Airport', 'PVK', 45),
        'vonitsa' => array('Aktion Airport', 'PVK', 15),
        'astakos' => array('Aktion Airport', 'PVK', 60),
        'plataria' => array('Aktion Airport', 'PVK', 50),
        'mitikas' => array('Aktion Airport', 'PVK', 55),
        'mytikas' => array('Aktion Airport', 'PVK', 55),
        'perigiali' => array('Aktion Airport', 'PVK', 25),
        
        // CORFU AREA (CFU - Corfu Airport)
        'd-marin marina gouvia' => array('Corfu Airport', 'CFU', 8),
        'd-marin gouvia' => array('Corfu Airport', 'CFU', 8),
        'gouvia' => array('Corfu Airport', 'CFU', 8),
        'corfu harbor' => array('Corfu Airport', 'CFU', 3),
        'corfu' => array('Corfu Airport', 'CFU', 3),
        'mandraki' => array('Corfu Airport', 'CFU', 4),
        'benitses' => array('Corfu Airport', 'CFU', 12),
        'palaiokastritsas' => array('Corfu Airport', 'CFU', 25),
        'alipa' => array('Corfu Airport', 'CFU', 25),
        
        // PAXOS
        'paxos' => array('Corfu Airport', 'CFU', 50),
        'gaios' => array('Corfu Airport', 'CFU', 50),
        
        // KEFALONIA AREA (EFL - Kefalonia Airport)
        'argostoli' => array('Kefalonia Airport', 'EFL', 10),
        'fiskardo' => array('Kefalonia Airport', 'EFL', 50),
        'sami' => array('Kefalonia Airport', 'EFL', 25),
        'agia effimia' => array('Kefalonia Airport', 'EFL', 35),
        'agia pelagia' => array('Kefalonia Airport', 'EFL', 20),
        'lixouri' => array('Kefalonia Airport', 'EFL', 15),
        'poros' => array('Kefalonia Airport', 'EFL', 30),
        
        // ITHACA
        'ithaca' => array('Kefalonia Airport', 'EFL', 40),
        'vathy' => array('Kefalonia Airport', 'EFL', 40),
        
        // ZAKYNTHOS AREA (ZTH - Zakynthos Airport)
        'zakynthos' => array('Zakynthos Airport', 'ZTH', 5),
        'zante' => array('Zakynthos Airport', 'ZTH', 5),
        'agios sostis' => array('Zakynthos Airport', 'ZTH', 8),
    );
    
    // Try exact match first
    if (isset($airport_map[$base_lower])) {
        return $airport_map[$base_lower];
    }
    
    // Try partial match
    foreach ($airport_map as $search_term => $airport_info) {
        if (strpos($base_lower, $search_term) !== false) {
            return $airport_info;
        }
    }
    
    return null;
}
