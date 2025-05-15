<?php
require_once '../core/Controller.php';
require_once '../app/models/PortfolioModel.php';

class PortfolioController extends Controller {
    private $portfolioModel;

    public function __construct() {
        $this->portfolioModel = new Portfolio();
        // $this->controllerName = $this->childFileName(__FILE__);
    }

    // Standard list
    public function list($date=null) {
        $records = $this->portfolioModel->getPortfolio($date);
        if (isset($_GET['export']) && $_GET['export']) {
            $this->exportExcel($records);
            exit; // Terminate script execution
        }
        $this->renderView('portfolio/list', 
        ['title' => 'Portfolio', 'data' => $records]);
    }

    // aggregated list    
    public function lista($groupBy='symbol') {
        $records = $this->portfolioModel->getPortfolio();
        $records = $this->portfolioModel->aggregateRecords($records, $groupBy);
        // echo "<pre>";print_r($records);exit;
        $this->renderView('portfolio/lista',
        ['title' => 'Portfolio', 'data' => $records, 'group' => $groupBy]);
    }

    // short list
    public function lists($date=null) {
        $records = $this->portfolioModel->getPortfolio($date);
        if (isset($_GET['export']) && $_GET['export']) {
            $this->exportExcel($records);
            exit; // Terminate script execution
        }
        // sort
        usort($records, function($a, $b) {
            $item1=$a['symbol_title'];
            $item2=$b['symbol_title'];
            if ($item1 === 'Euro (Cash)') $item1='AAA';
            if ($item2 === 'Euro (Cash)') $item2='AAA';
            return strcmp($item1, $item2);
        });
        // foreach($records as $record) {
        //     echo $record['symbol_title']."<br>";
        // }
        // exit;
        $this->renderView('portfolio/list-short', 
        ['title' => 'Portfolio', 'data' => $records]);
    }
}
