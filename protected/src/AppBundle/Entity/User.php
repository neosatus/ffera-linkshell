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
	
	protected $validJobs = ["WHM", "BLM", "RDM", "WAR", "THF", "MNK", "BRD", "BST", "DRK", "SMN", "NIN", "SAM", "RNG", "DRG", "PLD", "BLU", "COR", "PUP", "DNC", "SCH"];

    public function __construct()
    {
        parent::__construct();
        $this->visible = 1;
    }
	
	public function getValidJobs((
	{
		return $this->validJobs;
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
		// Check return cases
		if ($mainJob == $subJob) return;
		
		// Allow swapping
		if ($this->mainJob == $subJob and $this->subJob == $mainJob)
		{
			$this->mainJob = $mainJob;
			$this->subJob = $subJob;
			return;
		}
		
		$this->mainJob = $this->processJobUpdate($this->mainJob, $mainJob);
		$this->subJob = $this->processJobUpdate($this->subJob, $subJob);
    }
	
	protected function processJobUpdate($oldJob, $newJob)
	{
		// Check if it's actually changing (can't change to blank)
		if (empty($newJob) or $oldJob == $newJob) return $oldJob;
		
		// If it was already set, it costs 20 points
		if (!empty($oldJob))
		{
			if ($this->getPoints() < 20) return $oldJob;
            $this->removePoints(20);
		}
		
		return $newJob;
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