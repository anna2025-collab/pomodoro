<?php

  namespace Tests\Feature;

  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;
  use Laravel\Sanctum\Sanctum;
  use Tests\TestCase;

  class FocusSessionTest extends TestCase
  {
      use RefreshDatabase;

      public function test_user_can_create_focus_session(): void
      {
          $user = User::factory()->create();

          Sanctum::actingAs($user);

          $response = $this->postJson('/api/focus-sessions', [
              'mode' => 'focus',
              'duration_seconds' => 1500,
          ]);

          $response->assertOk();

          $this->assertDatabaseHas('focus_sessions', [
              'user_id' => $user->id,
              'mode' => 'focus',
              'duration_seconds' => 1500,
          ]);
      }
  }

