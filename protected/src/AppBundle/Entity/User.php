<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points = 0;

    /**
     * @ORM\Column(length=3, nullable=true)
     */
    protected $mainJob;

    /**
     * @ORM\Column(length=3, nullable=true)
     */
    protected $subJob;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible = 1;

    public function __construct()
    {
        parent::__construct();
        $this->visible = 1;
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function addPoints($value)
    {
        $this->points += $value;
    }

    public function removePoints($value)
    {
        $this->points -= $value;
    }

    public function getMainJob()
    {
        return $this->mainJob;
    }

    public function setMainJob($job)
    {
        $this->mainJob = $job;
    }

    public function setSubJob($job)
    {
        $this->subJob = $job;
    }

    public function getSubJob()
    {
        return $this->subJob;
    }

    public function updateJobs($mainJob, $subJob)
    {
        // Do all the return checks
        if ($this->mainJob <> null and $this->mainJob == $mainJob and $this->subJob <> null and $this->subJob == $subJob) return;
        if ($mainJob == $subJob) return;
        
        // Do the free change checks
        if ($this->mainJob <> null and $this->mainJob == $subJob and $this->subJob <> null and $this->subJob == $mainJob)
        {
            $this->mainJob = $mainJob;
            $this->subJob = $subJob;
            return;
        }
        if ($this->mainJob <> null and $this->subJob == null and $this->mainJob <> $mainJob and $subJob == null)
        {
            $this->subJob = $mainJob;
            return;
        }
        if ($this->mainJob == null and $this->subJob <> null and $mainJob == null and $this->subJob <> $subJob)
        {
            $this->mainJob = $subJob;
            return;
        }
        if ($this->mainJob == null or $this->subJob == null)
        {
            $this->mainJob = $mainJob;
            $this->subJob = $subJob;
            return;
        }

        // Do the paid changes
        if ($this->mainJob <> $mainJob and $this->subJob <> $subJob)
        {
            if ($this->getPoints() < 40) return;
            $this->removePoints(40);
        }
        else
        {
            if ($this->getPoints() < 20) return;
            $this->removePoints(20);
        }

        $this->mainJob = $mainJob;
        $this->subJob = $subJob;
        return;
    }
    
    public function getVisibility()
    {
        return $this->visible;
    }
    
    public function setVisibility($visible = true)
    {
        $this->visible = $visible;
    }
}
?>