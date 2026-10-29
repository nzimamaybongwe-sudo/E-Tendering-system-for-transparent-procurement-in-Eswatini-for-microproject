<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Manage Site Visits</h2>
            <p class="text-muted mb-0 small">Verify the dates and officers met by contractors during compulsory project site visits.</p>
        </div>
        <a href="index.php?route=officer-dashboard" class="btn btn-secondary btn-sm rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
    </div>

    <!-- Search/Filter Controls -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
        <div class="row g-2">
            <div class="col-md-8">
                <input type="text" id="siteVisitSearch" class="form-control form-control-sm rounded-pill px-3" placeholder="Search by Company Name or Officer Name...">
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-dark btn-sm rounded-pill w-100 fw-bold" onclick="resetFilters()">Reset Search</button>
            </div>
        </div>
    </div>

    <!-- Site Visits Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-dark text-white fw-bold py-3">Site Visit Declarations List</div>
        <div class="card-body p-0">
            <?php if (empty($bids)): ?>
                <div class="text-center py-5 text-muted small">
                    <i class="bi bi-geo h1 opacity-25"></i>
                    <p class="mt-2 mb-0">No site visits have been declared by bidders yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle" id="siteVisitsTable">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3" style="width:200px;">Contractor/Company</th>
                                <th style="width:300px;">Tender Title</th>
                                <th class="text-center" style="width:130px;">Site Visit Date</th>
                                <th style="width:150px;">Officer Met</th>
                                <th>Contact Information</th>
                                <th class="text-center" style="width:120px;">Compliance</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            <?php foreach ($bids as $bid): ?>
                                <tr class="site-visit-row">
                                    <td class="ps-3 fw-bold company-name"><?= htmlspecialchars($bid['company_name']) ?></td>
                                    <td class="tender-title"><?= htmlspecialchars($bid['tender_title']) ?></td>
                                    <td class="text-center"><?= date('d M Y', strtotime($bid['site_visit_date'])) ?></td>
                                    <td class="officer-met fw-semibold"><?= htmlspecialchars($bid['site_visit_officer']) ?></td>
                                    <td>
                                        <div class="text-muted">Person: <?= htmlspecialchars($bid['contact_person'] ?: 'N/A') ?></div>
                                        <div class="text-muted">Phone: <?= htmlspecialchars($bid['phone'] ?: 'N/A') ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Declared</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('siteVisitSearch').addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('.site-visit-row');
        
        rows.forEach(row => {
            const company = row.querySelector('.company-name').textContent.toLowerCase();
            const officer = row.querySelector('.officer-met').textContent.toLowerCase();
            const tender = row.querySelector('.tender-title').textContent.toLowerCase();
            
            if (company.includes(query) || officer.includes(query) || tender.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    function resetFilters() {
        document.getElementById('siteVisitSearch').value = '';
        document.querySelectorAll('.site-visit-row').forEach(row => {
            row.style.display = '';
        });
    }
</script>
