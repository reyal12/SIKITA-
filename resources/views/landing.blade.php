<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIKITA – Sistem Informasi Klinik Terintegrasi</title>
    <meta name="description" content="SIKITA membantu Anda mengakses layanan klinik secara praktis, mulai dari pendaftaran online, pemilihan dokter, hingga pembayaran dan akses catatan medis.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        
        *, *::before, *::after { box-sizing: border-box; }

        
        html { scroll-behavior: smooth; }

       
        .lp-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0 clamp(1rem, 5vw, 3rem);
            height: 68px;
            background: rgba(243, 239, 227, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(192, 195, 185, 0.4);
            transition: box-shadow 0.3s ease, background 0.3s ease;
        }

        .lp-navbar.scrolled {
            background: rgba(243, 239, 227, 0.98);
            box-shadow: 0 4px 24px -4px rgba(46, 58, 51, 0.1);
        }

        .lp-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: var(--warna-teks);
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: 0.01em;
            flex-shrink: 0;
        }

        .lp-brand-mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            background: var(--warna-primer);
            color: #fff;
            flex-shrink: 0;
        }

        .lp-brand-mark svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .lp-nav-links a {
            display: block;
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
            color: var(--warna-teks-muted);
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .lp-nav-links a:hover {
            color: var(--warna-teks);
            background: rgba(118, 147, 130, 0.1);
        }

        .lp-nav-actions {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex-shrink: 0;
        }

        .lp-hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            width: 38px;
            height: 38px;
            padding: 6px;
            border: 1.5px solid var(--warna-netral);
            border-radius: 10px;
            background: transparent;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .lp-hamburger span {
            display: block;
            height: 2px;
            border-radius: 2px;
            background: var(--warna-teks);
            transition: all 0.3s ease;
        }

        .lp-hamburger:hover {
            background: rgba(118, 147, 130, 0.1);
        }

        
        .lp-mobile-nav {
            display: none;
            position: fixed;
            top: 68px;
            left: 0;
            right: 0;
            z-index: 99;
            padding: 1rem;
            background: rgba(243, 239, 227, 0.98);
            border-bottom: 1px solid rgba(192, 195, 185, 0.5);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 24px -4px rgba(46, 58, 51, 0.12);
        }

        .lp-mobile-nav.open { display: block; }

        .lp-mobile-nav ul {
            list-style: none;
            margin: 0 0 1rem;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .lp-mobile-nav ul a {
            display: block;
            padding: 0.65rem 0.9rem;
            border-radius: 10px;
            color: var(--warna-teks);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .lp-mobile-nav ul a:hover {
            background: rgba(118, 147, 130, 0.1);
        }

        .lp-mobile-nav-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .lp-mobile-nav-actions .sikita-btn-primer,
        .lp-mobile-nav-actions .sikita-btn-outline {
            width: 100%;
        }

        
        .lp-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem 1.3rem;
            border-radius: 12px;
            background: var(--warna-primer);
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
            box-shadow: 0 2px 8px rgba(118, 147, 130, 0.3);
        }

        .lp-btn-primary:hover {
            background: var(--warna-primer-hover);
            box-shadow: 0 4px 14px rgba(118, 147, 130, 0.4);
            transform: translateY(-1px);
        }

        .lp-btn-primary:active {
            transform: scale(0.99) translateY(0);
        }

        .lp-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem 1.3rem;
            border-radius: 12px;
            border: 1.5px solid rgba(192, 195, 185, 0.8);
            background: transparent;
            color: var(--warna-teks);
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .lp-btn-outline:hover {
            border-color: var(--warna-primer);
            color: var(--warna-primer-hover);
            background: rgba(118, 147, 130, 0.07);
            transform: translateY(-1px);
        }

        
        .lp-container {
            width: min(1160px, calc(100% - 2.5rem));
            margin: 0 auto;
        }

        .lp-section {
            padding: 5rem 0;
        }

        .lp-section-sm {
            padding: 4rem 0;
        }

        
        .lp-eyebrow {
            display: inline-block;
            margin-bottom: 0.75rem;
            padding: 0.3rem 0.85rem;
            border-radius: 999px;
            background: rgba(118, 147, 130, 0.12);
            color: var(--warna-primer-hover);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .lp-heading {
            margin: 0 0 1rem;
            color: var(--warna-teks);
            font-size: clamp(1.9rem, 3.5vw, 2.6rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.025em;
        }

        .lp-lead {
            margin: 0;
            color: var(--warna-teks-muted);
            font-size: clamp(1rem, 1.8vw, 1.1rem);
            line-height: 1.65;
            max-width: 54ch;
        }

       
        .lp-hero {
            padding: 7.5rem 0 5rem;
            background: linear-gradient(135deg, #f3efe3 0%, #edf5ef 55%, #e8dfc8 100%);
            overflow: hidden;
            position: relative;
        }

        .lp-hero::before {
            content: '';
            position: absolute;
            top: -120px;
            right: -120px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(118, 147, 130, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .lp-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(118, 147, 130, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .lp-hero-inner {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            z-index: 1;
            max-width: 760px;
        }

        .lp-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(118, 147, 130, 0.25);
            color: var(--warna-primer-hover);
            font-size: 0.78rem;
            font-weight: 700;
        }

        .lp-hero-badge span.dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--warna-primer);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.85); }
        }

        .lp-hero-title {
            margin: 0 0 1.25rem;
            color: var(--warna-teks);
            font-size: clamp(2.1rem, 4.5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .lp-hero-title .accent {
            color: var(--warna-primer);
        }

        .lp-hero-desc {
            margin: 0 0 2rem;
            color: var(--warna-teks-muted);
            font-size: clamp(1rem, 1.8vw, 1.075rem);
            line-height: 1.7;
            max-width: 52ch;
        }

        .lp-hero-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
        }

        .lp-btn-hero-primary {
            padding: 0.85rem 1.75rem;
            font-size: 1rem;
            border-radius: 14px;
        }

        .lp-btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.85rem 1.5rem;
            border-radius: 14px;
            border: 1.5px solid rgba(192, 195, 185, 0.9);
            background: transparent;
            color: var(--warna-teks);
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .lp-btn-hero-secondary:hover {
            border-color: var(--warna-primer);
            color: var(--warna-primer-hover);
            background: rgba(118, 147, 130, 0.07);
        }

       
        .lp-hero-visual {
            display: flex;
            justify-content: flex-end;
            position: relative;
        }

        .lp-hero-mockup {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(192, 195, 185, 0.5);
            box-shadow:
                0 4px 6px -1px rgba(46, 58, 51, 0.06),
                0 24px 48px -12px rgba(46, 58, 51, 0.15);
            overflow: hidden;
        }

        .lp-mockup-topbar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid #edf0ee;
            background: #fafaf8;
        }

        .lp-mockup-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .lp-mockup-dot--red { background: #ff6059; }
        .lp-mockup-dot--yellow { background: #ffbd2e; }
        .lp-mockup-dot--green { background: #28c840; }

        .lp-mockup-title-bar {
            flex: 1;
            margin-left: 0.5rem;
            height: 10px;
            border-radius: 5px;
            background: #e8ebe9;
            max-width: 160px;
        }

        .lp-mockup-body {
            padding: 1.25rem;
        }

        .lp-mockup-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.15rem;
            border-radius: 14px;
            background: linear-gradient(110deg, #edf5ef 0%, #f7faf7 100%);
            border: 1px solid #d8edd8;
            margin-bottom: 1rem;
        }

        .lp-mockup-banner-text h3 {
            margin: 0 0 0.2rem;
            color: #344b3b;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .lp-mockup-banner-text p {
            margin: 0;
            color: #718078;
            font-size: 0.74rem;
        }

        .lp-mockup-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--warna-primer);
            display: grid;
            place-items: center;
            color: #fff;
            flex-shrink: 0;
        }

        .lp-mockup-icon svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-mockup-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.65rem;
            margin-bottom: 1rem;
        }

        .lp-mockup-card {
            padding: 0.9rem;
            border-radius: 12px;
            border: 1px solid #e8ece9;
            background: #fff;
        }

        .lp-mockup-card-label {
            margin: 0 0 0.3rem;
            color: #87938c;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .lp-mockup-card-val {
            margin: 0;
            color: #34443b;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .lp-mockup-card-sub {
            margin: 0.2rem 0 0;
            color: #87938c;
            font-size: 0.7rem;
        }

        .lp-mockup-list-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 0;
            border-bottom: 1px solid #edf0ee;
        }

        .lp-mockup-list-item:last-child { border-bottom: 0; }

        .lp-mockup-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dce9df;
            display: grid;
            place-items: center;
            color: #4b6b55;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .lp-mockup-list-text { flex: 1; min-width: 0; }

        .lp-mockup-list-text strong {
            display: block;
            color: #34443b;
            font-size: 0.78rem;
        }

        .lp-mockup-list-text span {
            color: #87938c;
            font-size: 0.68rem;
        }

        .lp-mockup-badge {
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .lp-mockup-badge--green {
            background: #edf7f1;
            color: #2f6f4e;
        }

        .lp-mockup-badge--yellow {
            background: #fff3d4;
            color: #755300;
        }

        
        .lp-about {
            background: #fff;
        }

        .lp-about-inner {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 4rem;
            align-items: center;
        }

        .lp-about-visual {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }

        .lp-about-card {
            padding: 1.4rem;
            border-radius: 16px;
            border: 1px solid rgba(192, 195, 185, 0.5);
            background: var(--warna-latar);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .lp-about-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px -6px rgba(46, 58, 51, 0.12);
        }

        .lp-about-card:nth-child(2),
        .lp-about-card:nth-child(4) {
            margin-top: 0;
        }

        .lp-about-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(118, 147, 130, 0.12);
            color: var(--warna-primer-hover);
            display: grid;
            place-items: center;
            margin-bottom: 0.85rem;
        }

        .lp-about-card-icon svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-about-card h4 {
            margin: 0 0 0.3rem;
            color: var(--warna-teks);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .lp-about-card p {
            margin: 0;
            color: var(--warna-teks-muted);
            font-size: 0.8rem;
            line-height: 1.55;
        }

        
        .lp-features {
            background: var(--warna-latar);
        }

        .lp-features-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 3.5rem;
        }

        .lp-features-header .lp-lead {
            margin: 0 auto;
        }

        .lp-features-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.25rem;
        }

        .lp-feature-card {
            padding: 1.75rem 1.5rem;
            border-radius: 18px;
            background: #fff;
            border: 1px solid rgba(192, 195, 185, 0.4);
            box-shadow: 0 4px 16px -6px rgba(46, 58, 51, 0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .lp-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px -8px rgba(46, 58, 51, 0.14);
            border-color: rgba(118, 147, 130, 0.35);
        }

        .lp-feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            background: rgba(118, 147, 130, 0.12);
            color: var(--warna-primer);
            display: grid;
            place-items: center;
            margin-bottom: 1.15rem;
            transition: background-color 0.25s ease;
        }

        .lp-feature-card:hover .lp-feature-icon {
            background: var(--warna-primer);
            color: #fff;
        }

        .lp-feature-icon svg {
            width: 26px;
            height: 26px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-feature-card h3 {
            margin: 0 0 0.6rem;
            color: var(--warna-teks);
            font-size: 1rem;
            font-weight: 700;
        }

        .lp-feature-card p {
            margin: 0;
            color: var(--warna-teks-muted);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        
        .lp-steps {
            background: #fff;
        }

        .lp-steps-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 3.5rem;
        }

        .lp-steps-timeline {
            position: relative;
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 0;
        }

        .lp-steps-timeline::before {
            content: '';
            position: absolute;
            top: 28px;
            left: calc(100% / 14);
            right: calc(100% / 14);
            height: 2px;
            background: linear-gradient(to right, var(--warna-primer), rgba(118, 147, 130, 0.2));
            z-index: 0;
        }

        .lp-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 0.5rem;
            position: relative;
            z-index: 1;
        }

        .lp-step-number {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fff;
            border: 2.5px solid var(--warna-primer);
            color: var(--warna-primer);
            font-size: 1rem;
            font-weight: 800;
            display: grid;
            place-items: center;
            margin-bottom: 1rem;
            flex-shrink: 0;
            transition: background 0.25s ease, color 0.25s ease;
            box-shadow: 0 0 0 5px rgba(118, 147, 130, 0.1);
        }

        .lp-step:hover .lp-step-number {
            background: var(--warna-primer);
            color: #fff;
        }

        .lp-step-icon {
            width: 28px;
            height: 28px;
            margin-bottom: 0.5rem;
        }

        .lp-step h4 {
            margin: 0 0 0.35rem;
            color: var(--warna-teks);
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .lp-step p {
            margin: 0;
            color: var(--warna-teks-muted);
            font-size: 0.74rem;
            line-height: 1.5;
        }

        
        .lp-adv {
            background: var(--warna-latar);
        }

        .lp-adv-inner {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 4rem;
            align-items: center;
        }

        .lp-adv-list {
            list-style: none;
            margin: 1.5rem 0 0;
            padding: 0;
            display: grid;
            gap: 1rem;
        }

        .lp-adv-item {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
        }

        .lp-adv-check {
            flex-shrink: 0;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--warna-primer);
            color: #fff;
            display: grid;
            place-items: center;
            margin-top: 0.1rem;
        }

        .lp-adv-check svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-adv-item-text h4 {
            margin: 0 0 0.2rem;
            color: var(--warna-teks);
            font-size: 0.95rem;
            font-weight: 700;
        }

        .lp-adv-item-text p {
            margin: 0;
            color: var(--warna-teks-muted);
            font-size: 0.85rem;
            line-height: 1.55;
        }

        
        .lp-adv-visual {
            padding: 2rem;
            background: #fff;
            border-radius: 20px;
            border: 1px solid rgba(192, 195, 185, 0.4);
            box-shadow: 0 8px 32px -8px rgba(46, 58, 51, 0.1);
        }

        .lp-adv-visual-heading {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1.5rem;
        }

        .lp-adv-visual-heading .mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--warna-primer);
            display: grid;
            place-items: center;
            color: #fff;
        }

        .lp-adv-visual-heading .mark svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-adv-visual-heading strong {
            color: var(--warna-teks);
            font-size: 0.9rem;
        }

        .lp-adv-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid #edf0ee;
        }

        .lp-adv-row:last-child { border-bottom: 0; }

        .lp-adv-row-label {
            color: var(--warna-teks-muted);
            font-size: 0.82rem;
        }

        .lp-adv-row-val {
            color: #344b3b;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .lp-adv-row-bar-wrap {
            flex: 1;
            height: 7px;
            border-radius: 999px;
            background: #e8ece9;
            overflow: hidden;
            margin: 0 0.75rem;
        }

        .lp-adv-row-bar {
            height: 100%;
            border-radius: 999px;
            background: var(--warna-primer);
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        
        .lp-cta {
            background: linear-gradient(135deg, #2E3A33 0%, #3f524a 100%);
            position: relative;
            overflow: hidden;
        }

        .lp-cta::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(118, 147, 130, 0.25) 0%, transparent 70%);
        }

        .lp-cta::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232, 223, 200, 0.12) 0%, transparent 70%);
        }

        .lp-cta-inner {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .lp-cta .lp-eyebrow {
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.85);
        }

        .lp-cta .lp-heading {
            color: #fff;
        }

        .lp-cta .lp-lead {
            color: rgba(255, 255, 255, 0.72);
            margin: 0 auto 2.25rem;
        }

        .lp-btn-cta {
            padding: 0.9rem 2rem;
            font-size: 1.025rem;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .lp-btn-cta:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.3);
        }

        
        .lp-footer {
            background: #1e2822;
            color: rgba(255, 255, 255, 0.72);
            padding: 3.5rem 0 2rem;
        }

        .lp-footer-top {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .lp-footer-brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.9rem;
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.05rem;
        }

        .lp-footer-brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--warna-primer);
            display: grid;
            place-items: center;
            color: #fff;
        }

        .lp-footer-brand-mark svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-footer-desc {
            margin: 0;
            font-size: 0.84rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.5);
        }

        .lp-footer-col h5 {
            margin: 0 0 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .lp-footer-col ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 0.6rem;
        }

        .lp-footer-col ul a {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.84rem;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .lp-footer-col ul a:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .lp-footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .lp-footer-contact-item:last-child { margin-bottom: 0; }

        .lp-footer-contact-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            margin-top: 0.1rem;
            fill: none;
            stroke: rgba(118, 147, 130, 0.9);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.5rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.35);
        }

        
        @media (max-width: 1024px) {
            .lp-features-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 860px) {
            .lp-nav-links,
            .lp-nav-actions { display: none; }
            .lp-hamburger { display: flex; }

            .lp-about-inner,
            .lp-adv-inner {
                grid-template-columns: 1fr;
            }

            .lp-steps-timeline {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .lp-steps-timeline::before { display: none; }

            .lp-step {
                flex-direction: row;
                text-align: left;
                gap: 1rem;
            }

            .lp-step-number {
                margin-bottom: 0;
                flex-shrink: 0;
            }

            .lp-footer-top {
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
            }
        }

        @media (max-width: 600px) {
            .lp-section { padding: 3.5rem 0; }
            .lp-hero { padding: 6rem 0 3.5rem; }

            .lp-features-grid {
                grid-template-columns: 1fr;
            }

            .lp-about-visual {
                grid-template-columns: 1fr;
            }

            .lp-about-card:nth-child(2),
            .lp-about-card:nth-child(4) {
                margin-top: 0;
            }

            .lp-footer-top {
                grid-template-columns: 1fr;
            }

            .lp-footer-bottom {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
        }

        
        .lp-fade-in {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }

        .lp-fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .lp-fade-in-delay-1 { transition-delay: 0.1s; }
        .lp-fade-in-delay-2 { transition-delay: 0.2s; }
        .lp-fade-in-delay-3 { transition-delay: 0.3s; }
        .lp-fade-in-delay-4 { transition-delay: 0.4s; }
    </style>
</head>
<body>


<nav class="lp-navbar" id="navbar">
    <a href="{{ route('landing') }}" class="lp-brand">
        <span class="lp-brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                <path d="M12 2v20M2 12h20"/>
            </svg>
        </span>
        SIKITA
    </a>

    <ul class="lp-nav-links" role="list">
        <li><a href="{{ route('landing') }}">Beranda</a></li>
        <li><a href="#tentang">Tentang Kami</a></li>
        <li><a href="#layanan">Layanan</a></li>
        <li><a href="#fitur">Fitur</a></li>

    </ul>

    <div class="lp-nav-actions">
        <a href="{{ route('login') }}" class="lp-btn-outline">Masuk</a>
        <a href="{{ route('register') }}" class="lp-btn-primary">Daftar Sekarang</a>
    </div>

    <button class="lp-hamburger" id="hamburger" aria-label="Buka menu navigasi" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>
</nav>


<div class="lp-mobile-nav" id="mobile-nav" role="navigation" aria-label="Navigasi mobile">
    <ul>
        <li><a href="{{ route('landing') }}" class="mobile-nav-link">Beranda</a></li>
        <li><a href="#tentang" class="mobile-nav-link">Tentang Kami</a></li>
        <li><a href="#layanan" class="mobile-nav-link">Layanan</a></li>
        <li><a href="#fitur" class="mobile-nav-link">Fitur</a></li>
        
    </ul>
    <div class="lp-mobile-nav-actions">
        <a href="{{ route('login') }}" class="sikita-btn-outline">Masuk</a>
        <a href="{{ route('register') }}" class="sikita-btn-primer">Daftar Sekarang</a>
    </div>
</div>

<main>

    <section class="lp-hero" id="beranda" aria-labelledby="hero-title">
        <div class="lp-container">
            <div class="lp-hero-inner">
                <div class="lp-hero-content">
                    <div class="lp-hero-badge">
                        <span class="dot" aria-hidden="true"></span>
                        Platform Layanan Klinik Digital
                    </div>
                    <h1 class="lp-hero-title" id="hero-title">
                        Layanan Kesehatan Lebih <span class="accent">Mudah</span> dalam Satu Sistem.
                    </h1>
                    <p class="lp-hero-desc">
                        SIKITA membantu Anda mengakses layanan klinik secara praktis, mulai dari pendaftaran online, pemilihan dokter, hingga pembayaran dan akses catatan medis.
                    </p>
                    <div class="lp-hero-actions">
                        <a href="{{ route('register') }}" class="lp-btn-primary lp-btn-hero-primary">
                            Daftar Sekarang
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="#tentang" class="lp-btn-hero-secondary">
                            Pelajari Lebih Lanjut
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14M5 12l7 7 7-7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================= TENTANG ======================= --}}
    <section class="lp-about lp-section" id="tentang" aria-labelledby="about-title">
        <div class="lp-container">
            <div class="lp-about-inner">
                <div class="lp-about-visual lp-fade-in" aria-hidden="true">
                    <div class="lp-about-card">
                        <div class="lp-about-card-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <h4>Terintegrasi</h4>
                        <p>Semua layanan klinik dalam satu platform terpadu.</p>
                    </div>
                    <div class="lp-about-card">
                        <div class="lp-about-card-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 12h6M12 9v6"/></svg>
                        </div>
                        <h4>Mudah Digunakan</h4>
                        <p>Antarmuka yang ramah pengguna untuk semua kalangan.</p>
                    </div>
                    <div class="lp-about-card">
                        <div class="lp-about-card-icon">
                            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12c0 4.97-4.03 9-9 9S3 16.97 3 12 7.03 3 12 3s9 4.03 9 9z"/></svg>
                        </div>
                        <h4>Terpercaya</h4>
                        <p>Data medis Anda tersimpan aman dan terkelola dengan baik.</p>
                    </div>
                    <div class="lp-about-card">
                        <div class="lp-about-card-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h4>Aman &amp; Terjamin</h4>
                        <p>Privasi dan keamanan data pasien menjadi prioritas utama.</p>
                    </div>
                </div>

                <div class="lp-about-text lp-fade-in lp-fade-in-delay-1">
                    <span class="lp-eyebrow">Tentang SIKITA</span>
                    <h2 class="lp-heading" id="about-title">Platform Layanan Klinik untuk Kemudahan Pasien</h2>
                    <p class="lp-lead">
                        SIKITA (Sistem Informasi Klinik Terintegrasi) merupakan platform layanan kesehatan berbasis web yang dirancang untuk mempermudah proses administrasi dan pelayanan pasien. Melalui SIKITA, pasien dapat melakukan berbagai kebutuhan layanan klinik secara online tanpa harus melalui proses administrasi yang rumit.
                    </p>
                    <div style="margin-top: 2rem;">
                        <a href="{{ route('register') }}" class="lp-btn-primary">Mulai Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="lp-features lp-section" id="fitur" aria-labelledby="features-title">
        <div class="lp-container">
            <div class="lp-features-header lp-fade-in">
                <span class="lp-eyebrow">Fitur Utama</span>
                <h2 class="lp-heading" id="features-title">Semua yang Anda Butuhkan</h2>
                <p class="lp-lead">Layanan lengkap dalam satu sistem yang terintegrasi untuk pengalaman klinik yang lebih baik.</p>
            </div>

            <div class="lp-features-grid" id="layanan">
                <div class="lp-feature-card lp-fade-in lp-fade-in-delay-1">
                    <div class="lp-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                    </div>
                    <h3>Pendaftaran Online</h3>
                    <p>Pasien dapat melakukan registrasi dan pendaftaran layanan klinik melalui website dengan mudah tanpa perlu antre.</p>
                </div>

                <div class="lp-feature-card lp-fade-in lp-fade-in-delay-2">
                    <div class="lp-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3>Pilih Dokter</h3>
                    <p>Pasien dapat melihat informasi dokter dan memilih dokter sesuai kebutuhan serta jadwal praktik yang tersedia.</p>
                </div>

                <div class="lp-feature-card lp-fade-in lp-fade-in-delay-3">
                    <div class="lp-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h3>Catatan Medis</h3>
                    <p>Pasien dapat melihat catatan pemeriksaan, diagnosis, dan informasi medis yang diberikan dokter setelah konsultasi.</p>
                </div>

                <div class="lp-feature-card lp-fade-in lp-fade-in-delay-4">
                    <div class="lp-feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    </div>
                    <h3>Pembayaran Online</h3>
                    <p>Pasien dapat melihat rincian tagihan dan melakukan pembayaran layanan klinik secara transparan melalui website.</p>
                </div>
            </div>
        </div>
    </section>

    
    <section class="lp-steps lp-section" aria-labelledby="steps-title">
        <div class="lp-container">
            <div class="lp-steps-header lp-fade-in">
                <span class="lp-eyebrow">Cara Penggunaan</span>
                <h2 class="lp-heading" id="steps-title">Alur Penggunaan SIKITA</h2>
                <p class="lp-lead">Ikuti langkah-langkah mudah berikut untuk mendapatkan layanan klinik terbaik.</p>
            </div>

            <div class="lp-steps-timeline" role="list">
                <div class="lp-step lp-fade-in lp-fade-in-delay-1" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">1</div>
                    <div>
                        <h4>Daftar atau Login</h4>
                        <p>Buat akun atau masuk ke akun SIKITA Anda.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-2" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">2</div>
                    <div>
                        <h4>Pilih Dokter &amp; Jadwal</h4>
                        <p>Tentukan dokter dan waktu pemeriksaan.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-2" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">3</div>
                    <div>
                        <h4>Datang ke Klinik</h4>
                        <p>Kunjungi klinik sesuai jadwal untuk pemeriksaan.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-3" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">4</div>
                    <div>
                        <h4>Lihat Catatan Medis</h4>
                        <p>Akses hasil pemeriksaan dokter secara digital.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-3" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">5</div>
                    <div>
                        <h4>Ambil Obat Resep</h4>
                        <p>Tebus resep dokter di apotek klinik.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-4" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">6</div>
                    <div>
                        <h4>Selesaikan Pembayaran</h4>
                        <p>Bayar tagihan melalui website atau secara tunai.</p>
                    </div>
                </div>
                <div class="lp-step lp-fade-in lp-fade-in-delay-4" role="listitem">
                    <div class="lp-step-number" aria-hidden="true">7</div>
                    <div>
                        <h4>Lihat Riwayat</h4>
                        <p>Pantau riwayat kunjungan dan rekam medis.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="lp-adv lp-section" aria-labelledby="adv-title">
        <div class="lp-container">
            <div class="lp-adv-inner">
                <div class="lp-fade-in">
                    <span class="lp-eyebrow">Keunggulan</span>
                    <h2 class="lp-heading" id="adv-title">Mengapa Memilih SIKITA?</h2>
                    <p class="lp-lead">Dirancang khusus untuk kemudahan pasien dan efisiensi administrasi klinik.</p>

                    <ul class="lp-adv-list" role="list">
                        <li class="lp-adv-item">
                            <span class="lp-adv-check" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div class="lp-adv-item-text">
                                <h4>Proses pendaftaran lebih praktis</h4>
                                <p>Daftar layanan klinik kapan saja dan di mana saja tanpa perlu antre.</p>
                            </div>
                        </li>
                        <li class="lp-adv-item">
                            <span class="lp-adv-check" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div class="lp-adv-item-text">
                                <h4>Informasi dokter dan jadwal lebih mudah diakses</h4>
                                <p>Lihat profil dokter dan pilih jadwal yang sesuai kebutuhan Anda.</p>
                            </div>
                        </li>
                        <li class="lp-adv-item">
                            <span class="lp-adv-check" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div class="lp-adv-item-text">
                                <h4>Catatan medis tersimpan secara digital</h4>
                                <p>Riwayat kesehatan Anda aman, terstruktur, dan mudah diakses kapan pun.</p>
                            </div>
                        </li>
                        <li class="lp-adv-item">
                            <span class="lp-adv-check" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div class="lp-adv-item-text">
                                <h4>Rincian pembayaran lebih transparan</h4>
                                <p>Lihat detail tagihan dengan jelas sebelum melakukan pembayaran.</p>
                            </div>
                        </li>
                        <li class="lp-adv-item">
                            <span class="lp-adv-check" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div class="lp-adv-item-text">
                                <h4>Seluruh layanan terintegrasi dalam satu platform</h4>
                                <p>Dari pendaftaran hingga pembayaran, semuanya tersedia di satu tempat.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="lp-adv-visual lp-fade-in lp-fade-in-delay-2" role="presentation">
                    <div class="lp-adv-visual-heading">
                        <span class="mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 2v20M2 12h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <strong>Ringkasan Keunggulan SIKITA</strong>
                    </div>

                    <div class="lp-adv-row">
                        <span class="lp-adv-row-label">Kemudahan Akses</span>
                        <div class="lp-adv-row-bar-wrap"><div class="lp-adv-row-bar" style="width: 92%"></div></div>
                        <span class="lp-adv-row-val">Tinggi</span>
                    </div>
                    <div class="lp-adv-row">
                        <span class="lp-adv-row-label">Efisiensi Waktu</span>
                        <div class="lp-adv-row-bar-wrap"><div class="lp-adv-row-bar" style="width: 88%"></div></div>
                        <span class="lp-adv-row-val">Tinggi</span>
                    </div>
                    <div class="lp-adv-row">
                        <span class="lp-adv-row-label">Transparansi Data</span>
                        <div class="lp-adv-row-bar-wrap"><div class="lp-adv-row-bar" style="width: 95%"></div></div>
                        <span class="lp-adv-row-val">Sangat Tinggi</span>
                    </div>
                    <div class="lp-adv-row">
                        <span class="lp-adv-row-label">Keamanan Data</span>
                        <div class="lp-adv-row-bar-wrap"><div class="lp-adv-row-bar" style="width: 90%"></div></div>
                        <span class="lp-adv-row-val">Tinggi</span>
                    </div>
                    <div class="lp-adv-row">
                        <span class="lp-adv-row-label">Kepuasan Pengguna</span>
                        <div class="lp-adv-row-bar-wrap"><div class="lp-adv-row-bar" style="width: 87%"></div></div>
                        <span class="lp-adv-row-val">Tinggi</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="lp-cta lp-section" aria-labelledby="cta-title">
        <div class="lp-container">
            <div class="lp-cta-inner lp-fade-in">
                <span class="lp-eyebrow">Mulai Sekarang</span>
                <h2 class="lp-heading" id="cta-title">Mulai Akses Layanan Kesehatan dengan Lebih Mudah.</h2>
                <p class="lp-lead">
                    Gunakan SIKITA untuk mendapatkan pengalaman pelayanan klinik yang lebih praktis, terintegrasi, dan efisien.
                </p>
                <a href="{{ route('register') }}" class="lp-btn-primary lp-btn-cta">
                    Daftar Sekarang — Gratis
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</main>



<script>
    
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });

    
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobile-nav');

    hamburger.addEventListener('click', () => {
        const isOpen = mobileNav.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', isOpen);
        hamburger.querySelectorAll('span').forEach((s, i) => {
            if (isOpen) {
                if (i === 0) s.style.transform = 'translateY(7px) rotate(45deg)';
                if (i === 1) s.style.opacity = '0';
                if (i === 2) s.style.transform = 'translateY(-7px) rotate(-45deg)';
            } else {
                s.style.transform = '';
                s.style.opacity = '';
            }
        });
    });


    document.querySelectorAll('.mobile-nav-link').forEach(link => {
        link.addEventListener('click', () => {
            mobileNav.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
            hamburger.querySelectorAll('span').forEach(s => {
                s.style.transform = '';
                s.style.opacity = '';
            });
        });
    });

    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.lp-fade-in').forEach(el => observer.observe(el));

    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>
</body>
</html>
