<div x-data="{ openModal: false }"></div>
<!-- ================= NAVBAR ================= -->
<header class="fixed top-0 left-0 w-full z-50 bg-[#071b34]/95 backdrop-blur-md border-b border-white/10">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<div class="w-full px-4 md:max-w-7xl md:mx-auto md:px-6">

    <div class="flex items-center justify-between h-[70px]">

        <!-- LOGO -->
        <a href="#home" class="text-white text-lg font-semibold tracking-wide">
            SETTLE<span class="text-[#f4c877]">PE</span>
        </a>

        <!-- DESKTOP MENU -->
        <nav class="hidden md:flex items-center gap-6 text-[13px] text-gray-300">

            <a href="#home" class="hover:text-white transition">Home</a>
            <a href="#services" class="hover:text-white transition">Services</a>
            <a href="#how-it-works" class="hover:text-white transition">How It Works</a>
            <a href="#faq" class="hover:text-white transition">FAQs</a>
            <a href="#contact" class="hover:text-white transition">Contact</a>

        </nav>

        <!-- CTA BUTTON -->
        <div class="hidden md:flex items-center gap-3">

            <a href="#calculator"
               class="bg-[#f4c877] text-[#07142b] px-4 py-2 rounded-md text-[13px] font-medium hover:opacity-90">
               Check Settlement
            </a>

            <a href="https://wa.me/919876543210"
               class="bg-green-500 text-white px-4 py-2 rounded-md text-[13px] font-medium flex items-center gap-2">
               <span class="w-2 h-2 bg-white rounded-full"></span>
               WhatsApp
            </a>

        </div>

        <!-- MOBILE MENU BUTTON -->
        <button @click="open = !open" class="md:hidden text-white text-2xl">
            ☰
        </button>

    </div>

</div>

<!-- MOBILE MENU -->
<div x-data="{open:false}" x-show="open" x-transition
     class="md:hidden bg-[#071b34] px-6 pb-6 space-y-4 text-[14px] text-gray-300">

    <a href="#home" class="block">Home</a>
    <a href="#services" class="block">Services</a>
    <a href="#how-it-works" class="block">How It Works</a>
    <a href="#faq" class="block">FAQs</a>
    <a href="#contact" class="block">Contact</a>

    <a href="#calculator"
       class="block bg-[#f4c877] text-[#07142b] px-4 py-2 rounded-md text-center">
       Check Settlement
    </a>

</div>

</header>
<head>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
<meta charset="UTF-8">
<title>Law Matics</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Inter', sans-serif;
    background: #f5f7fb;
}
.law-range {
    -webkit-appearance: none;
    appearance: none;
    height: 4px;
    border-radius: 999px;
    background: linear-gradient(to right, #07142b 0%, #07142b 25%, #cbd5e1 25%, #cbd5e1 100%);
    outline: none;
}

.law-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 15px;
    height: 15px;
    background: #f4c877;
    border: 1.5px solid #07142b;
    border-radius: 999px;
    cursor: pointer;
}

.law-range::-moz-range-thumb {
    width: 15px;
    height: 15px;
    background: #f4c877;
    border: 1.5px solid #07142b;
    border-radius: 999px;
    cursor: pointer;
}
html {
    scroll-behavior: smooth;
}
<style>
/* MOBILE ONLY FIX */
@media (max-width: 768px) {

    /* Remove max-width container effect */
    .max-w-7xl,
    .max-w-6xl,
    .max-w-5xl {
        max-width: 100% !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
    }

    /* Reduce section spacing */
    section {
        padding-top: 40px !important;
        padding-bottom: 40px !important;
    }

    /* Reduce gaps */
    .gap-10 { gap: 16px !important; }
    .gap-8 { gap: 14px !important; }
    .gap-6 { gap: 12px !important; }

    /* Fix cards padding */
    .p-6 { padding: 16px !important; }
    .p-8 { padding: 18px !important; }

    /* Fix grid stacking */
    .md\:grid-cols-2,
    .md\:grid-cols-3,
    .md\:grid-cols-4 {
        grid-template-columns: 1fr !important;
    }

    /* Make images tighter */
    img {
        max-width: 100%;
        height: auto;
    }

}
</style>
</style>

</head>
<!-- ================= HERO SECTION ================= -->
<section id="home" class="relative flex justify-center bg-[#07142b] pt-14 md:pt-18">

<!-- CONTAINER -->
<div class="w-full px-4 md:max-w-7xl md:mx-auto md:px-6 py-12 md:py-20">

    <div class="grid md:grid-cols-2 gap-10 items-center">

        <!-- LEFT CONTENT -->
        <div class="text-white">

            <!-- TOP BADGE -->
            <div class="inline-block bg-[#0b2545] text-[#f4c877] text-[12px] px-4 py-2 rounded-full mb-5">
                India’s Trusted Loan Settlement Experts
            </div>

            <!-- HEADING -->
            <h1 class="text-[28px] md:text-[40px] leading-[36px] md:leading-[48px] font-semibold">
                Struggling With Loan Repayments?
                <br>
                We Help You Settle It
                <span class="text-[#f4c877]">Legally & Stress-Free.</span>
            </h1>

            <!-- SUBTEXT -->
            <p class="text-[#cbd5e1] text-[13px] md:text-[14px] mt-4 max-w-[480px]">
                Reduce your total loan amount and settle legally with expert negotiation.
            </p>

            <!-- TRUST POINTS -->
            <div class="flex flex-wrap gap-4 mt-5 text-[12px] text-[#cbd5e1]">
                <span class="flex items-center gap-2">✔ Legal & Secure</span>
                <span class="flex items-center gap-2">✔ Trusted by 10,000+ Clients</span>
                <span class="flex items-center gap-2">✔ 100% Confidential</span>
            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col sm:flex-row gap-3 mt-6">

               <button 
    type="button"
    class="openConsultModal bg-[#f4c877] text-[#07142b] px-6 py-3 rounded-md font-semibold text-[14px]">
    Get Free Consultation
</button>

                <button class="bg-[#22c55e] px-6 py-3 rounded-md text-white text-[14px] flex items-center justify-center gap-2">
                    <span>🟢</span> Chat on WhatsApp
                </button>

            </div>

            <!-- TRUST FOOTER -->
            <div class="flex items-center gap-3 mt-6">

                <div class="flex -space-x-2">
                    <img src="https://i.pravatar.cc/30?img=1" class="rounded-full border border-white">
                    <img src="https://i.pravatar.cc/30?img=2" class="rounded-full border border-white">
                    <img src="https://i.pravatar.cc/30?img=3" class="rounded-full border border-white">
                </div>

                <div class="text-[12px] text-[#cbd5e1]">
                    Trusted by 10,000+ Clients ⭐ 4.9/5
                </div>

            </div>

        </div>

       <div class="relative flex justify-center">

    <!-- IMAGE CONTAINER (IMPORTANT) -->
    <div class="relative w-full>

        <!-- DARK CARD BACKGROUND -->
        <div class="absolute inset-0 bg-[#0b1f3a] rounded-2xl"></div>

        <!-- IMAGE -->
        <img 
            src="{{ asset('images/hero.jpg') }}"
            class="relative rounded-2xl w-full h-[420px] object-cover shadow-2xl"
        >

        <!-- DARK OVERLAY (GRADIENT LIKE DESIGN) -->
        <div class="absolute inset-0 rounded-2xl bg-gradient-to-t from-[#07142b]/80 via-transparent to-transparent"></div>

    </div>

    <!-- FLOATING TAGS -->
    <div class="hidden md:block absolute right-[-0px] top-[90px] space-y-3">

        <div class="bg-white text-sm px-4 py-2 rounded-lg shadow">
            Harassment Calls?
        </div>

        <div class="bg-white text-sm px-4 py-2 rounded-lg shadow">
            High Interest?
        </div>

        <div class="bg-white text-sm px-4 py-2 rounded-lg shadow">
            Legal Notices?
        </div>

    </div>

    <!-- BOTTOM CARD -->
    <div class="hidden md:block absolute bottom-[-20px] left-[40px] bg-white px-5 py-3 rounded-xl shadow-lg text-sm">
        ✔ We Handle Everything.<br>
        Focus on Your Future.
    </div>

</div>

</div>
</section>

<!-- ================= CALCULATOR EXACT COMPACT ================= -->
<section id="calculator"
 class="bg-[#f5f7fb] pt-16 pb-10">
    <div class="w-full px-4 md:max-w-7xl md:mx-auto md:px-6">

        <div class="bg-white rounded-[18px] border border-[#e5e7eb] shadow-sm px-8 py-6 -mt-6">

            <div class="text-center mb-6">
                <h2 class="text-[20px] font-semibold text-[#0f172a]">
                    Check Your Settlement in 30 Seconds
                </h2>
                <p class="text-[12px] text-[#6b7280] mt-1">
                    Get an estimate of how much you can save.
                </p>
            </div>

            <div class="grid md:grid-cols-[1.2fr_1fr] gap-8 items-start">

                <!-- LEFT FORM -->
                <div class="grid grid-cols-2 gap-x-10 gap-y-5">

                    <!-- Loan amount -->
                    <div>
                        <label class="text-[11px] font-semibold text-[#334155]">Total Loan Amount</label>

                        <input
                            type="text"
                            value="₹ 5,00,000"
                            class="mt-2 w-full h-[34px] border border-[#dfe5ee] rounded-[6px] px-3 text-[12px] font-medium bg-white"
                        >

                        <input type="range" class="law-range mt-5 w-full">

                        <div class="flex justify-between text-[10px] text-[#94a3b8] mt-2">
                            <span>₹ 50,000</span>
                            <span>₹ 50,00,000</span>
                        </div>
                    </div>

                    <!-- Loan type -->
                    <div>
                        <label class="text-[11px] font-semibold text-[#334155]">Loan Type</label>
                        <select class="mt-2 w-full h-[34px] border border-[#dfe5ee] rounded-[6px] px-3 text-[12px] bg-white">
                            <option>Personal Loan</option>
                        </select>

                        <label class="block text-[11px] font-semibold text-[#334155] mt-5">Monthly Income</label>
                        <select class="mt-2 w-full h-[34px] border border-[#dfe5ee] rounded-[6px] px-3 text-[12px] bg-white">
                            <option>₹ 25,000 - ₹ 50,000</option>
                        </select>
                    </div>

                </div>

                <!-- RIGHT RESULT -->
                <div class="border border-[#dfe5ee] rounded-[12px] bg-[#fbfcfe] px-8 py-5 text-center min-h-[190px]">

                    <p class="text-[12px] font-semibold text-[#475569] mb-2">
                        Estimated You Can Save
                    </p>

                    <div class="relative w-[82px] h-[82px] mx-auto">
                        <svg class="w-full h-full -rotate-90">
                            <circle cx="41" cy="41" r="33" stroke="#e5e7eb" stroke-width="7" fill="none"/>
                            <circle cx="41" cy="41" r="33" stroke="#16a34a" stroke-width="7" fill="none"
                                    stroke-dasharray="207" stroke-dashoffset="114" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center text-[18px] font-bold text-[#0f172a]">
                            45%
                        </div>
                    </div>

                    <p class="text-[11px] text-[#64748b] mt-2">You Could Save</p>

                    <h3 class="text-[22px] leading-tight font-bold text-[#16a34a]">
                        ₹ 2,75,000
                    </h3>

                    <button class="openConsultModal bg-[#f4c877] px-6 py-3 rounded-md">
    Get Free Consultation
</button>

                </div>

            </div>

        </div>

    </div>
</section>
<!-- ================= PROBLEM vs SOLUTION ================= -->
<section class="bg-[#f5f7fb] py-16">

<div class="max-w-6xl mx-auto px-6">

    <!-- TITLE -->
    <div class="text-center mb-10">
        <h2 class="text-[22px] font-semibold text-[#111827]">
            The Problem Borrowers Face
        </h2>
        <p class="text-[13px] text-[#6b7280] mt-1">
            You are not alone. We understand what you are going through.
        </p>
    </div>

    <!-- GRID -->
    <div class="relative grid md:grid-cols-2 gap-8">

        <!-- CENTER ARROW -->
        <div class="hidden md:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
            <div class="w-[42px] h-[42px] bg-white border border-[#e5e7eb] rounded-full shadow flex items-center justify-center">
                →
            </div>
        </div>

        <!-- LEFT CARD -->
        <div class="h-[420px] bg-gradient-to-b from-[#fff5f5] to-white border border-[#f3caca] rounded-2xl flex flex-col">

            <!-- CONTENT -->
            <div class="p-6 flex-1">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[14px] font-semibold text-[#dc2626]">What You Face</h3>
                    <div class="w-[28px] h-[28px] bg-[#dc2626] text-white rounded-full flex items-center justify-center text-sm">✕</div>
                </div>

                <div class="space-y-4 text-[13px] text-[#374151]">

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-red-500 mt-[2px]">●</span>
                        <p>High interest rates and increasing penalties</p>
                    </div>

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-red-500 mt-[2px]">●</span>
                        <p>Harassment calls and threatening recovery tactics</p>
                    </div>

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-red-500 mt-[2px]">●</span>
                        <p>Legal notices and lack of legal awareness</p>
                    </div>

                    <div class="flex gap-3">
                        <span class="text-red-500 mt-[2px]">●</span>
                        <p>No proper guidance or structured negotiation channel</p>
                    </div>

                </div>

            </div>

            <!-- IMAGE AREA (THIS IS THE FIX) -->
            <div class="flex justify-center items-end h-[140px]">
                <img src="{{ asset('images/stress-man.png') }}" class="h-[240px] object-contain">
            </div>

        </div>

        <!-- RIGHT CARD -->
        <div class="h-[420px] bg-gradient-to-b from-[#f3fff7] to-white border border-[#bde5c8] rounded-2xl flex flex-col">

            <!-- CONTENT -->
            <div class="p-6 flex-1">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-[14px] font-semibold text-[#15803d]">Our Solution</h3>
                    <div class="w-[28px] h-[28px] bg-[#16a34a] text-white rounded-full flex items-center justify-center text-sm">✓</div>
                </div>

                <div class="space-y-4 text-[13px] text-[#374151]">

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-green-600 mt-[2px]">✔</span>
                        <p>We negotiate legally to reduce your outstanding</p>
                    </div>

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-green-600 mt-[2px]">✔</span>
                        <p>We handle all harassment and legal notices</p>
                    </div>

                    <div class="flex gap-3 border-b border-dashed pb-3">
                        <span class="text-green-600 mt-[2px]">✔</span>
                        <p>We protect your rights and provide legal support</p>
                    </div>

                    <div class="flex gap-3">
                        <span class="text-green-600 mt-[2px]">✔</span>
                        <p>We provide a structured and transparent settlement process</p>
                    </div>

                </div>

            </div>

            <!-- IMAGE AREA -->
            <div class="flex justify-center items-end h-[140px]">
                <img src="{{ asset('images/happy-man.png') }}" class="h-[240px] object-contain">
            </div>

        </div>

    </div>

    <!-- CTA -->
    <div class="mt-10 bg-[#e9eef5] rounded-xl flex flex-col md:flex-row items-center justify-between px-6 py-4">

        <p class="text-[13px] text-[#334155]">
            Still facing these issues? Let our experts handle it for you.
        </p>

        <button class="mt-3 md:mt-0 bg-[#f4c877] text-[#07142b] px-5 py-2 rounded-md text-sm font-medium">
            Talk to Our Legal Expert
        </button>

    </div>

</div>
</section>
<!-- ================= SERVICES + HOW IT WORKS ================= -->
<section id="services" class="bg-[#f5f7fb] py-16">

<div class="max-w-6xl mx-auto px-6">

    <!-- ================= OUR CORE SERVICES ================= -->
    <div class="text-center mb-10">
        <h2 class="text-[22px] font-semibold text-[#111827]">
            Our Core Services
        </h2>
        <p class="text-[13px] text-[#6b7280] mt-1">
            Comprehensive solutions for all your loan settlement needs.
        </p>
    </div>

    <!-- CARDS -->
    <div class="grid md:grid-cols-4 gap-5">

        <!-- CARD 1 -->
        <div class="bg-white border border-[#e5e7eb] rounded-xl p-5 text-center shadow-sm">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl">
                💼
            </div>
            <h3 class="text-[14px] font-semibold text-[#111827] mb-2">Loan Settlement</h3>
            <ul class="text-[12px] text-[#6b7280] space-y-1 mb-3">
                <li>• Negotiation with banks & NBFCs</li>
                <li>• Reduce outstanding loan</li>
                <li>• Structured repayment plans</li>
            </ul>
            <button class="text-[12px] text-[#111827] border border-[#e5e7eb] px-3 py-1 rounded-md hover:bg-gray-50">
                Learn More
            </button>
        </div>

        <!-- CARD 2 -->
        <div class="bg-white border border-[#e5e7eb] rounded-xl p-5 text-center shadow-sm">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl">
                ⚖️
            </div>
            <h3 class="text-[14px] font-semibold text-[#111827] mb-2">Legal Assistance</h3>
            <ul class="text-[12px] text-[#6b7280] space-y-1 mb-3">
                <li>• Handling recovery harassment</li>
                <li>• Legal notice management</li>
                <li>• Guidance on borrower rights</li>
            </ul>
            <button class="text-[12px] text-[#111827] border border-[#e5e7eb] px-3 py-1 rounded-md hover:bg-gray-50">
                Learn More
            </button>
        </div>

        <!-- CARD 3 -->
        <div class="bg-white border border-[#e5e7eb] rounded-xl p-5 text-center shadow-sm">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xl">
                📊
            </div>
            <h3 class="text-[14px] font-semibold text-[#111827] mb-2">Debt Management</h3>
            <ul class="text-[12px] text-[#6b7280] space-y-1 mb-3">
                <li>• Financial assessment</li>
                <li>• Debt restructuring</li>
                <li>• Credit recovery planning</li>
            </ul>
            <button class="text-[12px] text-[#111827] border border-[#e5e7eb] px-3 py-1 rounded-md hover:bg-gray-50">
                Learn More
            </button>
        </div>

        <!-- CARD 4 -->
        <div class="bg-white border border-[#e5e7eb] rounded-xl p-5 text-center shadow-sm">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl">
                📂
            </div>
            <h3 class="text-[14px] font-semibold text-[#111827] mb-2">Case Handling</h3>
            <ul class="text-[12px] text-[#6b7280] space-y-1 mb-3">
                <li>• Dedicated case manager</li>
                <li>• Timeline tracking</li>
                <li>• Documentation follow-ups</li>
            </ul>
            <button class="text-[12px] text-[#111827] border border-[#e5e7eb] px-3 py-1 rounded-md hover:bg-gray-50">
                Learn More
            </button>
        </div>

    </div>

    <!-- ================= HOW IT WORKS ================= -->
    <section id="how-it-works" class="py-16">
        <div class="text-center mt-16 mb-10">
            <h2 class="text-[22px] font-semibold text-[#111827]">
                How It Works
            </h2>
            <p class="text-[13px] text-[#6b7280] mt-1">
                Our proven 5-step process to help you get debt relief legally.
            </p>
        </div>

        <!-- STEPS -->
    <div class="relative">

        <!-- LINE -->
        <div class="hidden md:block absolute top-8 left-0 w-full border-t border-dashed border-[#cbd5e1]"></div>

        <div class="grid md:grid-cols-5 gap-6 text-center">

            <!-- STEP -->
            <div>
                <div class="relative z-10 w-10 h-10 mx-auto bg-[#0f172a] text-white rounded-full flex items-center justify-center text-xs mb-3">1</div>
                <div class="w-14 h-14 mx-auto border border-[#e5e7eb] rounded-full flex items-center justify-center text-xl mb-3">📄</div>
                <h4 class="text-[13px] font-semibold text-[#111827]">Case Evaluation</h4>
                <p class="text-[12px] text-[#6b7280] mt-1">Analyze loan details and repayment capacity</p>
            </div>

            <div>
                <div class="relative z-10 w-10 h-10 mx-auto bg-[#0f172a] text-white rounded-full flex items-center justify-center text-xs mb-3">2</div>
                <div class="w-14 h-14 mx-auto border border-[#e5e7eb] rounded-full flex items-center justify-center text-xl mb-3">🎯</div>
                <h4 class="text-[13px] font-semibold text-[#111827]">Strategy Planning</h4>
                <p class="text-[12px] text-[#6b7280] mt-1">Customized settlement strategy</p>
            </div>

            <div>
                <div class="relative z-10 w-10 h-10 mx-auto bg-[#0f172a] text-white rounded-full flex items-center justify-center text-xs mb-3">3</div>
                <div class="w-14 h-14 mx-auto border border-[#e5e7eb] rounded-full flex items-center justify-center text-xl mb-3">🤝</div>
                <h4 class="text-[13px] font-semibold text-[#111827]">Negotiation</h4>
                <p class="text-[12px] text-[#6b7280] mt-1">Best possible settlement outcome</p>
            </div>

            <div>
                <div class="relative z-10 w-10 h-10 mx-auto bg-[#0f172a] text-white rounded-full flex items-center justify-center text-xs mb-3">4</div>
                <div class="w-14 h-14 mx-auto border border-[#e5e7eb] rounded-full flex items-center justify-center text-xl mb-3">🛡️</div>
                <h4 class="text-[13px] font-semibold text-[#111827]">Legal Protection</h4>
                <p class="text-[12px] text-[#6b7280] mt-1">Handling notices & harassment</p>
            </div>

            <div>
                <div class="relative z-10 w-10 h-10 mx-auto bg-[#0f172a] text-white rounded-full flex items-center justify-center text-xs mb-3">5</div>
                <div class="w-14 h-14 mx-auto border border-[#e5e7eb] rounded-full flex items-center justify-center text-xl mb-3">✔</div>
                <h4 class="text-[13px] font-semibold text-[#111827]">Closure</h4>
                <p class="text-[12px] text-[#6b7280] mt-1">Financial recovery guidance</p>
            </div>

        </div>

    </div>

    <!-- CTA -->
    <div class="text-center mt-10">
        <button class="bg-[#f4c877] text-[#07142b] px-6 py-3 rounded-md text-sm font-medium shadow hover:opacity-90">
            Start Your Case Today
        </button>
    </div>

</div>
</section>
<!-- ================= TESTIMONIALS ================= -->
<section class="bg-[#f5f7fb] pb-10">

<div class="max-w-6xl mx-auto px-6">

    <!-- DARK BOX -->
    <div class="bg-gradient-to-r from-[#071b34] to-[#0b2545] rounded-2xl px-8 py-10 text-white shadow-lg">

        <!-- TITLE -->
        <div class="text-center mb-8">
            <h2 class="text-[20px] font-semibold">What Our Clients Say</h2>
            <p class="text-[12px] text-gray-300 mt-1">Real people. Real stories. Real relief.</p>
        </div>

        <!-- CARDS -->
        <div class="grid md:grid-cols-3 gap-6">

            <!-- CARD -->
            <div class="bg-white/5 border border-white/10 rounded-xl p-5">
                <p class="text-[13px] text-gray-200 mb-4 leading-relaxed">
                    “Law Matics helped me settle my loan and I saved 50% of my total amount. Highly recommended.”
                </p>
                <p class="text-[12px] text-gray-400 mb-1">— Rakesh Verma</p>
                <div class="text-yellow-400 text-sm">★★★★★</div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-xl p-5">
                <p class="text-[13px] text-gray-200 mb-4 leading-relaxed">
                    “Excellent support throughout the process. They handled harassment calls and legal notices.”
                </p>
                <p class="text-[12px] text-gray-400 mb-1">— Priya Sharma</p>
                <div class="text-yellow-400 text-sm">★★★★★</div>
            </div>

            <div class="bg-white/5 border border-white/10 rounded-xl p-5">
                <p class="text-[13px] text-gray-200 mb-4 leading-relaxed">
                    “Very professional team. The settlement process was smooth and transparent.”
                </p>
                <p class="text-[12px] text-gray-400 mb-1">— Amit Kumar</p>
                <div class="text-yellow-400 text-sm">★★★★★</div>
            </div>

        </div>

        <!-- DOTS -->
        <div class="flex justify-center mt-6 gap-2">
            <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
            <div class="w-2 h-2 bg-white/30 rounded-full"></div>
            <div class="w-2 h-2 bg-white/30 rounded-full"></div>
        </div>

    </div>

</div>
</section>


<!-- ================= CTA BANNER ================= -->
<section class="bg-[#f5f7fb] pb-10">

<div class="max-w-6xl mx-auto px-6">

    <div class="bg-gradient-to-r from-[#071b34] to-[#0b2545] rounded-2xl px-8 py-8 flex flex-col md:flex-row items-center justify-between">

        <!-- LEFT -->
        <div class="text-white">
            <h3 class="text-[18px] font-semibold mb-2">
                Don’t Wait. Settle Your Loan Today!
            </h3>
            <p class="text-[13px] text-gray-300 mb-4">
                Every day of delay increases your burden. Let our experts help you get a fresh start.
            </p>

            <!-- BUTTONS -->
            <div class="flex gap-3 flex-wrap">
                <button class="openConsultModal bg-[#f4c877] px-6 py-3 rounded-md">
    Get Free Consultation
</button>

                <button class="bg-green-500 text-white px-5 py-2 rounded-md text-sm font-medium flex items-center gap-2">
                    <span class="w-2 h-2 bg-white rounded-full"></span>
                    Chat on WhatsApp
                </button>
            </div>
        </div>

        <!-- RIGHT ICON -->
        <div class="mt-6 md:mt-0">
            <div class="w-28 h-28 rounded-full border-4 border-yellow-400 flex items-center justify-center">
                <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-white text-2xl">
                    ✓
                </div>
            </div>
        </div>

    </div>

</div>
</section>
<!-- ================= FAQ SECTION ================= -->
<section  id="faq" class="bg-[#f5f7fb] py-16">

<div class="max-w-4xl mx-auto px-6">

    <!-- TITLE -->
    <div class="text-center mb-10">
        <h2 class="text-[22px] font-semibold text-[#111827]">
            Frequently Asked Questions
        </h2>
        <p class="text-[13px] text-[#6b7280] mt-1">
            Everything you need to know about loan settlement.
        </p>
    </div>

    <!-- ACCORDION -->
    <div class="space-y-4">

        <!-- ITEM -->
        <div x-data="{open:false}" class="bg-white border border-[#e5e7eb] rounded-lg">

            <button @click="open=!open"
                class="w-full flex justify-between items-center px-5 py-4 text-left text-[14px] font-medium text-[#111827]">

                Is loan settlement legal in India?
                <span x-text="open ? '-' : '+'" class="text-lg"></span>
            </button>

            <div x-show="open" x-transition
                class="px-5 pb-4 text-[13px] text-[#6b7280]">
                Yes, loan settlement is a legally accepted process where lenders agree to accept a reduced amount as full payment under financial hardship cases.
            </div>

        </div>

        <!-- ITEM -->
        <div x-data="{open:false}" class="bg-white border border-[#e5e7eb] rounded-lg">

            <button @click="open=!open"
                class="w-full flex justify-between items-center px-5 py-4 text-left text-[14px] font-medium text-[#111827]">

                Will settlement affect my CIBIL score?
                <span x-text="open ? '-' : '+'" class="text-lg"></span>
            </button>

            <div x-show="open" x-transition
                class="px-5 pb-4 text-[13px] text-[#6b7280]">
                Yes, settlement may temporarily impact your credit score, but we guide you on rebuilding your credit profile after closure.
            </div>

        </div>

        <!-- ITEM -->
        <div x-data="{open:false}" class="bg-white border border-[#e5e7eb] rounded-lg">

            <button @click="open=!open"
                class="w-full flex justify-between items-center px-5 py-4 text-left text-[14px] font-medium text-[#111827]">

                How much loan reduction can I expect?
                <span x-text="open ? '-' : '+'" class="text-lg"></span>
            </button>

            <div x-show="open" x-transition
                class="px-5 pb-4 text-[13px] text-[#6b7280]">
                It depends on your financial condition, loan type, and overdue period. Typically, reductions can range from 30% to 70%.
            </div>

        </div>

        <!-- ITEM -->
        <div x-data="{open:false}" class="bg-white border border-[#e5e7eb] rounded-lg">

            <button @click="open=!open"
                class="w-full flex justify-between items-center px-5 py-4 text-left text-[14px] font-medium text-[#111827]">

                How long does the process take?
                <span x-text="open ? '-' : '+'" class="text-lg"></span>
            </button>

            <div x-show="open" x-transition
                class="px-5 pb-4 text-[13px] text-[#6b7280]">
                The timeline varies from 30 to 90 days depending on lender response and case complexity.
            </div>

        </div>

    </div>

</div>
<div class="text-center mt-10">
    <p class="text-[13px] text-[#6b7280] mb-3">
        Still have questions?
    </p>
    <button class="bg-[#f4c877] px-6 py-2 rounded-md text-sm font-medium">
        Talk to Our Legal Expert
    </button>
</div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="bg-white border-t border-[#e5e7eb] pt-10 pb-6">

<div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">

    <!-- BRAND -->
    <div>
        <h3 class="text-[16px] font-semibold text-[#111827] mb-2">LAW MATICS</h3>
        <p class="text-[12px] text-[#6b7280]">
            Law Matics is a legal-financial assistance platform committed to helping borrowers achieve debt relief with dignity.
        </p>

        <div class="flex gap-3 mt-4 text-[#6b7280] text-sm">
            <span>🌐</span>
            <span>📘</span>
            <span>▶️</span>
        </div>
    </div>

    <!-- LINKS -->
    <div>
        <h4 class="text-[13px] font-semibold mb-3 text-[#111827]">Quick Links</h4>
       <ul class="text-[12px] text-[#6b7280] space-y-2">
    <li><a href="#home" class="hover:text-[#111827]">Home</a></li>
    <li><a href="#about" class="hover:text-[#111827]">About Us</a></li>
    <li><a href="#services" class="hover:text-[#111827]">Services</a></li>
    <li><a href="#how-it-works" class="hover:text-[#111827]">How It Works</a></li>
    <li><a href="#faq" class="hover:text-[#111827]">FAQs</a></li>
    <li><a href="#contact" class="hover:text-[#111827]">Contact Us</a></li>
</ul>
    </div>

    <!-- SERVICES -->
    <div>
        <h4 class="text-[13px] font-semibold mb-3 text-[#111827]">Our Services</h4>
        <ul class="text-[12px] text-[#6b7280] space-y-2">
            <li>Loan Settlement</li>
            <li>Legal Assistance</li>
            <li>Debt Management Advisory</li>
            <li>Case Handling System</li>
        </ul>
    </div>

    <!-- CONTACT -->
    <div>
        <h4 class="text-[13px] font-semibold mb-3 text-[#111827]">Get in Touch</h4>
        <ul class="text-[12px] text-[#6b7280] space-y-2">
            <li>📞 +91 98765 43210</li>
            <li>✉️ info@lawmatics.com</li>
            <li>📍 123, Legal Street, Connaught Place, New Delhi - 110001</li>
            <li>🕒 Mon - Sat 9:00 AM - 7:00 PM</li>
        </ul>
    </div>

</div>

<!-- BOTTOM -->
<div class="text-center text-[11px] text-[#9ca3af] mt-8">
    © 2024 Law Matics. All Rights Reserved.
</div>

</footer>
<div x-data="{ open: false }" @open-consult.window="open = true">

  <!-- CONSULTATION POPUP -->
<div id="consultModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center px-4">

    <div class="bg-white w-full max-w-md rounded-xl p-6 shadow-xl relative">

        <button 
            type="button"
            id="closeConsultModal"
            class="absolute top-4 right-4 text-2xl text-gray-500">
            ×
        </button>

        <h2 class="text-[20px] font-semibold text-[#07142b] mb-2">
            Get Free Consultation
        </h2>

        <p class="text-[13px] text-gray-500 mb-5">
            Share your details and our expert will contact you shortly.
        </p>

       <form id="consultForm" class="space-y-3">

    <input name="name" required placeholder="Full Name" class="w-full border p-2 rounded">
    <input name="phone" required placeholder="Phone Number" class="w-full border p-2 rounded">
    <input name="location" required placeholder="Location" class="w-full border p-2 rounded">
    <input name="amount" required placeholder="Loan Amount" class="w-full border p-2 rounded">

    <button type="submit"
        class="w-full bg-black text-white py-2 rounded">
        Submit
    </button>

</form>

<!-- THANK YOU -->
<div id="thankYouBox" class="hidden text-center mt-4">

    <h3 class="text-green-600 text-lg font-semibold mb-2">
        Thank You!
    </h3>

    <p class="text-sm text-gray-600 mb-4">
        Our legal team will contact you shortly on WhatsApp.
    </p>

    <a id="whatsappBtn"
       target="_blank"
       class="inline-block bg-green-500 text-white px-5 py-2 rounded-md">
       Chat on WhatsApp
    </a>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("consultModal");
    const openButtons = document.querySelectorAll(".openConsultModal");
    const closeButton = document.getElementById("closeConsultModal");

    const form = document.getElementById("consultForm");
    const thankYouBox = document.getElementById("thankYouBox");
    const whatsappBtn = document.getElementById("whatsappBtn");

    // OPEN MODAL
    openButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            modal.style.display = "flex";
        });
    });

    // CLOSE MODAL
    closeButton.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // FORM SUBMIT
    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        const name = formData.get("name");
        const phone = formData.get("phone");
        const location = formData.get("location");
        const amount = formData.get("amount");

        // WhatsApp message
        const message = `New Lead:%0AName: ${name}%0APhone: ${phone}%0ALocation: ${location}%0ALoan Amount: ${amount}`;

        const yourNumber = "919876543210"; // 🔴 PUT YOUR NUMBER

        whatsappBtn.href = `https://wa.me/${yourNumber}?text=${message}`;

        // SHOW THANK YOU
        form.style.display = "none";
        thankYouBox.style.display = "block";

        // SEND TO BACKEND
        fetch("/submit-lead", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                name, phone, location, amount
            })
        });

    });

});
</script>
    </div>

</div>

</div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
</div>