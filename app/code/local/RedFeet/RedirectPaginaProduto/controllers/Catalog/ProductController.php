<?php

require_once('Mage/Catalog/controllers/ProductController.php');
/**
 * Product controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class RedFeet_RedirectPaginaProduto_Catalog_ProductController extends Mage_Catalog_ProductController
{   
    // O cliente tenta fazer um coment�rio em um determinado produto, mais n�o esta logado,
    // a linha a baixo direciona o cliente para a tela de login, ap�s o login efetuado, redireciona para a pagina
    // que estava viasualizando anteriormente, liberando os coment�rios.
    // comentarios est�o no arquivo : redfeet,template,catalog,prtoduct,view.phtml
    public function redirectPaginaProdutoAposLogarAction(){
        Mage::app()->getResponse()->setRedirect(Mage::helper('adminhtml')->getUrl("customer/account/login"));//redirect to login page
    }
    

}
