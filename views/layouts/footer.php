</div><!-- /.container -->

<!-- Site Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="footer-brand mb-2">
                    <i class="bi bi-building-fill me-2"></i>MicroProjects Eswatini
                </div>
                <p class="mb-0" style="font-size:0.82rem;">
                    A transparent, digital e-tendering platform for community construction and infrastructure projects across Eswatini.
                </p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white fw-bold mb-2" style="font-size:0.85rem; letter-spacing:0.06em; text-transform:uppercase;">Quick Links</h6>
                <ul class="list-unstyled mb-0" style="font-size:0.82rem;">
                    <li><a href="index.php?route=tenders"><i class="bi bi-briefcase me-1"></i>Open Tenders</a></li>
                    <li><a href="index.php?route=public-stats"><i class="bi bi-bar-chart-line me-1"></i>Statistics</a></li>
                    <li><a href="index.php?route=guidance"><i class="bi bi-book me-1"></i>Bidding Guide</a></li>
                    <li><a href="index.php?route=register"><i class="bi bi-person-plus me-1"></i>Register as Contractor</a></li>
                </ul>
            </div>

        </div>
        <hr class="footer-divider">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size:0.78rem;">
            <span>&copy; <?= date('Y') ?> MicroProjects Eswatini E-Tendering System. All rights reserved.</span>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS (single load) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="assets/app.js?v=<?= time() ?>" defer></script>
</body>
</html>
