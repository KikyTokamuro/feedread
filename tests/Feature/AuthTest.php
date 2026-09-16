<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_a_user_can_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('main'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'secret-password']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_only_administrators_reach_the_account_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_an_administrator_can_create_an_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Second Person',
            'email' => 'Second@Example.com',
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Second Person',
            'email' => 'second@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_an_administrator_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_an_administrator_can_delete_another_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $other = User::factory()->create(['is_admin' => true]);

        $this->actingAs($other)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_deleting_an_account_removes_its_feeds(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $victim = User::factory()->create();
        $feed = Feed::factory()->create(['user_id' => $victim->id]);

        $this->actingAs($admin)->delete(route('users.destroy', $victim));

        $this->assertDatabaseMissing('feeds', ['id' => $feed->id]);
    }

    public function test_a_non_administrator_cannot_create_accounts(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->post(route('users.store'), [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }
}
