<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/22/16
 * Time: 10:55 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="item_order")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Item")
     */
    protected $item;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $buyer;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $delivered = false;

    function __construct()
    {

    }

    function getId()
    {
        return $this->id;
    }

    function getItem()
    {
        return $this->item;
    }

    function setItem(Item $item)
    {
        $this->item = $item;
    }

    function getBuyer()
    {
        return $this->buyer;
    }

    function setBuyer(User $buyer)
    {
        $this->buyer = $buyer;
    }

    function fundOrder()
    {
        if ($this->buyer->getPoints() >= $this->item->getPrice())
        {
            $this->buyer->removePoints($this->item->getPrice());
            return true;
        }
        
        return false;
    }

    function refundOrder()
    {
        $this->buyer->addPoints($this->item->getPrice());
    }

    function wasDelivered()
    {
        return $this->delivered;
    }

    function deliverOrder()
    {
        $this->item->setCount($this->item->getCount() - 1);
        $this->delivered = true;
    }
}