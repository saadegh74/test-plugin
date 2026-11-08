<?php

/**
 * Provides an API for all classes that are runnable.
 *
 * @since 1.0.0
 *
 * @package iThemesSecurity\Strauss\StellarWP\Telemetry\Contracts
 */
namespace iThemesSecurity\Strauss\StellarWP\Telemetry\Contracts;

/**
 * Provides an API for all classes that are runnable.
 *
 * @since 1.0.0
 *
 * @package \iThemesSecurity\Strauss\StellarWP\Telemetry\Contracts
 */
interface Runnable
{
    /**
     * Run the intended action.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function run();
}