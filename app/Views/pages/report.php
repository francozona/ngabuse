<!DOCTYPE html>
<html lang="en" x-data="abuseForm()" x-init="init()">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NiRA — Report DNS Abuse</title>
  <link rel="icon" href="logo.png" type="image/png" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            nira: {
              green: '#179e4f',
              dark: '#0d6b35',
              light: '#e8f7ee',
              xlight: '#f3fbf6',
            }
          },
          fontFamily: {
            display: ['Syne', 'sans-serif'],
            body: ['DM Sans', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <style>
    [x-cloak] { display: none !important; }

    body { font-family: 'DM Sans', sans-serif; }

    .bg-grid {
      background-image:
        linear-gradient(rgba(23,158,79,0.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(23,158,79,0.06) 1px, transparent 1px);
      background-size: 32px 32px;
    }

    .noise::after {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
    }

    .step-connector {
      position: relative;
    }
    .step-connector::after {
      content: '';
      position: absolute;
      top: 50%;
      left: calc(100% + 4px);
      width: calc(100% - 8px);
      height: 2px;
      background: currentColor;
      opacity: 0.2;
      transform: translateY(-50%);
    }

    .card-glass {
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(12px);
      box-shadow: 0 0 0 1px rgba(23,158,79,0.12), 0 24px 60px rgba(0,0,0,0.07);
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(18px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .anim-fadein { animation: fadeUp 0.38s ease both; }

    @keyframes pulse-ring {
      0%   { box-shadow: 0 0 0 0 rgba(23,158,79,0.4); }
      70%  { box-shadow: 0 0 0 10px rgba(23,158,79,0); }
      100% { box-shadow: 0 0 0 0 rgba(23,158,79,0); }
    }
    .pulse { animation: pulse-ring 1.8s infinite; }

    @keyframes checkDraw {
      from { stroke-dashoffset: 60; }
      to   { stroke-dashoffset: 0; }
    }
    .check-anim { animation: checkDraw 0.5s 0.3s ease both; }

    .file-drop.dragging {
      border-color: #179e4f;
      background: #e8f7ee;
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #179e4f;
      box-shadow: 0 0 0 3px rgba(23,158,79,0.15);
    }

    .floating-label-group { position: relative; }
    .floating-label-group input,
    .floating-label-group select,
    .floating-label-group textarea {
      padding-top: 1.4rem;
      padding-bottom: 0.5rem;
    }
    .floating-label-group label {
      position: absolute;
      top: 50%;
      left: 14px;
      transform: translateY(-50%);
      font-size: 0.875rem;
      color: #9ca3af;
      pointer-events: none;
      transition: all 0.18s ease;
    }
    .floating-label-group textarea ~ label { top: 20px; transform: none; }
    .floating-label-group input:focus ~ label,
    .floating-label-group input:not(:placeholder-shown) ~ label,
    .floating-label-group select:focus ~ label,
    .floating-label-group select:not([value=""]) ~ label,
    .floating-label-group textarea:focus ~ label,
    .floating-label-group textarea:not(:placeholder-shown) ~ label {
      top: 8px;
      transform: none;
      font-size: 0.7rem;
      color: #179e4f;
      font-weight: 600;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .progress-bar-inner {
      transition: width 0.5s cubic-bezier(0.4,0,0.2,1);
    }

    .chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 999px;
      background: #e8f7ee;
      color: #0d6b35;
      font-size: 0.75rem;
      font-weight: 600;
    }
    .chip button { line-height: 1; cursor: pointer; }

    select option { color: #111; }
    nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 6vw;
  height:68px;
  background:rgba(255,255,255,0.9);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--line);
}

.nav-logo{display:flex;align-items:center;gap:10px}
.nav-logo-mark{
  width:40px;height:40px;
  background:white;
  border-radius:8px;
  display:flex;align-items:center;justify-content:center;
}
.nav-logo-mark svg{width:18px;height:18px;fill:#fff}
.nav-logo-text{font-family:'Syne',sans-serif;font-weight:800;font-size:15px;letter-spacing:-0.03em;color:var(--ink)}
.nav-logo-sub{font-size:10px;color:#565151;letter-spacing:0.04em;margin-top:-1px}

.nav-links{display:flex;align-items:center;gap:32px}
.nav-links a{font-size:13px;font-weight:500;color:var(--muted);transition:color .2s}
.nav-links a:hover{color:var(--g)}

.nav-cta{
  background:#179e4f;color:#fff;
  font-family:'Syne',sans-serif;font-weight:700;font-size:13px;
  padding:10px 22px;border-radius:8px;
  transition:background .2s,transform .15s;
  display:inline-flex;align-items:center;gap:6px;
}
.nav-cta:hover{background:#179e4f;transform:translateY(-1px)}
.nav-cta svg{width:14px;height:14px}

  </style>
</head>
<body class="min-h-screen bg-slate-50 relative overflow-x-hidden">

 

  <nav class="relative">
  <a href="/" class="nav-logo">
    <div class="nav-logo-mark">
        <img class="" src="/logo.png"/> 
    </div>
    <div>
      <div class="nav-logo-text font-display" style="color:#179e4f;">.NG DNS ABUSE</div>
    </div>
  </div>
  <div class="nav-links">
     
    <a href="/#dns-abuse">DNS Abuse</a>
    <a href="/#how">How It Works</a>
    <a href="/#faq">FAQ</a>
  </div>
  <a href="/report" class="nav-cta font-display">
    Report DNS Abuse
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
  </a>
</nav>

  <!-- Main -->
  <main class="relative z-10 max-w-2xl mx-auto px-4 py-10">

    <!-- Step indicator -->
    <div class="mb-8" x-show="step < 3">
      <!-- Progress bar -->
      <div class="h-1 bg-nira-green/15 rounded-full mb-6 overflow-hidden">
        <div class="progress-bar-inner h-full bg-nira-green rounded-full"
             :style="`width: ${step === 1 ? '50' : '100'}%`"></div>
      </div>
      <!-- Steps -->
      <div class="flex items-start gap-0">
        <template x-for="(s, i) in steps" :key="i">
          <div class="flex-1 flex flex-col items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-display font-bold transition-all duration-300"
                 :class="step > i + 1
                   ? 'bg-nira-green text-white shadow-md shadow-nira-green/30'
                   : step === i + 1
                     ? 'bg-nira-green text-white shadow-md shadow-nira-green/30 pulse'
                     : 'bg-white text-gray-400 border-2 border-gray-200'">
              <span x-show="step <= i + 1" x-text="i + 1"></span>
              <span x-show="step > i + 1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
              </span>
            </div>
            <p class="mt-2 text-[11px] font-display font-semibold text-center leading-tight"
               :class="step === i + 1 ? 'text-nira-green' : step > i + 1 ? 'text-gray-500' : 'text-gray-300'"
               x-text="s"></p>
          </div>
        </template>
      </div>
    </div>

    <!-- ── STEP 1 ── -->
    <div x-show="step === 1" x-cloak class="anim-fadein">
      <div class="card-glass rounded-2xl p-7 mb-4">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="font-display text-2xl font-800 text-gray-900 leading-tight">Report DNS Abuse</h1>
            <p class="text-sm text-gray-500 mt-0.5">Tell us about the abusive domain</p>
          </div>
        </div>

        <!-- Reporter Section -->
        <div class="mb-6">
          <p class="text-[11px] font-display font-700 text-nira-green uppercase tracking-widest mb-3 flex items-center gap-2">
             Reporter Info
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Name -->
            <div class="floating-label-group">
              <input type="text" x-model="form.name" placeholder=" "
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
                :class="errors.name ? 'border-red-400' : ''">
              <label>Full Name *</label>
              <p x-show="errors.name" class="text-xs text-red-500 mt-1 ml-1" x-text="errors.name"></p>
            </div>
            <!-- Email -->
            <div class="floating-label-group">
              <input type="email" x-model="form.email" placeholder=" "
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
                :class="errors.email ? 'border-red-400' : ''">
              <label>Email Address *</label>
              <p x-show="errors.email" class="text-xs text-red-500 mt-1 ml-1" x-text="errors.email"></p>
            </div>
          </div>
        </div>

        <!-- Domain Section -->
        <div class="mb-6">
          <p class="text-[11px] font-display font-700 text-nira-green uppercase tracking-widest mb-3 flex items-center gap-2">
            Domain Details
          </p>
          <!-- Domain + TLD side by side
          <div class="flex gap-2 mb-3">
            <div class="floating-label-group flex-1">
              <input type="text" x-model="form.domain_name" placeholder=" "
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
                :class="errors.domain_name ? 'border-red-400' : ''">
              <label>Domain Name *</label>
              <p x-show="errors.domain_name" class="text-xs text-red-500 mt-1 ml-1" x-text="errors.domain_name"></p>
            </div>
            <div class="floating-label-group w-36">
              <select x-model="form.tld"
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all appearance-none"
                :class="errors.tld ? 'border-red-400' : ''">
                <option value=""></option>
                <template x-for="t in tlds" :key="t">
                  <option :value="t" x-text="t"></option>
                </template>
              </select>
              <label>TLD *</label>
              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
              </div>
            </div>
          </div> -->
          <!-- Domain preview pill -->
          <div x-show="form.url" class="flex items-center gap-2 mb-3">
            <span class="text-xs text-gray-400">Reporting:</span>
            <span class="chip">
              <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z" clip-rule="evenodd"/></svg>
              <span x-text="form.url"></span>
            </span>
          </div>
          <!-- URL -->
         <div class="floating-label-group mb-3"
            x-data="{
                isValidNgUrl(url) {
                    try {
                        const parsed = new URL(url);
 
                        return parsed.hostname.toLowerCase().includes('.ng');
                    } 
                    catch {
                        return false;
                    }
                }
            }">

            <input
                type="url"
                x-model="form.url"
                placeholder=" "
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
                :class="errors.url ? 'border-red-400' : ''"
                @input="
                    if(form.url && !isValidNgUrl(form.url)) {
                        errors.url = 'Only .ng domains or subdomains are allowed';
                    } else {
                        errors.url = '';
                    }
                "
            >

            <label>Abusive URL * (https://…)</label>

            <p
                x-show="errors.url"
                class="text-xs text-red-500 mt-1 ml-1"
                x-text="errors.url">
            </p>
        </div>
        </div>

        <!-- Abuse Section -->
        <div class="mb-6">
          <p class="text-[11px] flex font-display font-700 text-nira-green uppercase tracking-widest mb-3 flex items-center gap-2">
           Abuse Details &nbsp; <!-- Category badge -->
          <div x-show="form.abuse_category" class="mb-2">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
                 :class="categoryMeta[form.abuse_category]?.bg || 'bg-gray-100 text-gray-700'">
              <span x-text="categoryMeta[form.abuse_category]?.icon"></span>
              <span x-text="categoryMeta[form.abuse_category]?.desc || form.abuse_category"></span>
            </div>
          </div>
          </p>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
            <!-- Date observed -->
            <div class="floating-label-group">
              <input type="date" x-model="form.date_first_observed"
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
                :max="today"
                :class="errors.date_first_observed ? 'border-red-400' : ''">
              <label class="!top-2 !transform-none !text-[10px] !text-nira-green !font-semibold !uppercase !tracking-wide">Date First Observed *</label>
              <p x-show="errors.date_first_observed" class="text-xs text-red-500 mt-1 ml-1" x-text="errors.date_first_observed"></p>
            </div>
            <!-- Category -->
            <div class="floating-label-group">
    
              <select x-model="form.abuse_category"
                  class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all appearance-none"
                  :class="errors.abuse_category ? 'border-red-400' : ''">

                  <option value=""></option>

                  <template x-for="c in categories" :key="c">
                      <option :value="c" x-text="c"></option>
                  </template>
              </select>

              <label>Abuse Category *</label>

              <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                  </svg>
              </div>

              <p x-show="errors.abuse_category"
                class="text-xs text-red-500 mt-1 ml-1"
                x-text="errors.abuse_category">
              </p>
          </div>

          <!-- Show extra input -->
          <div class="floating-label-group mt-3"
              x-show="form.abuse_category === 'Other forms of DNS Abuse'"
              x-transition>

              <input
                  type="text"
                  placeholder=" "
                  x-model="form.other_abuse_category"
                  @input="form.abuse_category = form.other_abuse_category"
                  class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all"
              >

              <label>Specify DNS Abuse Type *</label>
          </div>

         

        
        </div>
          <!-- Description -->
          <div class="floating-label-group mb-3">
            <textarea x-model="form.description" rows="3" placeholder=" "
              class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all resize-none"
              :class="errors.description ? 'border-red-400' : ''"></textarea>
            <label>Description of Abuse Activity *</label>
            <p x-show="errors.description" class="text-xs text-red-500 mt-1 ml-1" x-text="errors.description"></p>
          </div>
        <!-- Optional Registrar Section -->
        <div>
          <button @click="showRegistrar = !showRegistrar"
            class="flex items-center gap-2 text-sm text-gray-400 hover:text-nira-green transition-colors mb-3 font-display">
            <svg class="w-4 h-4 transition-transform duration-200" :class="showRegistrar ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            Registrar notification (optional)
          </button>
          <div x-show="showRegistrar" x-collapse class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="floating-label-group">
              <input type="text" x-model="form.registrar_notified" placeholder=" "
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all">
              <label>Registrar Notified</label>
            </div>
            <div class="floating-label-group">
              <input type="date" x-model="form.registrar_notification_date" :max="today"
                class="w-full border border-gray-200 rounded-xl bg-white px-3.5 text-sm text-gray-800 transition-all">
              <label class="!top-2 !transform-none !text-[10px] !text-nira-green !font-semibold !uppercase !tracking-wide">Notification Date</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Next button -->
      <button @click="nextStep()"
        class="w-full bg-nira-green hover:bg-nira-dark text-white font-display font-700 rounded-xl py-4 text-base flex items-center justify-center gap-2 transition-all duration-200 shadow-lg shadow-nira-green/30 hover:shadow-xl hover:shadow-nira-green/40 hover:-translate-y-0.5 active:translate-y-0">
        Continue to Evidence
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </button>
      </div>
    </div>


    <!-- ── STEP 2 ── -->
    <div x-show="step === 2" x-cloak class="anim-fadein">
      <div class="card-glass rounded-2xl p-7 mb-4">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="font-display text-2xl font-800 text-gray-900 leading-tight">Upload Evidence</h1>
            <p class="text-sm text-gray-500 mt-0.5">Attach screenshots, logs, or PDFs</p>
          </div>
          <div class="w-12 h-12 rounded-xl bg-nira-light flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
          </div>
        </div>

        <!-- Domain summary pill -->
        <div class="flex items-center gap-3 mb-6 p-3.5 rounded-xl bg-nira-xlight border border-nira-green/15">
          <div class="w-8 h-8 rounded-lg bg-nira-green/10 flex items-center justify-center">
            <svg class="w-4 h-4 text-nira-green" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-400 font-display font-semibold uppercase tracking-wide">Reporting domain</p>
            <p class="text-sm font-display font-700 text-gray-800 truncate" x-text="form.url"></p>
          </div>
          <span class="chip shrink-0" x-text="form.abuse_category"></span>
        </div>

        <!-- Drop zone -->
        <div
          class="file-drop relative border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center transition-all duration-200 cursor-pointer hover:border-nira-green/40 hover:bg-nira-xlight"
          :class="{ 'dragging': isDragging, 'border-red-400 bg-red-50': errors.files }"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="handleDrop($event)"
          @click="$refs.fileInput.click()">
          <input type="file" x-ref="fileInput" multiple accept=".pdf,.png,.jpg,.jpeg"
            class="hidden" @change="handleFiles($event.target.files)">

          <div x-show="files.length === 0">
            <div class="w-14 h-14 rounded-2xl bg-nira-light flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            </div>
            <p class="font-display font-700 text-gray-700 mb-1">Drop files here or click to browse</p>
            <p class="text-xs text-gray-400">PDF, PNG, JPG · Max 3 MB each · Up to 3 files</p>
          </div>

          <div x-show="files.length > 0" class="text-left space-y-2" @click.stop>
            <template x-for="(f, i) in files" :key="i">
              <div class="flex items-center gap-3 p-3 rounded-xl bg-white border border-nira-green/15 group">
                <!-- Icon by type -->
                <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                     :class="f.type === 'application/pdf' ? 'bg-red-50' : 'bg-blue-50'">
                  <svg x-show="f.type === 'application/pdf'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                  <svg x-show="f.type !== 'application/pdf'" class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-display font-600 text-gray-800 truncate" x-text="f.name"></p>
                  <p class="text-xs text-gray-400" x-text="formatSize(f.size)"></p>
                </div>
                <!-- Size check -->
                <span x-show="f.size > 3145728" class="text-xs text-red-500 font-semibold">Too large</span>
                <span x-show="f.size <= 3145728" class="w-5 h-5 text-nira-green">
                  <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
                <button @click="removeFile(i)" class="w-6 h-6 flex items-center justify-center rounded-full text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all ml-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </template>

            <!-- Add more -->
            <button x-show="files.length < 3"
              @click="$refs.fileInput.click()"
              class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-dashed border-nira-green/30 text-sm text-nira-green hover:bg-nira-xlight transition-all font-display font-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
              Add another file
            </button>
          </div>
        </div>
        <p x-show="errors.files" class="text-xs text-red-500 mt-2 ml-1" x-text="errors.files"></p>

        <!-- Guidelines -->
        <div class="mt-5 grid grid-cols-3 gap-2">
          <div class="p-3 rounded-xl bg-gray-50 text-center">
            <p class="text-lg font-display font-800 text-nira-green">≤3</p>
            <p class="text-[10px] text-gray-500 font-display font-600 uppercase tracking-wide">files max</p>
          </div>
          <div class="p-3 rounded-xl bg-gray-50 text-center">
            <p class="text-lg font-display font-800 text-nira-green">3 MB</p>
            <p class="text-[10px] text-gray-500 font-display font-600 uppercase tracking-wide">per file</p>
          </div>
          <div class="p-3 rounded-xl bg-gray-50 text-center">
            <p class="text-lg font-display font-800 text-nira-green">PNG·JPG·PDF</p>
            <p class="text-[10px] text-gray-500 font-display font-600 uppercase tracking-wide">formats</p>
          </div>
        </div>
      </div>

      <!-- Buttons -->
      <div class="flex gap-3">
        <button @click="step = 1"
          class="flex items-center gap-2 px-5 py-4 rounded-xl border border-gray-200 text-sm font-display font-700 text-gray-600 hover:bg-gray-50 transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
          Back
        </button>
        <button @click="submitForm()"
          class="flex-1 bg-nira-green hover:bg-nira-dark text-white font-display font-700 rounded-xl py-4 text-base flex items-center justify-center gap-2 transition-all duration-200 shadow-lg shadow-nira-green/30 hover:shadow-xl hover:shadow-nira-green/40 hover:-translate-y-0.5 active:translate-y-0"
          :disabled="isSubmitting"
          :class="isSubmitting ? 'opacity-80 cursor-not-allowed' : ''">
          <span x-show="!isSubmitting">Submit Report</span>
          <span x-show="isSubmitting" class="flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            Submitting…
          </span>
          <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.269 20.876L5.999 12zm0 0h7.5"/></svg>
        </button>
      </div>
    </div>

    <!-- ── STEP 3: SUCCESS ── -->
    <div x-show="step === 3" x-cloak class="anim-fadein">
      <div class="card-glass rounded-2xl p-8 text-center">
        <!-- Animated check -->
        <div class="w-20 h-20 rounded-full bg-nira-light mx-auto mb-5 flex items-center justify-center">
          <svg class="w-10 h-10" viewBox="0 0 40 40" fill="none">
            <circle cx="20" cy="20" r="18" stroke="#179e4f" stroke-width="2.5" opacity="0.3"/>
            <path class="check-anim" d="M10 20l7 8 13-16" stroke="#179e4f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="60" stroke-dashoffset="60"/>
          </svg>
        </div>

        <h2 class="font-display text-2xl font-800 text-gray-900 mb-2">Report Submitted!</h2>
        <p class="text-sm text-gray-500 mb-6">NiRA has received your abuse report and will investigate promptly.Kindly use the reference number below to track your report attaching this reference in your mail subject. </p>

        <!-- Ticket ID -->
        <div class="inline-flex items-center gap-3 px-5 py-3.5 rounded-xl bg-nira-light border border-nira-green/20 mb-6">
          <div>
            <p class="text-[10px] font-display font-700 text-nira-green uppercase tracking-widest">Reference Number</p>
            <p class="font-display font-800 text-nira-dark text-lg tracking-wider" x-text="ticketId"></p>
          </div>
          <button @click="copyTicket()" class="w-8 h-8 rounded-lg bg-nira-green/10 hover:bg-nira-green/20 flex items-center justify-center text-nira-green transition-all" title="Copy">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/></svg>
          </button>
        </div>

        <!-- Summary card -->
        <div class="text-left bg-gray-50 rounded-xl p-4 mb-6 space-y-2.5">
          <div class="flex justify-between text-sm">
            <span class="text-gray-400 font-display">Domain</span>
            <span class="font-display font-700 text-gray-800" x-text="form.url"></span>
          </div>
          <div class="h-px bg-gray-100"></div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-400 font-display">Category</span>
            <span class="font-display font-700 text-gray-800" x-text="form.abuse_category"></span>
          </div>
          <div class="h-px bg-gray-100"></div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-400 font-display">Confirmation sent to</span>
            <span class="font-display font-600 text-gray-700 truncate ml-4" x-text="form.email"></span>
          </div>
          <div class="h-px bg-gray-100"></div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-400 font-display">Files uploaded</span>
            <span class="font-display font-700 text-gray-800" x-text="files.length + ' file(s)'"></span>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="resetForm()"
            class="flex-1 border border-nira-green/30 text-nira-green hover:bg-nira-xlight font-display font-700 rounded-xl py-3.5 text-sm transition-all">
            Submit Another Report
          </button>
          <!-- <a href="https://register.ng" target="_blank"
            class="flex-1 bg-nira-green hover:bg-nira-dark text-white font-display font-700 rounded-xl py-3.5 text-sm flex items-center justify-center transition-all shadow-md shadow-nira-green/30">
            Back to NiRA
          </a> -->
        </div>
      </div>
    </div>

  </main>

  <!-- Footer -->
  <footer class="relative z-10 text-center py-8 text-xs text-gray-400 font-body">
    © 2026 Nigeria Internet Registration Association (NiRA) 
  </footer>

<script>
function abuseForm() {
  return {
    step: 1,
    showRegistrar: false,
    isDragging: false,
    isSubmitting: false,
    ticketId: '',
    today: new Date().toISOString().split('T')[0],
    steps: ['Domain Details', 'Upload Evidence', 'Confirmation'],
    tlds: ['.ng', '.com.ng', '.org.ng', '.gov.ng', '.edu.ng', '.net.ng', '.sch.ng', '.name.ng', '.mobi.ng', '.mil.ng', '.i.ng'],
    categories: ['Malware', 'Botnets', 'Phishing', 'Pharming', 'Squatting (cyber/typo)','DDOS', 'Spam', 'Other forms of DNS Abuse'],
    categoryMeta: {
      'Malware':   { bg: 'bg-red-50 text-red-700',       icon: '', desc: 'Malicious software' },
      'Botnets':   { bg: 'bg-orange-50 text-orange-700', icon: '', desc: 'Botnet infrastructure' },
      'Phishing':  { bg: 'bg-yellow-50 text-yellow-700', icon: '', desc: 'Credential phishing' },
      'Pharming':  { bg: 'bg-purple-50 text-purple-700', icon: '', desc: 'DNS redirection' },
      'DDOS':  { bg: 'bg-red-50 text-red-700', icon: '', desc: 'traffic attack' },
      'Squatting':  { bg: 'bg-red-50 text-red-700', icon: '', desc: 'High traffic attack' },
      'Spam':      { bg: 'bg-blue-50 text-blue-700',     icon: '', desc: 'Unsolicited bulk email' },
      'Other forms of DNS Abuse': { bg: 'bg-gray-100 text-gray-700', icon: '⚠️', desc: 'Other DNS Abuse' },
    },
    form: {
      name: '', email: '', url: '', 
      date_first_observed: '', abuse_category: '', description: '',
      registrar_notified: '', registrar_notification_date: ''
    },
    files: [],
    errors: {},

    init() {},

    // ── Validation ───────────────────────────────────────────────

    validateStep1() {
      this.errors = {};
      if (!this.form.name.trim())         this.errors.name = 'Full name is required';
      const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!this.form.email.trim())        this.errors.email = 'Email is required';
      else if (!emailRe.test(this.form.email)) this.errors.email = 'Enter a valid email address';
      if (!this.form.url.trim())          this.errors.url = 'URL is required';
      if (!this.form.date_first_observed) this.errors.date_first_observed = 'Date is required';
      if (!this.form.abuse_category)      this.errors.abuse_category = 'Select an abuse category';
      if (!this.form.description.trim())  this.errors.description = 'Please describe the abuse';
      return Object.keys(this.errors).length === 0;
    },

    validateStep2() {
      this.errors = {};
      if (this.files.length === 0) {
        this.errors.files = 'At least one evidence file is required';
        return false;
      }
      const oversized = this.files.filter(f => f.size > 3145728);
      if (oversized.length > 0) {
        this.errors.files = 'One or more files exceed the 3 MB limit';
        return false;
      }
      return true;
    },

    nextStep() {
      if (this.validateStep1()) this.step = 2;
      else {
        this.$nextTick(() => {
          const el = document.querySelector('.border-red-400');
          if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
      }
    },

    // ── File helpers ─────────────────────────────────────────────

    handleFiles(fileList) {
      const allowed = ['application/pdf', 'image/png', 'image/jpeg'];
      const incoming = Array.from(fileList).filter(f => allowed.includes(f.type));
      const remaining = 3 - this.files.length;
      this.files = [...this.files, ...incoming.slice(0, remaining)];
      if (this.errors.files) delete this.errors.files;
    },
    handleDrop(e) {
      this.isDragging = false;
      this.handleFiles(e.dataTransfer.files);
    },
    removeFile(i) { this.files.splice(i, 1); },
    formatSize(bytes) {
      if (bytes < 1024)    return bytes + ' B';
      if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / 1048576).toFixed(1) + ' MB';
    },

    // ── Submit ───────────────────────────────────────────────────

    async submitForm() {
      if (!this.validateStep2()) return;

      this.isSubmitting = true;
      this.errors = {};

      try {
        // Build a multipart FormData payload that matches the
        // field names expected by AbuseReportController::store()
        const fd = new FormData();

        // Text fields
        fd.append('name',                         this.form.name.trim());
        fd.append('email',                        this.form.email.trim());
        fd.append('url',                          this.form.url.trim());
        fd.append('date_first_observed',          this.form.date_first_observed);
        fd.append('abuse_category',               this.form.abuse_category);
        fd.append('description',                  this.form.description.trim());

        if (this.form.registrar_notified.trim()) {
          fd.append('registrar_notified',         this.form.registrar_notified.trim());
        }
        if (this.form.registrar_notification_date) {
          fd.append('registrar_notification_date', this.form.registrar_notification_date);
        }

        // Evidence files — all sent under the key "files[]"
        this.files.forEach(file => fd.append('files[]', file));

        const response = await fetch('/api/abuse-reports', {
          method: 'POST',
          body: fd,
          // Do NOT set Content-Type manually; the browser sets it
          // automatically with the correct multipart boundary.
          headers: {
            // CodeIgniter CSRF: if CSRF protection is enabled,
            // read the cookie and pass its value here.
            // 'X-CSRF-TOKEN': this.getCsrfToken(),
          },
        });

        const json = await response.json();

        if (!response.ok) {
          // Server returned 4xx / 5xx
          if (json.errors) {
            // Map server-side field errors back to the UI
            this.errors = this.mapServerErrors(json.errors);

            // If there are step-1 errors, take the user back
            const step1Keys = ['name','email','url',
                               'date_first_observed','abuse_category','description'];
            const hasStep1Error = step1Keys.some(k => this.errors[k]);
            if (hasStep1Error) this.step = 1;
          } else {
            alert(json.message || 'Submission failed. Please try again.');
          }
          return;
        }

        // ✅ Success
        this.ticketId = json.ticket_id;
        this.step = 3;

      } catch (err) {
        console.error('Submission error:', err);
        alert('A network error occurred. Please check your connection and try again.');
      } finally {
        this.isSubmitting = false;
      }
    },

    /**
     * Map server error keys to the form.errors object.
     * The CI controller uses the same field names as the form,
     * except 'files[]' → 'files'.
     */
    mapServerErrors(serverErrors) {
      const map = {};
      for (const [key, msg] of Object.entries(serverErrors)) {
        const normalised = key.replace('[]', '');
        map[normalised] = msg;
      }
      return map;
    },

    // ── CSRF helper (enable if CI CSRF is on) ───────────────────
    // getCsrfToken() {
    //   const match = document.cookie.match(/csrf_cookie_name=([^;]+)/);
    //   return match ? decodeURIComponent(match[1]) : '';
    // },

    // ── Copy ticket ID ───────────────────────────────────────────

    copyTicket() {
      navigator.clipboard.writeText(this.ticketId)
        .then(() => alert('Ticket ID copied to clipboard'))
        .catch(() => {
          // Fallback for browsers that block clipboard without https
          const el = document.createElement('textarea');
          el.value = this.ticketId;
          document.body.appendChild(el);
          el.select();
          document.execCommand('copy');
          document.body.removeChild(el);
          alert('Ticket ID copied to clipboard');
        });
    },

    // ── Reset ────────────────────────────────────────────────────

    resetForm() {
      this.step = 1;
      this.files = [];
      this.errors = {};
      this.ticketId = '';
      this.showRegistrar = false;
      this.form = {
        name: '', email: '', url: '',
        date_first_observed: '', abuse_category: '', description: '',
        registrar_notified: '', registrar_notification_date: ''
      };
    }
  };
}
</script>
</body>
</html>
