<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamCrmTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_login_redirects_users_by_role(): void
    {
        User::factory()->create([
            'email' => 'sales@settlepe.test',
            'password' => 'password',
            'role' => User::ROLE_SALES,
        ]);

        $this->post('/team/login', [
            'email' => 'sales@settlepe.test',
            'password' => 'password',
        ])->assertRedirect('/team');

        $this->get('/team')->assertRedirect('/team/sales');
    }

    public function test_guest_team_pages_redirect_to_team_login(): void
    {
        $this->get('/team/sales')->assertRedirect('/team/login');
        $this->get('/team/admin')->assertRedirect('/team/login');
        $this->get('/team/rm')->assertRedirect('/team/login');
    }

    public function test_customer_cibil_fetch_becomes_sales_lead(): void
    {
        $customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Ravi Customer',
            'sales_status' => 'cibil_fetched',
            'consultation_fee_paid_at' => null,
            'service_fee_paid_at' => null,
        ]);

        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($sales)
            ->get('/team/sales')
            ->assertOk()
            ->assertDontSee('Ravi Customer');

        $this->actingAs($admin)
            ->patch(route('team.leads.assignment', $customer), [
                'assigned_sales_id' => $sales->id,
                'assigned_rm_id' => null,
            ])
            ->assertRedirect();

        $this->actingAs($sales)
            ->get('/team/sales')
            ->assertOk()
            ->assertSee('Ravi Customer')
            ->assertSee('CRIF report fetched');

        $this->assertNull($customer->fresh()->consultation_fee_paid_at);
    }

    public function test_sales_can_mark_service_fee_paid_and_rm_can_update_case(): void
    {
        $lead = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Service Lead',
            'assigned_sales_id' => null,
            'assigned_rm_id' => null,
            'sales_status' => 'consultation_paid',
            'consultation_fee_paid_at' => now(),
            'service_fee_paid_at' => null,
            'rm_status' => null,
        ]);

        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $rm = User::factory()->create(['role' => User::ROLE_RM]);
        $lead->update([
            'assigned_sales_id' => $sales->id,
            'assigned_rm_id' => $rm->id,
        ]);

        $this->actingAs($sales)
            ->patch(route('team.leads.sales', $lead), [
                'sales_status' => 'settlement_pitched',
                'call_disposition' => 'interested',
                'priority' => 'high',
                'follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'sales_notes' => 'Customer agreed to pay service fee.',
                'mark_service_fee_paid' => '1',
            ])
            ->assertRedirect();

        $lead->refresh();

        $this->assertSame('service_fee_paid', $lead->sales_status);
        $this->assertNotNull($lead->service_fee_paid_at);
        $this->assertSame('work_started', $lead->rm_status);

        $this->actingAs($rm)
            ->get('/team/rm')
            ->assertOk()
            ->assertSee('Service Lead');

        $this->actingAs($rm)
            ->patch(route('team.leads.rm', $lead), [
                'rm_status' => 'settlement_offered',
                'rm_notes' => 'Bank offer shared.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $lead->id,
            'rm_status' => 'settlement_offered',
            'rm_notes' => 'Bank offer shared.',
        ]);

        $this->assertDatabaseHas('lead_activities', [
            'lead_user_id' => $lead->id,
            'event' => 'Sales status updated',
        ]);
    }

    public function test_sales_conversion_auto_assigns_available_rm(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $rm = User::factory()->create(['role' => User::ROLE_RM]);
        $lead = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Paid Recovery Lead',
            'assigned_sales_id' => $sales->id,
            'assigned_rm_id' => null,
            'sales_status' => 'consultation_paid',
            'consultation_fee_paid_at' => now(),
            'service_fee_paid_at' => null,
            'rm_status' => null,
        ]);

        $this->actingAs($sales)
            ->patch(route('team.leads.sales', $lead), [
                'sales_status' => 'service_fee_paid',
                'call_disposition' => 'converted',
                'priority' => 'normal',
                'sales_notes' => 'Customer agreed to start recovery.',
                'mark_service_fee_paid' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $lead->id,
            'assigned_rm_id' => $rm->id,
            'sales_status' => 'service_fee_paid',
            'rm_status' => 'work_started',
        ]);

        $this->actingAs($rm)
            ->get('/team/rm')
            ->assertOk()
            ->assertSee('Paid Recovery Lead');
    }

    public function test_sales_can_update_unpaid_lead_from_pre_consultation_queue(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $lead = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Unpaid Lead',
            'assigned_sales_id' => $sales->id,
            'sales_status' => 'cibil_fetched',
            'consultation_fee_paid_at' => null,
            'follow_up_at' => null,
            'call_disposition' => null,
        ]);

        $this->actingAs($sales)
            ->get('/team/sales')
            ->assertOk()
            ->assertSee('Unpaid Signups')
            ->assertSee('Unpaid Lead');

        $followUpAt = now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s');

        $this->actingAs($sales)
            ->patch(route('team.leads.sales', $lead), [
                'sales_status' => 'no_reply',
                'call_disposition' => null,
                'priority' => 'high',
                'follow_up_at' => $followUpAt,
                'sales_notes' => 'Customer did not answer, follow up tomorrow.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $lead->id,
            'sales_status' => 'no_reply',
            'call_disposition' => 'not_connected',
            'priority' => 'high',
            'sales_notes' => 'Customer did not answer, follow up tomorrow.',
        ]);
    }

    public function test_admin_can_access_sales_and_rm_dashboards(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/team/admin')
            ->assertOk()
            ->assertSee('Lead Operations')
            ->assertSee('Financial Snapshot')
            ->assertSee('Sales Performance');
        $this->actingAs($admin)->get('/team/sales')->assertOk()->assertSee('Sales CRM');
        $this->actingAs($admin)->get('/team/rm')->assertOk()->assertSee('RM CRM');
    }

    public function test_sales_cannot_access_rm_dashboard(): void
    {
        $sales = User::factory()->create([
            'role' => User::ROLE_SALES,
        ]);

        $this->actingAs($sales)->get('/team/rm')->assertForbidden();
    }

    public function test_admin_can_manage_detail_page_documents_and_settlement_tracker(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $sales = User::factory()->create(['role' => User::ROLE_SALES]);
        $rm = User::factory()->create(['role' => User::ROLE_RM]);
        $lead = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'name' => 'Detail Lead',
            'sales_status' => 'service_fee_paid',
            'consultation_fee_paid_at' => now(),
            'service_fee_paid_at' => now(),
            'rm_status' => 'work_started',
        ]);

        $this->actingAs($admin)
            ->patch(route('team.leads.assignment', $lead), [
                'assigned_sales_id' => $sales->id,
                'assigned_rm_id' => $rm->id,
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('team.leads.show', $lead))
            ->assertOk()
            ->assertSee('CRIF credit report')
            ->assertSee('Account details')
            ->assertSee('Activity timeline')
            ->assertSee('WhatsApp templates')
            ->assertSee('Settlement tracker');

        $this->actingAs($admin)
            ->post(route('team.leads.activities', $lead), [
                'notes' => 'Manual note added.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('team.leads.documents', $lead), [
                'document_type' => 'loan_statement',
                'document' => UploadedFile::fake()->create('statement.pdf', 24, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('team.leads.settlement-accounts', $lead), [
                'bank_name' => 'HDFC Bank',
                'outstanding_amount' => 132500,
                'offered_settlement_amount' => 85000,
                'final_settlement_amount' => null,
                'due_date' => now()->addDays(10)->toDateString(),
                'closure_letter_status' => 'requested',
                'notes' => 'Offer requested.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lead_activities', [
            'lead_user_id' => $lead->id,
            'event' => 'Manual note',
            'notes' => 'Manual note added.',
        ]);

        $this->assertDatabaseHas('lead_documents', [
            'lead_user_id' => $lead->id,
            'document_type' => 'loan_statement',
            'original_name' => 'statement.pdf',
        ]);

        $this->assertDatabaseHas('settlement_accounts', [
            'lead_user_id' => $lead->id,
            'bank_name' => 'HDFC Bank',
            'closure_letter_status' => 'requested',
        ]);
    }
}
