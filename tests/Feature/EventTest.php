<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\Tag;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $permission = Permission::firstOrCreate(["name" => "gestionarEventos"]);
        $role = Role::firstOrCreate(["name" => "admin"]);
        $role->permissions()->syncWithoutDetaching($permission->id);

        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching($role->id);

        $this->actingAs($user, 'sanctum');
    }

    /** @test */
    public function a_user_can_retrieve_all_events()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson("/api/events");

        $response->assertStatus(200)->assertJsonCount(3, "data");
    }

    /** @test */
    public function a_user_can_create_an_event()
    {
        $media = Media::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $eventData = [
            "title" => "New Event Title",
            "summary" => "A short summary for the event.",
            "content_html" => "<p>Full event details here.</p>",
            "location" => "Virtual - Zoom",
            "starts_at" => now()->addDays(1)->format("Y-m-d H:i:s"),
            "ends_at" => now()->addDays(1)->addHours(2)->format("Y-m-d H:i:s"),
            "status" => "published",
            "banner_media_id" => $media->id,
            "tags" => $tags->pluck("id")->toArray(),
        ];

        $response = $this->postJson("/api/events", $eventData);

        $response
            ->assertStatus(201)
            ->assertJsonFragment(["title" => "New Event Title"])
            ->assertJsonCount(2, "data.tags");

        $this->assertDatabaseHas("events", [
            "title" => "New Event Title",
            "slug" => Str::slug("New Event Title"),
            "location" => "Virtual - Zoom",
            "banner_media_id" => $media->id,
            "created_by" => Auth::id(),
            "updated_by" => Auth::id(),
        ]);

        $event = Event::firstWhere("title", "New Event Title");
        $this->assertCount(2, $event->tags);
    }

    /** @test */
    public function an_event_title_and_starts_at_are_required()
    {
        $response = $this->postJson("/api/events", [
            "title" => null,
            "starts_at" => null,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(["title", "starts_at"]);
    }

    /** @test */
    public function an_event_slug_must_be_unique()
    {
        Event::factory()->create(["slug" => "existing-event-slug"]);

        $eventData = [
            "title" => "Another Event",
            "slug" => "existing-event-slug",
            "starts_at" => now()->addDay(),
            "status" => "draft",
        ];

        $response = $this->postJson("/api/events", $eventData);

        $response->assertStatus(422)->assertJsonValidationErrors(["slug"]);
    }

    /** @test */
    public function an_event_ends_at_must_be_after_starts_at()
    {
        $eventData = [
            "title" => "Invalid Event",
            "starts_at" => now()->addDay()->format("Y-m-d H:i:s"),
            "ends_at" => now()->format("Y-m-d H:i:s"), // ends_at is before starts_at
            "status" => "draft",
        ];

        $response = $this->postJson("/api/events", $eventData);

        $response->assertStatus(422)->assertJsonValidationErrors(["ends_at"]);
    }

    /** @test */
    public function a_user_can_retrieve_a_single_event()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/" . $event->id);

        $response
            ->assertStatus(200)
            ->assertJson(["data" => $event->toArray() + ["tags" => []]]);
    }

    /** @test */
    public function a_user_can_update_an_event()
    {
        $event = Event::factory()->create([
            "title" => "Old Event Title",
            "slug" => "old-event-title",
            "status" => "draft",
        ]);
        $newMedia = Media::factory()->create();
        $newTags = Tag::factory()->count(1)->create();

        $updatedData = [
            "title" => "Updated Event Title",
            "slug" => "updated-event-title",
            "summary" => "Updated summary.",
            "location" => "Online - Google Meet",
            "starts_at" => now()->addDays(2)->format("Y-m-d H:i:s"),
            "ends_at" => now()->addDays(2)->addHours(3)->format("Y-m-d H:i:s"),
            "status" => "published",
            "banner_media_id" => $newMedia->id,
            "tags" => $newTags->pluck("id")->toArray(),
        ];

        $response = $this->putJson("/api/events/" . $event->id, $updatedData);

        $response
            ->assertStatus(200)
            ->assertJsonFragment(["title" => "Updated Event Title"])
            ->assertJsonCount(1, "data.tags");

        $this->assertDatabaseHas("events", [
            "id" => $event->id,
            "title" => "Updated Event Title",
            "slug" => "updated-event-title",
            "location" => "Online - Google Meet",
            "banner_media_id" => $newMedia->id,
            "updated_by" => Auth::id(),
        ]);

        $event->refresh();
        $this->assertCount(1, $event->tags);
    }

    /** @test */
    public function a_user_can_soft_delete_an_event()
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/" . $event->id);

        $response->assertStatus(204);

        $this->assertDatabaseHas("events", [
            "id" => $event->id,
            "soft_deleted" => true,
        ]);
    }
}
