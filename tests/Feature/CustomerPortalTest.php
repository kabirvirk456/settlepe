<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_customer_is_redirected_to_the_case_portal(): void
    {
        $customer = $this->enrolledCustomer();
        $customer->settlementAccounts()->create([
            'bank_name' => 'HDFC Bank', 'product' => 'Personal loan',
            'account_reference' => 'XXXX1234', 'stage' => 'negotiation',
            'customer_visible' => true, 'outstanding_amount' => 200000,
            'offered_settlement_amount' => 125000, 'closure_letter_status' => 'pending',
        ]);
        $customer->customerTasks()->create(['title' => 'Upload bank statement', 'priority' => 'high', 'status' => 'pending']);
        $customer->legalNotices()->create([
            'lender_name' => 'HDFC Bank', 'notice_type' => 'Demand notice',
            'received_at' => now(), 'priority' => 'urgent', 'status' => 'under_review',
            'customer_instructions' => 'Upload all pages of the notice.',
        ]);
        $customer->leadActivities()->create(['event' => 'Lender contacted', 'notes' => 'Negotiation initiated.', 'customer_visible' => true]);

        $this->actingAs($customer)->get('/dashboard')->assertRedirect('/portal');
        $this->actingAs($customer)->get('/portal')
            ->assertOk()
            ->assertSee('My Settlement Case')
            ->assertSee('HDFC Bank')
            ->assertSee('Upload bank statement')
            ->assertSee('Demand notice')
            ->assertSee('Negotiation initiated.');

        $this->assertNotNull($customer->fresh()->case_reference);
    }

    public function test_non_enrolled_customer_cannot_open_portal(): void
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER, 'service_fee_paid_at' => null]);

        $this->actingAs($customer)->get('/portal')->assertForbidden();
    }

    public function test_customer_can_upload_a_private_document_and_complete_only_their_task(): void
    {
        Storage::fake('local');
        $customer = $this->enrolledCustomer();
        $other = $this->enrolledCustomer();
        $task = $customer->customerTasks()->create(['title' => 'Review offer', 'priority' => 'normal', 'status' => 'pending']);
        $otherTask = $other->customerTasks()->create(['title' => 'Other task', 'priority' => 'normal', 'status' => 'pending']);

        $this->actingAs($customer)->post('/portal/documents', [
            'document_type' => 'bank_notice',
            'document' => UploadedFile::fake()->create('notice.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $document = $customer->leadDocuments()->firstOrFail();
        Storage::disk('local')->assertExists($document->path);
        $this->assertSame('pending', $document->review_status);

        $this->actingAs($customer)->patch(route('portal.tasks.complete', $task))->assertRedirect();
        $this->assertSame('completed', $task->fresh()->status);
        $this->actingAs($customer)->patch(route('portal.tasks.complete', $otherTask))->assertNotFound();
    }

    public function test_admin_can_manage_customer_portal_content_from_the_crm(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = $this->enrolledCustomer();

        $this->actingAs($admin)->patch(route('team.leads.case', $customer), [
            'case_stage' => 'offer_received',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('team.leads.tasks', $customer), [
            'title' => 'Approve lender offer', 'description' => 'Review the offer with your RM.',
            'priority' => 'urgent', 'due_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('team.leads.legal-notices', $customer), [
            'lender_name' => 'Axis Bank', 'notice_type' => 'Demand notice',
            'received_at' => now()->toDateString(), 'response_due_at' => now()->addWeek()->toDateString(),
            'priority' => 'high', 'status' => 'under_review',
            'customer_instructions' => 'Speak with your RM before responding.',
        ])->assertRedirect();

        $this->assertSame('offer_received', $customer->fresh()->case_stage);
        $this->assertDatabaseHas('customer_tasks', ['lead_user_id' => $customer->id, 'title' => 'Approve lender offer']);
        $this->assertDatabaseHas('legal_notices', ['lead_user_id' => $customer->id, 'lender_name' => 'Axis Bank']);
        $this->assertDatabaseHas('lead_activities', ['lead_user_id' => $customer->id, 'customer_visible' => true]);
    }

    private function enrolledCustomer(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Portal Customer',
            'cibil_profile_completed_at' => now(),
            'service_fee_paid_at' => now(),
            'case_stage' => 'negotiation',
        ]);
    }
}
