<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_accepts_an_eight_character_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Anggota Baru',
            'email' => 'anggota8@example.test',
            'motorcycle_type' => 'Honda Vario',
            'license_plate' => 'D 1111 AA',
            'password' => 'Ab1!efgh',
            'password_confirmation' => 'Ab1!efgh',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'anggota8@example.test',
        ]);
    }

    public function test_registration_rejects_a_password_shorter_than_eight_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Anggota Baru',
            'email' => 'anggota7@example.test',
            'motorcycle_type' => 'Honda Vario',
            'license_plate' => 'D 1111 AA',
            'password' => 'Ab1!efg',
            'password_confirmation' => 'Ab1!efg',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'anggota7@example.test',
        ]);
    }
}
