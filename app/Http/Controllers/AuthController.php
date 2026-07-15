<?php

namespace App\Http\Controllers;

use App\Models\CrifReport;
use App\Models\User;
use App\Services\AisensyOtpService;
use App\Services\CrifReportService;
use App\Services\CrifReportSummaryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', [
            'otpMobile' => session('auth_flow') === 'login' ? session('login_mobile') : null,
        ]);
    }

    public function sendOtp(Request $request, AisensyOtpService $otpService): RedirectResponse
    {
        $flow = (string) $request->input('flow', $request->routeIs('register.store') ? 'register' : 'login');
        $request->merge([
            'mobile' => $this->normalizeMobile($request->input('mobile')),
            'flow' => $flow,
        ]);

        $validated = $request->validate([
            'mobile' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
            'flow' => ['required', 'in:login,register'],
        ]);

        $existingUser = User::where('mobile', $validated['mobile'])->exists();

        if ($flow === 'login' && ! $existingUser) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => 'No account was found for this mobile number. Please register first.']);
        }

        if ($flow === 'register' && $existingUser) {
            return redirect()
                ->route('login')
                ->withErrors(['mobile' => 'An account already exists for this number. Please log in.']);
        }

        if ($flow === 'register' && ! $request->boolean('accept_terms')) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['accept_terms' => 'You must accept the Terms and Conditions to register.']);
        }

        try {
            $result = $otpService->sendCode(
                '+91'.$validated['mobile'],
                (string) $request->input('name', 'Applicant'),
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => $exception->getMessage()]);
        }

        $request->session()->put('login_mobile', $validated['mobile']);
        $request->session()->put('auth_flow', $flow);
        $request->session()->put('login_otp_request_id', $result['request_id'] ?? null);
        $request->session()->put('login_otp_expires_at', now()->addMinutes((int) config('services.aisensy.ttl_minutes', 10))->timestamp);

        if ($flow === 'register') {
            $request->session()->put('login_terms_acceptance', [
                'version' => (string) config('services.legal.terms_version', '2026-07-13'),
                'ip' => $request->ip(),
                'accepted_at' => now()->toIso8601String(),
            ]);
        }

        if (isset($result['code'])) {
            $request->session()->put('login_otp_hash', $this->otpHash($validated['mobile'], $result['code']));
        }

        return redirect()
            ->route($flow === 'register' ? 'register' : 'login')
            ->with('status', 'OTP sent on WhatsApp to +91 '.$validated['mobile'].'.');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $mobile = $request->session()->get('login_mobile');
        $flow = $request->session()->get('auth_flow', 'login');

        if (! $mobile) {
            return redirect()
                ->route('login')
                ->withErrors(['mobile' => 'Enter your mobile number to receive the OTP.']);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if ($this->otpExpired($request)) {
            return back()
                ->withErrors(['otp' => 'OTP expired. Please request a new OTP.']);
        }

        $isApproved = hash_equals(
            (string) $request->session()->get('login_otp_hash'),
            $this->otpHash($mobile, $validated['otp']),
        );

        if (! $isApproved) {
            return back()
                ->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }

        $user = User::where('mobile', $mobile)->first();

        if (! $user && $flow !== 'register') {
            return redirect()->route('register')->withErrors(['mobile' => 'Please register this mobile number first.']);
        }

        if (! $user) {
            $user = User::create([
                'name' => 'Applicant',
                'email' => $mobile.'@otp.settlepe.test',
                'password' => Hash::make(Str::random(40)),
                'mobile' => $mobile,
                'role' => User::ROLE_CUSTOMER,
            ]);
        }

        if ($user->wasRecentlyCreated && is_array($termsAcceptance = $request->session()->get('login_terms_acceptance'))) {
            $user->update([
                'terms_accepted_at' => $termsAcceptance['accepted_at'] ?? now(),
                'terms_version' => $termsAcceptance['version'] ?? config('services.legal.terms_version'),
                'terms_accepted_ip' => $termsAcceptance['ip'] ?? $request->ip(),
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->forget(['login_mobile', 'login_otp_request_id', 'login_otp_hash', 'login_otp_expires_at', 'login_terms_acceptance', 'auth_flow']);

        if (! $user->cibil_profile_completed_at) {
            return redirect()
                ->route('cibil.profile')
                ->with('status', 'Mobile verified. Complete your details to fetch the CRIF report.');
        }

        return redirect()->route('dashboard')->with('status', 'Welcome back. Your CRIF report is ready.');
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'otpMobile' => session('auth_flow') === 'register' ? session('login_mobile') : null,
        ]);
    }

    public function register(Request $request, AisensyOtpService $otpService): RedirectResponse
    {
        return $this->sendOtp($request, $otpService);
    }

    private function otpHash(string $mobile, string $otp): string
    {
        return hash_hmac('sha256', $this->normalizeMobile($mobile).'|'.$otp, config('app.key'));
    }

    private function otpExpired(Request $request): bool
    {
        $expiresAt = $request->session()->get('login_otp_expires_at');

        return ! $expiresAt || now()->timestamp > (int) $expiresAt;
    }

    public function showCibilProfile(Request $request): View|RedirectResponse
    {
        if ($request->user()->cibil_profile_completed_at
            && $request->user()->crifReports()->where('status', 'completed')->exists()) {
            return redirect()->route('dashboard');
        }

        return view('auth.cibil-form', [
            'pendingReport' => $request->user()->crifReports()
                ->where('status', 'authentication_pending')
                ->latest()
                ->first(),
        ]);
    }

    public function storeCibilProfile(Request $request, CrifReportService $crif): RedirectResponse
    {
        $request->merge([
            'pan_card' => strtoupper(str_replace(' ', '', (string) $request->input('pan_card'))),
        ]);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'dob' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString(), 'after_or_equal:'.now()->subYears(100)->toDateString()],
            'gender' => ['required', 'in:male,female'],
            'pan_card' => ['required', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'income' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ]);

        $user = $request->user();
        $fullName = collect([$validated['first_name'], $validated['middle_name'] ?? null, $validated['last_name']])
            ->filter()
            ->implode(' ');

        $user->update([
            ...$validated,
            'name' => $fullName,
            'age' => Carbon::parse($validated['dob'])->age,
        ]);

        $report = $user->crifReports()->create(['status' => 'requesting']);

        try {
            $response = $crif->requestReport([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? '',
                'last_name' => $validated['last_name'],
                'dob' => Carbon::parse($validated['dob'])->format('d-m-Y'),
                'pan_number' => $validated['pan_card'],
                'mobile_number' => $user->mobile,
                'email' => $validated['email'],
                'gender' => $validated['gender'] === 'male' ? 'M' : 'F',
                'remark' => 'Customer credit report request '.$user->id,
            ]);
            $report->update(['initial_response' => $response]);

            if ($crif->containsCompletedReport($response)) {
                $this->completeCrifReport($user, $report, $response);

                return redirect()->route('dashboard')->with('status', 'CRIF report fetched successfully.');
            }

            $identifiers = $crif->identifiers($response);

            $report->update([
                ...$identifiers,
                'status' => 'authentication_pending',
                'authentication_prompt' => $crif->authenticationPrompt($response),
                'error_message' => null,
            ]);
        } catch (RuntimeException $exception) {
            $report->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);

            return back()
                ->withInput($request->except('pan_card'))
                ->withErrors(['crif' => $exception->getMessage()]);
        }

        return redirect()
            ->route('cibil.profile')
            ->with('status', 'Details verified. Complete the CRIF authentication question to fetch your report.');
    }

    public function authenticateCrifReport(Request $request, CrifReportService $crif): RedirectResponse
    {
        $validated = $request->validate([
            'auth_answers' => ['required', 'string', 'max:2000'],
        ]);
        $user = $request->user();
        /** @var CrifReport|null $report */
        $report = $user->crifReports()->where('status', 'authentication_pending')->latest()->first();

        if (! $report) {
            return redirect()->route('cibil.profile')->withErrors(['crif' => 'No CRIF authentication request is pending. Please submit your details again.']);
        }

        try {
            $response = $crif->authenticate(
                $report->report_id,
                $report->order_id,
                $validated['auth_answers'],
                'Customer credit report authentication '.$user->id,
            );
        } catch (RuntimeException $exception) {
            $report->update(['error_message' => $exception->getMessage()]);

            return back()->withErrors(['auth_answers' => $exception->getMessage()]);
        }

        $this->completeCrifReport($user, $report, $response);

        return redirect()->route('dashboard')->with('status', 'CRIF report fetched successfully.');
    }

    public function dashboard(Request $request, CrifReportSummaryService $summaryService): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isTeamMember()) {
            return redirect()->route('team.home');
        }

        if (! $user->cibil_profile_completed_at) {
            return redirect()->route('cibil.profile');
        }

        if ($user->service_fee_paid_at) {
            return redirect()->route('portal.dashboard');
        }

        $report = $user->crifReports()->where('status', 'completed')->latest('completed_at')->first();
        $summary = $summaryService->summarize($report?->report_response);
        $accounts = $summary['accounts'];

        $totalPrincipal = array_sum(array_column($accounts, 'principal_amount'));
        $totalRemaining = array_sum(array_column($accounts, 'remaining_amount'));
        $highestDpd = $accounts ? max(array_column($accounts, 'dpd')) : 0;
        $activeAccountsCount = count(array_filter($accounts, fn (array $account) => $account['status'] === 'active'));
        $closedAccountsCount = count(array_filter($accounts, fn (array $account) => $account['status'] === 'closed'));
        $overdueAccountsCount = count(array_filter($accounts, fn (array $account) => $account['status'] === 'overdue'));

        return view('dashboard', [
            'accounts' => $accounts,
            'cibilScore' => $summary['score'],
            'report' => $report,
            'highestDpd' => $highestDpd,
            'totalPrincipal' => $totalPrincipal,
            'totalRemaining' => $totalRemaining,
            'activeAccountsCount' => $activeAccountsCount,
            'closedAccountsCount' => $closedAccountsCount,
            'overdueAccountsCount' => $overdueAccountsCount,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }

    private function normalizeMobile(?string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile ?? '');

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits;
    }

    private function completeCrifReport(User $user, CrifReport $report, array $response): void
    {
        $report->update([
            'status' => 'completed',
            'report_response' => $response,
            'error_message' => null,
            'completed_at' => now(),
        ]);

        $user->update([
            'sales_status' => $user->sales_status ?: 'cibil_fetched',
            'cibil_profile_completed_at' => now(),
        ]);

        $user->leadActivities()->create([
            'event' => 'CRIF fetched',
            'notes' => 'Customer fetched a completed CRIF credit report.',
        ]);
    }
}
