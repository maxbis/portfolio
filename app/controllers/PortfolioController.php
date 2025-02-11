<?php
require_once '../core/Controller.php';
require_once '../app/models/Portfolio.php';

class PortfolioController extends Controller {
    private $portfolioModel;

    public function __construct() {
        $this->portfolioModel = new Portfolio();
        // $this->controllerName = $this->childFileName(__FILE__);
    }

    public function list() {
        $records = $this->portfolioModel->getPortfolio();
        $this->renderView('portfolio/list', 
        ['title' => 'Portfolio', 'data' => $records]);
    }

    public function lista($groupBy='symbol') {
        $records = $this->portfolioModel->getPortfolio();
        $records = $this->portfolioModel->aggregateRecords($records, $groupBy);
        $this->renderView('portfolio/lista',
        ['title' => 'Portfolio', 'data' => $records]);
    }
}
