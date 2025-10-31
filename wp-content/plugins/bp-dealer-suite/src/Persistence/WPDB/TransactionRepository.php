<?php
namespace BP\DealerSuite\Persistence\WPDB;

use wpdb;

class TransactionRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function existsByReference( string $reference ): bool {
        $row = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->db->prefix}bp_transactions WHERE reference = %s", $reference ) );

        return ! empty( $row );
    }

    public function createBonus( int $userId, float $amount, array $meta ): void {
        $this->db->insert(
            $this->db->prefix . 'bp_transactions',
            [
                'user_id'   => $userId,
                'type'      => 'bonus',
                'amount'    => $amount,
                'meta'      => wp_json_encode( $meta ),
                'status'    => 'completed',
                'reference' => $meta['reference'] ?? null,
            ],
            [ '%d', '%s', '%f', '%s', '%s', '%s' ]
        );
    }
}
