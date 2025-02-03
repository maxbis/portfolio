<?php
require_once '../core/Controller.php';
require_once '../app/models/Portfolio.php';

class PortfolioController extends Controller {
    private $portfolioModel;

    public function __construct() {
        $this->portfolioModel = new Portfolio();
    }

    // Display the portfolio overview
    public function list() {
        $portfolio = $this->portfolioModel->getPortfolio();
        $this->renderView('portfolio/list', ['portfolio' => $portfolio]);
    }
}
