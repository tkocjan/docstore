<?php
namespace Zstore\Domain\Catalog;
use Doctrine\ORM\Mapping as ORM,
    Zstore\Domain\Entity,
    Zstore\Domain\Catalog\IProductImageEntity;

/** @ORM\Entity
 *  @ORM\Table(name="productImage") */
class ProductImageEntity extends Entity implements IProductImageEntity
{    
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") @var integer */
    protected $imageId;
    
    /* //var integer protected productId */
    /** @ORM\ManyToOne(targetEntity="ProductEntity", inversedBy="images")
     *  @ORM\JoinColumn(name="productId", referencedColumnName="productId") */
    protected $product;
    
    /** @ORM\Column(type="string") @var string */
    protected $thumbnail;
    
    /** @ORM\Column(type="string") @var string */
    protected $full;
    
    /** @ORM\Column(type="string") @var string */
    protected $isDefault;
    
    // declare so Id cannot be set
    // declare as public for proxy generation
    public function _get__imageId() {
        return $this->imageId;
    }

    /**
     * Is this a default image
     * 
     * @return boolean 
     */
    public function isDefault() {
        return 'Yes' === $this->isDefault ? true : false;
    }
}
