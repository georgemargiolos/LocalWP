<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Price formatting helper class
 */
class YOLO_YS_Price_Formatter {
    
    /**
     * Format price with standard formatting (comma for thousands, dot for decimals)
     * 
     * @param float $price Price amount
     * @param string $currency Currency code (EUR, USD, GBP)
     * @param bool $show_decimals Whether to show decimal places
     * @return string Formatted price string
     */
    public static function format_price($price, $currency = 'EUR', $show_decimals = true) {
        if (empty($price) || $price <= 0) {
            return '';
        }
        
        // Standard format for all currencies: 18,681.00 (comma for thousands, dot for decimals)
        $decimals = $show_decimals ? 2 : 0;
        $formatted = number_format($price, $decimals, '.', ',');
        
        return $formatted . ' ' . $currency;
    }
    
    /**
     * Format price for display with currency symbol
     * 
     * @param float $price Price amount
     * @param string $currency Currency code
     * @param bool $show_decimals Whether to show decimal places
     * @return string Formatted price with symbol
     */
    public static function format_price_with_symbol($price, $currency = 'EUR', $show_decimals = true) {
        if (empty($price) || $price <= 0) {
            return '';
        }
        
        $symbols = array(
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£'
        );
        
        $symbol = isset($symbols[$currency]) ? $symbols[$currency] : $currency;
        $decimals = $show_decimals ? 2 : 0;
        
        // Standard format for all currencies: comma for thousands, dot for decimals
        $formatted = $symbol . number_format($price, $decimals, '.', ',');
        
        return $formatted;
    }
    
    /**
     * Calculate deposit amount based on percentage
     * 
     * @param float $total_price Total charter price
     * @param int $percentage Deposit percentage (1-100)
     * @return float Deposit amount
     */
    public static function calculate_deposit($total_price, $percentage = 50) {
        if (empty($total_price) || $total_price <= 0) {
            return 0;
        }
        
        $percentage = max(1, min(100, intval($percentage)));
        return round(($total_price * $percentage) / 100, 2);
    }
    
    /**
     * Calculate remaining balance after deposit
     * 
     * @param float $total_price Total charter price
     * @param float $deposit_paid Deposit amount paid
     * @return float Remaining balance
     */
    public static function calculate_remaining_balance($total_price, $deposit_paid) {
        return round($total_price - $deposit_paid, 2);
    }
    
    /**
     * Format price for Stripe (convert to cents)
     * 
     * @param float $price Price amount
     * @param string $currency Currency code
     * @return int Price in smallest currency unit (cents)
     */
    public static function format_for_stripe($price, $currency = 'EUR') {
        // Most currencies use 2 decimal places (cents)
        // Some currencies like JPY use 0 decimal places
        $zero_decimal_currencies = array('JPY', 'KRW', 'VND');
        
        if (in_array(strtoupper($currency), $zero_decimal_currencies)) {
            return intval($price);
        }
        
        return intval(round($price * 100));
    }
}
