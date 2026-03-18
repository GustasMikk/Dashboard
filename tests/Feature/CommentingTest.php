<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\IncidentGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CommentingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin1;
    protected User $admin2;
    protected IncidentGroup $group1;
    protected IncidentGroup $group2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::create([
            'name'     => 'Admin1',
            'email'    => 'admin1@admin.com',
            'password' => Hash::make('admin1'),
        ]);

        $this->admin2 = User::create([
            'name'     => 'Admin2',
            'email'    => 'admin2@admin.com',
            'password' => Hash::make('admin2'),
        ]);

        $this->group1 = IncidentGroup::create([
            'title'              => 'Test Group',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'test-host',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);

        $this->group2 = IncidentGroup::create([
            'title'              => 'Test Group 2',
            'status'             => 'open',
            'highest_severity'   => 'high',
            'host'               => 'test-host-2',
            'opened_at'          => now(),
            'last_occurrence_at' => now(),
            'total_occurrences'  => 0,
        ]);
    }

    public function test_users_create_comments(): void
    {
        Comment::insert([
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin1->id, 'comment_text' => 'abc'],
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin1->id, 'comment_text' => 'abcd'],
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin2->id, 'comment_text' => 'abcde'],
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin2->id, 'comment_text' => 'abcdef'],
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin2->id, 'comment_text' => 'abcdefg'],
            ['incident_group_id' => $this->group1->id, 'user_id' => $this->admin2->id, 'comment_text' => 'abcdefgh'],
        ]);

        $this->assertEquals(2, Comment::where('user_id', $this->admin1->id)->count());
        $this->assertEquals(4, Comment::where('user_id', $this->admin2->id)->count());
    }

    public function test_edit_comment(): void
    {
        $comment = Comment::create([
            'incident_group_id' => $this->group1->id,
            'user_id'           => $this->admin1->id,
            'comment_text'      => 'original comment',
        ]);

        $comment->update(['comment_text' => 'edited comment']);

        $this->assertEquals('edited comment', $comment->fresh()->comment_text);
        $this->assertDatabaseHas('comments', ['comment_text' => 'edited comment']);
        $this->assertDatabaseMissing('comments', ['comment_text' => 'original comment']);
    }

    public function test_delete_comment(): void
    {
        $comment = Comment::create([
            'incident_group_id' => $this->group1->id,
            'user_id'           => $this->admin1->id,
            'comment_text'      => 'comment to delete',
        ]);

        $this->assertDatabaseCount('comments', 1);

        $comment->delete();

        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseMissing('comments', ['comment_text' => 'comment to delete']);
    }

    public function test_deleting_incident_group_deletes_comments(): void
    {
        Comment::create([
            'incident_group_id' => $this->group1->id,
            'user_id'           => $this->admin1->id,
            'comment_text'      => 'test comment',
        ]);

        $this->group1->delete();

        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseMissing('comments', ['comment_text' => 'test comment']);
    }
}