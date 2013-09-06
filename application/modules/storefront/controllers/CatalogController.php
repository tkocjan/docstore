<?php
use Doctrine\Common\Util\Debug;

class Storefront_CatalogController extends Zend_Controller_Action
{
    /**
     * @var Zstore\Domain\Catalog\CatalogService
     */
    protected $_catalogService;
    
    public function init()
    {
        //Logger::info(__METHOD__.': apc='.print_r(apc_sma_info(), true));
        
        $this->_catalogService = new Zstore\Domain\Catalog\CatalogService();
        
        //$this->doctrineTest();        
    }

    public function indexAction()
    {
        //Logger::info(__METHOD__.': entry');
        
        $products = $this->_catalogService->//getCached('product')->
            getProductsByCategory(
                $this->_getParam('categoryIdent', 0),
		$this->_getParam('page', 1),
                PRODUCT_PAGE_SIZE,
                array('name')
            );
        //$d = Debug::export($products, 3);
        //Logger::info('product='.print_r($d, true));

        $category = $this->_catalogService->//getCached('category')->
            getCategoryByIdent($this->_getParam('categoryIdent', ''));
        if (null === $category) {
            throw new SF_Exception_404('Unknown category ' . 
                $this->_getParam('categoryIdent'));
        }
        //$d = Debug::export($category, 3);
        //Logger::info('category='.print_r($d, true));

        //Logger::info(__METHOD__.': $categoryId='.var_export($category->categoryId, true));        
        $subs = $this->_catalogService->//getCached('category')->
            getCategoriesThatHaveParentId($category->categoryId);
        //$d = Debug::export($subs, 3);
        //Logger::info('subs='.print_r($d, true));
        $this->getBreadcrumb($category);
 
        $this->view->assign(array(
            'category' => $category,
            'subCategories' => $subs,
            'products' => $products
        ));
    }
    
    public function viewAction()
    {
        $product = $this->_catalogService->getProductByIdent(
                $this->_getParam('productIdent', 0));
        
        if (null === $product) {
            throw new SF_Exception_404(
                    'Unknown product ' . $this->_getParam('productIdent'));
        }
        
        $category = $this->_catalogService->getCategoryByIdent(
                $this->_getParam('categoryIdent', ''));
        $this->getBreadcrumb($category);
        
        $this->view->assign(array(
            'product' => $product,
            )
        );
    }
    
    public function getBreadcrumb($category)
    {
        $this->view->bread = 
            $this->_catalogService->getParentCategories($category);
    }
    
    protected function doctrineTest() {
        //below is for doctrine testing
        $userClassname = '\Zstore\Domain\User\UserEntity';
        $productClassname = '\Zstore\Domain\Catalog\ProductEntity';
        $categoryClassname = '\Zstore\Domain\Catalog\CategoryEntity';        

        $doctrine = Zend_Registry::get('doctrine');
        $em = $doctrine->getEntityManager();
        $userRepository = 
                $em->getRepository($userClassname);
        $productRepository = 
                $em->getRepository($productClassname);
        $categoryRepository = 
                $em->getRepository($categoryClassname);

        $users = $userRepository->findAll();
        $user = $users[0];
        Logger::info(__METHOD__.': get_class($user)='.get_class($user));
        Logger::info(__METHOD__.': $user='.var_export($user, true));
        Logger::info(__METHOD__.': $user.toArray()='.print_r($user->toArray(), true));
        
        $user = $userRepository->findOneBy(array('userId' => 9999));
        //Logger::info(__METHOD__.': get_class($user9999)='.get_class($user));
        Logger::info(__METHOD__.': $user9999='.var_export($user, true));

        //Logger::info(__METHOD__.': $users='.print_r($users, true));
        
        $product = $productRepository->find(4);
        Logger::info(__METHOD__.': get_class($product)='.get_class($product));
        Logger::info(__METHOD__.': $product->name='.$product->name);
        //Logger::info(__METHOD__.': $products='.print_r($products, true));

        //$category = $product->getCategory();
        $category = $product->category;
        Logger::info(__METHOD__.': get_class($category)='.get_class($category));
        //Logger::info(__METHOD__.': $category->name='.$category->getName());
        Logger::info(__METHOD__.': $category->name='.$category->name);
        Logger::info(__METHOD__.': $category->ident='.$category->ident);
        $array = $category->toArray();
        $keys = array_keys($array);
        Logger::info(__METHOD__.': $keys='.print_r($keys, true));
                
        $parent=$category->parent;
        if (is_null($parent))
            Logger::info(__METHOD__.': $category->parent is null');
        else {
            Logger::info(__METHOD__.': get_class($category->parent)='.get_class($parent));
            Logger::info(__METHOD__.': $category->parent->name='.$parent->name);
        }
                
        //$image = $product->images[0]; //only gets non-default images
        $images = $product->getImages(true);
        $image = $images[0];
        Logger::info(__METHOD__.': get_class($image)='.get_class($image));
        Logger::info(__METHOD__.': $image->isDefault='.$image->isDefault);
        foreach($images as $image) {
            Logger::info(__METHOD__.': $image->full='.$image->full);
            $isDefault =$image->isDefault;
            Logger::info(__METHOD__.': $image->isDefault()='.var_export($image->isDefault(), true));
        }
        
        $em->flush();
        $em->clear();
        //Logger::info(__METHOD__.': users='.print_r($users, true));
        
        $userRef = $em->getReference($userClassname, 1);
        Logger::info(__METHOD__.': get_class($userRef)='.get_class($userRef));
        Logger::info(__METHOD__.': $userRef->getTitle()='.$userRef->title);
    }
}
