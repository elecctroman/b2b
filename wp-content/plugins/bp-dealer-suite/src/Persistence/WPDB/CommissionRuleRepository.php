<?php
namespace BP\DealerSuite\Persistence\WPDB;

use BP\DealerSuite\Domain\Entity\CommissionRule;
use wpdb;

class CommissionRuleRepository {
    private wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function findByProductId( int $productId ): ?CommissionRule {
        $row = $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->db->prefix}bp_commissions WHERE product_id = %d", $productId ) );

        if ( ! $row ) {
            return null;
        }

        return $this->mapRow( $row );
    }

    public function upsert( int $productId, array $data ): void {
        $this->db->replace( $this->db->prefix . 'bp_commissions', array_merge( [
            'product_id' => $productId,
        ], $data ), [ '%d' ] );
    }

    private function mapRow( $row ): CommissionRule {
        return new CommissionRule(
            (int) $row->id,
            (int) $row->product_id,
            isset( $row->supplier_cost ) ? (float) $row->supplier_cost : null,
            isset( $row->commission_rate ) ? (float) $row->commission_rate : null,
            isset( $row->min_margin_rate ) ? (float) $row->min_margin_rate : null,
            isset( $row->last_calculated_sale_price ) ? (float) $row->last_calculated_sale_price : null
        );
    }
}
