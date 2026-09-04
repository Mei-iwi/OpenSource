<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    private bool $hadEnvValue = false;

    private mixed $previousEnvValue = null;

    private bool $hadServerValue = false;

    private mixed $previousServerValue = null;

    protected function setUp(): void
    {
        $this->hadEnvValue = array_key_exists('PUBLIC_REGISTRATION_ENABLED', $_ENV);
        $this->previousEnvValue = $_ENV['PUBLIC_REGISTRATION_ENABLED'] ?? null;
        $this->hadServerValue = array_key_exists('PUBLIC_REGISTRATION_ENABLED', $_SERVER);
        $this->previousServerValue = $_SERVER['PUBLIC_REGISTRATION_ENABLED'] ?? null;

        $_ENV['PUBLIC_REGISTRATION_ENABLED'] = 'false';
        $_SERVER['PUBLIC_REGISTRATION_ENABLED'] = 'false';

        parent::setUp();
    }

    protected function tearDown(): void
    {
        if ($this->hadEnvValue) {
            $_ENV['PUBLIC_REGISTRATION_ENABLED'] = $this->previousEnvValue;
        } else {
            unset($_ENV['PUBLIC_REGISTRATION_ENABLED']);
        }

        if ($this->hadServerValue) {
            $_SERVER['PUBLIC_REGISTRATION_ENABLED'] = $this->previousServerValue;
        } else {
            unset($_SERVER['PUBLIC_REGISTRATION_ENABLED']);
        }

        parent::tearDown();
    }

    public function test_registration_routes_are_unavailable_when_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->assertFalse($this->app['router']->has('register'));
    }
}
