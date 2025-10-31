<?php
namespace BP\DealerSuite\Persistence\WPDB;

use BP\DealerSuite\Domain\Entity\DealerLevel;
use wpdb;

class DealerLevelRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * @return DealerLevel[]
     */
    public function allActive(): array {
        $results = $this->db->get_results( "SELECT * FROM {$this->db->prefix}bp_dealer_levels WHERE active = 1" );

        return array_map( [ $this, 'mapRow' ], $results );
    }

    public function findByCode( string $code ): ?DealerLevel {
        $row = $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->db->prefix}bp_dealer_levels WHERE code = %s", $code ) );

        if ( ! $row ) {
            return null;
        }

        return $this->mapRow( $row );
    }

    /**
     * @param object $row
     */
    private function mapRow( $row ): DealerLevel {
        return new DealerLevel(
            (int) $row->id,
            (string) $row->code,
            (string) $row->name,
            (float) $row->min_spend,
            (int) $row->min_orders,
            (float) $row->discount_rate,
            (bool) $row->active,
            (int) $row->priority
        );
    }
}
