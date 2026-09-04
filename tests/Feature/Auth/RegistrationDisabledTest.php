<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    public function test_registration_routes_are_unavailable_when_disabled(): void
    {
        config()->set('features.public_registration', false);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->assertTrue($this->app['router']->has('register'));

        $this->get('/')->assertDontSee(route('register'));
    }
}
