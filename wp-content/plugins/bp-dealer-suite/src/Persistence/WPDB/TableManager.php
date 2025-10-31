<?php
namespace BP\DealerSuite\Persistence\WPDB;

class TableManager {
    public function install(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $tables = [];

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_dealer_levels (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(60) NOT NULL,
            name varchar(191) NOT NULL,
            min_spend decimal(18,4) NOT NULL DEFAULT 0,
            min_orders bigint(20) unsigned NOT NULL DEFAULT 0,
            discount_rate decimal(6,4) NOT NULL DEFAULT 0,
            active tinyint(1) NOT NULL DEFAULT 1,
            priority int NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY code (code),
            PRIMARY KEY (id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_bonus_tiers (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            min_deposit decimal(18,4) NOT NULL DEFAULT 0,
            bonus_rate decimal(6,4) NOT NULL DEFAULT 0,
            active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_dealer_meta (
            user_id bigint(20) unsigned NOT NULL,
            level_code varchar(60) NOT NULL,
            custom_discount_rate decimal(6,4) NULL,
            lifetime_spend decimal(18,4) NOT NULL DEFAULT 0,
            lifetime_orders bigint(20) unsigned NOT NULL DEFAULT 0,
            wallet_balance_snapshot decimal(18,4) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_commissions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            product_id bigint(20) unsigned NOT NULL,
            supplier_cost decimal(18,4) NULL,
            commission_rate decimal(6,4) NULL,
            min_margin_rate decimal(6,4) NULL,
            last_calculated_sale_price decimal(18,4) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY product_id (product_id)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_transactions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            type varchar(40) NOT NULL,
            amount decimal(18,4) NOT NULL,
            meta longtext NULL,
            status varchar(40) NOT NULL DEFAULT 'completed',
            reference varchar(191) NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            UNIQUE KEY reference (reference)
        ) $charset_collate;";

        $tables[] = "CREATE TABLE {$wpdb->prefix}bp_stats_cache (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            period_start datetime NOT NULL,
            period_type varchar(20) NOT NULL,
            user_id bigint(20) unsigned NULL,
            orders bigint(20) unsigned NOT NULL DEFAULT 0,
            revenue decimal(18,4) NOT NULL DEFAULT 0,
            discount_total decimal(18,4) NOT NULL DEFAULT 0,
            bonus_total decimal(18,4) NOT NULL DEFAULT 0,
            gross_margin decimal(18,4) NOT NULL DEFAULT 0,
            json_blob longtext NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY period (period_start, period_type)
        ) $charset_collate;";

        foreach ( $tables as $sql ) {
            dbDelta( $sql );
        }

        $this->seed();
    }

    private function seed(): void {
        global $wpdb;

        $levels = [
            [ 'code' => 'BRONZE', 'name' => 'Bronze', 'min_spend' => 0, 'min_orders' => 0, 'discount_rate' => 0.05, 'active' => 1, 'priority' => 1 ],
            [ 'code' => 'SILVER', 'name' => 'Silver', 'min_spend' => 10000, 'min_orders' => 20, 'discount_rate' => 0.07, 'active' => 1, 'priority' => 5 ],
            [ 'code' => 'GOLD', 'name' => 'Gold', 'min_spend' => 30000, 'min_orders' => 60, 'discount_rate' => 0.10, 'active' => 1, 'priority' => 10 ],
        ];

        foreach ( $levels as $level ) {
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT code FROM {$wpdb->prefix}bp_dealer_levels WHERE code = %s", $level['code'] ) );

            if ( $exists ) {
                continue;
            }

            $wpdb->insert( $wpdb->prefix . 'bp_dealer_levels', $level, [ '%s', '%s', '%f', '%d', '%f', '%d', '%d' ] );
        }

        $tiers = [
            [ 'min_deposit' => 1000, 'bonus_rate' => 0.05, 'active' => 1 ],
            [ 'min_deposit' => 5000, 'bonus_rate' => 0.10, 'active' => 1 ],
            [ 'min_deposit' => 10000, 'bonus_rate' => 0.125, 'active' => 1 ],
        ];

        foreach ( $tiers as $tier ) {
            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}bp_bonus_tiers WHERE min_deposit = %f AND bonus_rate = %f", $tier['min_deposit'], $tier['bonus_rate'] ) );

            if ( $exists ) {
                continue;
            }

            $wpdb->insert( $wpdb->prefix . 'bp_bonus_tiers', $tier, [ '%f', '%f', '%d' ] );
        }
    }
}
