<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/20/16
 * Time: 2:52 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Bounty
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
    protected $active = true;

    /**
     * @ORM\Column(length=100)
     */
    protected $target;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $points;

    function __construct()
    {

    }

    function getId()
    {
        return $this->id;
    }

    function isActive()
    {
        return $this->active;
    }

    function toggleActive()
    {
        $this->active ? $this->active = false : $this->active = true;
    }

    function setTarget($target)
    {
        $this->target = $target;
    }

    function getTarget()
    {
        return $this->target;
    }

    function setPoints($points)
    {
        $this->points = $points;
    }

    function getPoints()
    {
        return $this->points;
    }
}