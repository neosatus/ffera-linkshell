<?php
/**
 * Created by PhpStorm.
 * User: timuptain
 * Date: 6/20/16
 * Time: 1:02 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/** @ORM\Entity */
class News
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $poster;

    /**
     * @ORM\Column(length=100)
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    function __construct()
    {
        $this->date = new DateTime();
    }

    function getId()
    {
        return $this->id;
    }

    function getDate()
    {
        return $this->date;
    }

    function getPoster()
    {
        return $this->poster;
    }

    function setPoster($poster)
    {
        $this->poster = $poster;
    }

    function getTitle()
    {
        return $this->title;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }

    function getContent()
    {
        return $this->content;
    }

    function setContent($content)
    {
        $this->content = $content;
    }
}