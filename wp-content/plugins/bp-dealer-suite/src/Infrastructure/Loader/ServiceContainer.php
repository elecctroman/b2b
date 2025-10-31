<?php
namespace BP\DealerSuite\Infrastructure\Loader;

use Closure;
use RuntimeException;

class ServiceContainer {
    /** @var array<string, callable|object> */
    private array $bindings = [];

    /**
     * @template T
     * @param class-string<T>|string $id
     * @param callable|object $concrete
     */
    public function set( string $id, $concrete ): void {
        $this->bindings[ $id ] = $concrete;
    }

    /**
     * @template T
     * @param class-string<T>|string $id
     * @return T|mixed
     */
    public function get( string $id ) {
        if ( ! isset( $this->bindings[ $id ] ) ) {
            throw new RuntimeException( sprintf( 'Service "%s" is not registered.', $id ) );
        }

        $entry = $this->bindings[ $id ];

        if ( $entry instanceof Closure ) {
            $service               = $entry();
            $this->bindings[ $id ] = $service;
            return $service;
        }

        return $entry;
    }

    public function has( string $id ): bool {
        return isset( $this->bindings[ $id ] );
    }
}
