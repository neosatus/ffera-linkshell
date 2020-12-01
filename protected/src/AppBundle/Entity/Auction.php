<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/16/16
 * Time: 10:55 AM
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Auction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $open = true;

    /**
     * @ORM\Column(length=50)
     */
    protected $itemName;

    /**
     * @ORM\Column(length=50)
     */
    protected $itemReq;

    /**
     * @ORM\Column(type="integer")
     */
    protected $minBid = 0;

    /**
     * @ORM\OneToMany(targetEntity="Bid", mappedBy="auction")
     */
    protected $bids;

    /**
     * @ORM\OneToOne(targetEntity="Bid")
     */
    protected $winningBid = null;

    function __construct()
    {
        $this->bids = new ArrayCollection();
    }

    function getId()
    {
        return $this->id;
    }

    function isOpen()
    {
        return $this->open;
    }

    function closeAuction()
    {
        $this->open = false;
    }

    function getItemName()
    {
        return $this->itemName;
    }

    function setItemName($name)
    {
        $this->itemName = $name;
    }

    function getItemReq()
    {
        return $this->itemReq;
    }

    function setItemReq($req)
    {
        $this->itemReq = $req;
    }

    function getMinBid()
    {
        return $this->minBid;
    }

    function setMinBid($bid)
    {
        $this->minBid = $bid;
    }

    function getBids()
    {
        return $this->bids;
    }

    function addBid(Bid $bid)
    {
        if (!$this->isOpen() or $bid->getBid() < $this->getMinBid()) return false;

        $this->bids->add($bid);
        return true;
    }

    function getUserBid(User $user)
    {
        foreach ($this->bids as $bid)
        {
            if ($bid->getBidder()->getId() == $user->getId())
            {
                return $bid;
            }
        }

        return false;
    }

    function makeWinningBid(Bid $bid)
    {
        if ($bid->fundBid())
        {
            if ($this->winningBid instanceof Bid)
            {
                $this->winningBid->refundBid();
            }

            $this->winningBid = $bid;
            return true;
        }

        return false;
    }

    function getWinningBid()
    {
        return $this->winningBid;
    }
}