<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ContentLiveBranchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_marketing_cannot_delete_other_branch_content_or_live(): void
    {
        $mine = Branch::factory()->create();
        $other = Branch::factory()->create();
        $emp = Employee::factory()->create(['branch_id' => $other->id]);
        $now = now();

        $postId = DB::table('content_posts')->insertGetId(['branch_id' => $other->id, 'employee_id' => $emp->id, 'platform' => 'tiktok',
            'title' => 'x', 'published_at' => $now->toDateString(), 'views' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $liveId = DB::table('live_sessions')->insertGetId(['branch_id' => $other->id, 'employee_id' => $emp->id,
            'session_date' => $now->toDateString(), 'created_at' => $now, 'updated_at' => $now]);

        $mgr = $this->userWithRole('manager_marketing', $mine);

        $this->actingAs($mgr)->delete("/content/posts/{$postId}")->assertForbidden();
        $this->actingAs($mgr)->delete("/live-sessions/{$liveId}")->assertForbidden();
        $this->assertDatabaseHas('content_posts', ['id' => $postId]);
        $this->assertDatabaseHas('live_sessions', ['id' => $liveId]);

        $owner = $this->userWithRole('owner', $mine);
        $this->actingAs($owner)->delete("/content/posts/{$postId}")->assertRedirect();
        $this->assertDatabaseMissing('content_posts', ['id' => $postId]);
    }
}
