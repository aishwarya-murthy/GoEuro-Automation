<?php
/**
 * Created by PhpStorm.
 *
 * PHP version 5
 *
 * @category  AcceptanceTests
 * @package   Commons
 * @author    aishwarya.murthy <aishwarya.murthy@home24.de>
 * @copyright 2014 Home24 GmbH
 * @license   Proprietary license
 * @link      http://www.home24.de
 * User: amurthy
 * Date: 18.12.14
 * Time: 12:04
 */
namespace commons;
use Codeception\Actor;
use Codeception\Module\BrowserShimHelper;
use Codeception\Module\EnvspecificHelper;
use Codeception\Module\PhpBrowser;
use Codeception\Module\TestHelper;
use Codeception\Module\WebDriver;

/**
 * Class ActorInterface
 *
 * @category  AcceptanceTests
 * @package   Commons
 * @author    aishwarya.murthy <aishwarya.murthy@home24.de>
 * @copyright 2014 Home24 GmbH
 * @license   Proprietary license
 * @link      http://www.home24.
 * @mixin TestHelper
 * @mixin BrowserShimHelper
 * @mixin PhpBrowser
 * @mixin WebDriver
 */
class ActorInterface extends Actor
{
}
