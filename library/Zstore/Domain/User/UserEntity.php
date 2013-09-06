<?php
namespace Zstore\Domain\User;
use Doctrine\ORM\Mapping as ORM,
    Zstore\Domain\User\IUserEntity,
    Zstore\Domain\Entity,
    Logger;

/** @ORM\Entity(repositoryClass="Zstore\Domain\Doctrine\DocUserRepository")
 *  @ORM\Table(name="user") */
class UserEntity extends Entity implements IUserEntity
{    
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") @var integer */
    protected $userId;
    /** @ORM\Column(type="string") @var string */
    protected $title;
    /** @ORM\Column(type="string") @var string */
    protected $firstname;
    /** @ORM\Column(type="string") @var string */
    protected $lastname;
    /** @ORM\Column(type="string") @var string */
    protected $email;
    /** @ORM\Column(type="string", columnDefinition="CHAR(40)") @var string */
    protected $passwd;
    /** @ORM\Column(type="string", columnDefinition="CHAR(32)") @var string */
    protected $salt;
    /** @ORM\Column(type="string") @var string */
    protected $role;

    //computed property - declare so user can't override
    //if not declared & user set would be public and get would return public
    // could also check in set
    protected $fullname;
    //computed property - declare so user can't override
    protected $roleId;
    
    /*
    //overriding for computed properties
    public function __get($field) {
        //Logger::info(__METHOD__.': $field='.$field);
        switch ($field) {
        case 'fullname':    return $this->getFullname();
        case 'roleId':      return $this->getRoleId();
        default:            return parent::__get($field);
        }
    }

    //overriding for computed properties
    public function toArray() {
        //Logger::info(__METHOD__.': entry);
        $array = parent::toArray();
        $array['fullname'] = getFullname();
        $array['roleId'] = getRoleId();
        return $array;
    }
     * 
     */

    // declare so userId cannot be set
    // declare as public for proxy generation
    public function _get__userId() {
        return $this->userId;
    }

    // declare as public for proxy generation
    public function _get__fullname() {
        return $this->title .' '.$this->firstname.' '.$this->lastname;
    }

    // declare as public for proxy generation
    public function _get__roleId() {
        if (null === $this->role)
            return 'Guest';
        else
            return $this->role;
    }
}
