<?php
namespace BP\DealerSuite\Infrastructure\Admin;

use BP\DealerSuite\Persistence\WPDB\DealerProfileRepository;
use BP\DealerSuite\Persistence\WPDB\DealerLevelRepository;
use BP\DealerSuite\Integration\TerraWallet\TerraWalletAdapter;

class MyAccountEndpoint {
    public function __construct( private DealerProfileRepository $profiles, private DealerLevelRepository $levels, private TerraWalletAdapter $wallet ) {}

    public function register(): void {
        add_filter( 'woocommerce_account_menu_items', [ $this, 'addMenuItem' ] );
        add_action( 'woocommerce_account_bp-dealer-suite_endpoint', [ $this, 'renderEndpoint' ] );
        add_rewrite_endpoint( 'bp-dealer-suite', EP_ROOT | EP_PAGES );
    }

    public function addMenuItem( array $items ): array {
        $items['bp-dealer-suite'] = __( 'Bayi Ayarları', 'bp-dealer-suite' );
        return $items;
    }

    public function renderEndpoint(): void {
        $userId  = get_current_user_id();
        $profile = $this->profiles->findByUserId( $userId );

        if ( ! $profile ) {
            echo '<p>' . esc_html__( 'Bayi profili bulunamadı.', 'bp-dealer-suite' ) . '</p>';
            return;
        }

        $level = $this->levels->findByCode( $profile->levelCode() );

        echo '<div class="bp-dealer-profile">';
        echo '<h3>' . esc_html__( 'Bayi Profili', 'bp-dealer-suite' ) . '</h3>';

        if ( $level ) {
            echo '<p><strong>' . esc_html__( 'Seviye', 'bp-dealer-suite' ) . ':</strong> ' . esc_html( $level->name() ) . '</p>';
        }

        echo '<p><strong>' . esc_html__( 'Toplam Harcama', 'bp-dealer-suite' ) . ':</strong> ' . esc_html( wc_price( $profile->lifetimeSpend() ) ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Toplam Sipariş', 'bp-dealer-suite' ) . ':</strong> ' . esc_html( $profile->lifetimeOrders() ) . '</p>';
        echo '<p><strong>' . esc_html__( 'Cüzdan Bakiyesi', 'bp-dealer-suite' ) . ':</strong> ' . esc_html( wc_price( $this->wallet->getBalance( $userId ) ) ) . '</p>';
        echo '</div>';
    }
}
