<?php
// help.php - user manual page for the E-Tendering System
?>

<div class="page-bg" style="background-image: url('assets/003.png');"></div>

<div class="container py-5">
    <div class="row gx-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="mb-3">Interactive User Manual</h1>
                    <p class="text-muted">Learn the system step-by-step with quick navigation, live search, and collapsible sections designed for new users.</p>
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8">
                            <input id="manualSearch" type="search" class="form-control" placeholder="Search topics, keywords, or page names...">
                        </div>
                        <div class="col-md-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-outline-primary" id="expandAll">Expand All</button>
                            <button type="button" class="btn btn-outline-secondary" id="collapseAll">Collapse All</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion" id="helpAccordion">

                <div class="accordion-item help-section" data-keywords="home overview welcome navigation">
                    <h2 class="accordion-header" id="headingIntro">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIntro" aria-expanded="false" aria-controls="collapseIntro">
                            1. Getting Started
                        </button>
                    </h2>
                    <div id="collapseIntro" class="accordion-collapse collapse" aria-labelledby="headingIntro" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>The <strong>Home</strong> page is your starting point. It displays the system overview, recent announcements, and links to the most important sections:</p>
                            <ul>
                                <li><strong>Available Tenders:</strong> Browse open tenders and view details.</li>
                                <li><strong>Public Statistics:</strong> Track procurement metrics.</li>
                                <li><strong>Guidance / Help:</strong> Access tutorials and this manual.</li>
                            </ul>
                            <p>Tip: Use the top navigation menu to move between pages quickly.</p>
                            <div class="text-center mt-4">
                                <img src="/E-Tendering system project/public/assets/platform-overview.png" alt="Platform Overview" class="img-fluid rounded shadow-sm border border-2">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="register login account contractor evaluator admin">
                    <h2 class="accordion-header" id="headingRegister">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRegister" aria-expanded="false" aria-controls="collapseRegister">
                            2. Registering and Logging In
                        </button>
                    </h2>
                    <div id="collapseRegister" class="accordion-collapse collapse" aria-labelledby="headingRegister" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>Choose the account type that matches your role:</p>
                            <ul>
                                <li><strong>Contractor:</strong> Submit bids, upload documents, and view award results.</li>
                                <li><strong>Evaluator:</strong> Score bid submissions and support award decisions.</li>
                                <li><strong>Admin:</strong> Manage users, publish tenders, and review results.</li>
                            </ul>
                            <p>If you forget your password, click <strong>Forgot Password</strong> and follow the reset instructions.</p>
                            <div class="text-center mt-4">
                                <img src="/E-Tendering system project/public/assets/registration-overview.png" alt="Registration Overview" class="img-fluid rounded shadow-sm border border-2">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="tender open close bid details documents deadlines">
                    <h2 class="accordion-header" id="headingTenders">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTenders" aria-expanded="false" aria-controls="collapseTenders">
                            3. Available Tenders
                        </button>
                    </h2>
                    <div id="collapseTenders" class="accordion-collapse collapse" aria-labelledby="headingTenders" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>The <strong>Available Tenders</strong> page shows all bids you can participate in.</p>
                            <ol>
                                <li>Click a tender to read the full requirements.</li>
                                <li>Download the documents and confirm the submission deadline.</li>
                                <li>Prepare your bid and upload it before the deadline.</li>
                            </ol>
                            <p>Watch for tender-specific instructions and required attachments.</p>
                            <div class="text-center mt-4">
                                <img src="/E-Tendering system project/public/assets/available-tenders-overview.png" alt="Available Tenders Overview" class="img-fluid rounded shadow-sm border border-2">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="contractor profile upload documents bid submission status">
                    <h2 class="accordion-header" id="headingContractor">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContractor" aria-expanded="false" aria-controls="collapseContractor">
                            4. Contractor Workflow
                        </button>
                    </h2>
                    <div id="collapseContractor" class="accordion-collapse collapse" aria-labelledby="headingContractor" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>As a contractor, your main tasks are:</p>
                            <ul>
                                <li>Complete your profile.</li>
                                <li>Upload all required documents and certifications.</li>
                                <li>Find tenders that match your skills.</li>
                                <li>Submit bids early and confirm all files are attached.</li>
                            </ul>
                            <p>After submitting, track the bid status from your dashboard.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="evaluator score review evaluation tender assessment">
                    <h2 class="accordion-header" id="headingEvaluator">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvaluator" aria-expanded="false" aria-controls="collapseEvaluator">
                            5. Evaluator Workflow
                        </button>
                    </h2>
                    <div id="collapseEvaluator" class="accordion-collapse collapse" aria-labelledby="headingEvaluator" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>Evaluators use the dashboard to:</p>
                            <ul>
                                <li>Select assigned tenders.</li>
                                <li>Open each bid submission and review supporting documents.</li>
                                <li>Score bids using the evaluation form.</li>
                                <li>Submit your evaluation so the award process can continue.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="admin publish tender applicants approve reject audit appeals blacklist">
                    <h2 class="accordion-header" id="headingAdmin">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin" aria-expanded="false" aria-controls="collapseAdmin">
                            6. Admin Workflow
                        </button>
                    </h2>
                    <div id="collapseAdmin" class="accordion-collapse collapse" aria-labelledby="headingAdmin" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>Admins manage the system by:</p>
                            <ul>
                                <li>Reviewing and approving new user accounts.</li>
                                <li>Publishing tenders and uploading tender documents.</li>
                                <li>Monitoring evaluations, awards, appeals, and audit logs.</li>
                                <li>Managing contractor blacklisting and system integrity.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="navigation menu links pages quick start tips">
                    <h2 class="accordion-header" id="headingNavigation">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNavigation" aria-expanded="false" aria-controls="collapseNavigation">
                            7. Navigation Tips
                        </button>
                    </h2>
                    <div id="collapseNavigation" class="accordion-collapse collapse" aria-labelledby="headingNavigation" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>Use these pages to move through the system:</p>
                            <ul>
                                <li><strong>Home:</strong> Overview and quick access.</li>
                                <li><strong>Available Tenders:</strong> Browse active bids.</li>
                                <li><strong>Public Statistics:</strong> Track procurement performance.</li>
                                <li><strong>Help:</strong> Return here for guidance and tools.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="accordion-item help-section" data-keywords="support contact admin questions help manual">
                    <h2 class="accordion-header" id="headingSupport">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSupport" aria-expanded="false" aria-controls="collapseSupport">
                            8. Need More Help?
                        </button>
                    </h2>
                    <div id="collapseSupport" class="accordion-collapse collapse" aria-labelledby="headingSupport" data-bs-parent="#helpAccordion">
                        <div class="accordion-body">
                            <p>If you get stuck, use the top menu to return to this page or contact your system administrator for support with registration, bid submission, or user approval.</p>
                            <p>Keep this page open while you work through the system to refer back to the steps easily.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4 sticky-top" style="top: 90px;">
                <div class="card-body">
                    <h5 class="card-title">Jump to Section</h5>
                    <div class="list-group list-group-flush" id="helpNav">
                        <a class="list-group-item list-group-item-action" href="#headingIntro">Getting Started</a>
                        <a class="list-group-item list-group-item-action" href="#headingRegister">Registering & Logging In</a>
                        <a class="list-group-item list-group-item-action" href="#headingTenders">Available Tenders</a>
                        <a class="list-group-item list-group-item-action" href="#headingContractor">Contractor Workflow</a>
                        <a class="list-group-item list-group-item-action" href="#headingEvaluator">Evaluator Workflow</a>
                        <a class="list-group-item list-group-item-action" href="#headingAdmin">Admin Workflow</a>
                        <a class="list-group-item list-group-item-action" href="#headingNavigation">Navigation Tips</a>
                        <a class="list-group-item list-group-item-action" href="#headingSupport">Need More Help?</a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">How to Use This Manual</h5>
                    <?php $videoSrc = rtrim(dirname($config['base_url'] ?? '/'), '/') . '/Presentation1.mp4'; ?>
                    <video class="w-100 rounded-3 mb-3" controls preload="metadata">
                        <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <p class="small text-muted">Type keywords in the search box to filter the sections instantly. Use Expand All when you want to read everything at once.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sections = Array.from(document.querySelectorAll('.help-section'));
        const searchInput = document.getElementById('manualSearch');
        const accordion = document.getElementById('helpAccordion');

        function filterSections() {
            const query = searchInput.value.trim().toLowerCase();
            sections.forEach(section => {
                const keywords = section.dataset.keywords || '';
                const title = section.querySelector('.accordion-button').textContent.toLowerCase();
                const match = query === '' || keywords.includes(query) || title.includes(query);
                section.style.display = match ? 'block' : 'none';

                const collapse = section.querySelector('.accordion-collapse');
                if (match && query !== '') {
                    collapse.classList.add('show');
                } else if (query === '') {
                    collapse.classList.remove('show');
                }
            });
        }

        searchInput.addEventListener('input', filterSections);

        document.getElementById('expandAll').addEventListener('click', function () {
            sections.forEach(section => section.querySelector('.accordion-collapse').classList.add('show'));
        });

        document.getElementById('collapseAll').addEventListener('click', function () {
            sections.forEach(section => section.querySelector('.accordion-collapse').classList.remove('show'));
        });

        document.getElementById('helpNav').addEventListener('click', function (event) {
            if (event.target.matches('a')) {
                event.preventDefault();
                const targetId = event.target.getAttribute('href').substring(1);
                const header = document.getElementById(targetId);
                if (header) {
                    const button = header.querySelector('.accordion-button');
                    button.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    button.click();
                }
            }
        });
    });
</script>
