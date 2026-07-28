<x-site-layout
    title="Membership Registration — NBBEU"
    description="Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>NBBEU Membership Registration</h1>
            <p>Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="form mx-auto">
            <a href="{{ route('login') }}" class="google-btn">
                <svg viewBox="0 0 18 18" width="18" height="18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84c-.21 1.13-.84 2.09-1.8 2.73v2.27h2.91c1.7-1.57 2.69-3.87 2.69-6.64z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.91-2.27c-.81.54-1.84.86-3.05.86-2.35 0-4.34-1.58-5.05-3.71H.96v2.34C2.44 15.98 5.48 18 9 18z"/>
                    <path fill="#FBBC05" d="M3.95 10.7c-.18-.54-.28-1.11-.28-1.7s.1-1.16.28-1.7V4.96H.96A8.996 8.996 0 000 9c0 1.45.35 2.83.96 4.04l2.99-2.34z"/>
                    <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.44 2.02.96 4.96l2.99 2.34C4.66 5.16 6.65 3.58 9 3.58z"/>
                </svg>
                <span>Sign up with Google</span>
            </a>
            <p class="form-aside">Already have a Google-linked NBBEU account? Signing up with Google will take you to the login page.</p>

            <div class="form-divider"><span>or register with your details</span></div>
        </div>

        <livewire:registration-wizard />
    </section>
</x-site-layout>
