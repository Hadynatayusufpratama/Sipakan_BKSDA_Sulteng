<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#064e3b">
    <title>Daftar Petugas - SIPAKAN BKSDA Sulteng</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 font-sans text-slate-900 antialiased selection:bg-emerald-200 selection:text-emerald-950">
    <main class="relative isolate flex min-h-screen items-center justify-center overflow-hidden px-4 py-10 sm:px-6">
        <div class="pointer-events-none absolute -left-28 -top-32 h-80 w-80 rounded-full border border-emerald-200/10"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-32 h-[28rem] w-[28rem] rounded-full bg-emerald-400/10 blur-3xl"></div>

        <div class="relative z-10 w-full max-w-md">
            <section class="rounded-2xl border border-white/60 bg-white/95 p-6 shadow-2xl shadow-black/25 backdrop-blur-md sm:p-8" aria-labelledby="register-title">
                <header class="mb-7 flex items-center gap-3.5">
                    <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-12 w-12 shrink-0 rounded-xl bg-white object-contain p-1 shadow-lg ring-1 ring-slate-200">
                    <div class="min-w-0">
                        <p class="text-base font-bold tracking-tight text-slate-900">SIPAKAN <span class="text-emerald-800">BKSDA Sulteng</span></p>
                        <p class="mt-0.5 text-xs text-slate-500">Pendaftaran Petugas Lapangan</p>
                    </div>
                </header>

                <div class="mb-6">
                    <p class="text-sm font-semibold text-emerald-800">Buat akun petugas</p>
                    <h1 id="register-title" class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900">Daftar akun</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Lengkapi data berikut. Akun dapat digunakan setelah disetujui Admin.</p>
                </div>

                @if($errors->any())
                    <div role="alert" aria-live="polite" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                        <p class="font-semibold">Pendaftaran belum dapat diproses.</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-rose-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus placeholder="Nama sesuai identitas" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Alamat email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="nama@contoh.com" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Kata sandi</label>
                        <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Konfirmasi kata sandi</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/15">
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-emerald-800 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-900/20 transition-all duration-200 hover:bg-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-600/25 focus:ring-offset-2">
                        Kirim pendaftaran
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-semibold text-emerald-800 underline-offset-4 hover:text-emerald-950 hover:underline">Masuk di sini</a>
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
</body>
</html>
