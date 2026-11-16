<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#064e3b">
    <title>Masuk - SIPAKAN BKSDA Sulteng</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 font-sans text-slate-900 antialiased selection:bg-emerald-200 selection:text-emerald-950">
    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
        <div class="pointer-events-none absolute -left-28 -top-32 h-80 w-80 rounded-full border border-emerald-200/10"></div>
        <div class="pointer-events-none absolute -left-12 -top-16 h-48 w-48 rounded-full border border-emerald-200/10"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-32 h-[28rem] w-[28rem] rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute right-8 top-1/4 h-40 w-40 rounded-full bg-green-300/10 blur-3xl"></div>

        <div class="relative z-10 w-full max-w-md">
            <section class="rounded-2xl border border-white/60 bg-white/95 p-6 shadow-2xl shadow-black/25 backdrop-blur-md sm:p-8" aria-labelledby="login-title">
                <header class="mb-8 flex items-center gap-3.5">
                    <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-12 w-12 shrink-0 rounded-xl bg-white object-contain p-1 shadow-lg shadow-emerald-950/20 ring-1 ring-slate-200">
                    <div class="min-w-0">
                        <p class="text-base font-bold tracking-tight text-slate-900">SIPAKAN <span class="text-emerald-800">BKSDA Sulteng</span></p>
                        <p class="mt-0.5 text-xs text-slate-500">Portal Petugas &amp; Admin Lapangan</p>
                    </div>
                </header>

                <div class="mb-7">
                    <p class="text-sm font-semibold text-emerald-800">Selamat datang</p>
                    <h1 id="login-title" class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900">Masuk ke akun</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email dan kata sandi untuk melanjutkan.</p>
                </div>

                @if(session('status'))
                    <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div id="login-errors" role="alert" aria-live="polite" class="mb-5 flex gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.5a.75.75 0 0 0-1.5 0v4a.75.75 0 0 0 1.5 0v-4ZM10 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-semibold">Periksa kembali informasi masuk Anda.</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5 text-rose-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form id="login-form" action="{{ route('login.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="m3.5 7 8.5 6 8.5-6"/>
                                </svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus placeholder="nama@contoh.com" @if($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-12 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                        </div>
                        @if($errors->has('email'))
                            <p id="email-error" class="mt-2 text-xs font-medium text-rose-700">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Kata sandi</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                                    <path d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/>
                                </svg>
                            </span>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi" @if($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-12 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                        </div>
                        @if($errors->has('password'))
                            <p id="password-error" class="mt-2 text-xs font-medium text-rose-700">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <label for="remember" class="inline-flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input id="remember" type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-2 focus:ring-emerald-600 focus:ring-offset-1">
                        Ingat saya
                    </label>

                    <button id="login-button" type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-800 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-900 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-emerald-600/25 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-80">
                        <svg id="login-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"/>
                        </svg>
                        <span id="login-button-text">Masuk</span>
                        <svg id="login-arrow" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.72 5.53a.75.75 0 1 1 1.06-1.06l5 5a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06-1.06l3.72-3.72H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-slate-600">
                    Petugas baru?
                    <a href="{{ route('register') }}" class="font-semibold text-emerald-800 underline-offset-4 hover:text-emerald-950 hover:underline">Daftar di sini</a>
                </p>

                <p class="mt-6 text-center text-xs leading-5 text-slate-400">
                    Akses portal ini hanya untuk pengguna yang berwenang.
                </p>
            </section>

            <footer class="mt-5 flex justify-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-medium tracking-wide text-emerald-50/85 backdrop-blur">
                    <svg class="h-3.5 w-3.5 text-emerald-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 1.5a6.5 6.5 0 0 0-6.5 6.5c0 4.8 6.5 10.5 6.5 10.5s6.5-5.7 6.5-10.5A6.5 6.5 0 0 0 10 1.5Zm0 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" clip-rule="evenodd"/>
                    </svg>
                    Palu, Sulteng - BKSDA
                </span>
            </footer>
        </div>
    </main>

    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            const button = document.getElementById('login-button');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            document.getElementById('login-spinner').classList.remove('hidden');
            document.getElementById('login-arrow').classList.add('hidden');
            document.getElementById('login-button-text').textContent = 'Memproses...';
        });
    </script>
</body>
</html>
