<?php

namespace Tests\Feature;

use App\Models\ConsultationPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConsultationPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.base_url' => 'https://api.razorpay.com/v1',
            'services.razorpay.key_id' => 'rzp_test_example',
            'services.razorpay.key_secret' => 'test-secret',
            'services.razorpay.webhook_secret' => 'webhook-secret',
            'services.razorpay.consultation_amount' => 9900,
            'services.razorpay.currency' => 'INR',
        ]);
    }

    public function test_customer_can_create_a_razorpay_consultation_order(): void
    {
        $user = User::factory()->create(['consultation_fee_paid_at' => null]);
        Http::fake([
            'api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_test123',
                'amount' => 9900,
                'currency' => 'INR',
                'status' => 'created',
            ]),
        ]);

        $this->actingAs($user)
            ->postJson('/consultation/orders')
            ->assertOk()
            ->assertJsonPath('key', 'rzp_test_example')
            ->assertJsonPath('order_id', 'order_test123')
            ->assertJsonPath('amount', 9900);

        $this->assertDatabaseHas('consultation_payments', [
            'user_id' => $user->id,
            'order_id' => 'order_test123',
            'amount' => 9900,
            'currency' => 'INR',
            'status' => 'created',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.razorpay.com/v1/orders'
            && $request['amount'] === 9900
            && $request['currency'] === 'INR'
            && $request['notes']['service'] === 'consultation');
    }

    public function test_captured_payment_is_verified_before_consultation_is_activated(): void
    {
        $user = User::factory()->create(['consultation_fee_paid_at' => null]);
        $payment = $user->consultationPayments()->create([
            'amount' => 9900,
            'currency' => 'INR',
            'receipt' => 'consult-test-verify',
            'order_id' => 'order_test123',
            'status' => 'created',
        ]);
        $paymentId = 'pay_test456';
        $signature = hash_hmac('sha256', $payment->order_id.'|'.$paymentId, 'test-secret');

        Http::fake([
            'api.razorpay.com/v1/payments/'.$paymentId => Http::response([
                'id' => $paymentId,
                'order_id' => $payment->order_id,
                'amount' => 9900,
                'currency' => 'INR',
                'status' => 'captured',
                'captured' => true,
            ]),
        ]);

        $this->actingAs($user)
            ->postJson('/consultation/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_order_id' => $payment->order_id,
                'razorpay_signature' => $signature,
            ])
            ->assertOk()
            ->assertJsonPath('verified', true);

        $this->assertNotNull($user->fresh()->consultation_fee_paid_at);
        $this->assertDatabaseHas('consultation_payments', [
            'id' => $payment->id,
            'payment_id' => $paymentId,
            'status' => 'captured',
        ]);
        $this->assertDatabaseHas('lead_activities', [
            'lead_user_id' => $user->id,
            'event' => 'Consultation paid',
        ]);
    }

    public function test_invalid_checkout_signature_does_not_activate_consultation(): void
    {
        $user = User::factory()->create(['consultation_fee_paid_at' => null]);
        $payment = $user->consultationPayments()->create([
            'amount' => 9900,
            'currency' => 'INR',
            'receipt' => 'consult-test-invalid',
            'order_id' => 'order_test_invalid',
            'status' => 'created',
        ]);
        Http::fake();

        $this->actingAs($user)
            ->postJson('/consultation/verify', [
                'razorpay_payment_id' => 'pay_invalid',
                'razorpay_order_id' => $payment->order_id,
                'razorpay_signature' => str_repeat('0', 64),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Payment verification failed. No consultation was activated.');

        $this->assertNull($user->fresh()->consultation_fee_paid_at);
        Http::assertNothingSent();
    }

    public function test_signed_captured_webhook_recovers_a_missed_browser_callback(): void
    {
        $user = User::factory()->create(['consultation_fee_paid_at' => null]);
        $payment = $user->consultationPayments()->create([
            'amount' => 9900,
            'currency' => 'INR',
            'receipt' => 'consult-test-webhook',
            'order_id' => 'order_webhook123',
            'status' => 'created',
        ]);
        $payload = json_encode([
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => [
                'id' => 'pay_webhook456',
                'order_id' => $payment->order_id,
                'amount' => 9900,
                'currency' => 'INR',
                'status' => 'captured',
                'captured' => true,
            ]]],
        ], JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $payload, 'webhook-secret');

        $this->call(
            'POST',
            '/payments/razorpay/webhook',
            server: ['HTTP_X_RAZORPAY_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            content: $payload,
        )->assertOk()->assertJsonPath('received', true);

        $this->assertNotNull($user->fresh()->consultation_fee_paid_at);
        $this->assertSame('captured', ConsultationPayment::find($payment->id)->status);
    }
}
