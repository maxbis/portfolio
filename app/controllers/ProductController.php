<?php
require_once '../core/ControllerModel.php';

class ProductController extends Controller {
    public function list() {
        $productModel = $this->loadModel('Product');
        $products = $productModel->getAll();
        $this->renderView('product', ['products' => $products]);
    }
}