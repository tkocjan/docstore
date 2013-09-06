<?php
namespace Zstore\Domain\Catalog;
use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection,
    Zstore\Domain\Entity,
    Zstore\Domain\Catalog\IProductEntity,
    Exception;

/** @ORM\Entity(repositoryClass="Zstore\Domain\Doctrine\DocProductRepository")
 *  @ORM\Table(name="product") */
class ProductEntity extends Entity implements IProductEntity {

    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") @var integer */
    protected $productId;
    
    //ORM\ManyToOne(targetEntity="Zstore\Domain\Catalog\CategoryEntity")
    //works because in same namespace
    /** @ORM\ManyToOne(targetEntity="CategoryEntity")
     *  @ORM\JoinColumn(name="categoryId", referencedColumnName="categoryId") 
     *  @var CategoryEntity */
    protected $category;

    /** @ORM\Column(type="string") @var string */
    protected $ident;
    
    /** @ORM\Column(type="string") @var string */
    protected $name;
    
    /** @ORM\Column(type="text") @var string */
    protected $description;
    
    /** @ORM\Column(type="string") @var string */
    protected $shortDescription;
    
    /** @ORM\Column(type="decimal", precision=10, scale=2) @var string */
    protected $price;
    
    /** @ORM\Column(type="integer") @var integer */
    protected $discountPercent;
    
    /** @ORM\Column(type="string") @var string */
    protected $taxable;
    
    /** @ORM\OneToMany(targetEntity="ProductImageEntity",
                       mappedBy="product", cascade={"all"})
     *  @var ProductImageEntity[] */
    protected $images = null;
    
    /* @var ProductImageEntity */
    protected $defaultImage;
    
    public function __construct() {
        $this->images = new ArrayCollection();
    }

    // declare so Id cannot be set
    // declare as public for proxy generation
    public function _get__productId() {
        return $this->productId;
    }
    
    public function _get__images() {
        return $this->getImages();  // only return non-default images
    }
    
    public function _set__images($value) {
        throw new Exception("Can't set images property");
    }
    
    public function _get__defaultImage() {
        return $this->getDefaultImage();
    }
    
    /**
     * Get product images
     *
     * @param  boolean $includeDefault Whether to include the default
     * @return array Containing Storefront_Resource_ProductImage_Item
     */
    public function getImages($includeDefault=false)
    {
        if (true === $includeDefault)
            return $this->images;
        
        $images = array();
        foreach($this->images as $image) {
            if (!$image->isDefault())
                $images[] = $image;
        }
        return $images;
    }
    
    /**
     * Get the default image
     *
     * @return Storefront_Model_Catalog_ProductImageEntity
     */
    public function getDefaultImage()
    {
        foreach($this->images as $image) {
            if ($image->isDefault())
                return $image;
        }
    }
    public function addImage($image)
    {
        $image->product = $this;
        if ($image->isDefault()) {
            foreach($this->images as $currImage) {
                $currImage->isDefault = 'No';
            }
        }

        $this->images[] = $image;        
    }
        
    //must go through getPrice
    public function _get__price() {
        return $this->getprice();
    }
            
    //but can set price
    public function _set__price($value) {
        $this->price = $value;
    }
            
    /**
     * Get the price
     *
     * @param  boolean $withDiscount Include discount calculation
     * @param  boolean $withTax      Include tax calculation
     * @return string The products price
     */
    public function getPrice($withDiscount=true,$withTax=true)
    {
        $price = $this->price;
        if (true === $this->isDiscounted() && true === $withDiscount) {
            $discount = $this->discountPercent;
            $discounted = ($price*$discount)/100;
            $price = round($price - $discounted, 2);
        }
        if (true === $this->isTaxable() && true === $withTax) {
            $taxService = new \Storefront_Service_Taxation();
            $price = $taxService->addTax($price);
        }
        return $price;
    }
    
    /**
     * Is this product discounted ?
     *
     * @return boolean
     */
    public function isDiscounted() 
    {
        return 0 == $this->discountPercent ? false : true;
    }
    
    /**
     * Is this product taxable?
     *
     * @return boolean
     */
    public function isTaxable() 
    {
       return 'Yes' == $this->taxable ? true : false;
    }    
}
