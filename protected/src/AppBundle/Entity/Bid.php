<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/17/16
 * Time: 10:13 AM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Bid
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Auction", inversedBy="bids")
     * @ORM\JoinColumn(name="auction_id", referencedColumnName="id")
     */
    protected $auction;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $bidder;

    /**
     * @ORM\Column(type="integer")
     */
    protected $bid = 0;

    function __construct()
    {

    }

    function getId()
    {
        return $this->id;
    }

    function getAuction()
    {
        return $this->auction;
    }

    function setAuction($auctionId)
    {
        $this->auction = $auctionId;
    }

    function getBidder()
    {
        return $this->bidder;
    }

    function setBidder(User $bidder)
    {
        $this->bidder = $bidder;
    }

    function getBid()
    {
        return $this->bid;
    }

    function setBid($bid)
    {
        $this->bid = $bid;
    }

    function fundBid($no_negative = true)
    {
        if ($no_negative and $this->bidder->getPoints() < $this->bid) return false;

        $this->bidder->removePoints($this->bid);
        return true;
    }

    function refundBid()
    {
        $this->bidder->addPoints($this->bid);
    }
}