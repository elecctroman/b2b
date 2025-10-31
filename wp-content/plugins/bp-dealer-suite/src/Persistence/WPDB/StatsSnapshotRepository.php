<?php
namespace BP\DealerSuite\Persistence\WPDB;

use BP\DealerSuite\Domain\Entity\StatsSnapshot;
use wpdb;

class StatsSnapshotRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function upsert( StatsSnapshot $snapshot ): void {
        $this->db->replace(
            $this->db->prefix . 'bp_stats_cache',
            [
                'period_start'  => $snapshot->periodStart(),
                'period_type'   => $snapshot->periodType(),
                'user_id'       => $snapshot->userId(),
                'orders'        => $snapshot->orders(),
                'revenue'       => $snapshot->revenue(),
                'discount_total'=> $snapshot->discountTotal(),
                'bonus_total'   => $snapshot->bonusTotal(),
                'gross_margin'  => $snapshot->grossMargin(),
                'json_blob'     => wp_json_encode( $snapshot->payload() ),
            ]
        );

        /**
         * Action fired when stats snapshot is built.
         */
        do_action( 'bp_dealer_stats_snapshot_built', $snapshot );
    }
}
