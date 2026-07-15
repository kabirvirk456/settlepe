@extends('layouts.auth')

@section('title', 'Terms and Conditions | Settle Pe')
@section('panel_width', 'w-full max-w-4xl')

@section('content')
<article class="rounded-xl border border-[#dfe5ec] bg-white px-6 py-8 shadow-sm md:px-10 md:py-10">
    <header class="border-b border-[#e7edf3] pb-7">
        <p class="text-xs font-semibold uppercase tracking-widest text-[#b7862d]">Sharley Ventures · Settle Pe</p>
        <h1 class="mt-3 text-3xl font-semibold text-[#10223f] md:text-4xl">Terms and Conditions</h1>
        <p class="mt-3 text-sm text-[#6b7280]">Effective date: 13 July 2026 · Version 2026-07-13</p>
        <p class="mt-5 text-sm leading-7 text-[#4b5563]">These Terms and Conditions (“Terms”) govern your use of the services provided by Settle Pe, a brand operated by Sharley Ventures (“Company”, “we”, “our”, or “us”). By accessing our website, submitting your information, contacting us, or using any of our services, you acknowledge that you have read, understood, and agreed to be bound by these Terms.</p>
    </header>

    @php
        $sections = [
            ['About Settle Pe', [
                'Settle Pe is a debt settlement assistance platform operated by Sharley Ventures. We assist individuals facing financial hardship by providing professional debt settlement support, including consultation, financial assessment, documentation assistance, negotiation support, and coordination with lenders where authorized by the customer.',
                'Settle Pe is not a bank, Non-Banking Financial Company (NBFC), financial institution, recovery agency, debt collection agency, Asset Reconstruction Company (ARC), or an agent or representative of any lender. Our role is limited to providing professional settlement assistance services to customers.',
            ]],
            ['Nature of Services', [
                'Our services may include assessment of financial hardship, review of loan accounts, debt settlement consultation, preparation of settlement proposals, documentation assistance, communication and negotiation support with lenders where authorized, case management, and coordination with our legal partner where legal assistance is required.',
                'The fees charged by Settle Pe are professional service fees for the services provided and are not linked to any guaranteed settlement outcome.',
            ]],
            ['No Guarantee of Settlement', [
                'Settle Pe does not guarantee settlement of any loan, any reduction in loan amount, waiver of interest, penalties or charges, loan closure, improvement of any credit score, or that any lender will accept a settlement proposal.',
                'The final decision regarding any settlement, settlement amount, repayment terms, or loan closure rests solely with the respective bank, NBFC, or financial institution.',
                'Any settlement amount communicated during consultation is only an estimate based on experience and available information and shall not be considered a promise or guarantee.',
            ]],
            ['Independent Service Provider', [
                'Settle Pe acts independently on behalf of its customers and does not represent any lender, bank, NBFC, recovery agency, or government authority.',
            ]],
            ['Customer Responsibilities', [
                'The Customer agrees to provide true, complete, and accurate information; submit genuine and valid documents; promptly provide additional information reasonably requested; inform Settle Pe of lender communication that may affect the case; and cooperate throughout the settlement process.',
                'The Company shall not be responsible for consequences arising from false, incomplete, misleading, or inaccurate information provided by the Customer.',
            ]],
            ['Professional Fees', [
                'Fees charged by Settle Pe are solely for professional debt settlement assistance, advisory services, documentation support, negotiation assistance, and case management. These fees are not payments towards any outstanding loan and shall not be treated as payments to any lender.',
                'Customers remain responsible for making settlement or loan payments directly to the respective lender unless otherwise instructed by the lender.',
            ]],
            ['Refund Policy', [
                'Unless otherwise agreed in writing or required under applicable law, professional fees paid to Settle Pe are non-refundable once services have commenced.',
            ]],
            ['Legal Services', [
                'Settle Pe itself does not practice law. Where legal notices, litigation, court representation, legal drafting, or other legal services become necessary, such services shall be handled by independent advocates or law firms associated with our legal partner.',
                'Any legal engagement shall be subject to separate professional arrangements between the customer and the respective legal professional where applicable.',
            ]],
            ['No Legal Advice', [
                'Information, guidance, and discussions provided by Settle Pe are intended for general debt settlement assistance and should not be construed as legal advice. Customers are encouraged to seek independent legal advice wherever appropriate.',
            ]],
            ['Credit Score and Credit Information', [
                'By using our services, the Customer expressly authorizes Settle Pe to collect, access, review, and process the Customer’s credit score, credit report, and related financial information, either directly from the Customer or through authorized service providers, solely to understand loan obligations, review liabilities, assess financial hardship, evaluate settlement options, prepare settlement proposals, and provide debt settlement assistance.',
                'Customer credit information is protected using reasonable technical and organizational security measures, including secure storage and encryption where applicable. Settle Pe does not sell, rent, or commercially exploit customer financial information.',
                'Customer information shall only be shared with the Customer’s authorization; with lenders where necessary to provide requested services; with authorized legal partners assisting in the matter; or where disclosure is required by applicable law, regulation, or court order.',
            ]],
            ['Confidentiality', [
                'Settle Pe shall make reasonable efforts to maintain the confidentiality of customer information and shall use such information only for providing requested services and complying with applicable legal obligations.',
            ]],
            ['Communication Consent', [
                'By submitting your information, you consent to receive communications from Settle Pe through telephone calls, SMS, WhatsApp, email, and website notifications. Communications may relate to your enquiry, service updates, documentation, appointment scheduling, settlement progress, or customer support.',
            ]],
            ['Intellectual Property', [
                'All content available on the Settle Pe website, including text, graphics, logos, trademarks, software, documents, and branding, is the property of Sharley Ventures or its licensors and may not be copied, reproduced, distributed, or used without prior written permission.',
            ]],
            ['Limitation of Liability', [
                'To the fullest extent permitted by applicable law, Sharley Ventures and Settle Pe shall not be liable for rejection of settlement proposals; lender decisions; lender recovery or legal proceedings; changes in credit score or credit history; loss of future borrowing eligibility; financial losses arising from lender decisions; or indirect, incidental, or consequential damages.',
                'In any event, the Company’s total liability shall not exceed the professional fees actually paid by the Customer for the relevant services.',
            ]],
            ['Suspension or Termination', [
                'Settle Pe may suspend or terminate services if false information or forged documents are submitted, fraudulent activity is suspected, the Customer engages in abusive or unlawful conduct, professional fees remain unpaid, or these Terms are violated.',
            ]],
            ['Force Majeure', [
                'The Company shall not be responsible for delays or failures caused by events beyond its reasonable control, including natural disasters, government actions, banking disruptions, cyber incidents, strikes, regulatory changes, or failures of third-party service providers.',
            ]],
            ['Governing Law and Jurisdiction', [
                'These Terms shall be governed by and construed in accordance with the laws of India. Subject to Clause 18, courts at Mumbai, Maharashtra, India, shall have exclusive jurisdiction over matters arising out of or relating to these Terms and Settle Pe’s services.',
            ]],
            ['Settlement of Disputes (Arbitration)', [
                'The parties shall first endeavor to resolve disputes amicably through mutual discussions. If a dispute is not resolved within thirty (30) days from written notice by either party, it shall be referred to and finally resolved by arbitration under the Arbitration and Conciliation Act, 1996, as amended.',
                'Arbitration shall be conducted by a sole arbitrator mutually appointed by the parties. If they fail to agree, appointment shall be made under the Act. The seat and venue shall be Mumbai, Maharashtra, India; proceedings shall be in English; and the award shall be final and binding and may be enforced before a court of competent jurisdiction.',
            ]],
            ['Changes to These Terms', [
                'Sharley Ventures reserves the right to amend or update these Terms at any time. Revised Terms become effective upon publication on the Settle Pe website. Continued use after publication constitutes acceptance of the revised Terms.',
            ]],
            ['Contact Information', [
                'Sharley Ventures · Operating Brand: Settle Pe · Website: https://settlepe.in · Email: support@settlepe.in · Phone: 9137696147',
            ]],
        ];
    @endphp

    <div class="mt-8 space-y-8">
        @foreach($sections as $index => [$heading, $paragraphs])
            <section>
                <h2 class="text-lg font-semibold text-[#10223f]">{{ $index + 1 }}. {{ $heading }}</h2>
                <div class="mt-3 space-y-3 text-sm leading-7 text-[#4b5563]">
                    @foreach($paragraphs as $paragraph)<p>{{ $paragraph }}</p>@endforeach
                </div>
            </section>
        @endforeach
    </div>

    <section class="mt-10 rounded-xl border border-[#ead8a8] bg-[#fffaf0] p-6">
        <h2 class="text-lg font-semibold text-[#10223f]">Customer Acknowledgement</h2>
        <p class="mt-3 text-sm leading-7 text-[#4b5563]">By using Settle Pe’s services, I acknowledge that I have read and understood these Terms; Settle Pe provides professional debt settlement assistance only; no settlement amount or approval is guaranteed; all settlement decisions are made by the lender; I authorize Settle Pe to process my credit information solely to provide requested services; my information will be handled confidentially and shared only as necessary or legally required; and legal services, where required, will be handled by Settle Pe’s legal partner or associated advocates.</p>
    </section>

    <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-[#e7edf3] pt-6">
        <a href="{{ route('login') }}" class="rounded-lg bg-[#10223f] px-5 py-3 text-sm font-semibold text-white">Return to registration</a>
        <p class="text-xs text-[#6b7280]">Questions: <a href="mailto:support@settlepe.in" class="font-semibold underline">support@settlepe.in</a></p>
    </div>
</article>
@endsection
