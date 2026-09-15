<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dark_scheme_is_stored_on_the_account(): void
    {
        $user = $this->user();
        $other = $this->user();

        $this->actingAs($user)
            ->patch(route('settings.update'), ['dark' => '1'])
            ->assertRedirect(route('settings.show'));

        $this->assertTrue($user->fresh()->dark);
        $this->assertFalse($other->fresh()->dark);
    }

    public function test_every_account_sees_its_own_scheme(): void
    {
        $dark = $this->user(dark: true);
        $light = $this->user();

        $this->actingAs($dark)
            ->get(route('main'))
            ->assertSee('data-bs-theme="dark"', false);

        $this->actingAs($light)
            ->get(route('main'))
            ->assertDontSee('data-bs-theme="dark"', false);
    }

    public function test_the_scheme_can_be_switched_off_again(): void
    {
        $user = $this->user(dark: true);

        // An unchecked checkbox is not submitted at all.
        $this->actingAs($user)
            ->patch(route('settings.update'))
            ->assertRedirect(route('settings.show'));

        $this->assertFalse($user->fresh()->dark);
    }

    public function test_the_settings_page_shows_the_preference_of_the_account(): void
    {
        $this->actingAs($this->user(dark: true))
            ->get(route('settings.show'))
            ->assertOk()
            ->assertSee('checked', false);
    }

    public function test_the_sign_in_page_is_always_light(): void
    {
        // Signed out there is no account to read a colour scheme from.
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-bs-theme="dark"', false);
    }

    /**
     * The preferences are not mass assignable, so set them the way the
     * controller does.
     */
    private function user(bool $dark = false): User
    {
        $user = User::factory()->create();

        $user->dark = $dark;
        $user->save();

        return $user;
    }
}
