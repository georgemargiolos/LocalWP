<?php
/**
 * Marina Coordinates Helper
 * 
 * Provides GPS coordinates for Greek Ionian marinas
 * Used for Google Maps embed on yacht details page
 * 
 * @since 86.4
 * @updated 91.25 - Expanded marina list with 60+ locations
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get GPS coordinates for a marina/home base
 * 
 * @param string $home_base The yacht's home base name
 * @return array|null Array with ['lat' => float, 'lng' => float, 'name' => string] or null if unknown
 */
function yolo_ys_get_marina_coordinates($home_base) {
    if (empty($home_base)) {
        return null;
    }
    
    $base_lower = strtolower(trim($home_base));
    
    // Marina coordinates mapping for Greek Ionian and surrounding areas
    // Format: 'search_term' => ['lat' => latitude, 'lng' => longitude, 'name' => 'Full Marina Name']
    // v91.25: Expanded list with 60+ marinas from verified GPS sources
    $marina_coords = array(
        // ============================================
        // LEFKADA ISLAND
        // ============================================
        
        // D-Marin Lefkas (main charter base)
        'd-marin' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        'd-marin lefkas' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        'lefkas marina' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        'lefkada marina' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        'lefkas' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        'lefkada' => array('lat' => 38.8300, 'lng' => 20.7125, 'name' => 'D-Marin Lefkas'),
        
        // Marina Perigiali (south of Lefkas town)
        'marina perigiali' => array('lat' => 38.7075, 'lng' => 20.6783, 'name' => 'Marina Perigiali, Lefkada'),
        'perigiali' => array('lat' => 38.7075, 'lng' => 20.6783, 'name' => 'Marina Perigiali, Lefkada'),
        
        // Nydri (popular charter base)
        'nydri' => array('lat' => 38.7043, 'lng' => 20.7113, 'name' => 'Nydri, Lefkada'),
        'nidri' => array('lat' => 38.7043, 'lng' => 20.7113, 'name' => 'Nydri, Lefkada'),
        
        // Vliho Bay
        'vliho' => array('lat' => 38.7000, 'lng' => 20.7200, 'name' => 'Vliho Bay, Lefkada'),
        'vlychos' => array('lat' => 38.7000, 'lng' => 20.7200, 'name' => 'Vliho Bay, Lefkada'),
        
        // Vasiliki
        'vasiliki' => array('lat' => 38.6258, 'lng' => 20.6038, 'name' => 'Vasiliki, Lefkada'),
        'vassiliki' => array('lat' => 38.6258, 'lng' => 20.6038, 'name' => 'Vasiliki, Lefkada'),
        
        // Nikiana
        'nikiana' => array('lat' => 38.7607, 'lng' => 20.7212, 'name' => 'Nikiana, Lefkada'),
        
        // Lygia
        'lygia' => array('lat' => 38.7867, 'lng' => 20.7000, 'name' => 'Lygia, Lefkada'),
        'ligia' => array('lat' => 38.7867, 'lng' => 20.7000, 'name' => 'Lygia, Lefkada'),
        
        // Sivota (Lefkada)
        'sivota' => array('lat' => 38.6233, 'lng' => 20.6839, 'name' => 'Sivota, Lefkada'),
        'sivota lefkada' => array('lat' => 38.6233, 'lng' => 20.6839, 'name' => 'Sivota, Lefkada'),
        
        // ============================================
        // PREVEZA AREA
        // ============================================
        
        // Preveza Main Port
        'preveza' => array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Preveza Marina'),
        'preveza main port' => array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Preveza Main Port'),
        'preveza marina' => array('lat' => 38.9500, 'lng' => 20.7517, 'name' => 'Preveza Marina'),
        
        // Cleopatra Marina (Aktio)
        'cleopatra marina' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Cleopatra Marina, Preveza'),
        'cleopatra' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Cleopatra Marina, Preveza'),
        'kleopatra' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Cleopatra Marina, Preveza'),
        
        // Aktio Marina
        'aktio' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Aktio Marina, Preveza'),
        'aktio marina' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Aktio Marina, Preveza'),
        'aktion' => array('lat' => 38.9508, 'lng' => 20.7640, 'name' => 'Aktio Marina, Preveza'),
        
        // ============================================
        // CORFU
        // ============================================
        
        'gouvia' => array('lat' => 39.6502, 'lng' => 19.8518, 'name' => 'Gouvia Marina, Corfu'),
        'gouvia marina' => array('lat' => 39.6502, 'lng' => 19.8518, 'name' => 'Gouvia Marina, Corfu'),
        'corfu' => array('lat' => 39.6243, 'lng' => 19.9217, 'name' => 'Corfu Marina'),
        'kerkyra' => array('lat' => 39.6243, 'lng' => 19.9217, 'name' => 'Kerkyra (Corfu)'),
        'corfu town' => array('lat' => 39.6243, 'lng' => 19.9217, 'name' => 'Corfu Town Marina'),
        
        // ============================================
        // KEFALONIA
        // ============================================
        
        'argostoli' => array('lat' => 38.1786, 'lng' => 20.4937, 'name' => 'Argostoli, Kefalonia'),
        'argostolion' => array('lat' => 38.1786, 'lng' => 20.4937, 'name' => 'Argostoli, Kefalonia'),
        
        'fiskardo' => array('lat' => 38.4608, 'lng' => 20.5787, 'name' => 'Fiskardo, Kefalonia'),
        'fiscardo' => array('lat' => 38.4608, 'lng' => 20.5787, 'name' => 'Fiskardo, Kefalonia'),
        
        'sami' => array('lat' => 38.2523, 'lng' => 20.6459, 'name' => 'Sami, Kefalonia'),
        'same' => array('lat' => 38.2523, 'lng' => 20.6459, 'name' => 'Sami, Kefalonia'),
        
        'agia effimia' => array('lat' => 38.3017, 'lng' => 20.6013, 'name' => 'Agia Effimia, Kefalonia'),
        'agia efimia' => array('lat' => 38.3017, 'lng' => 20.6013, 'name' => 'Agia Effimia, Kefalonia'),
        'ag. effimia' => array('lat' => 38.3017, 'lng' => 20.6013, 'name' => 'Agia Effimia, Kefalonia'),
        
        'lixouri' => array('lat' => 38.2038, 'lng' => 20.4414, 'name' => 'Lixouri, Kefalonia'),
        'lixuri' => array('lat' => 38.2038, 'lng' => 20.4414, 'name' => 'Lixouri, Kefalonia'),
        
        'poros' => array('lat' => 38.1491, 'lng' => 20.7797, 'name' => 'Poros, Kefalonia'),
        'poros kefalonia' => array('lat' => 38.1491, 'lng' => 20.7797, 'name' => 'Poros, Kefalonia'),
        
        'assos' => array('lat' => 38.3790, 'lng' => 20.5382, 'name' => 'Assos, Kefalonia'),
        
        // ============================================
        // ZAKYNTHOS
        // ============================================
        
        'zakynthos' => array('lat' => 37.7867, 'lng' => 20.8983, 'name' => 'Zakynthos Marina'),
        'zante' => array('lat' => 37.7867, 'lng' => 20.8983, 'name' => 'Zante Marina'),
        'zakynthos town' => array('lat' => 37.7867, 'lng' => 20.8983, 'name' => 'Zakynthos Town'),
        
        // ============================================
        // ITHACA
        // ============================================
        
        'ithaca' => array('lat' => 38.3665, 'lng' => 20.7188, 'name' => 'Vathy, Ithaca'),
        'ithaki' => array('lat' => 38.3665, 'lng' => 20.7188, 'name' => 'Vathy, Ithaca'),
        'vathy' => array('lat' => 38.3665, 'lng' => 20.7188, 'name' => 'Vathy, Ithaca'),
        'vathy ithaca' => array('lat' => 38.3665, 'lng' => 20.7188, 'name' => 'Vathy, Ithaca'),
        
        'kioni' => array('lat' => 38.4478, 'lng' => 20.6930, 'name' => 'Kioni, Ithaca'),
        
        'frikes' => array('lat' => 38.4582, 'lng' => 20.6639, 'name' => 'Frikes, Ithaca'),
        
        // ============================================
        // MEGANISI
        // ============================================
        
        'meganisi' => array('lat' => 38.6681, 'lng' => 20.7783, 'name' => 'Vathy, Meganisi'),
        'vathy meganisi' => array('lat' => 38.6681, 'lng' => 20.7783, 'name' => 'Vathy, Meganisi'),
        'spartochori' => array('lat' => 38.6609, 'lng' => 20.7614, 'name' => 'Spartochori, Meganisi'),
        'spilia' => array('lat' => 38.6609, 'lng' => 20.7614, 'name' => 'Spartochori, Meganisi'),
        
        // ============================================
        // PAXOS / ANTIPAXOS
        // ============================================
        
        'paxos' => array('lat' => 39.2000, 'lng' => 20.1833, 'name' => 'Paxos'),
        'paxoi' => array('lat' => 39.2000, 'lng' => 20.1833, 'name' => 'Paxos'),
        'gaios' => array('lat' => 39.1950, 'lng' => 20.1817, 'name' => 'Gaios, Paxos'),
        'lakka' => array('lat' => 39.2333, 'lng' => 20.1333, 'name' => 'Lakka, Paxos'),
        'loggos' => array('lat' => 39.2167, 'lng' => 20.1667, 'name' => 'Loggos, Paxos'),
        
        // ============================================
        // MAINLAND GREECE (IONIAN COAST)
        // ============================================
        
        // Syvota (mainland - different from Sivota Lefkada)
        'syvota' => array('lat' => 39.4083, 'lng' => 20.2350, 'name' => 'Syvota, Mainland'),
        'syvota mainland' => array('lat' => 39.4083, 'lng' => 20.2350, 'name' => 'Syvota, Mainland'),
        
        'vonitsa' => array('lat' => 38.9200, 'lng' => 20.8833, 'name' => 'Vonitsa'),
        
        'palairos' => array('lat' => 38.7833, 'lng' => 20.8550, 'name' => 'Palairos'),
        'paleros' => array('lat' => 38.7833, 'lng' => 20.8550, 'name' => 'Palairos'),
        
        'vounaki' => array('lat' => 38.7800, 'lng' => 20.8750, 'name' => 'Vounaki Marina'),
        'vounaki marina' => array('lat' => 38.7800, 'lng' => 20.8750, 'name' => 'Vounaki Marina'),
        
        'plataria' => array('lat' => 39.4567, 'lng' => 20.2700, 'name' => 'Plataria'),
        
        'astakos' => array('lat' => 38.5317, 'lng' => 21.0800, 'name' => 'Astakos'),
        
        'mytikas' => array('lat' => 38.6650, 'lng' => 20.9447, 'name' => 'Mytikas'),
        'mytika' => array('lat' => 38.6650, 'lng' => 20.9447, 'name' => 'Mytikas'),
        
        // ============================================
        // SMALLER ISLANDS
        // ============================================
        
        'kalamos' => array('lat' => 38.6192, 'lng' => 20.9433, 'name' => 'Kalamos'),
        'kastos' => array('lat' => 38.5683, 'lng' => 20.9233, 'name' => 'Kastos'),
        'atokos' => array('lat' => 38.4750, 'lng' => 20.8167, 'name' => 'Atokos'),
        
        // ============================================
        // ATHENS AREA
        // ============================================
        
        'athens' => array('lat' => 37.9417, 'lng' => 23.6500, 'name' => 'Athens Marina'),
        'alimos' => array('lat' => 37.9117, 'lng' => 23.7117, 'name' => 'Alimos Marina, Athens'),
        'alimos marina' => array('lat' => 37.9117, 'lng' => 23.7117, 'name' => 'Alimos Marina, Athens'),
        'lavrion' => array('lat' => 37.7133, 'lng' => 24.0550, 'name' => 'Lavrion, Athens'),
        'lavrio' => array('lat' => 37.7133, 'lng' => 24.0550, 'name' => 'Lavrion, Athens'),
        
        // ============================================
        // SARONIC GULF
        // ============================================
        
        'aegina' => array('lat' => 37.7467, 'lng' => 23.4267, 'name' => 'Aegina'),
        'poros' => array('lat' => 37.5083, 'lng' => 23.4583, 'name' => 'Poros'),
        'hydra' => array('lat' => 37.3483, 'lng' => 23.4633, 'name' => 'Hydra'),
        'spetses' => array('lat' => 37.2617, 'lng' => 23.1550, 'name' => 'Spetses'),
        
        // ============================================
        // CYCLADES (common charter destinations)
        // ============================================
        
        'mykonos' => array('lat' => 37.4467, 'lng' => 25.3283, 'name' => 'Mykonos'),
        'santorini' => array('lat' => 36.3933, 'lng' => 25.4617, 'name' => 'Santorini'),
        'paros' => array('lat' => 37.0867, 'lng' => 25.1517, 'name' => 'Paros'),
        'naxos' => array('lat' => 37.1067, 'lng' => 25.3767, 'name' => 'Naxos'),
        'syros' => array('lat' => 37.4433, 'lng' => 24.9417, 'name' => 'Syros'),
        
        // ============================================
        // DODECANESE
        // ============================================
        
        'rhodes' => array('lat' => 36.4500, 'lng' => 28.2267, 'name' => 'Rhodes'),
        'kos' => array('lat' => 36.8933, 'lng' => 26.9867, 'name' => 'Kos'),
        
        // ============================================
        // SPORADES
        // ============================================
        
        'skiathos' => array('lat' => 39.1617, 'lng' => 23.4900, 'name' => 'Skiathos'),
        'skopelos' => array('lat' => 39.1217, 'lng' => 23.7267, 'name' => 'Skopelos'),
        'alonissos' => array('lat' => 39.1500, 'lng' => 23.8667, 'name' => 'Alonissos'),
        
        // ============================================
        // HALKIDIKI
        // ============================================
        
        'porto carras' => array('lat' => 40.0617, 'lng' => 23.7833, 'name' => 'Porto Carras Marina'),
        'sani' => array('lat' => 40.0917, 'lng' => 23.3083, 'name' => 'Sani Marina'),
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
    
    // v91.25: Return null for unknown marinas instead of fallback
    // This allows the map template to use text search as fallback
    error_log("YOLO Marina Coords: Unknown marina '$home_base' - no coordinates available");
    return null;
}
