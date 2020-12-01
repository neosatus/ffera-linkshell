<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/16/16
 * Time: 9:04 AM
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Raid
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
    protected $raidTarget;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $raidDate;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="on_time_users",
     *      joinColumns={@ORM\JoinColumn(name="raid_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    protected $onTimeAttendees;

    /**
     * @ORM\ManyToMany(targetEntity="User")
     * @ORM\JoinTable(name="late_users",
     *      joinColumns={@ORM\JoinColumn(name="raid_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    protected $lateAttendees;

    function __construct()
    {
        $this->onTimeAttendees = new ArrayCollection();
        $this->lateAttendees = new ArrayCollection();
    }

    function getId()
    {
        return $this->id;
    }

    function setRaidTarget($target)
    {
        $this->raidTarget = $target;
    }

    function getRaidTarget()
    {
        return $this->raidTarget;
    }

    function setRaidDate($time)
    {
        $this->raidDate = new \DateTime($time);
    }

    function getRaidDate()
    {
        return $this->raidDate;
    }

    function getOnTimeAttendees()
    {
        return $this->onTimeAttendees;
    }

    function getLateAttendees()
    {
        return $this->lateAttendees;
    }
    
    function addOnTimeAttendee(User $user)
    {
        if (!$this->onTimeAttendees->contains($user))
        {
            $user->addPoints(2);
            $this->onTimeAttendees->add($user);
        }
    }

    function removeOnTimeAttendee(User $user)
    {
        if ($this->onTimeAttendees->contains($user))
        {
            $user->removePoints(2);
            $this->onTimeAttendees->removeElement($user);
        }
    }

    function addLateAttendee(User $user)
    {
        if (!$this->lateAttendees->contains($user))
        {
            $user->addPoints(1);
            $this->lateAttendees->add($user);
        }
    }

    function removeLateAttendee(User $user)
    {
        if ($this->lateAttendees->contains($user))
        {
            $user->removePoints(1);
            $this->lateAttendees->removeElement($user);
        }
    }
}