<?php
class Storefront_SampleContentController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->content = Cosmos_Api::get()->content->read();
    }
}