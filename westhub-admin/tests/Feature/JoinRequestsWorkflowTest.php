<?php

namespace Tests\Feature;

use App\Jobs\Mail\SendJoinDecisionMessage;
use App\Jobs\JoinRequests\SyncJoinRequestDecisionToGoogleSheet;
use App\Livewire\Admin\JoinRequests\Index;
use App\Models\JoinRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JoinRequestsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_pending_request_updates_status_and_dispatches_email_job(): void
    {
        Bus::fake();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $request = JoinRequest::query()->create([
            'full_name' => 'Alex Carter',
            'email' => 'alex@example.com',
            'profession' => 'Nurse',
            'status' => JoinRequest::STATUS_NEW,
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('readyToLoad', true)
            ->call('accept', $request->id);

        $this->assertDatabaseHas('join_requests', [
            'id' => $request->id,
            'status' => JoinRequest::STATUS_ACCEPTED,
            'reviewed_by' => $user->id,
        ]);

        $this->assertDatabaseHas('join_request_events', [
            'join_request_id' => $request->id,
            'old_status' => JoinRequest::STATUS_NEW,
            'new_status' => JoinRequest::STATUS_ACCEPTED,
        ]);

        Bus::assertDispatched(SendJoinDecisionMessage::class, function (SendJoinDecisionMessage $job) use ($request) {
            return $job->joinRequestId === $request->id && $job->templateKey === 'join.accepted';
        });

        Bus::assertDispatched(SyncJoinRequestDecisionToGoogleSheet::class, function (SyncJoinRequestDecisionToGoogleSheet $job) use ($request) {
            return $job->joinRequestId === $request->id;
        });
    }

    public function test_declining_non_pending_request_does_not_send_email_or_change_status(): void
    {
        Bus::fake();

        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $request = JoinRequest::query()->create([
            'full_name' => 'Morgan Lee',
            'email' => 'morgan@example.com',
            'profession' => 'Therapist',
            'status' => JoinRequest::STATUS_ACCEPTED,
        ]);

        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('readyToLoad', true)
            ->call('decline', $request->id);

        $this->assertDatabaseHas('join_requests', [
            'id' => $request->id,
            'status' => JoinRequest::STATUS_ACCEPTED,
        ]);

        $this->assertDatabaseCount('join_request_events', 0);

        Bus::assertNotDispatched(SendJoinDecisionMessage::class);
        Bus::assertNotDispatched(SyncJoinRequestDecisionToGoogleSheet::class);
    }
}
