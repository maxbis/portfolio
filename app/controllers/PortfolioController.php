<?php
require_once '../core/Controller.php';
require_once '../app/models/Portfolio.php';

class PortfolioController extends Controller {
    private $portfolioModel;

    public function __construct() {
        $this->portfolioModel = new Portfolio();
    }

    public function list() {
        $records = $this->portfolioModel->getPortfolio();
        $this->renderView('portfolio/list', ['title' => 'Portfolio', 'data' => $records]);
    }
}
