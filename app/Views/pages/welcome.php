<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>NiRA Abuse Manager — Nigeria Internet Registration Association</title>
<link rel="icon" href="/logo.png" type="image/png" />
 <script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

:root{
  --g:#179e4f;
  --gd:#0d6b35;
  --gl:#e8f7ee;
  --ink:#116a35;
  --muted:#5a7060;
  --line:rgba(23,158,79,0.15);
  --off:#f5fbf7;
}

html{scroll-behavior:smooth}

body{
  font-family:'DM Sans',sans-serif;
  background:#fff;
  color:var(--ink);
  overflow-x:hidden;
}

a{color:inherit;text-decoration:none}

.font-display{font-family:'Syne',sans-serif}
.font-mono{font-family:'DM Mono',monospace}

/* ─── SCROLLBAR ─── */
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:#fff}
::-webkit-scrollbar-thumb{background:var(--g);border-radius:99px}

/* ─── NAV ─── */
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
  background:var(--g);color:#fff;
  font-family:'Syne',sans-serif;font-weight:700;font-size:13px;
  padding:10px 22px;border-radius:8px;
  transition:background .2s,transform .15s;
  display:inline-flex;align-items:center;gap:6px;
}
.nav-cta:hover{background:var(--gd);transform:translateY(-1px)}
.nav-cta svg{width:14px;height:14px}

/* ─── HERO ─── */
.hero{
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 1fr;
  position:relative;
  overflow:hidden;
}

.hero-left{
  display:flex;flex-direction:column;justify-content:center;
  padding:120px 6vw 80px;
  position:relative;z-index:2;
}

.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--gl);
  border:1px solid rgba(23,158,79,0.25);
  border-radius:99px;
  padding:6px 14px 6px 8px;
  margin-bottom:32px;
  width:fit-content;
}
.hero-badge-dot{
  width:8px;height:8px;border-radius:50%;
  background:var(--g);
  box-shadow:0 0 0 3px rgba(23,158,79,0.2);
  animation:blink 2s infinite;
}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.4}}
.hero-badge span{font-size:11px;font-weight:600;font-family:'Syne',sans-serif;color:var(--gd);letter-spacing:0.06em;text-transform:uppercase}

.hero-h1{
  font-family:'Syne',sans-serif;
  font-size:clamp(44px,5.5vw,80px);
  font-weight:800;
  line-height:0.95;
  letter-spacing:-0.04em;
  color:var(--ink);
  margin-bottom:28px;
}
.hero-h1 em{
  font-style:normal;
  color:var(--g);
  position:relative;
  display:inline-block;
}
.hero-h1 em::after{
  content:'';
  position:absolute;
  bottom:-4px;left:0;right:0;
  height:4px;
  background:var(--g);
  border-radius:2px;
  opacity:0.3;
}

.hero-desc{
  font-size:17px;
  font-weight:300;
  line-height:1.7;
  color:var(--muted);
  max-width:440px;
  margin-bottom:44px;
}

.hero-actions{display:flex;align-items:center;gap:16px;flex-wrap:wrap}

.btn-primary{
  background:var(--g);color:#fff;
  font-family:'Syne',sans-serif;font-weight:700;font-size:15px;
  padding:16px 32px;border-radius:12px;
  display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 8px 32px rgba(23,158,79,0.35);
  transition:all .2s;
}
.btn-primary:hover{background:var(--gd);transform:translateY(-2px);box-shadow:0 14px 40px rgba(23,158,79,0.4)}
.btn-primary svg{width:18px;height:18px;transition:transform .2s}
.btn-primary:hover svg{transform:translateX(3px)}

.btn-ghost{
  border:1.5px solid var(--line);
  color:var(--muted);
  font-family:'Syne',sans-serif;font-weight:600;font-size:14px;
  padding:14px 24px;border-radius:12px;
  display:inline-flex;align-items:center;gap:8px;
  transition:all .2s;
}
.btn-ghost:hover{border-color:var(--g);color:var(--g);background:var(--gl)}

.hero-stats{
  display:flex;gap:32px;margin-top:52px;
  padding-top:32px;
  border-top:1px solid var(--line);
}
.stat-item{display:flex;flex-direction:column}
.stat-num{font-family:'Syne',sans-serif;font-weight:800;font-size:28px;color:var(--ink);letter-spacing:-0.03em}
.stat-label{font-size:12px;color:var(--muted);margin-top:2px}

/* ─── HERO RIGHT ─── */
.hero-right{
  position:relative;
  display:flex;align-items:center;justify-content:center;
  background:var(--off);
  border-left:1px solid var(--line);
}

.hero-visual{
  position:relative;
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  padding:120px 48px 80px;
}

.globe-wrap{
  position:relative;
  width:340px;height:340px;
}
.globe-ring{
  position:absolute;
  border:1.5px solid rgba(23,158,79,0.2);
  border-radius:50%;
  animation:spin linear infinite;
}
.globe-ring:nth-child(1){inset:0;animation-duration:18s}
.globe-ring:nth-child(2){inset:20px;border-style:dashed;animation-duration:26s;animation-direction:reverse}
.globe-ring:nth-child(3){inset:50px;border-color:rgba(23,158,79,0.35);animation-duration:14s}
@keyframes spin{to{transform:rotate(360deg)}}

.globe-core{
  position:absolute;
  inset:70px;
  background:radial-gradient(circle at 35% 35%, #22c966, var(--g) 50%, var(--gd));
  border-radius:50%;
  box-shadow:0 24px 80px rgba(23,158,79,0.5);
  display:flex;align-items:center;justify-content:center;
}
.globe-core svg{width:72px;height:72px;fill:rgba(255,255,255,0.9)}

.orbit-dot{
  position:absolute;
  width:12px;height:12px;
  background:var(--g);
  border-radius:50%;
  border:2px solid #fff;
  box-shadow:0 2px 12px rgba(23,158,79,0.5);
}

.floating-cards{
  position:absolute;
  display:flex;flex-direction:column;gap:12px;
  right:-20px;
  top:50%;transform:translateY(-50%);
}
.fcard{
  background:#fff;
  border:1px solid var(--line);
  border-radius:12px;
  padding:12px 16px;
  display:flex;align-items:center;gap:10px;
  box-shadow:0 4px 20px rgba(0,0,0,0.06);
  width:200px;
  animation:float ease-in-out infinite;
}
.fcard:nth-child(1){animation-duration:4s;animation-delay:0s}
.fcard:nth-child(2){animation-duration:5s;animation-delay:1s}
.fcard:nth-child(3){animation-duration:4.5s;animation-delay:0.5s}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
.fcard-icon{
  width:32px;height:32px;border-radius:8px;
  display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;
}
.fcard-text{display:flex;flex-direction:column}
.fcard-label{font-size:10px;font-weight:600;font-family:'Syne',sans-serif;letter-spacing:0.04em;color:var(--muted);text-transform:uppercase}
.fcard-val{font-size:13px;font-weight:700;font-family:'Syne',sans-serif;color:var(--ink)}

/* ─── MARQUEE ─── */
.marquee-wrap{
  background:var(--g);
  padding:14px 0;
  overflow:hidden;
  display:flex;
  border-top:1px solid rgba(255,255,255,0.15);
  border-bottom:1px solid rgba(255,255,255,0.15);
}
.marquee-track{
  display:flex;gap:0;
  animation:marquee 28s linear infinite;
  white-space:nowrap;
}
@keyframes marquee{to{transform:translateX(-50%)}}
.marquee-item{
  display:inline-flex;align-items:center;gap:10px;
  padding:0 28px;
  font-family:'Syne',sans-serif;font-size:12px;font-weight:700;
  color:rgba(255,255,255,0.85);
  letter-spacing:0.08em;text-transform:uppercase;
}
.marquee-item::after{
  content:'·';color:rgba(255,255,255,0.4);font-size:18px;
}

/* ─── SECTIONS ─── */
section{padding:100px 6vw}

.section-tag{
  display:inline-flex;align-items:center;gap:8px;
  font-family:'DM Mono',monospace;font-size:11px;font-weight:500;
  color:var(--g);letter-spacing:0.1em;text-transform:uppercase;
  margin-bottom:20px;
}
.section-tag::before{content:'//';opacity:0.5}

.section-h2{
  font-family:'Syne',sans-serif;
  font-size:clamp(34px,4vw,58px);
  font-weight:800;
  line-height:1;
  letter-spacing:-0.03em;
  margin-bottom:20px;
}

.section-lead{
  font-size:18px;font-weight:300;line-height:1.7;color:var(--muted);
}

/* ─── ABOUT ─── */
.about-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:80px;
  align-items:center;
}
.about-text{}
.about-aside{}

.about-kpi-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:16px;
}
.kpi-card{
  background:var(--off);
  border:1px solid var(--line);
  border-radius:16px;
  padding:24px;
}
.kpi-card:first-child{
  grid-column:span 2;
  background:var(--g);
  border-color:transparent;
}
.kpi-card:first-child .kpi-num{color:#fff}
.kpi-card:first-child .kpi-label{color:rgba(255,255,255,0.7)}
.kpi-num{font-family:'Syne',sans-serif;font-size:42px;font-weight:800;letter-spacing:-0.04em;color:var(--ink)}
.kpi-label{font-size:13px;color:var(--muted);margin-top:4px}

.mission-block{
  margin-top:36px;padding:28px;
  border-left:3px solid var(--g);
  background:var(--gl);
  border-radius:0 12px 12px 0;
}
.mission-block p{font-size:16px;line-height:1.7;color:var(--ink);font-style:italic;font-weight:300}
.mission-block cite{display:block;margin-top:10px;font-size:12px;font-family:'Syne',sans-serif;font-weight:700;color:var(--g);font-style:normal;letter-spacing:0.05em;text-transform:uppercase}

/* ─── ROLES ─── */
.roles-section{background:var(--ink)}
.roles-section .section-tag{color:#4ade80}
.roles-section .section-h2{color:#fff}
.roles-section .section-lead{color:rgba(255,255,255,0.5)}

.roles-grid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:1px;
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.06);
  border-radius:20px;
  overflow:hidden;
  margin-top:56px;
}
.role-card{
  background:rgba(255,255,255,0.03);
  padding:40px 32px;
  transition:background .25s;
  position:relative;
  overflow:hidden;
}
.role-card::before{
  content:'';
  position:absolute;
  top:0;left:0;right:0;
  height:2px;
  background:linear-gradient(90deg,var(--g),transparent);
  transform:scaleX(0);transform-origin:left;
  transition:transform .3s;
}
.role-card:hover{background:rgba(23,158,79,0.08)}
.role-card:hover::before{transform:scaleX(1)}

.role-icon{
  width:52px;height:52px;
  border-radius:14px;
  background:rgba(23,158,79,0.15);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:24px;
  font-size:24px;
}
.role-title{
  font-family:'Syne',sans-serif;font-weight:700;font-size:19px;
  color:#fff;margin-bottom:12px;
}
.role-desc{font-size:14px;line-height:1.7;color:rgba(255,255,255,0.5)}

/* ─── DNS ABUSE ─── */
.dns-section{background:var(--off)}

.dns-intro-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:80px;
  align-items:start;
  margin-bottom:72px;
}

.abuse-types{display:flex;flex-direction:column;gap:0;margin-top:40px}
.abuse-item{
  display:flex;align-items:flex-start;gap:20px;
  padding:24px 0;
  border-bottom:1px solid var(--line);
}
.abuse-item:last-child{border-bottom:none}
.abuse-num{
  font-family:'DM Mono',monospace;font-size:11px;font-weight:500;
  color:var(--g);min-width:28px;margin-top:2px;
}
.abuse-content{}
.abuse-title{font-family:'Syne',sans-serif;font-weight:700;font-size:16px;margin-bottom:6px}
.abuse-desc{font-size:14px;color:var(--muted);line-height:1.6}

.dns-aside{
  position:sticky;
  top:100px;
  display:flex;flex-direction:column;gap:16px;
}
.dns-info-card{
  background:#fff;
  border:1px solid var(--line);
  border-radius:16px;
  padding:28px;
}
.dns-info-card h4{
  font-family:'Syne',sans-serif;font-weight:700;font-size:16px;
  margin-bottom:14px;
  display:flex;align-items:center;gap:8px;
}
.dns-info-card h4 span{
  width:28px;height:28px;border-radius:7px;
  background:var(--gl);
  display:flex;align-items:center;justify-content:center;font-size:13px;
}
.dns-info-card p{font-size:14px;color:var(--muted);line-height:1.65}

.report-cta-banner{
  background:var(--g);
  border-radius:20px;
  padding:48px;
  display:flex;align-items:center;justify-content:space-between;
  gap:32px;
  flex-wrap:wrap;
}
.report-cta-banner h3{
  font-family:'Syne',sans-serif;font-weight:800;font-size:28px;
  color:#fff;line-height:1.15;
  max-width:380px;
}
.report-cta-banner p{color:rgba(255,255,255,0.7);font-size:15px;margin-top:8px}
.btn-white{
  background:#fff;color:var(--g);
  font-family:'Syne',sans-serif;font-weight:800;font-size:15px;
  padding:18px 36px;border-radius:12px;
  display:inline-flex;align-items:center;gap:8px;white-space:nowrap;
  transition:all .2s;box-shadow:0 8px 30px rgba(0,0,0,0.15);
  flex-shrink:0;
}
.btn-white:hover{transform:translateY(-2px);box-shadow:0 14px 40px rgba(0,0,0,0.2)}
.btn-white svg{width:18px;height:18px}

/* ─── HOW IT WORKS ─── */
.how-section{}

.how-header{
  display:grid;grid-template-columns:1fr 1fr;gap:60px;
  align-items:end;margin-bottom:72px;
}

.steps-visual{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:20px;
}
.step-card{
  background:var(--off);
  border:1px solid var(--line);
  border-radius:16px;
  padding:32px 28px;
  position:relative;
  overflow:hidden;
  transition:border-color .2s,box-shadow .2s;
}
.step-card:hover{border-color:var(--g);box-shadow:0 8px 30px rgba(23,158,79,0.1)}
.step-card:first-child{
  grid-column:span 2;
  background:var(--ink);
  border-color:transparent;
}
.step-card:first-child .step-num{color:rgba(255,255,255,0.15)}
.step-card:first-child .step-title{color:#fff}
.step-card:first-child .step-body{color:rgba(255,255,255,0.5)}
.step-num{
  font-family:'DM Mono',monospace;font-weight:500;
  font-size:48px;color:var(--line);
  line-height:1;margin-bottom:16px;
}
.step-title{font-family:'Syne',sans-serif;font-weight:700;font-size:17px;margin-bottom:8px}
.step-body{font-size:14px;color:var(--muted);line-height:1.6}

.step-card .step-icon{
  position:absolute;
  top:24px;right:24px;
  width:40px;height:40px;
  border-radius:10px;
  background:rgba(23,158,79,0.1);
  display:flex;align-items:center;justify-content:center;
  font-size:18px;
}
.step-card:first-child .step-icon{background:rgba(255,255,255,0.07)}

/* ─── REGISTRARS ─── */
.reg-section{background:var(--ink)}
.reg-section .section-h2{color:#fff}
.reg-section .section-tag{color:#4ade80}
.reg-section .section-lead{color:rgba(255,255,255,0.5)}

.reg-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:16px;
  margin-top:56px;
}
.reg-card{
  background:rgba(255,255,255,0.04);
  border:1px solid rgba(255,255,255,0.07);
  border-radius:14px;
  padding:28px 20px;
  text-align:center;
  transition:background .2s,border-color .2s;
}
.reg-card:hover{background:rgba(23,158,79,0.12);border-color:rgba(23,158,79,0.3)}
.reg-card-icon{font-size:28px;margin-bottom:12px}
.reg-card-name{font-family:'Syne',sans-serif;font-weight:700;font-size:14px;color:#fff;margin-bottom:4px}
.reg-card-role{font-size:12px;color:rgba(255,255,255,0.4)}

.reg-note{
  margin-top:36px;
  padding:24px 28px;
  background:rgba(23,158,79,0.1);
  border:1px solid rgba(23,158,79,0.2);
  border-radius:14px;
  font-size:14px;
  color:rgba(255,255,255,0.6);
  line-height:1.7;
}
.reg-note strong{color:#4ade80}

/* ─── FAQ ─── */
.faq-grid{
  display:grid;grid-template-columns:1fr 1fr;gap:60px;
  align-items:start;margin-top:60px;
}
.faq-list{display:flex;flex-direction:column;gap:0}
.faq-item{border-bottom:1px solid var(--line);padding:20px 0;cursor:pointer}
.faq-q{
  font-family:'Syne',sans-serif;font-weight:700;font-size:16px;
  display:flex;justify-content:space-between;align-items:center;gap:16px;
  color:var(--ink);
}
.faq-q svg{width:18px;height:18px;color:var(--g);flex-shrink:0;transition:transform .25s}
.faq-a{font-size:14px;line-height:1.7;color:var(--muted);max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s}
.faq-item.open .faq-q svg{transform:rotate(45deg)}
.faq-item.open .faq-a{max-height:200px;padding-top:12px}

.faq-contact{
  background:var(--off);border:1px solid var(--line);border-radius:20px;
  padding:36px;
}
.faq-contact h4{font-family:'Syne',sans-serif;font-weight:800;font-size:22px;margin-bottom:12px}
.faq-contact p{font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:24px}
.contact-item{
  display:flex;align-items:center;gap:12px;
  padding:14px 16px;
  background:#fff;border:1px solid var(--line);border-radius:10px;
  margin-bottom:10px;font-size:14px;color:var(--ink);font-weight:500;
}
.contact-item svg{width:18px;height:18px;color:var(--g);flex-shrink:0}

/* ─── FOOTER ─── */
footer{
  background:var(--ink);
  padding:64px 6vw 32px;
}
.footer-top{
  display:grid;
  grid-template-columns:2fr 1fr 1fr 1fr;
  gap:48px;
  padding-bottom:48px;
  border-bottom:1px solid rgba(255,255,255,0.07);
}
.footer-brand p{font-size:14px;color:rgba(255,255,255,0.45);line-height:1.7;margin-top:16px;max-width:260px}
.footer-col h5{font-family:'Syne',sans-serif;font-weight:700;font-size:12px;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.3);margin-bottom:16px}
.footer-col ul{list-style:none;display:flex;flex-direction:column;gap:10px}
.footer-col ul li a{font-size:14px;color:rgba(255,255,255,0.5);transition:color .2s}
.footer-col ul li a:hover{color:#fff}
.footer-bottom{
  display:flex;align-items:center;justify-content:space-between;
  padding-top:28px;
  font-size:13px;color:rgba(255,255,255,0.3);
  flex-wrap:wrap;gap:12px;
}
.footer-bottom a{color:rgba(255,255,255,0.45);transition:color .2s}
.footer-bottom a:hover{color:#fff}

/* ─── RESPONSIVE ─── */
@media(max-width:1024px){
  .hero{grid-template-columns:1fr;min-height:auto;padding-top:68px}
  .hero-right{display:none}
  .about-grid,.dns-intro-grid,.how-header,.faq-grid,.footer-top{grid-template-columns:1fr;gap:40px}
  .roles-grid{grid-template-columns:1fr 1fr}
  .reg-grid{grid-template-columns:1fr 1fr}
  .steps-visual{grid-template-columns:1fr}
  .step-card:first-child{grid-column:span 1}
  .hero-left{padding:100px 6vw 60px}
}
@media(max-width:640px){
  .roles-grid,.reg-grid{grid-template-columns:1fr}
  .about-kpi-grid .kpi-card:first-child{grid-column:span 1}
  .nav-links{display:none}
  .hero-stats{flex-wrap:wrap;gap:20px}
  .report-cta-banner{padding:32px 24px}
}

/* ─── ENTRY ANIMATIONS ─── */
.reveal{
  opacity:0;transform:translateY(24px);
  transition:opacity .65s cubic-bezier(.22,1,.36,1),transform .65s cubic-bezier(.22,1,.36,1);
}
.reveal.visible{opacity:1;transform:none}
</style>
</head>
<body>

<nav>
  <div class="nav-logo">
    <div class="nav-logo-mark">
        <img class="" src="/logo.png"/> 
    </div>
    <div>
      <div class="nav-logo-text font-display">DNS ABUSE</div>
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

<!-- ══ HERO ══ -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      <span>Official .ng Domain Authority</span>
    </div>
    <h1 class="hero-h1 font-display">
      Nigeria's<br>
      <em>Internet</em><br>
      Identity Hub
    </h1>
    <p class="hero-desc">
      NiRA manages the .ng country-code top-level domain, enforces internet policy across Nigeria, and operates a dedicated portal to report and eliminate DNS abuse under the .ng namespace.
    </p>
    <div class="hero-actions">
      <a href="/report" class="btn-primary font-display">
        Report DNS Abuse
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
      </a>
    
    </div>
  
  </div>
  <div class="hero-right">
    <img src="/bg.jpg"/>
    <!-- <div class="hero-visual">
      <div class="globe-wrap">
        <div class="globe-ring"></div>
        <div class="globe-ring"></div>
        <div class="globe-ring"></div>
        <div class="globe-core">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
        </div>
        <div class="orbit-dot" style="top:8px;left:50%;transform:translateX(-50%)"></div>
        <div class="orbit-dot" style="bottom:28px;right:18px"></div>
        <div class="orbit-dot" style="top:50%;left:4px;transform:translateY(-50%);width:8px;height:8px;background:#4ade80"></div>
      </div>
      <div class="floating-cards">
        <div class="fcard">
          <div class="fcard-icon" style="background:#fef2f2">🛡️</div>
          <div class="fcard-text">
            <span class="fcard-label">Protected</span>
            <span class="fcard-val font-display">example.ng</span>
          </div>
        </div>
        <div class="fcard">
          <div class="fcard-icon" style="background:#f0fdf4">✅</div>
          <div class="fcard-text">
            <span class="fcard-label">Report Filed</span>
            <span class="fcard-val font-display">NRA-20241</span>
          </div>
        </div>
        <div class="fcard">
          <div class="fcard-icon" style="background:#fffbeb">⚡</div>
          <div class="fcard-text">
            <span class="fcard-label">Action Taken</span>
            <span class="fcard-val font-display">48hr Response</span>
          </div>
        </div>
      </div>
    </div> -->
  </div>
</section>

<!-- ══ MARQUEE ══ -->
<div class="marquee-wrap">
  <div class="marquee-track">
    <span class="marquee-item">.ng</span>
    <span class="marquee-item">.com.ng</span>
    <span class="marquee-item">.org.ng</span>
    <span class="marquee-item">.gov.ng</span>
    <span class="marquee-item">.edu.ng</span>
    <span class="marquee-item">.net.ng</span>
    <span class="marquee-item">.sch.ng</span>
    <span class="marquee-item">.name.ng</span>
    <span class="marquee-item">.mobi.ng</span>
    <span class="marquee-item">.mil.ng</span>
    <span class="marquee-item">.i.ng</span>
    <span class="marquee-item">.ng</span>
    <span class="marquee-item">.com.ng</span>
    <span class="marquee-item">.org.ng</span>
    <span class="marquee-item">.gov.ng</span>
    <span class="marquee-item">.edu.ng</span>
    <span class="marquee-item">.net.ng</span>
    <span class="marquee-item">.sch.ng</span>
    <span class="marquee-item">.name.ng</span>
    <span class="marquee-item">.mobi.ng</span>
    <span class="marquee-item">.mil.ng</span>
    <span class="marquee-item">.i.ng</span>
  </div>
</div>



<!-- ══ DNS ABUSE ══ -->
<section id="dns-abuse" class="dns-section">
  <div class="reveal">
    <div class="section-tag font-mono">DNS Abuse</div>
    <h2 class="section-h2 font-display">Understanding<br>DNS Abuse</h2>
  </div>
  <div class="dns-intro-grid">
    <div>
      <p class="section-lead" style="margin-bottom:0">
        DNS abuse refers to the exploitation of the Domain Name System to facilitate harmful, criminal, or deceptive activities. When .ng domains are used as vehicles for such abuse, they erode public trust and harm Nigerian internet users.
      </p>
      <div class="abuse-types">
        <div class="abuse-item reveal">
          <span class="abuse-num font-mono">01</span>
          <div class="abuse-content">
            <div class="abuse-title font-display">🦠 Malware Distribution</div>
            <p class="abuse-desc">Domains that host, distribute, or command malicious software designed to compromise the security or privacy of users' systems, steal data, or cause financial damage.</p>
          </div>
        </div>
        <div class="abuse-item reveal">
          <span class="abuse-num font-mono">02</span>
          <div class="abuse-content">
            <div class="abuse-title font-display">🤖 Botnet Infrastructure</div>
            <p class="abuse-desc">Domains used as command-and-control servers for networks of infected computers (botnets), which can be directed to launch attacks, send spam, or mine cryptocurrency without the device owner's knowledge.</p>
          </div>
        </div>
        <div class="abuse-item reveal">
          <span class="abuse-num font-mono">03</span>
          <div class="abuse-content">
            <div class="abuse-title font-display">🎣 Phishing</div>
            <p class="abuse-desc">Fraudulent websites impersonating legitimate businesses  banks, government agencies, and popular services — to trick users into surrendering their credentials, personal data, or money.</p>
          </div>
        </div>
        <div class="abuse-item reveal">
          <span class="abuse-num font-mono">04</span>
          <div class="abuse-content">
            <div class="abuse-title font-display">🔀 Pharming</div>
            <p class="abuse-desc">Attacks that manipulate DNS resolution to silently redirect users from legitimate websites to fraudulent ones, even when the correct URL is typed without any visible warning.</p>
          </div>
        </div>
        <div class="abuse-item reveal">
          <span class="abuse-num font-mono">05</span>
          <div class="abuse-content">
            <div class="abuse-title font-display">📨 Spam Operations</div>
            <p class="abuse-desc">Domains registered specifically to send unsolicited bulk email at scale, circumventing spam filters and flooding inboxes with fraudulent offers, scams, or harmful links.</p>
          </div>
        </div>
      </div>
    </div>
    <div class="dns-aside">
      <div class="dns-info-card reveal">
        <h4 class="font-display"><span>⚡</span> Why Report Quickly?</h4>
        <p>DNS abuse campaigns are often time-sensitive. Phishing sites are frequently active for under 24 hours before being replaced. Fast reporting gives NiRA and registrars the best chance to take action before significant harm occurs.</p>
      </div>
      <div class="dns-info-card reveal">
        <h4 class="font-display"><span>🔍</span> What Happens to Reports?</h4>
        <p>Each report is triaged by NiRA's technical team. Verified abuse leads to formal notification to the responsible registrar and where warranted immediate suspension of the offending domain under the .ng Registry Agreement.</p>
      </div>
      <div class="dns-info-card reveal">
        <h4 class="font-display"><span>🔒</span> Is My Report Confidential?</h4>
        <p>Reporter details are never shared publicly. NiRA may share information with the relevant accredited registrar solely for the purpose of investigating and remediating the reported abuse.</p>
      </div>
    </div>
  </div>
  <div class="report-cta-banner reveal">
    <div>
      <h3 class="font-display">Seen abuse on a .ng domain?<br></h3>
    </div>
    <a href="/report" class="btn-white font-display">
      Report it now
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
    </a>
  </div>
</section> 
 
<!-- ══ ROLES ══ -->
<section class="roles-section">
  <div class="reveal">
    <div class="section-tag font-mono">What We Do</div>
    <h2 class="section-h2 font-display">Registry, Policy<br>& Protection</h2>
    <p class="section-lead" style="max-width:520px">NiRA operates across three critical pillars that collectively safeguard Nigeria's internet identity and the millions of people who rely on .ng domains daily.</p>
  </div>
  <div class="roles-grid">
    <div class="role-card reveal">
      <div class="role-icon">🌐</div>
      <div class="role-title font-display">Domain Registry</div>
      <p class="role-desc">NiRA maintains the authoritative registry database for all .ng second-level domains. We coordinate with accredited registrars to ensure every registration is valid, properly delegated, and correctly routed through the global DNS.</p>
    </div>
    <div class="role-card reveal">
      <div class="role-icon">⚖️</div>
      <div class="role-title font-display">Policy Governance</div>
      <p class="role-desc">We formulate and enforce registration policies, dispute-resolution procedures, and compliance frameworks in alignment with ICANN guidelines. NiRA ensures the .ng namespace operates under a fair, transparent rules-based system.</p>
    </div>
    <div class="role-card reveal">
      <div class="role-icon">🛡️</div>
      <div class="role-title font-display">Abuse Prevention</div>
      <p class="role-desc">Through this DNS Abuse Portal, NiRA investigates reports of malicious activity on .ng domains — including phishing, malware, botnets, and pharming — and works with registrars to suspend or remediate offending domains promptly.</p>
    </div>
  </div>
</section>

<!-- ══ HOW IT WORKS ══ -->
<section id="how" class="how-section">
  <div class="how-header">
    <div class="reveal">
      <div class="section-tag font-mono">Process</div>
      <h2 class="section-h2 font-display">How the Report Process Works</h2>
    </div>
    <div class="reveal">
      <p class="section-lead">From submission to resolution, every abuse report follows a clear, accountable process. Here is what happens at each stage.</p>
    </div>
  </div>
  <div class="steps-visual">
    <div class="step-card reveal">
      <div class="step-icon">📋</div>
      <div class="step-num font-mono">01</div>
      <div class="step-title font-display">Submit Your Report</div>
      <p class="step-body">Fill in the two-step form with the offending domain, your contact details, the category of abuse, and a description of what you observed. Attach screenshots, logs, or PDFs as supporting evidence  up to three files, 3 MB each.  You receive a unique reference / ticket ID number immediately upon submission to track progress.</p>
    </div>
    <div class="step-card reveal">
      <div class="step-icon">🔍</div>
      <div class="step-num font-mono">02</div>
      <div class="step-title font-display">NiRA Reviews &amp; Investigation</div>
      <p class="step-body">Our technical team reviews each submission, verifies the domain falls under the .ng registry, and classifies the severity of the reported abuse.</p>
    </div>
    <div class="step-card reveal">
      <div class="step-icon">📬</div>
      <div class="step-num font-mono">03</div>
      <div class="step-title font-display">Registrar is Notified</div>
      <p class="step-body">NiRA formally notifies the accredited registrar responsible for the domain, providing the abuse evidence and requesting remediation within a defined window under the Registry–Registrar Agreement.</p>
    </div>
    <div class="step-card reveal">
      <div class="step-icon">✅</div>
      <div class="step-num font-mono">04</div>
      <div class="step-title font-display">Action &amp; Resolution</div>
      <p class="step-body">The registrar takes appropriate action — which may include suspending the domain, locking DNS propagation, or requiring the registrant to remediate the abusive content. NiRA monitors compliance and, where necessary, escalates to direct registry-level suspension.</p>
    </div>
  </div>
</section>

  

<!-- ══ FAQ ══ -->
<section id="faq">
  <div class="reveal">
    <div class="section-tag font-mono">FAQ</div>
    <h2 class="section-h2 font-display">Common Questions</h2>
  </div>
  <div class="faq-grid">
    <div class="faq-list reveal">
      <div class="faq-item">
        <div class="faq-q font-display">
          Who can submit an abuse report?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">Anyone can file a report members of the public, businesses, security researchers, registrars, or government agencies. You do not need to be a NiRA member or a domain registrant to report abuse on a .ng domain.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q font-display">
          What if I don't have evidence files?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">Evidence attachments are required to process your report. At minimum, a screenshot of the abusive page or a log excerpt is sufficient. Evidence helps NiRA validate the report and gives the responsible registrar concrete grounds to act.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q font-display">
          How long does it take to resolve a report?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">NiRA investigate reports within one business day. Once forwarded to the registrar, the response timeline depends on the severity: critical abuse (active phishing, malware) is typically escalated for action within 24–48 hours; other cases may take up to 5 business days.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q font-display">
          Can I report domains not under .ng?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">NiRA's jurisdiction covers only .ng and its second-level namespaces (e.g. .com.ng, .gov.ng). For domains under other TLDs such as .com or .org, please contact the respective registry or ICANN's abuse reporting channels.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q font-display">
          What is a reference number used for?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">Your reference / ticket ID number uniquely identifies your abuse report in NiRA's system. Include it in the subject line of any follow-up email to admin@nira.org.ng so our team can locate your case instantly without requiring you to re-enter all information.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q font-display">
          Will the domain registrant know I reported them?
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        </div>
        <div class="faq-a">Your personal details are never shared with the registrant. NiRA communicates with the registrar — not the domain owner — and your identity is treated as confidential throughout the investigation process.</div>
      </div>
    </div>
    <div class="faq-contact reveal">
      <h4 class="font-display">Still have questions?</h4>
      <p>If your query is not answered here, reach out to NiRA directly. Our team is available on business days and will respond to all enquiries.</p>
      <div class="contact-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        admin@nira.org.ng 
      </div>
      <div class="contact-item">
          <svg class="w-6 h-6 text-nira-green" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/></svg>
          nira.org.ng
      </div>
      <div class="contact-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
        +234 (0) 700 22556472
      </div>
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--line)">
        <p style="font-size:13px;color:var(--muted);line-height:1.6">When emailing, always include your report reference number in the subject line for the fastest response.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <div class="nav-logo" style="margin-bottom:0">
        <div class="nav-logo-mark">
          <img class="px-1 py-1" src="/logo.png"/>
        </div>
        <div>
          <div class="nav-logo-text font-display" style="color:#fff">NiRA .ng</div>
          <div class="nav-logo-sub" style="color:white;">Nigeria Internet Registration Association</div>
        </div>
      </div>
      <p>The official registry operator for Nigeria's .ng country-code top-level domain. Promoting a safe, stable, and open Nigerian internet since 2005.</p>
    </div>
    <div class="footer-col">
      <h5 class="font-display">Registry</h5>
      <ul>
        <li><a href="#">About NiRA</a></li>
        <li><a href="#">Domain Policy</a></li>
        <li><a href="#">Dispute Resolution</a></li>
        <li><a href="#">WHOIS Lookup</a></li>
        <li><a href="#">Registrar Accreditation</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5 class="font-display">DNS Abuse</h5>
      <ul>
        <li><a href="/report">Report Abuse</a></li>
        <li><a href="#dns-abuse">What is DNS Abuse?</a></li>
        <li><a href="#how">How It Works</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5 class="font-display">Connect</h5>
      <ul>
        <li><a href="#">admin@nira.org.ng </a></li>
        <li><a href="https://register.ng">register.ng</a></li>
        <li><a href="#">Twitter / X</a></li>
        <li><a href="#">LinkedIn</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom font-display">
    <span>© 2026 Nigeria Internet Registration Association (NiRA). All rights reserved.</span>
    <div style="display:flex;gap:24px">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Use</a>
      <a href="#">Cookies</a>
    </div>
  </div>
</footer>

<script>
(function(){
  const items = document.querySelectorAll('.faq-item');
  items.forEach(item => {
    item.querySelector('.faq-q').addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      items.forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  const revealEls = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  revealEls.forEach(el => observer.observe(el));
})();
</script>
</body>
</html>