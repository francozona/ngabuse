<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NiRA — Admin Login</title>
  <link rel="icon" href="/logo.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            nira: {
              green:  '#179e4f',
              dark:   '#0d6b35',
              mid:    '#12833f',
              light:  '#e8f7ee',
              xlight: '#f3fbf6',
              muted:  '#a8d5bc',
            }
          },
          fontFamily: {
            display: ['Syne',   'sans-serif'],
            body:    ['DM Sans','sans-serif'],
          },
          boxShadow: {
            green: '0 8px 32px rgba(23,158,79,0.2)',
          }
        }
      }
    }
  </script>
</head>
<body class="min-h-screen bg-[#07130c] text-white overflow-hidden">

<div class="flex min-h-screen">

    <!-- LEFT PANEL -->
    <div class="hidden lg:flex relative w-1/2 overflow-hidden bg-gradient-to-br from-[#009343] via-[#0f2b1b] to-[#009343]">

        <!-- Glow -->
        <div class="absolute top-[-120px] left-[-120px] w-[420px] h-[420px] rounded-full bg-green-500/20 blur-3xl"></div>
        <div class="absolute bottom-[-150px] right-[-120px] w-[420px] h-[420px] rounded-full bg-emerald-400/10 blur-3xl"></div>

        <!-- Grid -->
        <div class="absolute inset-0 opacity-[0.04]"
             style="background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px),
                                    linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px);
                    background-size: 40px 40px;">
        </div>

        <div class="relative z-10 flex flex-col justify-between p-16 w-full">

            <div>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white backdrop-blur border border-white/10 flex items-center justify-center">
                        <img src="/logo.png" class="w-10 h-10 object-contain">
                    </div>

                    <div>
                        <h1 class="font-display text-3xl font-bold tracking-tight">
                            .NG DNS Abuse
                        </h1>

                        <p class="text-green-100/60 text-sm mt-1">
                            Administrative Control Center
                        </p>
                    </div>
                </div>

                <div class="mt-20 max-w-xl">
                    <h2 class="text-5xl leading-tight font-display font-bold tracking-tight">
                        Secure infrastructure for domain abuse operations.
                    </h2>

                    <p class="mt-6 text-lg text-green-50/60 leading-relaxed">
                        Monitor reports, coordinate enforcement workflows,
                        manage escalations, and oversee abuse response activities
                        across the registry ecosystem.
                    </p>
                </div>
            </div>

             
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="flex-1 flex items-center justify-center px-6 py-10  bg-white">

        <div class="w-full max-w-md">

            <!-- Mobile Brand -->
            <div class="lg:hidden mb-10 text-center">
                <img src="/logo.png" class="w-16 h-16 mx-auto mb-4">
                <h1 class="text-3xl font-display font-bold">
                    NiRA Admin
                </h1>
            </div>

            <!-- Login Card -->
            <div class="rounded-3xl border  bg-[#000000]/[1] border-white/10 
                        backdrop-blur-xl shadow-2xl p-8">

                <div class="mb-8">
                    <p class="text-green-400 text-sm font-semibold tracking-wide uppercase">
                        Restricted Access
                    </p>

                    <h2 class="mt-3 text-3xl font-display font-bold tracking-tight">
                        Sign in
                    </h2>

                    <p class="mt-2 text-sm text-white/50">
                        Use your administrator credentials to continue.
                    </p>
                </div>
                    <?php if (!empty($errors)): ?>
                        <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-700 text-sm space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <p class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <?= esc($error) ?>
                            </p>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <form action="/admin/login" method="post" class="space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-sm text-white/60 mb-2">
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="admin@nira.gov.ng"
                            required
                            class="w-full h-12 rounded-2xl bg-white/[0.04]
                                   border border-white/10 px-4 text-white
                                   placeholder:text-white/25
                                   focus:outline-none focus:ring-2
                                   focus:ring-green-500/40
                                   focus:border-green-500/30 transition"
                        >
                    </div>

                    <div>
                        <label class="block text-sm text-white/60 mb-2">
                            Password
                        </label>

                           <div class="relative">
                            <input
                            type="password"
                            name="password"
                            id="passwordInput"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            class="w-full h-12 rounded-2xl bg-white/[0.04]
                                   border border-white/10 px-4 text-white
                                   placeholder:text-white/25
                                   focus:outline-none focus:ring-2
                                   focus:ring-green-500/40
                                   focus:border-green-500/30 transition"
                            >
                            <!-- Toggle visibility -->
                            <button
                            type="button"
                            onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition"
                            tabindex="-1"
                            >
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full h-12 rounded-2xl bg-green-500
                               hover:bg-green-400 active:scale-[0.99]
                               font-semibold transition-all"
                    >
                        Access Dashboard
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
<script>
    function togglePassword() {
      const input   = document.getElementById('passwordInput');
      const icon    = document.getElementById('eyeIcon');
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      icon.innerHTML = visible
        ? `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
           <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
        : `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M6.938 6.938A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-1.479 2.86M6.938 6.938L3 3m3.938 3.938l10.124 10.124"/>`;
    }
  </script>

</body>
 
</html>