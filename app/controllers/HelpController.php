<?php
// app/controllers/HelpController.php
class HelpController extends Controller {
    // Show searchable Help Center landing page
    public function index() {
        // The view will load the search UI and include lunr.js
        $this->view('help/index', []);
    }

    // View a single article (slug passed via URL)
    public function view($slug) {
        $model = new HelpArticle();
        $article = $model->getBySlug($slug);
        if (!$article) {
            http_response_code(404);
            echo "Help article not found";
            return;
        }
        $this->view('help/view', ['article' => $article]);
    }

    // Download the PDF version of an article
    public function downloadPdf($slug) {
        $model = new HelpArticle();
        $article = $model->getBySlug($slug);
        if (!$article || empty($article['pdf_path'])) {
            http_response_code(404);
            echo "PDF not available";
            return;
        }
        $file = __DIR__ . '/../../public' . $article['pdf_path'];
        if (!file_exists($file)) {
            http_response_code(404);
            echo "File missing";
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}
?>
