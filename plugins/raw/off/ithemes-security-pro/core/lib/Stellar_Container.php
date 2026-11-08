<?php

namespace iThemesSecurity\Lib;

use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\Strauss\StellarWP\ContainerContract\ContainerInterface;

class Stellar_Container implements ContainerInterface {

	/** @var Container */
	private $container;

	public function __construct( Container $container ) { $this->container = $container; }

	public function bind( string $id, $implementation = null ) {
		$this->container[ $id ] = $this->container->factory( $this->make_builder( $implementation ?? $id ) );
	}

	public function get( string $id ) {
		return $this->container[ $id ];
	}

	public function has( string $id ) {
		return isset( $this->container[ $id ] );
	}

	public function singleton( string $id, $implementation = null ) {
		$this->container[ $id ] = $this->make_builder( $implementation ?? $id );
	}

	private function make_builder( $implementation ): \Closure {
		return static function ( Container $c ) use ( $implementation ) {
			if ( is_string( $implementation ) && class_exists( $implementation ) ) {
				return Stellar_Container::build( $implementation, $c );
			}

			if ( $implementation instanceof \Closure ) {
				return $implementation();
			}

			return $implementation;
		};
	}

	/**
	 * Instantiate a class by recursively resolving its constructor dependencies
	 * from the Pimple container, falling back to further autowiring for
	 * unregistered class-typed parameters.
	 */
	public static function build( string $class, Container $c ): object {
		$reflector   = new \ReflectionClass( $class );
		$constructor = $reflector->getConstructor();

		if ( $constructor === null || $constructor->getNumberOfParameters() === 0 ) {
			return new $class();
		}

		$args = [];
		foreach ( $constructor->getParameters() as $param ) {
			$type = $param->getType();
			if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
				$typeName = $type->getName();
				if ( isset( $c[ $typeName ] ) ) {
					$args[] = $c[ $typeName ];
				} elseif ( class_exists( $typeName ) ) {
					$args[] = self::build( $typeName, $c );
				} elseif ( $param->isDefaultValueAvailable() ) {
					$args[] = $param->getDefaultValue();
				}
			} elseif ( $param->isDefaultValueAvailable() ) {
				$args[] = $param->getDefaultValue();
			}
		}

		return $reflector->newInstanceArgs( $args );
	}
}
