<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.aisensy.api_key' => 'test-aisensy-key',
            'services.aisensy.base_url' => 'https://backend.aisensy.com',
            'services.aisensy.send_path' => '/campaign/t1/api/v2',
            'services.aisensy.campaign_name' => 'otp',
            'services.aisensy.source' => 'new-landing-page form',
            'services.aisensy.template_params' => ['otp'],
            'services.aisensy.first_name_fallback' => 'user',
            'services.aisensy.otp_button_enabled' => true,
            'services.aisensy.fixed_otp' => '654321',
            'services.aisensy.demo_otp' => null,
            'services.crif.base_url' => 'https://api.roopya.money/api/v2',
            'services.crif.domain_name' => 'test-domain',
            'services.crif.auth_key' => 'test-crif-key',
            'services.crif.request_api_code' => 'CBC016',
            'services.crif.auth_api_code' => 'CBA017',
            'services.crif.timeout' => 10,
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Check your loan report with mobile OTP.');
    }

    public function test_otp_can_be_requested(): void
    {
        Http::fake([
            'backend.aisensy.com/campaign/t1/api/v2' => Http::response([
                'submitted_message_id' => 'aisensy-message-123',
                'success' => true,
            ]),
        ]);

        $response = $this->post('/register', [
            'mobile' => '9876543210',
            'accept_terms' => '1',
            'flow' => 'register',
        ]);

        $response
            ->assertRedirect('/register')
            ->assertSessionHas('login_mobile', '9876543210')
            ->assertSessionHas('login_otp_request_id', 'aisensy-message-123')
            ->assertSessionHas('login_otp_hash');

        Http::assertSent(fn ($request) => $request->url() === 'https://backend.aisensy.com/campaign/t1/api/v2'
            && $request['apiKey'] === 'test-aisensy-key'
            && $request['campaignName'] === 'otp'
            && $request['destination'] === '919876543210'
            && $request['templateParams'] === ['654321']
            && $request['source'] === 'new-landing-page form'
            && $request['paramsFallbackValue'] === ['FirstName' => 'user']
            && $request['buttons'][0]['parameters'][0]['text'] === '654321'
            && $request['carouselCards'] === []);
    }

    public function test_users_can_login_with_otp_and_fetch_cibil_report(): void
    {
        Http::fake([
            'backend.aisensy.com/campaign/t1/api/v2' => Http::response([
                'submitted_message_id' => 'aisensy-message-123',
                'success' => true,
            ]),
            'api.roopya.money/api/v2/creditBureauCRIF' => Http::response([
                'success' => true,
                'data' => json_encode([
                    'report_id' => 'report-123',
                    'order_id' => 'order-456',
                    'question' => 'What is your oldest active credit account?',
                ]),
            ]),
            'api.roopya.money/api/v2/creditBureauCRIFUserAuth' => Http::response([
                'success' => true,
                'data' => [
                    'credit_score' => 742,
                    'tradelines' => [[
                        'member_name' => 'Example Bank',
                        'account_type' => 'Personal Loan',
                        'sanctioned_amount' => 500000,
                        'current_balance' => 125000,
                        'days_past_due' => 45,
                    ]],
                ],
            ]),
        ]);

        $this->post('/register', [
            'mobile' => '9876543210',
            'accept_terms' => '1',
            'flow' => 'register',
        ]);

        $this->post('/login/verify-otp', [
            'otp' => '654321',
        ])->assertRedirect('/cibil-profile');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'mobile' => '9876543210',
            'terms_version' => '2026-07-13',
        ]);

        $this->post('/cibil-profile', [
            'first_name' => 'Test',
            'middle_name' => '',
            'last_name' => 'Applicant',
            'dob' => '1994-05-12',
            'gender' => 'male',
            'pan_card' => 'abcde1234f',
            'email' => 'test@example.com',
            'income' => 65000,
        ])->assertRedirect('/cibil-profile');

        $this->get('/cibil-profile')
            ->assertOk()
            ->assertSee('What is your oldest active credit account?');

        $this->assertDatabaseHas('crif_reports', [
            'user_id' => auth()->id(),
            'report_id' => 'report-123',
            'order_id' => 'order-456',
            'status' => 'authentication_pending',
        ]);

        $this->post('/cibil-profile/authenticate', [
            'auth_answers' => 'HDFC Bank',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'mobile' => '9876543210',
            'name' => 'Test Applicant',
            'pan_card' => 'ABCDE1234F',
            'email' => 'test@example.com',
            'income' => 65000,
        ]);

        $this->assertDatabaseHas('crif_reports', [
            'user_id' => auth()->id(),
            'status' => 'completed',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.roopya.money/api/v2/creditBureauCRIF'
            && $request->hasHeader('domain_name', 'test-domain')
            && $request->hasHeader('authkey', 'test-crif-key')
            && $request->hasHeader('apicode', 'CBC016')
            && $request['dob'] === '12-05-1994'
            && $request['mobile_number'] === '9876543210'
            && $request['gender'] === 'M'
            && ! isset($request['address'], $request['village'], $request['city'], $request['state'], $request['pincode']));

        Http::assertSent(fn ($request) => $request->url() === 'https://api.roopya.money/api/v2/creditBureauCRIFUserAuth'
            && $request->hasHeader('apicode', 'CBA017')
            && $request['report_id'] === 'report-123'
            && $request['order_id'] === 'order-456'
            && $request['auth_answers'] === 'HDFC Bank');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Your CRIF credit report')
            ->assertSee('Credit bureau summary')
            ->assertSee('Credit accounts')
            ->assertSee('Book consultation — Rs. 99')
            ->assertSee('742')
            ->assertSee('Example Bank')
            ->assertSee('Rs. 125,000')
            ->assertDontSee('HDFC Bank');
    }

    public function test_invalid_otp_is_rejected(): void
    {
        Http::fake([
            'backend.aisensy.com/campaign/t1/api/v2' => Http::response([
                'submitted_message_id' => 'aisensy-message-123',
                'success' => true,
            ]),
        ]);

        $this->post('/register', [
            'mobile' => '9876543210',
            'accept_terms' => '1',
            'flow' => 'register',
        ]);

        $this->post('/login/verify-otp', [
            'otp' => '111111',
        ])->assertSessionHasErrors('otp');

        $this->assertGuest();
    }

    public function test_incomplete_application_reminder_is_sent_once_with_the_customer_name(): void
    {
        config([
            'services.aisensy.incomplete_application_campaign' => 'Incomplete Application Reminder',
            'services.aisensy.incomplete_application_delay_minutes' => 30,
            'services.aisensy.incomplete_application_url' => 'https://settlepe.example/login',
        ]);

        $user = User::factory()->create([
            'name' => 'Kabir',
            'mobile' => '9004542024',
            'role' => User::ROLE_CUSTOMER,
            'cibil_profile_completed_at' => null,
            'created_at' => now()->subHour(),
        ]);

        Http::fake([
            'backend.aisensy.com/campaign/t1/api/v2' => Http::response(['success' => true]),
        ]);

        $this->artisan('aisensy:send-incomplete-application-reminders')
            ->expectsOutput('Sent 1 incomplete application reminder(s).')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => $request['campaignName'] === 'Incomplete Application Reminder'
            && $request['destination'] === '919004542024'
            && $request['userName'] === 'Kabir'
            && $request['templateParams'] === ['$FirstName', 'https://settlepe.example/login']);

        $this->assertNotNull($user->fresh()->incomplete_application_reminded_at);

        $this->artisan('aisensy:send-incomplete-application-reminders')
            ->expectsOutput('Sent 0 incomplete application reminder(s).')
            ->assertSuccessful();

        Http::assertSentCount(1);
    }

    public function test_new_registration_requires_terms_acceptance_before_sending_otp(): void
    {
        Http::fake();

        $this->post('/register', ['mobile' => '9876543210', 'flow' => 'register'])
            ->assertSessionHasErrors('accept_terms');

        Http::assertNothingSent();
        $this->assertFalse(session()->has('login_mobile'));
    }

    public function test_terms_page_is_publicly_available(): void
    {
        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('Terms and Conditions')
            ->assertSee('Sharley Ventures')
            ->assertSee('Customer Acknowledgement');
    }

    public function test_existing_customer_can_login_without_terms_checkbox(): void
    {
        $customer = User::factory()->create([
            'mobile' => '9876543210',
            'role' => User::ROLE_CUSTOMER,
            'cibil_profile_completed_at' => now(),
        ]);
        Http::fake(['backend.aisensy.com/*' => Http::response(['success' => true, 'submitted_message_id' => 'login-message'])]);

        $this->post('/login/send-otp', ['mobile' => $customer->mobile, 'flow' => 'login'])
            ->assertRedirect('/login')
            ->assertSessionHas('auth_flow', 'login');
    }

    public function test_login_and_registration_have_separate_screens(): void
    {
        $this->get('/login')->assertOk()->assertSee('Welcome back')->assertDontSee('I have read and accept');
        $this->get('/register')->assertOk()->assertSee('Create your account')->assertSee('I have read and accept');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
