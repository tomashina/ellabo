<?php

class ControllerExtensionFeedGetProductId extends Controller {

    public function index()
    {


        if (isset($_GET['model'])) {


       // echo $_GET['model'];

            $this->load->model('catalog/product');     

            $product = $this->model_catalog_product->getProductIdByModel($_GET['model']);


               echo $product;

          
       }


    }


}

?>