<?php
namespace BP\DealerSuite\Persistence\WPDB;

use BP\DealerSuite\Domain\Entity\BonusTier;
use wpdb;

class BonusTierRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * @return BonusTier[]
     */
    public function allActive(): array {
        $results = $this->db->get_results( "SELECT * FROM {$this->db->prefix}bp_bonus_tiers WHERE active = 1 ORDER BY min_deposit ASC" );

        return array_map( [ $this, 'mapRow' ], $results );
    }

    public function matchDeposit( float $amount ): ?BonusTier {
        $row = $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->db->prefix}bp_bonus_tiers WHERE active = 1 AND min_deposit <= %f ORDER BY min_deposit DESC LIMIT 1", $amount ) );

        if ( ! $row ) {
            return null;
        }

        return $this->mapRow( $row );
    }

    private function mapRow( $row ): BonusTier {
        return new BonusTier(
            (int) $row->id,
            (float) $row->min_deposit,
            (float) $row->bonus_rate,
            (bool) $row->active
        );
    }
}
