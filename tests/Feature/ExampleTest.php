<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_is_available_at_its_named_route(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Sign in to your account')
            ->assertSee('Create an account')
            ->assertSee('name="username"', false)
            ->assertSee('name="password"', false);
    }

    public function test_registration_page_is_available_and_links_back_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertSee('Create your account')
            ->assertSee('name="username"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Already have an account? Sign in');
    }
}
