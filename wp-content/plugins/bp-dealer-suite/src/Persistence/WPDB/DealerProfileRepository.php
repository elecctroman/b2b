<?php
namespace BP\DealerSuite\Persistence\WPDB;

use BP\DealerSuite\Domain\Entity\DealerProfile;
use wpdb;

class DealerProfileRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function findByUserId( int $userId ): ?DealerProfile {
        $row = $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->db->prefix}bp_dealer_meta WHERE user_id = %d", $userId ) );

        if ( ! $row ) {
            return null;
        }

        return $this->mapRow( $row );
    }

    public function updateLevel( int $userId, string $code ): void {
        $this->db->replace(
            $this->db->prefix . 'bp_dealer_meta',
            [
                'user_id'                 => $userId,
                'level_code'              => $code,
                'updated_at'              => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s' ]
        );
    }

    public function updateStats( int $userId, float $spend, int $orders ): void {
        $this->db->query( $this->db->prepare( "UPDATE {$this->db->prefix}bp_dealer_meta SET lifetime_spend = lifetime_spend + %f, lifetime_orders = lifetime_orders + %d WHERE user_id = %d", $spend, $orders, $userId ) );
    }

    /**
     * @param object $row
     */
    private function mapRow( $row ): DealerProfile {
        return new DealerProfile(
            (int) $row->user_id,
            (string) $row->level_code,
            null !== $row->custom_discount_rate ? (float) $row->custom_discount_rate : null,
            (float) $row->lifetime_spend,
            (int) $row->lifetime_orders,
            (float) $row->wallet_balance_snapshot
        );
    }
}
