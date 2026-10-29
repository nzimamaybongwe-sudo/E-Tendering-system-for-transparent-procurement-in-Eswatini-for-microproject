<?php
class Controller {
    protected function view(string $view, array $data = []) {
        extract($data);
        include __DIR__ . '/../../app/views/' . $view . '.php';
    }
    protected function json(array $data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
