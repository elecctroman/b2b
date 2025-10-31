<?php
namespace BP\DealerSuite\Support;

use WC_Logger;
use WC_Log_Levels;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerFactory {
    public static function create(): LoggerInterface {
        $logger = new class() implements LoggerInterface {
            private WC_Logger $logger;

            public function __construct() {
                $this->logger = wc_get_logger();
            }

            public function emergency( $message, array $context = [] ): void {
                $this->log( LogLevel::EMERGENCY, $message, $context );
            }

            public function alert( $message, array $context = [] ): void {
                $this->log( LogLevel::ALERT, $message, $context );
            }

            public function critical( $message, array $context = [] ): void {
                $this->log( LogLevel::CRITICAL, $message, $context );
            }

            public function error( $message, array $context = [] ): void {
                $this->log( LogLevel::ERROR, $message, $context );
            }

            public function warning( $message, array $context = [] ): void {
                $this->log( LogLevel::WARNING, $message, $context );
            }

            public function notice( $message, array $context = [] ): void {
                $this->log( LogLevel::NOTICE, $message, $context );
            }

            public function info( $message, array $context = [] ): void {
                $this->log( LogLevel::INFO, $message, $context );
            }

            public function debug( $message, array $context = [] ): void {
                $this->log( LogLevel::DEBUG, $message, $context );
            }

            public function log( $level, $message, array $context = [] ): void {
                $this->logger->log( $level, wp_json_encode( [
                    'message' => $message,
                    'context' => $context,
                ] ), [ 'source' => 'bp-dealer-suite' ] );
            }
        };

        return $logger;
    }
}
