<?php

namespace Tests\Fasttests\Auth;

use Tests\TestCase;

/**
 * The auth pages hardcode a black background, so they must always resolve to the
 * dark theme. Without the forced dark class the light CSS variables apply and the
 * form renders near black text on a black page.
 */
class LoginPageThemeTest extends TestCase
{
    public function test_login_page_forces_the_dark_theme_on_the_root_element(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('data-force-dark="true"', false);
        $response->assertSee('data-theme="dark"', false);
        $response->assertSee('class="dark"', false);
    }

    public function test_login_page_includes_the_theme_bootstrap_script(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('root.classList.toggle("dark", isDark)', false);
    }

    public function test_theme_bootstrap_defaults_to_dark_rather_than_the_system_preference(): void
    {
        $rendered = view('includes.theme-bootstrap')->render();

        $this->assertStringContainsString('storedTheme || "dark"', $rendered);
        $this->assertStringNotContainsString('prefers-color-scheme', $rendered);
    }
}
