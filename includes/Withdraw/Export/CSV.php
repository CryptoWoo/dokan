<?php

namespace WeDevs\Dokan\Withdraw\Export;

class CSV {

    /**
     * Witdraws to export
     *
     * @var array
     */
    protected $withdraws = [];

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $withdraws
     */
    public function __construct( $withdraws ) {
        $this->withdraws = $withdraws;
    }

    /**
     * Export withdraws
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function export() {
        $date = date( 'Y-m-d-H-i-s', strtotime( current_time( 'mysql' ) ) );

        header( 'Content-type: html/csv' );
        header( 'Content-Disposition: attachment; filename="withdraw-' . $date . '.csv"' );

        $currency = get_option( 'woocommerce_currency' );

        foreach ( $this->withdraws as $withdraw ) {
            $email = dokan_get_seller_withdraw_mail( $withdraw->get_user_id() );

            echo esc_html( $email ) . ',';
            echo esc_html( $withdraw->get_amount() ) . ',';
            echo esc_html( $currency ) . "\n";
        }

        die();
    }

    /**
     * Export withdraws to Electrum Mass Payment CSV
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function export_electrum() {
        $date = date( 'Y-m-d-H-i-s', strtotime( current_time( 'mysql' ) ) );

        header( 'Content-type: html/csv' );
        header( 'Content-Disposition: attachment; filename="withdraw-' . $date . '.csv"' );

        $currency = get_option( 'woocommerce_currency' );

        // Get the CryptoWoo exchange rate from the database
        $price = \CW_ExchangeRates::processing()->get_exchange_rate(str_replace('TEST', '','BTC'), false, $currency); // TODO finish multi-currency support

        foreach ( $this->withdraws as $withdraw ) {
            $address = dokanwd_get_seller_payout_address( $withdraw->get_user_id() );

            $amount_fiat = $withdraw->get_amount(); // TODO Maybe subtract transaction fee before calculating payout amount

            $amount_crypto = \CW_Formatting::fbits($amount_fiat / $price, false);
            echo esc_html( $address ) . ',';
            echo esc_html( $amount_crypto ) . "\n";
        }

        die();
    }
}
