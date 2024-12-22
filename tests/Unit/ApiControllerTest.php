<?php

namespace Tests\Unit;

use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    use RefreshDatabase;


    /**
     * Test retrieving all URLs for authenticated user.
     *
     * @return void
     */
    public function test_index_urls()
    {
        // Create a user and issue a token
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Create 5 URLs for the user
        Url::factory()->count(5)->create(['user_id' => $user->id]);

        // Make a GET request with the Authorization header
        $response = $this->getJson('/api/urls', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and structure
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'URLs retrieved successfully.',
        ]);
        $response->assertJsonCount(5, 'data'); // Ensure 5 URLs are returned
    }

    /**
     * Test creating a new shortened URL.
     *
     * @return void
     */
    public function test_store_url()
    {
        // Create a user and issue a token
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // URL data
        $data = ['real_url' => 'https://example.com'];

        // Make a POST request to create the URL
        $response = $this->postJson('/api/urls', $data, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and message
        $response->assertStatus(201);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'URL successfully shortened.',
        ]);

        // Assert that a URL is created in the database
        $this->assertDatabaseHas('urls', ['real_url' => 'https://example.com']);
    }

    /**
     * Test updating a URL.
     *
     * @return void
     */
    public function test_update_url()
    {
        // Create a user and an existing URL
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        // New data for updating the URL
        $data = ['real_url' => 'https://new-url.com'];

        // Make a PATCH request to update the URL
        $response = $this->patchJson("/api/urls/{$url->id}", $data, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and message
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'URL updated successfully.',
        ]);

        // Assert the URL is updated in the database
        $this->assertDatabaseHas('urls', ['real_url' => 'https://new-url.com']);
    }

    /**
     * Test deleting a URL.
     *
     * @return void
     */
    public function test_delete_url()
    {
        // Create a user and an existing URL
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        // Make a DELETE request to delete the URL
        $response = $this->deleteJson("/api/urls/{$url->id}", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and message
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'URL deleted successfully.',
        ]);

        // Assert the URL is deleted from the database
        $this->assertDatabaseMissing('urls', ['id' => $url->id]);
    }

    /**
     * Test retrieving a single URL.
     *
     * @return void
     */
    public function test_show_url()
    {
        // Create a user and an existing URL
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        // Make a GET request to retrieve the URL
        $response = $this->getJson("/api/urls/{$url->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and structure
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'message' => 'URL retrieved successfully.',
        ]);
        $response->assertJsonFragment([
            'real_url' => $url->real_url,
            'short_url' => $url->short_url,
        ]);
    }

    /**
     * Test handling of unauthorized access.
     *
     * @return void
     */
    public function test_unauthorized_access()
    {
        // Create a user without issuing a token
        $user = User::factory()->create();

        // Make a GET request to retrieve URLs without authentication
        $response = $this->getJson('/api/urls');

        // Assert the response status and message
        $response->assertStatus(401);
        $response->assertJsonFragment([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * Test handling of URL not found.
     *
     * @return void
     */
    public function test_url_not_found()
    {
        // Create a user and issue a token
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        // Try to retrieve a non-existing URL
        $response = $this->getJson('/api/urls/999', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        // Assert the response status and message
        $response->assertStatus(404);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'URL not found.',
        ]);
    }
}
