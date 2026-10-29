<?php
/**
 * watch_live.php — Public guest observer page
 * No login required. Shows live opening status + meeting join link (if set).
 */
$hasMeetingLink = !empty($tender['consensus_meeting_link']);
$tenderId       = (int)$tender['tender_id'];
$statusLabel    = match($tender['status']) {
    'closed'    => ['label' => 'Opening in Progress', 'color' => '#f59e0b', 'pulse' => true],
    'evaluated' => ['label' => 'Evaluation Complete',  'color' => '#10b981', 'pulse' => false],
    'awarded'   => ['label' => 'Awarded',              'color' => '#6366f1', 'pulse' => false],
    default     => ['label' => ucfirst($tender['status']), 'color' => '#6b7280', 'pulse' => false],
};
$totalBids = (int)($stats['total_bids'] ?? 0);
$opened    = (int)($stats['opened']    ?? 0);
$sealed    = (int)($stats['sealed']    ?? 0);
$verified  = (int)($stats['verified']  ?? 0);
$scored    = (int)($stats['scored']    ?? 0);
$rejected  = (int)($stats['rejected']  ?? 0);
?>

<div class="page-bg" style="background-image: url('assets/003.png');"></div>

<!-- ── Hero Header ─────────────────────────────────────────────────────────── -->
<div class="wl-hero fade-in-up">
    <div class="wl-hero-inner">
        <!-- Live badge -->
        <div class="wl-live-row">
            <?php if ($statusLabel['pulse']): ?>
                <span class="wl-live-dot"></span>
            <?php endif; ?>
            <span class="wl-status-pill" style="background:<?= $statusLabel['color'] ?>22; color:<?= $statusLabel['color'] ?>; border:1px solid <?= $statusLabel['color'] ?>55;">
                <?= $statusLabel['pulse'] ? '🔴 ' : '' ?><?= htmlspecialchars($statusLabel['label']) ?>
            </span>
        </div>

        <p class="wl-ref">Ref #MP-<?= str_pad($tenderId, 5, '0', STR_PAD_LEFT) ?> &nbsp;·&nbsp; <?= htmlspecialchars($tender['category']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($tender['region']) ?></p>
        <h1 class="wl-title"><?= htmlspecialchars($tender['title']) ?></h1>

        <div class="wl-meta-row">
            <div class="wl-meta-item">
                <i class="bi bi-cash-stack"></i>
                <span>Budget: <strong>E <?= number_format($tender['budget'], 2) ?></strong></span>
            </div>
            <div class="wl-meta-item">
                <i class="bi bi-calendar-event"></i>
                <span>Deadline: <strong><?= date('d M Y, H:i', strtotime($tender['deadline'])) ?></strong></span>
            </div>
            <div class="wl-meta-item">
                <i class="bi bi-award"></i>
                <span>Grade: <strong><?= htmlspecialchars($tender['required_grade']) ?></strong></span>
            </div>
        </div>

        <!-- ── Join button — ONLY shown when a meeting link has been set ── -->
        <?php if ($hasMeetingLink): ?>
        <div class="wl-join-wrap">
            <a href="<?= htmlspecialchars($tender['consensus_meeting_link']) ?>"
               target="_blank" rel="noopener noreferrer"
               id="btn-join-observer"
               class="wl-join-btn">
                <i class="bi bi-camera-video-fill me-2"></i>
                Join as Observer
                <span class="wl-join-arrow">→</span>
            </a>
            <p class="wl-join-sub">
                <i class="bi bi-eye me-1"></i>
                Watch-only — you will not be able to vote or interact
            </p>
        </div>
        <?php else: ?>
        <div class="wl-no-link-notice">
            <i class="bi bi-clock-history me-2"></i>
            The live meeting link has not been published yet. Check back soon.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Live Status Panel ───────────────────────────────────────────────────── -->
<div class="row g-4 mb-4 fade-in-up" style="animation-delay:.1s">

    <!-- Stage label + auto-refresh indicator -->
    <div class="col-12">
        <div class="wl-stage-bar">
            <div class="wl-stage-left">
                <span class="wl-stage-icon"><i class="bi bi-broadcast"></i></span>
                <div>
                    <div class="wl-stage-label" id="js-stage-label"><?= htmlspecialchars($statusLabel['label']) ?></div>
                    <div class="wl-stage-sub">Live status · refreshes every 10 seconds</div>
                </div>
            </div>
            <span class="wl-refresh-dot" id="js-refresh-dot" title="Auto-refreshing"></span>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="col-6 col-md-3">
        <div class="wl-stat-card">
            <div class="wl-stat-icon" style="background:#e0f2fe;color:#0284c7"><i class="bi bi-envelope-paper"></i></div>
            <div class="wl-stat-val" id="js-total"><?= $totalBids ?></div>
            <div class="wl-stat-lbl">Total Bids</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="wl-stat-card">
            <div class="wl-stat-icon" style="background:#fef3c7;color:#d97706"><i class="bi bi-envelope-open"></i></div>
            <div class="wl-stat-val" id="js-opened"><?= $opened ?></div>
            <div class="wl-stat-lbl">Envelopes Opened</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="wl-stat-card">
            <div class="wl-stat-icon" style="background:#d1fae5;color:#059669"><i class="bi bi-patch-check"></i></div>
            <div class="wl-stat-val" id="js-verified"><?= $verified ?></div>
            <div class="wl-stat-lbl">Verified</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="wl-stat-card">
            <div class="wl-stat-icon" style="background:#ede9fe;color:#7c3aed"><i class="bi bi-lock-fill"></i></div>
            <div class="wl-stat-val" id="js-sealed"><?= $sealed ?></div>
            <div class="wl-stat-lbl">Still Sealed</div>
        </div>
    </div>

    <!-- Progress bar -->
    <div class="col-12">
        <div class="wl-progress-wrap">
            <div class="d-flex justify-content-between small mb-1" style="color:#6b7280;">
                <span>Opening Progress</span>
                <span id="js-progress-pct"><?= $totalBids > 0 ? round(($opened / $totalBids) * 100) : 0 ?>%</span>
            </div>
            <div class="wl-progress-track">
                <div class="wl-progress-fill" id="js-progress-bar"
                     style="width:<?= $totalBids > 0 ? round(($opened / $totalBids) * 100) : 0 ?>%"></div>
            </div>
        </div>
    </div>
</div>

<!-- ── How it Works ────────────────────────────────────────────────────────── -->
<div class="wl-how-section fade-in-up" style="animation-delay:.2s">
    <h3 class="wl-how-title">How the Tender Opening Works</h3>
    <div class="wl-how-grid">
        <div class="wl-how-step">
            <div class="wl-how-num">01</div>
            <div class="wl-how-icon"><i class="bi bi-envelope-check"></i></div>
            <h5>Bids Received</h5>
            <p>Contractors submit sealed digital envelopes before the deadline. No one can view the contents until the session opens.</p>
        </div>
        <div class="wl-how-step">
            <div class="wl-how-num">02</div>
            <div class="wl-how-icon"><i class="bi bi-search"></i></div>
            <h5>Public Opening</h5>
            <p>Evaluators open envelopes one by one in this live session. Each bid's documents are verified and recorded in the audit log.</p>
        </div>
        <div class="wl-how-step">
            <div class="wl-how-num">03</div>
            <div class="wl-how-icon"><i class="bi bi-trophy"></i></div>
            <h5>Award Announced</h5>
            <p>The highest-scoring bid is awarded the contract. All decisions are transparent and recorded permanently.</p>
        </div>
    </div>
</div>

<!-- ── Register CTA ────────────────────────────────────────────────────────── -->
<div class="wl-register-cta fade-in-up" style="animation-delay:.3s">
    <div class="wl-cta-inner">
        <div class="wl-cta-glow"></div>
        <i class="bi bi-building-add wl-cta-icon"></i>
        <h3>Want to compete in future tenders?</h3>
        <p>Register your company today and start submitting bids on MicroProjects Eswatini tenders. It's free to join.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="index.php?route=register" class="wl-cta-btn-primary">
                <i class="bi bi-person-plus me-2"></i>Register My Company
            </a>
            <a href="index.php?route=tenders" class="wl-cta-btn-outline">
                <i class="bi bi-list-ul me-2"></i>Browse All Tenders
            </a>
        </div>
    </div>
</div>

<!-- ── Auto-refresh Script ─────────────────────────────────────────────────── -->
<script>
(function () {
    const tenderId   = <?= $tenderId ?>;
    const dot        = document.getElementById('js-refresh-dot');
    const stageEl    = document.getElementById('js-stage-label');
    const totalEl    = document.getElementById('js-total');
    const openedEl   = document.getElementById('js-opened');
    const verifiedEl = document.getElementById('js-verified');
    const sealedEl   = document.getElementById('js-sealed');
    const pctEl      = document.getElementById('js-progress-pct');
    const barEl      = document.getElementById('js-progress-bar');

    function pulse() {
        dot.classList.add('wl-refresh-active');
        setTimeout(() => dot.classList.remove('wl-refresh-active'), 700);
    }

    function animateVal(el, newVal) {
        if (!el) return;
        const old = parseInt(el.textContent, 10);
        if (old === newVal) return;
        el.classList.add('wl-val-pop');
        el.textContent = newVal;
        setTimeout(() => el.classList.remove('wl-val-pop'), 400);
    }

    function refresh() {
        fetch(`index.php?route=api-opening-status&id=${tenderId}`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) return;
                pulse();
                if (stageEl) stageEl.textContent = d.stage_label;
                animateVal(totalEl,   d.total_bids);
                animateVal(openedEl,  d.opened);
                animateVal(verifiedEl,d.verified);
                animateVal(sealedEl,  d.sealed);
                const pct = d.total_bids > 0 ? Math.round((d.opened / d.total_bids) * 100) : 0;
                if (pctEl) pctEl.textContent = pct + '%';
                if (barEl) barEl.style.width = pct + '%';
            })
            .catch(() => {});
    }

    setInterval(refresh, 10000);
})();
</script>

<!-- ── Page Styles ─────────────────────────────────────────────────────────── -->
<style>
/* ── Animations ── */
.fade-in-up { animation: fadeInUp .5s ease both; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }

/* ── Hero ── */
.wl-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #1e1b4b 100%);
    border-radius: 1.5rem;
    padding: 3rem 2.5rem 2.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,.4);
}
.wl-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 70% 0%, rgba(99,102,241,.25) 0%, transparent 60%);
    pointer-events: none;
}
.wl-hero-inner { position: relative; z-index: 1; }

.wl-live-row { display:flex; align-items:center; gap:.75rem; margin-bottom:1rem; }
.wl-live-dot {
    width: 10px; height: 10px; border-radius: 50%; background: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,.3);
    animation: livePulse 1.2s ease-in-out infinite;
}
@keyframes livePulse {
    0%,100% { box-shadow: 0 0 0 3px rgba(239,68,68,.3); }
    50%      { box-shadow: 0 0 0 7px rgba(239,68,68,.1); }
}
.wl-status-pill {
    font-size: .75rem; font-weight: 700; letter-spacing: .05em;
    padding: .3rem .85rem; border-radius: 999px;
    text-transform: uppercase;
}
.wl-ref { color: rgba(255,255,255,.55); font-size: .8rem; letter-spacing:.04em; margin-bottom:.5rem; }
.wl-title { color: #fff; font-size: clamp(1.5rem,4vw,2.4rem); font-weight: 800; margin-bottom: 1.5rem; line-height:1.2; }

.wl-meta-row { display:flex; flex-wrap:wrap; gap:1.25rem; margin-bottom:2rem; }
.wl-meta-item { display:flex; align-items:center; gap:.5rem; color:rgba(255,255,255,.75); font-size:.9rem; }
.wl-meta-item i { color:rgba(255,255,255,.45); }
.wl-meta-item strong { color:#fff; }

/* ── Join Button ── */
.wl-join-wrap { text-align: center; }
.wl-join-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: #fff; font-weight: 800; font-size: 1.1rem;
    padding: 1rem 2.5rem; border-radius: 999px;
    text-decoration: none;
    box-shadow: 0 8px 30px rgba(239,68,68,.4);
    transition: transform .2s, box-shadow .2s;
    letter-spacing: .02em;
}
.wl-join-btn:hover { color:#fff; transform:translateY(-3px); box-shadow:0 14px 40px rgba(239,68,68,.55); }
.wl-join-arrow { font-size: 1.2rem; transition: transform .2s; }
.wl-join-btn:hover .wl-join-arrow { transform: translateX(4px); }
.wl-join-sub { color: rgba(255,255,255,.5); font-size: .8rem; margin-top: .75rem; }

/* ── No-link notice ── */
.wl-no-link-notice {
    display: inline-flex; align-items: center; gap: .5rem;
    color: rgba(255,255,255,.55); font-size: .9rem;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
    padding: .75rem 1.5rem; border-radius: 999px;
}

/* ── Stage Bar ── */
.wl-stage-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff; border-radius: 1rem; padding: 1rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.wl-stage-left { display:flex; align-items:center; gap:1rem; }
.wl-stage-icon {
    width:44px; height:44px; border-radius:50%;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:1.2rem; flex-shrink:0;
}
.wl-stage-label { font-weight:700; font-size:1rem; color:#1e293b; }
.wl-stage-sub { font-size:.75rem; color:#94a3b8; margin-top:.1rem; }

/* Refresh dot */
.wl-refresh-dot {
    width: 10px; height: 10px; border-radius: 50%;
    background: #cbd5e1; transition: background .3s, transform .3s;
}
.wl-refresh-active { background: #10b981 !important; transform: scale(1.5); }

/* ── Stat Cards ── */
.wl-stat-card {
    background: #fff; border-radius: 1rem; padding: 1.5rem;
    text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,.07);
    transition: transform .2s, box-shadow .2s;
}
.wl-stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
.wl-stat-icon {
    width:48px; height:48px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:1.3rem; margin:0 auto 1rem;
}
.wl-stat-val { font-size: 2rem; font-weight: 800; color: #0f172a; line-height:1; }
.wl-stat-lbl { font-size: .75rem; color: #64748b; margin-top: .35rem; font-weight: 600; text-transform: uppercase; letter-spacing:.04em; }
.wl-val-pop { animation: valPop .4s ease; }
@keyframes valPop { 0%,100%{transform:scale(1)} 50%{transform:scale(1.3)} }

/* ── Progress ── */
.wl-progress-wrap { background:#fff; border-radius:1rem; padding:1.25rem 1.5rem; box-shadow:0 2px 12px rgba(0,0,0,.07); }
.wl-progress-track { height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
.wl-progress-fill {
    height:100%; border-radius:999px;
    background: linear-gradient(90deg,#6366f1,#8b5cf6);
    transition: width .8s cubic-bezier(.4,0,.2,1);
}

/* ── How it Works ── */
.wl-how-section { background:#fff; border-radius:1.5rem; padding:2.5rem; margin-bottom:2rem; box-shadow:0 2px 12px rgba(0,0,0,.07); }
.wl-how-title { font-size:1.4rem; font-weight:800; color:#0f172a; text-align:center; margin-bottom:2rem; }
.wl-how-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:2rem; }
.wl-how-step { text-align:center; }
.wl-how-num { font-size:3rem; font-weight:900; color:#e2e8f0; line-height:1; margin-bottom:.5rem; }
.wl-how-icon {
    width:64px; height:64px; border-radius:1rem;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex; align-items:center; justify-content:center;
    font-size:1.6rem; color:#fff; margin:0 auto 1rem;
    box-shadow:0 8px 20px rgba(99,102,241,.35);
}
.wl-how-step h5 { font-weight:700; color:#1e293b; margin-bottom:.5rem; }
.wl-how-step p { font-size:.85rem; color:#64748b; }

/* ── Register CTA ── */
.wl-register-cta {
    background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1e1b4b 100%);
    border-radius:1.5rem; padding:3rem 2rem; text-align:center;
    position:relative; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.3);
}
.wl-cta-glow {
    position:absolute; inset:0;
    background:radial-gradient(ellipse at 50% 100%,rgba(99,102,241,.35) 0%,transparent 70%);
    pointer-events:none;
}
.wl-cta-inner { position:relative; z-index:1; }
.wl-cta-icon { font-size:3rem; color:rgba(255,255,255,.3); margin-bottom:1rem; display:block; }
.wl-register-cta h3 { color:#fff; font-weight:800; font-size:clamp(1.2rem,3vw,1.75rem); margin-bottom:.75rem; }
.wl-register-cta p { color:rgba(255,255,255,.6); max-width:500px; margin:0 auto 2rem; }
.wl-cta-btn-primary {
    display:inline-flex; align-items:center;
    background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;
    font-weight:700; padding:.85rem 2rem; border-radius:999px;
    text-decoration:none; box-shadow:0 8px 24px rgba(99,102,241,.4);
    transition:transform .2s,box-shadow .2s;
}
.wl-cta-btn-primary:hover { color:#fff; transform:translateY(-3px); box-shadow:0 14px 36px rgba(99,102,241,.55); }
.wl-cta-btn-outline {
    display:inline-flex; align-items:center;
    border:2px solid rgba(255,255,255,.25); color:rgba(255,255,255,.85);
    font-weight:700; padding:.85rem 2rem; border-radius:999px;
    text-decoration:none; transition:border-color .2s,color .2s;
}
.wl-cta-btn-outline:hover { color:#fff; border-color:rgba(255,255,255,.6); }
</style>
