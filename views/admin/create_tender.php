<div class="page-bg" style="background-image: url('assets/003.jpg');"></div>
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?route=admin-dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Create New Tender</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Create New Tender Notice</h2>
        <p class="text-muted">Fill in the technical requirements and set the scoring rubric for this procurement.</p>
    </div>
</div>

<form method="post" action="index.php?route=publish-tender">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
    
    <div class="row g-4">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom fw-bold py-3">General Information</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Project Title</label>
                        <input type="text" name="title" class="form-control form-control-lg border-2" placeholder="e.g. Construction of Community Water Tank" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Detailed Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Specify project scope, deliverables, and timelines..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white fw-bold py-3">Scoring Rubric Configuration</div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-4">Set the percentage weights for the final evaluation. Total must be 100%.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Technical Weight (%)</label>
                            <input type="number" name="weight_technical" class="form-control rubric-input" value="50" min="0" max="100" required>
                            <div class="form-text small">Experience, capacity, and methodology.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Financial Weight (%)</label>
                            <input type="number" name="weight_financial" class="form-control rubric-input" value="40" min="0" max="100" required>
                            <div class="form-text small">Price competitiveness.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Local Weight (%)</label>
                            <input type="number" name="weight_local" class="form-control rubric-input" value="10" min="0" max="100" required>
                            <div class="form-text small">Swati ownership bonus.</div>
                        </div>
                    </div>
                    <div id="rubric-alert" class="alert alert-danger mt-3 d-none">Total weights must sum to exactly 100%. Currently: <span id="current-total">0</span>%</div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-primary text-white fw-bold py-3">Logistics & Deadlines</div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Project Budget (E)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">E</span>
                                <input type="number" step="0.01" name="budget" class="form-control border-start-0" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Non-Refundable Tender Fee (E)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">E</span>
                                <input type="number" step="0.01" name="tender_fee" class="form-control border-start-0" placeholder="0.00 (Optional)">
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Project Region</label>
                        <select name="region" class="form-select" required>
                            <option value="Hhohho">Hhohho</option>
                            <option value="Manzini" selected>Manzini</option>
                            <option value="Shiselweni">Shiselweni</option>
                            <option value="Lubombo">Lubombo</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Project Category</label>
                        <select name="category" class="form-select" required>
                            <option value="Construction">Construction</option>
                            <option value="Infrastructure">Infrastructure</option>
                            <option value="Health Facilities">Health Facilities</option>
                            <option value="Roads">Roads</option>
                            <option value="Schools">Schools</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Required CIC Grade</label>
                        <select name="required_grade" class="form-select" required>
                            <option value="Grade 1">Grade 1 (Micro)</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-danger">Submission Deadline</label>
                        <input type="datetime-local" name="deadline" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Evaluation Deadline</label>
                        <input type="date" name="evaluation_deadline" class="form-control">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Admin Note</h6>
                    <p class="small text-muted mb-0">Publishing this tender will make it immediately visible to all registered SMEs. You can still upload tender documents and drawings after publishing.</p>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" id="submit-btn" class="btn btn-primary btn-lg fw-bold shadow py-3">Publish Tender Now</button>
            </div>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.rubric-input').forEach(input => {
        input.addEventListener('input', () => {
            const tech = parseInt(document.querySelector('[name="weight_technical"]').value) || 0;
            const fin = parseInt(document.querySelector('[name="weight_financial"]').value) || 0;
            const loc = parseInt(document.querySelector('[name="weight_local"]').value) || 0;
            const total = tech + fin + loc;
            
            const alert = document.getElementById('rubric-alert');
            const totalSpan = document.getElementById('current-total');
            const btn = document.getElementById('submit-btn');

            totalSpan.textContent = total;
            if (total !== 100) {
                alert.classList.remove('d-none');
                btn.disabled = true;
            } else {
                alert.classList.add('d-none');
                btn.disabled = false;
            }
        });
    });
</script>
