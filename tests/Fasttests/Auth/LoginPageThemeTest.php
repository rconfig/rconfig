<?php

test('login page forces the dark theme on the root element', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('data-force-dark="true"', false);
    $response->assertSee('data-theme="dark"', false);
    $response->assertSee('class="dark"', false);
});

test('login page includes the theme bootstrap script', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee('root.classList.toggle("dark", isDark)', false);
});

test('theme bootstrap defaults to dark rather than the system preference', function () {
    $rendered = view('includes.theme-bootstrap')->render();

    $this->assertStringContainsString('storedTheme || "dark"', $rendered);
    $this->assertStringNotContainsString('prefers-color-scheme', $rendered);
});
