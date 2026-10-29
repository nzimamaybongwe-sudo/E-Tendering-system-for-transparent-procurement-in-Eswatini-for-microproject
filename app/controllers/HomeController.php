<?php
class HomeController extends Controller {
    public function index() {
        // Load any data needed for home page
        $this->view('home', []);
    }
}
?>
