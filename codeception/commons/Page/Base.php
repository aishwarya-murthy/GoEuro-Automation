<?php
namespace commons\Page;

use Codeception\Actor;
/**
 * Created by PhpStorm.
 * User: amurthy
 * Date: 15.09.15
 * Time: 18:48
 */
abstract class Base
{
    /**
     * @var \commons\ActorInterface
     */
    protected $actor;

    /**
     * Constructor
     *
     * @param \commons\ActorInterface $actor actor
     */
    public function __construct($actor)
    {
        $this->actor = $actor;
    }

    /**
     * Static factory
     *
     * @param Actor|\Codeception\Module\ $i actor
     *
     * @return static
     */
    public static function of($i)
    {
        $className       = get_called_class();
        $classyClassName = $className;

        if (class_exists($classyClassName)) {
            $className = $classyClassName;
        }

        return new $className($i);
    }

    /**
     * Assert Page URL
     *
     * @return $this
     */
    public function assertNoHttpErrorsDisplayed()
    {
        $i = $this->actor;
        $i->dontSee("HTTP 404");
        $i->dontSee("HTTP 504");
        $i->dontSee("HTTP 503");
        return $this;
    }

    /**
     * Assert Page URL
     *
     * @param string $url Page url
     *
     * @return $this
     */
    public function assertURL($url)
    {
        $i = $this->actor;
        $i->seeInCurrentUrl($url);
        return $this;
    }

    /**
     * Asserts no error
     *
     * @return static
     */
    public function assertNoFatalError()
    {
        $i = $this->actor;
        $i->dontSee("Fatal Error");
        return $this;
    }

    /**
     * Open page
     *
     * @param string $pageURL URL to open
     *
     * @return void
     */
    public function openPage($pageURL)
    {
        $i = $this->actor;
        $i->amOnPage($pageURL);
    }
}
