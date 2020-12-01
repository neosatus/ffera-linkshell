<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/22/16
 * Time: 10:53 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Item
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(length=100)
     */
    protected $itemName;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $price;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $count;
    
    function __construct()
    {
        
    }
    
    function getId()
    {
        return $this->id;
    }
    
    function getItemName()
    {
        return $this->itemName;
    }
    
    function setItemName($itemName)
    {
        $this->itemName = $itemName;   
    }
    
    function getPrice()
    {
        return $this->price;
    }
    
    function setPrice($price)
    {
        $this->price = $price;
    }
    
    function getCount()
    {
        return $this->count;
    }
    
    function setCount($count)
    {
        $this->count = $count;
    }
}