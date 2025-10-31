<?php
namespace BP\DealerSuite\Infrastructure\Settings;

use BP\DealerSuite\Infrastructure\Loader\ServiceContainer;
use BP\DealerSuite\Support\OptionRepository;

class SettingsRegistrar {
    public function __construct( private ServiceContainer $container ) {
    }

    public function register(): void {
        add_filter( 'woocommerce_settings_tabs_array', [ $this, 'registerTab' ], 60 );
        add_action( 'woocommerce_settings_tabs_bp_dealer_suite', [ $this, 'renderSettings' ] );
        add_action( 'woocommerce_update_options_bp_dealer_suite', [ $this, 'saveSettings' ] );
    }

    public function registerTab( array $tabs ): array {
        $tabs['bp_dealer_suite'] = __( 'Bayi Suite', 'bp-dealer-suite' );
        return $tabs;
    }

    public function renderSettings(): void {
        woocommerce_admin_fields( $this->getSettings() );
    }

    public function saveSettings(): void {
        woocommerce_update_options( $this->getSettings() );
    }

    private function getSettings(): array {
        $settings = [
            'section_title' => [
                'name' => __( 'BusinessPlus Dealer Suite', 'bp-dealer-suite' ),
                'type' => 'title',
                'desc' => __( 'Configure dealer levels, bonus tiers and loss protection.', 'bp-dealer-suite' ),
                'id'   => 'bp_dealer_suite_section_title',
            ],
            'default_commission_rate' => [
                'name' => __( 'Varsayılan Komisyon Oranı (%)', 'bp-dealer-suite' ),
                'type' => 'number',
                'id'   => 'bp_dealer_suite_default_commission_rate',
                'css'  => 'width:80px;',
                'custom_attributes' => [ 'step' => '0.01', 'min' => '0', 'max' => '100' ],
            ],
            'min_margin_rate' => [
                'name' => __( 'Minimum Kâr Marjı (%)', 'bp-dealer-suite' ),
                'type' => 'number',
                'id'   => 'bp_dealer_suite_min_margin_rate',
                'css'  => 'width:80px;',
                'custom_attributes' => [ 'step' => '0.01', 'min' => '0', 'max' => '100' ],
            ],
            'loss_protection_mode' => [
                'name'    => __( 'Zarar Koruması Aşılırsa', 'bp-dealer-suite' ),
                'type'    => 'select',
                'id'      => 'bp_dealer_suite_loss_protection_mode',
                'options' => [
                    'auto_adjust_discount'    => __( 'İndirimi otomatik ayarla', 'bp-dealer-suite' ),
                    'block_order'             => __( 'Siparişi engelle', 'bp-dealer-suite' ),
                    'require_admin_approval'  => __( 'Yönetici onayı iste', 'bp-dealer-suite' ),
                ],
            ],
            'supplier_commission_rate' => [
                'name' => __( 'Tedarikçi Komisyon Oranı (%)', 'bp-dealer-suite' ),
                'type' => 'number',
                'id'   => 'bp_dealer_suite_supplier_commission_rate',
                'css'  => 'width:80px;',
                'custom_attributes' => [ 'step' => '0.01', 'min' => '0', 'max' => '100' ],
            ],
            'loss_protection_enabled' => [
                'name' => __( 'İndirim ve bonuslarda zarar koruması', 'bp-dealer-suite' ),
                'type' => 'checkbox',
                'id'   => 'bp_dealer_suite_loss_protection_enabled',
            ],
            'section_end' => [ 'type' => 'sectionend', 'id' => 'bp_dealer_suite_section_end' ],
        ];

        return $settings;
    }
}
