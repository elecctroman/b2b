<?php
namespace BP\DealerSuite\Support;

class OptionRepository {
    private string $option_key = 'bp_dealer_suite_options';

    /**
     * @return array<string, mixed>
     */
    public function all(): array {
        $defaults = $this->defaults();
        $stored   = get_option( $this->option_key, [] );

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        return wp_parse_args( $stored, $defaults );
    }

    /**
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function get( string $key, $default = null ) {
        $options = $this->all();
        return $options[ $key ] ?? $default;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function update( array $values ): void {
        $options = $this->all();
        update_option( $this->option_key, array_merge( $options, $values ) );
    }

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array {
        $currency           = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'TRY';
        $currencyPosition   = get_option( 'woocommerce_currency_pos', 'left' );
        $thousandSeparator  = function_exists( 'wc_get_price_thousand_separator' ) ? wc_get_price_thousand_separator() : ',';
        $decimalSeparator   = function_exists( 'wc_get_price_decimal_separator' ) ? wc_get_price_decimal_separator() : '.';
        $decimals           = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;

        return [
            'default_commission_rate'      => 0.50,
            'min_margin_rate'              => 0.10,
            'loss_protection_mode'         => 'auto_adjust_discount',
            'currency'                     => $currency,
            'currency_position'            => $currencyPosition,
            'thousand_separator'           => $thousandSeparator,
            'decimal_separator'            => $decimalSeparator,
            'decimals'                     => $decimals,
            'supplier_commission_rate'     => 0.20,
            'loss_protection_enabled'      => true,
            'terra_wallet_endpoint'        => '',
            'terra_wallet_auth_key'        => '',
            'api_rate_limit'               => 100,
            'api_ip_whitelist'             => [],
            'stats_schedule'               => 'hourly',
            'stats_cache_ttl'              => HOUR_IN_SECONDS,
            'log_level'                    => 'info',
            'cron_interval'                => 'hourly',
            'enable_auto_reindex'          => true,
        ];
    }
}
