<?php
// Browser caching disable karne ke headers taaki pichla user show na ho
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start(); // Session check karne ke liye zaroori hai
$siteName = 'MarketLink';
$year     = date('Y');

// --- Contact form handler ---
$formStatus = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {
        // TODO: send mail or insert into database tables.
        $formStatus = 'success';
    } else {
        $formStatus = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($siteName); ?> — Farm fresh, just a click away</title>
<meta name="description" content="MarketLink connects local farmers-market growers with customers: weekly stock, pre-orders, map-based pickup, and honest reviews.">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #20281c;
  --canvas: #f2ede0;
  --canvas-2: #e9e1cd;
  --evergreen: #2e4a2c;
  --evergreen-2: #233a22;
  --harvest: #e0a52c;
  --harvest-dark: #b9821a;
  --clay: #a9502f;
  --cream-line: rgba(32, 40, 28, .14);
  --font-display: 'Fraunces', serif;
  --font-body: 'Work Sans', sans-serif;
  --radius: 8px;
  --max: 1100px;
  --card-bg: #fbf8ee;
  --crate-bg: rgba(46, 74, 44, 0.92);
  --crate-border: #233a22;
}

*, *::before, *::after {
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  background: var(--canvas);
  color: var(--ink);
  font-family: var(--font-body);
  font-size: 16px;
  line-height: 1.55;
  -webkit-font-smoothing: antialiased;
  animation: pageEntrance 0.8s ease-out forwards;
}

/* --- KEYFRAME ANIMATIONS --- */
@keyframes pageEntrance {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideFromLeft {
  0% { opacity: 0; transform: translateX(-50px); }
  100% { opacity: 1; transform: translateX(0); }
}

@keyframes slideFromRight {
  0% { opacity: 0; transform: translateX(50px); }
  100% { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInUp {
  0% { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes floatAnim {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.animate-fade-up {
  animation: fadeInUp 0.9s cubic-bezier(0.1, 1, 0.3, 1) forwards;
}

.animate-float {
  animation: floatAnim 4s ease-in-out infinite;
}

img {
  max-width: 100%;
  display: block;
}

a {
  color: inherit;
  text-decoration: none;
}

h1, h2, h3 {
  font-family: var(--font-display);
  margin: 0;
  color: var(--evergreen-2);
  font-weight: 680;
  line-height: 1.1;
}

h2 {
  font-size: clamp(1.7rem, 3.2vw, 2.5rem);
}

h3 {
  font-size: 1.15rem;
}

p {
  margin: 0;
}

.eyebrow {
  font-family: var(--font-body);
  font-weight: 600;
  font-size: .85rem;
  color: var(--evergreen);
  text-transform: uppercase;
  letter-spacing: .05em;
}

.eyebrow::before {
  content: "— ";
  color: var(--harvest-dark);
}

.wrap {
  max-width: var(--max);
  margin: 0 auto;
  padding: 0 24px;
}

/* --- BUTTONS WITH HOVER ANIMATIONS --- */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 22px;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: .95rem;
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: transform .25s ease, background .2s ease, box-shadow .2s ease;
}

.btn:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 6px 18px rgba(46, 74, 44, 0.2);
}

.btn-primary {
  background: var(--evergreen);
  color: #fbf8ef;
}

.btn-primary:hover {
  background: var(--evergreen-2);
}

.btn-ghost {
  background: transparent;
  border-color: var(--cream-line);
  color: var(--evergreen-2);
}

.btn-ghost:hover {
  border-color: var(--evergreen);
  background: var(--cream-line);
}

.btn-harvest {
  background: var(--harvest);
  color: var(--evergreen-2);
}

.btn-harvest:hover {
  background: var(--harvest-dark);
  color: #fff;
}

/* --- HEADER / NAV WITH ANIMATION --- */
.site-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: var(--canvas);
  opacity: 0.95;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--cream-line);
  animation: slideFromLeft 0.8s ease-out forwards;
}

.site-header .nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 72px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-display);
  font-weight: 680;
  font-size: 1.25rem;
  color: var(--evergreen-2);
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.05);
}

.nav-links {
  display: flex;
  gap: 28px;
  align-items: center;
}

.nav-links a {
  font-size: .9rem;
  font-weight: 500;
  color: var(--ink);
  opacity: 0.8;
  transition: all 0.3s ease;
}

.nav-links a:hover {
  opacity: 1;
  color: var(--evergreen);
  transform: translateY(-2px);
}

.nav-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.burger {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  flex-direction: column;
  gap: 5px;
}

.burger span {
  display: block;
  width: 24px;
  height: 2px;
  background: var(--evergreen-2);
  transition: 0.3s;
}

/* --- HERO SECTION --- */
.hero {
  padding: 60px 0 80px;
}

.hero-grid {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 40px;
  align-items: center;
}

.hero-text {
  animation: slideFromLeft 1s ease-out forwards;
}

.hero-text h1 {
  font-size: 3.2rem;
  margin: 14px 0;
  letter-spacing: -0.02em;
}

.hero-text h1 em {
  font-style: italic;
  color: var(--evergreen);
}

.hero-lede {
  font-size: 1.08rem;
  color: var(--ink);
  opacity: 0.85;
  margin-bottom: 24px;
  max-width: 48ch;
}

.hero-actions {
  display: flex;
  gap: 14px;
  margin-bottom: 36px;
  flex-wrap: wrap;
}

.hero-stats {
  display: flex;
  gap: 36px;
  padding-top: 24px;
  border-top: 1px solid var(--cream-line);
}

.hero-stats b {
  display: block;
  font-family: var(--font-display);
  font-size: 1.5rem;
  color: var(--evergreen-2);
}

.hero-stats span {
  font-size: .82rem;
  color: var(--ink);
  opacity: 0.7;
}

/* --- 3D CRATE STAGE --- */
.hero-visual {
  animation: slideFromRight 1s ease-out forwards;
}

.crate-stage {
  height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
  perspective: 1000px;
}

.crate {
  width: 180px;
  height: 180px;
  position: relative;
  transform-style: preserve-3d;
  animation: rotateCrate 16s linear infinite;
}

.crate:hover {
  animation-play-state: paused;
}

@keyframes rotateCrate {
  0% { transform: rotateX(-15deg) rotateY(0deg); }
  100% { transform: rotateX(-15deg) rotateY(360deg); }
}

.face {
  position: absolute;
  width: 180px;
  height: 180px;
  background: var(--crate-bg);
  border: 2px solid var(--crate-border);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-weight: 600;
  color: #fbf8ef;
  font-size: 1.1rem;
  box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.2);
}

.face span {
  font-size: .75rem;
  font-family: var(--font-body);
  font-weight: 500;
  color: var(--harvest);
  margin-bottom: 4px;
  text-transform: uppercase;
}

.face-front { transform: rotateY(0deg) translateZ(90px); }
.face-back { transform: rotateY(180deg) translateZ(90px); }
.face-right { transform: rotateY(90deg) translateZ(90px); }
.face-left { transform: rotateY(-90deg) translateZ(90px); }
.face-top { transform: rotateX(90deg) translateZ(90px); }
.face-bottom { transform: rotateX(-90deg) translateZ(90px); }

.crate-caption {
  text-align: center;
  font-size: .85rem;
  color: var(--ink);
  opacity: 0.7;
  margin-top: 16px;
}

/* --- PROBLEM SECTION --- */
.problem {
  padding: 80px 0;
  background: var(--canvas-2);
  border-top: 1px solid var(--cream-line);
  border-bottom: 1px solid var(--cream-line);
}

.problem .wrap {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 50px;
  align-items: center;
}

.chalk {
  background: #1a2618;
  color: #f2ede0;
  padding: 36px;
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  transform: rotate(-1deg);
  transition: transform 0.3s ease;
  animation: slideFromLeft 1s ease-out forwards;
}

.chalk:hover {
  transform: rotate(0deg) scale(1.02);
}

.chalk-title {
  font-family: var(--font-display);
  font-size: 1.2rem;
  color: var(--harvest);
  margin-bottom: 16px;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.2);
  padding-bottom: 8px;
}

.chalk ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
  font-size: .95rem;
  opacity: 0.9;
}

.problem-content {
  animation: slideFromRight 1s ease-out forwards;
}

.problem-content h2 {
  font-size: 2.2rem;
  margin: 12px 0 16px;
}

.problem-list {
  list-style: none;
  padding: 0;
  margin-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.problem-list li {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 500;
  color: var(--ink);
  transition: transform 0.2s ease;
}

.problem-list li:hover {
  transform: translateX(5px);
}

/* --- HOW IT WORKS --- */
.how {
  padding: 90px 0;
}

.how-head {
  text-align: center;
  max-width: 600px;
  margin: 0 auto 50px;
  animation: fadeInUp 0.9s ease-out forwards;
}

.how-head h2 {
  font-size: 2.4rem;
  margin-top: 10px;
}

.steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
}

.step {
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 32px;
  border-radius: 12px;
  position: relative;
  transition: all 0.3s ease;
  animation: fadeInUp 1s ease-out forwards;
}

.step:hover {
  transform: translateY(-8px);
  box-shadow: 0 10px 25px rgba(46, 74, 44, 0.12);
  border-color: var(--evergreen);
}

.num {
  font-family: var(--font-display);
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--harvest);
  display: block;
  margin-bottom: 12px;
}

.step h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
}

.step p {
  font-size: .92rem;
  color: var(--ink);
  opacity: 0.8;
}

/* --- SEASONAL HARVEST GUIDE --- */
.seasonal {
  padding: 90px 0;
}

.seasonal h2 {
  font-size: 2.4rem;
  margin: 10px 0 40px;
}

.season-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.season-card {
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 24px;
  border-radius: 10px;
  transition: all 0.3s ease;
  animation: fadeInUp 1s ease-out forwards;
}

.season-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 25px rgba(46, 74, 44, 0.1);
}

.season-card h3 {
  font-family: var(--font-display);
  color: var(--evergreen);
  margin-bottom: 8px;
  font-size: 1.2rem;
}

.season-card p {
  font-size: .88rem;
  color: var(--ink);
  opacity: 0.8;
}

/* --- MAP + AI SECTION --- */
.split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  background: #1a2618;
  color: #fbf8ef;
  border-top: 1px solid var(--cream-line);
  border-bottom: 1px solid var(--cream-line);
}

.map-block, .ai-block {
  padding: 60px 40px;
  position: relative;
  transition: transform 0.3s ease;
}

.map-block {
  background: #233a22;
}

.ai-block {
  background: #fbf8ee;
  color: var(--ink);
}

.ai-chip {
  display: inline-block;
  background: var(--canvas-2);
  color: var(--evergreen);
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 12px;
}

.ai-sample {
  background: var(--canvas);
  border: 1px solid var(--cream-line);
  padding: 14px;
  border-radius: var(--radius);
  margin-top: 16px;
  font-size: 0.9rem;
}

/* --- REVIEWS --- */
.reviews {
  padding: 90px 0;
  background: var(--canvas-2);
}

.reviews h2 {
  font-size: 2.4rem;
  margin: 10px 0 40px;
}

.review-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

.review {
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 28px;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.3s ease;
  animation: fadeInUp 1s ease-out forwards;
}

.review:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 25px rgba(32, 40, 28, 0.08);
}

.stars {
  color: #d48b11;
  font-size: 1.1rem;
  margin-bottom: 12px;
  letter-spacing: 2px;
}

.quote {
  font-size: .95rem;
  font-style: italic;
  color: var(--ink);
  opacity: 0.85;
  margin-bottom: 20px;
}

.who {
  font-size: .85rem;
  font-weight: 600;
  color: var(--evergreen);
}

/* --- FAQ SECTION --- */
.faq {
  padding: 90px 0;
}

.faq-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 40px;
}

.faq-item {
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  border-radius: var(--radius);
  padding: 18px 22px;
  transition: all 0.3s ease;
  animation: slideFromLeft 1s ease-out forwards;
}

.faq-item:hover {
  transform: translateX(5px);
  border-color: var(--evergreen);
}

.faq-item[open] {
  background: var(--canvas);
  box-shadow: 0 4px 15px rgba(32, 40, 28, 0.05);
}

.faq-item summary {
  font-family: var(--font-display);
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--evergreen-2);
  cursor: pointer;
  outline: none;
}

.faq-item p {
  margin-top: 12px;
  font-size: .93rem;
  color: var(--ink);
  opacity: 0.8;
}

/* --- CTA SECTION --- */
.cta {
  padding: 80px 0;
  text-align: center;
}

.cta-box {
  background: var(--evergreen);
  color: #fbf8ef;
  padding: 60px 40px;
  border-radius: 16px;
  box-shadow: 0 20px 50px rgba(46, 74, 44, 0.2);
  animation: fadeInUp 1s ease-out forwards;
  transition: transform 0.3s ease;
}

.cta-box:hover {
  transform: scale(1.01);
}

.cta-box h2 {
  color: #fbf8ef;
  font-size: 2.5rem;
  margin: 10px 0 16px;
}

.cta-box p {
  max-width: 50ch;
  margin: 0 auto 30px;
  opacity: 0.9;
  color: #cfd6c4;
}

.cta-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  flex-wrap: wrap;
}

/* --- CONTACT --- */
.contact {
  padding: 90px 0;
  background: var(--canvas-2);
  border-top: 1px solid var(--cream-line);
}

.contact-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 50px;
}

.contact h2 {
  font-size: 2.2rem;
  margin: 10px 0 24px;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 22px;
}

.contact-form input, 
.contact-form textarea {
  width: 100%;
  padding: 14px;
  border: 1.5px solid var(--cream-line);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--ink);
  font-family: var(--font-body);
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.contact-form input:focus, 
.contact-form textarea:focus {
  outline: none;
  border-color: var(--evergreen);
  box-shadow: 0 0 10px rgba(46, 74, 44, 0.15);
  transform: translateY(-2px);
}
.nav-auth {
  display: flex;
  align-items: center;
  gap: 12px;
}
.contact-form textarea {
  min-height: 120px;
  resize: vertical;
}

.form-msg {
  padding: 12px;
  border-radius: var(--radius);
  margin-bottom: 16px;
  font-size: 0.9rem;
}

.form-msg.success {
  background: #e3efd8;
  color: #2e4a2c;
}

.form-msg.error {
  background: #fdf2f0;
  color: var(--clay);
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 24px;
  justify-content: center;
}

.contact-info .item {
  display: flex;
  gap: 16px;
  align-items: flex-start;
  transition: transform 0.2s ease;
}

.contact-info .item:hover {
  transform: translateX(4px);
}

.contact-info h3 {
  font-size: 1rem;
  margin-bottom: 4px;
}

.contact-info p {
  font-size: .9rem;
  color: var(--ink);
  opacity: 0.8;
  margin: 0;
}

/* --- FOOTER --- */
footer {
  background: var(--evergreen-2);
  color: #cfd6c4;
  padding: 60px 0 30px;
  font-size: .88rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr;
  gap: 40px;
  padding-bottom: 30px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.footer-grid h4 {
  color: #fbf8ef;
  font-family: var(--font-display);
  margin-bottom: 16px;
  font-size: 1.05rem;
}

.footer-grid ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-grid a {
  text-decoration: none;
  color: #c7cfb9;
  transition: color 0.2s ease, transform 0.2s ease;
}

.footer-grid a:hover {
  color: var(--harvest);
  transform: translateX(3px);
}

.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 20px;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  font-size: .85rem;
  opacity: 0.8;
}

/* --- RESPONSIVE MEDIA QUERIES --- */
@media (max-width: 900px) {
  .hero-grid,
  .problem .wrap,
  .split,
  .contact-grid,
  .footer-grid,
  .season-grid,
  .steps,
  .review-grid {
    grid-template-columns: 1fr;
  }
  
  .nav-links {
    display: none;
    position: absolute;
    top: 72px;
    left: 0;
    width: 100%;
    background: var(--canvas);
    flex-direction: column;
    padding: 24px;
    border-bottom: 1px solid var(--cream-line);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
  }
  
  .nav-links.open {
    display: flex;
  }
  
  .burger {
    display: flex;
  }
}
</style>
</head>
<body>

<!-- Apple-style Glassmorphism Navbar -->
<header class="site-header">
  <nav class="nav wrap">
    <a href="#top" class="logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M4 10 L12 4 L20 10 V20 H4 Z" stroke="#2e4a2c" stroke-width="1.6" fill="#e0a52c"/><path d="M9 20 V14 H15 V20" stroke="#2e4a2c" stroke-width="1.6" fill="none"/></svg>
      <?php echo htmlspecialchars($siteName); ?>
    </a>
    <button class="burger" id="burgerBtn" aria-label="Toggle menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <div class="nav-links" id="navLinks">
      <a href="#features">Features</a>
      <a href="markets.php">Markets</a>
      <a href="#faq">FAQ</a>
      <a href="#contact">Contact</a>
      
    </div>
    
    <div class="nav-auth">
      <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Agar user logged in hai -->
          <div class="user-profile-menu" style="display: flex; align-items: center; gap: 12px;">
              <a href="profile.php" class="profile-btn" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--ink); font-weight: 600;">
                  <span style="background: var(--evergreen); color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                      👤
                  </span>
                  <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
              </a>
              <a href="logout.php" class="btn-logout" style="font-size: 0.9rem; color: #a9502f; text-decoration: none; font-weight: 600;">Logout</a>
          </div>
      <?php else: ?>
          <!-- Agar user logged in nahi hai, toh classes ke zariye spacing theek karein -->
          <a href="login.php" class="btn btn-ghost" style="padding: 8px 16px; font-size: 0.9rem;">Log in</a>
          <a href="join.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">Create Account</a>
      <?php endif; ?>
    </div>
    </nav>
</header>

<main id="top">
  <!-- HERO SECTION -->
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-text animate-fade-up">
        <p class="eyebrow">A pre-order platform for local farmers markets</p>
        <h1>Know what's fresh<br>before you <em>leave home.</em></h1>
        <p class="hero-lede">MarketLink lets local farmers publish their weekly stock and pickup windows, and lets customers browse, reserve, and pick up — no more wasted trips for a sold-out stall.</p>
        <div class="hero-actions">
          <a href="#contact" class="btn btn-primary">Browse markets near you</a>
          <a href="#how" class="btn btn-ghost">See how it works</a>
        </div>
        <div class="hero-stats">
          <div><b>120+</b><span>Local farmers</span></div>
          <div><b>38</b><span>Weekly markets</span></div>
          <div><b>0%</b><span>Payment fees — pay at pickup</span></div>
        </div>
      </div>

      <div class="hero-visual animate-float">
        <div class="crate-stage">
          <div class="crate">
            <div class="face face-front"><span>This week</span>Vegetables</div>
            <div class="face face-back"><span>Fresh picked</span>Fruits</div>
            <div class="face face-right"><span>Farmhouse</span>Dairy &amp; Eggs</div>
            <div class="face face-left"><span>Oven warm</span>Baked Goods</div>
            <div class="face face-top"><span>Restock alert</span>Favorites</div>
            <div class="face face-bottom"><span>Ready for</span>Pickup</div>
          </div>
        </div>
        <p class="crate-caption">A live look at one Farmer's weekly crate — continuous smooth rotation.</p>
      </div>
    </div>

    
  </section>

  <!-- PROBLEM SECTION -->
  <section class="problem">
    <div class="wrap">
      <div class="chalk">
        <p class="chalk-title">Farmer's Market — today's board</p>
        <ul>
          <li>Tomatoes — sold out by 9am ✗</li>
          <li>Stall 12 closed this week (no notice)</li>
          <li>New honey vendor — where do I find them?</li>
          <li>Pickup only, cash or card at the stall</li>
        </ul>
      </div>
      <div class="problem-content">
        <p class="eyebrow">Why we built this</p>
        <h2>Chalkboards and word of mouth don't scale.</h2>
        <p class="lede">Customers show up not knowing who's selling, what's left, or whether a stall is even open. Farmers have no easy way to publish stock, take pre-orders, or build repeat customers.</p>
        <ul class="problem-list">
          <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="#e0a52c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> See real stock and prices before you drive over</li>
          <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="#e0a52c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Reserve items and pick a pickup time slot</li>
          <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="#e0a52c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Find every stall on a map, with directions</li>
          <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12l4 4L19 6" stroke="#e0a52c" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg> Pay Farmers directly at pickup — no platform fees</li>
        </ul>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="how" id="how">
    <div class="wrap">
      <div class="how-head">
        <p class="eyebrow">The pickup loop</p>
        <h2>From weekly harvest to your basket, in three steps</h2>
      </div>
      <div class="steps">
        <div class="step">
          <span class="num">01</span>
          <h3>Farmers list the week's stock</h3>
          <p>Prices, quantities, and pickup windows go up in minutes — reusable from a weekly template, adjusted as things sell out.</p>
        </div>
        <div class="step">
          <span class="num">02</span>
          <h3>Customers browse &amp; reserve</h3>
          <p>Filter by market, day, or category, add items to a cart, and lock in a pickup slot before the Farmer's cut-off time.</p>
        </div>
        <div class="step">
          <span class="num">03</span>
          <h3>Pick up &amp; pay in person</h3>
          <p>Walk up at your slot, pay the Farmer directly, and leave a review once you're home.</p>
        </div>
      </div>
    </div>
  </section>

  
  <!-- SEASONAL HARVEST GUIDE -->
  <section class="seasonal" id="season">
    <div class="wrap">
      <p class="eyebrow">What's in season</p>
      <h2>Fresh harvests throughout the year</h2>
      <div class="season-grid">
        <div class="season-card">
          <h3>Spring</h3>
          <p>Leafy greens, radishes, asparagus, and farm-fresh eggs from pasture-raised hens.</p>
        </div>
        <div class="season-card">
          <h3>Summer</h3>
          <p>Heirloom tomatoes, sweet corn, berries, stone fruits, and crisp cucumbers.</p>
        </div>
        <div class="season-card">
          <h3>Autumn</h3>
          <p>Pumpkins, apples, root vegetables, winter squash, and artisan honey batches.</p>
        </div>
        <div class="season-card">
          <h3>Winter</h3>
          <p>Storage crops, greenhouse microgreens, fresh-baked sourdough, and preserves.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- MAP + AI SECTION -->
  <section class="split" id="map">
    <div class="map-block">
      <p class="eyebrow" style="color:#cfe0c4">Find your stall</p>
      <h2 style="color:#fbf8ef">Every market, mapped.</h2>
      <p style="color:#cfd6c4; margin-top:12px;">Powered by Google Maps API / OpenStreetMap — markers for every market and stall, with directions straight to your pickup point.</p>
    </div>
    <div class="ai-block">
      <span class="ai-chip">Optional · AI assistant</span>
      <h2>Ask MarketLink, not Google.</h2>
      <p style="color:#4a5340; margin-top:12px;">A lightweight chatbot answers the questions customers ask most — market timings, who's open, and whether that heirloom tomato is still around.</p>
      <div class="ai-sample"><b>You:</b> Is the Saturday honey stall open this week?<br><b>MarketLink:</b> Yes — Hollow Creek Apiary is open 8am–1pm, stall 6.</div>
    </div>
  </section>

  <!-- REVIEWS SECTION -->
  <section class="reviews" id="reviews">
    <div class="wrap">
      <p class="eyebrow">From the pickup line</p>
      <h2>What early customers &amp; farmers say</h2>
      <div class="review-grid">
        <div class="review">
          <div class="stars">★★★★★</div>
          <p class="quote">I stopped driving to markets that turned out to be closed. I check MarketLink first now, every time.</p>
          <p class="who">— Amara, regular customer</p>
        </div>
        <div class="review">
          <div class="stars">★★★★★</div>
          <p class="quote">Pre-orders changed our Saturdays. We know what to pack before we even leave the farm.</p>
          <p class="who">— Delgado Family Farm</p>
        </div>
        <div class="review">
          <div class="stars">★★★★☆</div>
          <p class="quote">Reserving pickup slots means no more standing in line for produce that's already gone.</p>
          <p class="who">— Farhan, weekend shopper</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ SECTION -->
  <section class="faq" id="faq">
    <div class="wrap" style="max-width: 800px;">
      <div class="how-head">
        <p class="eyebrow">Got questions?</p>
        <h2>Frequently Asked Questions</h2>
      </div>
      <div class="faq-list">
        <details class="faq-item">
          <summary>Do I need to pay online when placing a pre-order?</summary>
          <p>No! MarketLink operates on a 0% platform fee model. You reserve your items online and pay the farmer directly in cash or card when you pick them up at their stall.</p>
        </details>
        <details class="faq-item">
          <summary>How do I register my farm or stall?</summary>
          <p>Click on "Join as a Farmer" at the top of the page, fill in your business details and operating days, and submit it for admin approval.</p>
        </details>
        <details class="faq-item">
          <summary>What happens if I miss my pickup window?</summary>
          <p>Farmers set specific pickup windows (e.g., Saturday 8am–12pm). If you're running late, you can message the farmer directly through your account dashboard.</p>
        </details>
      </div>
    </div>
  </section>

  <!-- CTA SECTION -->
  <section class="cta">
    <div class="wrap">
      <div class="cta-box">
        <p class="eyebrow" style="color:#e0a52c">Ready when you are</p>
        <h2>Your next market trip starts here.</h2>
        <p>Create a free account as a customer, or register your stall as a Farmer — no payment processing to set up, just your weekly stock.</p>
        <div class="cta-actions">
          <a href="join.php" class="btn btn-harvest">Create a customer account</a>
          <a href="join.php" class="btn btn-ghost" style="border-color:#fbf8ef; color:#fbf8ef;">Register as a Farmer</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="contact" id="contact">
    <div class="wrap contact-grid">
      <div>
        <p class="eyebrow">Questions before you join</p>
        <h2>Talk to the MarketLink team</h2>
        <?php if ($formStatus === 'success'): ?>
          <div class="form-msg success">Thanks — your message has been received. We'll reply within a day.</div>
        <?php elseif ($formStatus === 'error'): ?>
          <div class="form-msg error">Please fill in your name, a valid email, and a message.</div>
        <?php endif; ?>
        <form class="contact-form" method="post" action="#contact">
          <input type="text" name="name" placeholder="Your name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
          <input type="email" name="email" placeholder="Email address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          <textarea name="message" placeholder="How can we help?" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
          <button type="submit" name="contact_submit" class="btn btn-primary">Send message</button>
        </form>
      </div>
      <div class="contact-info">
        <div class="item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11Z" stroke="#2e4a2c" stroke-width="1.6"/></svg>
          <div><h3>Head office</h3><p>4 Harvest Lane, Karachi — map shown via Google Maps API.</p></div>
        </div>
        <div class="item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 5h18v14H3zM3 5l9 7 9-7" stroke="#2e4a2c" stroke-width="1.6"/></svg>
          <div><h3>Email</h3><p>hello@marketlink.example</p></div>
        </div>
        <div class="item">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 4.2 2 2 0 0 1 5 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.9 9.6a16 16 0 0 0 5.5 5.5l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z" stroke="#2e4a2c" stroke-width="1.6"/></svg>
          <div><h3>Support line</h3><p>+92 (555) 019-2044 · Mon–Sat, 8am–4pm</p></div>
        </div>
      </div>
    </div>
  </section>
</main>

<footer>
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="logo" style="color:#fbf8ef; margin-bottom:10px;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 10 L12 4 L20 10 V20 H4 Z" stroke="#e0a52c" stroke-width="1.6" fill="none"/></svg>
          <?php echo htmlspecialchars($siteName); ?>
        </div>
        <p>Connecting local farmers markets with the people who shop them — one weekly crate at a time.</p>
      </div>
      <div>
        <h4>Platform</h4>
        <ul>
          <li><a href="#how">How it works</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#season">Seasonal Guide</a></li>
          <li><a href="#map">Markets</a></li>
        </ul>
      </div>
      <div>
        <h4>Account</h4>
        <ul>
          <li><a href="login.php">Customer login</a></li>
          <li><a href="login.php">Farmer login</a></li>
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="#contact">Contact us</a></li>
          <li><a href="#faq">FAQ</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?php echo $year; ?> <?php echo htmlspecialchars($siteName); ?>. Payment is always settled in person at pickup.</span>
      <span>Theme: eGreen Basket</span>
    </div>
  </div>
</footer>

</body>
</html>