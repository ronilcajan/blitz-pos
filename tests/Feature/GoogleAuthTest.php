<?php

use App\Models\Role;
use App\Models\User;
use App\Models\Store;
use Laravel\Socialite\Facades\Socialite;


test('testExistingUserRedirectsToDashboard', function   ()
{
     // Create a user with a specific Google authentication ID
     $user = User::factory()->create(['gauth_id' => '123']);

     // Mock the Socialite driver
     Socialite::shouldReceive('driver->user')
         ->andReturn((object) ['id' => '123']);

     // Mock the callback method to simulate Google authentication
     $response = $this->get('/auth/google/callback');

     // Assert that the response redirects to the dashboard
     $response->assertRedirect(route('dashboard'));
});

test('testNewUserCreationAndRedirectToStoreUpdate', function   ()
{
    // Mock the Socialite driver
    Socialite::shouldReceive('driver->user')->andReturn((object)[
        'id' => '456',
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'avatar' => 'avatar.jpg'
    ]);

    // Mock role assignment
    Role::shouldReceive('addRole')->with('owner')->once();

    // Simulate the Google authentication callback
    $response = $this->get('/auth/google/callback');

    // Assert a new store is created
    $this->assertDatabaseHas('stores', [
        'name' => 'New User',
        'email' => 'newuser@example.com'
    ]);

    // Fetch the newly created store
    $store = Store::where('email', 'newuser@example.com')->first();

    // Assert a new user is created
    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'gauth_id' => '456',
        'store_id' => $store->id
    ]);

    // Assert the user is assigned the 'owner' role
    $this->assertTrue($store->owner->hasRole('owner'));

    // Assert the user is authenticated
    $this->assertAuthenticatedAs($store->owner);

    // Assert the response redirects to the store update page with a message
    $response->assertRedirect('stores/'.$store->id);
    $response->assertSessionHas('message', 'Please update your store details!');
});

test('testExceptionHandling', function   ()
{
    // Mock the Socialite driver to throw an exception
    Socialite::shouldReceive('driver->user')->andReturn((object)[
        'id' => '456',
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'avatar' => 'avatar.jpg'
    ]);

    // Mock the Store::create method to throw an exception
    Store::shouldReceive('create')->andThrow(new \Exception());

    // Simulate the Google authentication callback
    $response = $this->get('/auth/google/callback');

    // Assert that the transaction is rolled back and the user is redirected back with an error message
    $response->assertRedirect()->back();
    $response->assertSessionHasErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
});
