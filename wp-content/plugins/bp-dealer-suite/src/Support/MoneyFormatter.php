<?php
namespace BP\DealerSuite\Support;

class MoneyFormatter {
    public function format( float $amount, ?int $decimals = null ): string {
        $currency         = get_woocommerce_currency();
        $decimals         = $decimals ?? wc_get_price_decimals();
        $decimal_separator = wc_get_price_decimal_separator();
        $thousand_separator = wc_get_price_thousand_separator();

        return wc_price( $amount, [
            'currency'           => $currency,
            'decimal_separator'  => $decimal_separator,
            'thousand_separator' => $thousand_separator,
            'decimals'           => $decimals,
        ] );
    }
}
