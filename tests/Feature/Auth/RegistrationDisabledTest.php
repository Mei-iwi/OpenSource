<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        putenv('PUBLIC_REGISTRATION_ENABLED=false');

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        putenv('PUBLIC_REGISTRATION_ENABLED');

        parent::tearDownAfterClass();
    }

    public function test_registration_routes_are_unavailable_when_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->assertFalse($this->app['router']->has('register'));
    }
}
