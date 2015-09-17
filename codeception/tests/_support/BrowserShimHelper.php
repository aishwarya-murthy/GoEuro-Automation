<?php
/**
 * Created by PhpStorm.
 * User: amurthy
 * Date: 15.09.15
 * Time: 22:48
 */
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfig;
use Codeception\Lib\Interfaces\MultiSession;
use Codeception\Lib\Interfaces\Remote;
use Codeception\Module;
use Codeception\Step;
use Codeception\TestCase;
use commons\Libraries\BrowserShim\BrowserCommandRunner;
use LogicException;
use Symfony\Component\Yaml\Parser;

class BrowserShimHelper extends Module implements  Remote, MultiSession
{
    const MODULE_PHPBROWSER = 'PhpBrowser';
    const MODULE_WEBDRIVER  = 'WebDriver';

    const CONFIG_MODULE     = 'module';
    const CONFIG_URL        = 'url';
    const CONFIG_WD_BROWSER = 'browser';
    const CONFIG_WD_SIZE    = 'window_size';

    const ENV_CONFIG     = 'SHIM_CONFIG';
    const ENV_MODULE     = 'SHIM_MODULE';
    const ENV_URL        = 'SHIM_URL';
    const ENV_WD_BROWSER = 'SHIM_WD_BROWSER';
    const ENV_WD_SIZE    = 'SHIM_WD_SIZE';

    protected static $modules = array(
        self::MODULE_PHPBROWSER => '\Codeception\Module\PhpBrowser',
        self::MODULE_WEBDRIVER => '\Codeception\Module\WebDriver',
    );

    protected $requiredFields = array(self::CONFIG_URL);

    protected $config = array(
        self::CONFIG_MODULE => self::MODULE_PHPBROWSER,
    );

    protected $moduleName;

    /** @var Module */
    protected $module;

    /** @var BrowserCommandRunner */
    protected $runner;

    /**
     * Constructor
     *
     * @param array|null $config module configuration
     *
     * @return self
     *
     * @throws ModuleConfig
     */
    public function __construct($config = null)
    {
        $this->mergeEnvConfig($config);

        parent::__construct($config);

        $this->setEnvOption($config, self::CONFIG_MODULE, self::ENV_MODULE);

        if (!isset(static::$modules[$config[self::CONFIG_MODULE]])) {
            throw new ModuleConfig(
                get_class($this),
                "\nIncorrect module name given: ".$config[self::CONFIG_MODULE]."\n
                Please, update the configuration and set module to one of the correct values: ".implode(', ', array_keys(static::$modules))."\n\n"
            );
        }

        $this->moduleName = $config[self::CONFIG_MODULE];
        if (!isset($config[$this->moduleName])) {
            $config[$this->moduleName] = array();
        }

        $this->setEnvOption($config, self::CONFIG_URL, self::ENV_URL);
        if ($this->isWebDriver()) {
            $this->setEnvOption($config[$this->moduleName], self::CONFIG_WD_BROWSER, self::ENV_WD_BROWSER);
            $this->setEnvOption($config[$this->moduleName], self::CONFIG_WD_SIZE, self::ENV_WD_SIZE);
        }

        $config[$this->moduleName][self::CONFIG_URL] = $config[self::CONFIG_URL];

        $moduleClass  = static::$modules[$this->moduleName];
        $this->module = new $moduleClass($config[$this->moduleName]);
        $this->runner = new BrowserCommandRunner($this->module);
    }

    /**
     * Merges configuration file given in environmental variable
     *
     * @param array|null $config configuration
     *
     * @return void
     */
    protected function mergeEnvConfig(&$config)
    {
        if (!is_array($config)) {
            $config = array();
        }
        $envConfig = getenv(self::ENV_CONFIG);
        if ($envConfig !== false && $envConfig !== null) {
            $parser      = new Parser();
            $localConfig = $parser->parse(file_get_contents($envConfig));
            $config      = Configuration::mergeConfigs($config, $localConfig);
        }
    }

    /**
     * Sets option from environmental variable
     *
     * @param array  $config     configuration array
     * @param string $configName option name
     * @param string $envName    environmental variable name
     *
     * @return void
     */
    protected function setEnvOption(array &$config, $configName, $envName)
    {
        $envValue = getenv($envName);
        if ($envValue !== false && $envValue !== null) {
            $config[$configName] = $envValue;
        }
    }

    /**
     * Checks if PhpBrowser module is used
     *
     * @return bool
     */
    public function isPhpBrowser()
    {
        return $this->moduleName === self::MODULE_PHPBROWSER;
    }

    /**
     * Checks if WebDriver module is used
     *
     * @return bool
     */
    public function isWebDriver()
    {
        return $this->moduleName === self::MODULE_WEBDRIVER;
    }

    /**
     * Responds to the lack of method implementation for a given module
     *
     * @param string $moduleName module name
     * @param string $methodName method name
     *
     * @return void
     *
     * @throws LogicException
     */
    protected function methodNotImplemented($moduleName, $methodName)
    {
        throw new LogicException("Method $methodName is not implemented for module $moduleName");
    }

    /**
     * Initialize module
     *
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->module->_initialize();
    }

    /**
     * Cleanup module
     *
     * @return void
     */
    public function _cleanup()
    {
        parent::_cleanup();
        $this->module->_cleanup();
    }

    /**
     * Before suite hook
     *
     * @param array $settings settings
     *
     * @return void
     */
    public function _beforeSuite($settings = array())
    {
        parent::_beforeSuite($settings);
        $this->module->_beforeSuite($settings);
    }

    /**
     * After suite hook
     *
     * @return void
     */
    public function _afterSuite()
    {
        parent::_afterSuite();
        $this->module->_afterSuite();
    }

    /**
     * Before step hook
     *
     * @param Step $step step
     *
     * @return void
     */
    public function _beforeStep(Step $step)
    {
        parent::_beforeStep($step);
        $this->module->_beforeStep($step);
    }

    /**
     * After step hook
     *
     * @param Step $step step
     *
     * @return void
     */
    public function _afterStep(Step $step)
    {
        parent::_afterStep($step);
        $this->module->_afterStep($step);
    }

    /**
     * Before test case hook
     *
     * @param TestCase $test test
     *
     * @return void
     */
    public function _before(TestCase $test)
    {
        parent::_before($test);
        $this->module->_before($test);
    }

    /**
     * After test case hook
     *
     * @param TestCase $test test
     *
     * @return void
     */
    public function _after(TestCase $test)
    {
        parent::_after($test);
        $this->module->_after($test);
    }

    /**
     * Failed test case hook
     *
     * @param TestCase $test test
     * @param mixed    $fail fail
     *
     * @return void
     */
    public function _failed(TestCase $test, $fail)
    {
        parent::_failed($test, $fail);
        $this->module->_failed($test, $fail);
    }

    /**
     * Opens the page for the given relative URI.
     *
     * ``` php
     * <?php
     * // opens front page
     * $I->amOnPage('/');
     * // opens /register page
     * $I->amOnPage('/register');
     * ?>
     * ```
     *
     * @param string $page page URI
     *
     * @return void
     */
    public function amOnPage($page)
    {
        $this->runner->amOnPage($page);
    }

    /**
     * Changes the subdomain for the 'url' configuration parameter.
     * Does not open a page; use `amOnPage` for that.
     *
     * ``` php
     * <?php
     * // If config is: 'http://mysite.com'
     * // or config is: 'http://www.mysite.com'
     * // or config is: 'http://company.mysite.com'
     *
     * $I->amOnSubdomain('user');
     * $I->amOnPage('/');
     * // moves to http://user.mysite.com/
     * ?>
     * ```
     *
     * @param string $subdomain subdomain
     *
     * @return void
     */
    public function amOnSubdomain($subdomain)
    {
        $this->runner->amOnSubdomain($subdomain);
    }

    /**
     * Open web page at the given absolute URL and sets its hostname as the base host.
     *
     * ``` php
     * <?php
     * $I->amOnUrl('http://codeception.com');
     * $I->amOnPage('/quickstart'); // moves to http://codeception.com/quickstart
     * ?>
     * ```
     *
     * @param string $url url
     *
     * @return void
     */
    public function amOnUrl($url)
    {
        $this->runner->amOnUrl($url);
    }

    /**
     * Attaches a file relative to the Codeception data directory to the given file upload field.
     *
     * ``` php
     * <?php
     * // file is stored in 'tests/_data/prices.xls'
     * $I->attachFile('input[@type="file"]', 'prices.xls');
     * ?>
     * ```
     *
     * @param string $field    field
     * @param string $filename filename
     *
     * @return void
     */
    public function attachFile($field, $filename)
    {
        $this->runner->attachFile($field, $filename);
    }

    /**
     * Ticks a checkbox. For radio buttons, use the `selectOption` method instead.
     *
     * ``` php
     * <?php
     * $I->checkOption('#agree');
     * ?>
     * ```
     *
     * @param string $option option
     *
     * @return void
     */
    public function checkOption($option)
    {
        $this->runner->checkOption($option);
    }

    /**
     * Perform a click on a link or a button, given by a locator.
     * If a fuzzy locator is given, the page will be searched for a button, link, or image matching the locator string.
     * For buttons, the "value" attribute, "name" attribute, and inner text are searched.
     * For links, the link text is searched.
     * For images, the "alt" attribute and inner text of any parent links are searched.
     *
     * The second parameter is a context (CSS or XPath locator) to narrow the search.
     *
     * Note that if the locator matches a button of type `submit`, the form will be submitted.
     *
     * ``` php
     * <?php
     * // simple link
     * $I->click('Logout');
     * // button of form
     * $I->click('Submit');
     * // CSS button
     * $I->click('#form input[type=submit]');
     * // XPath
     * $I->click('//form/*[@type=submit]');
     * // link in context
     * $I->click('Logout', '#nav');
     * // using strict locator
     * $I->click(['link' => 'Login']);
     * ?>
     * ```
     *
     * @param string $link    link
     * @param string $context context
     *
     * @return void
     */
    public function click($link, $context = null)
    {
        $this->runner->click($link, $context);
    }

    /**
     * Checks that the current page doesn't contain the text specified.
     * Give a locator as the second parameter to match a specific region.
     *
     * ```php
     * <?php
     * $I->dontSee('Login'); // I can suppose user is already logged in
     * $I->dontSee('Sign Up','h1'); // I can suppose it's not a signup page
     * $I->dontSee('Sign Up','//body/h1'); // with XPath
     * ?>
     * ```
     *
     * @param string      $text     text
     * @param string|null $selector selector
     *
     * @return void
     */
    public function dontSee($text, $selector = null)
    {
        $this->runner->dontSee($text, $selector);
    }

    /**
     * Check that the specified checkbox is unchecked.
     *
     * ``` php
     * <?php
     * $I->dontSeeCheckboxIsChecked('#agree'); // I suppose user didn't agree to terms
     * $I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user didn't check the first checkbox in form.
     * ?>
     * ```
     *
     * @param string $checkbox checkbox
     *
     * @return void
     */
    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $this->runner->dontSeeCheckboxIsChecked($checkbox);
    }

    /**
     * Checks that there isn't a cookie with the given name.
     *
     * @param string $name   name
     * @param array  $params params
     *
     * @return void
     */
    public function dontSeeCookie($name, array $params = [])
    {
        $this->runner->dontSeeCookie($name, $params);
    }

    /**
     * Checks that the current URL doesn't equal the given string.
     * Unlike `dontSeeInCurrentUrl`, this only matches the full URL.
     *
     * ``` php
     * <?php
     * // current url is not root
     * $I->dontSeeCurrentUrlEquals('/');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function dontSeeCurrentUrlEquals($uri)
    {
        $this->runner->dontSeeCurrentUrlEquals($uri);
    }

    /**
     * Checks that current url doesn't match the given regular expression.
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->dontSeeCurrentUrlMatches('~$/users/(\d+)~');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function dontSeeCurrentUrlMatches($uri)
    {
        $this->runner->dontSeeCurrentUrlMatches($uri);
    }

    /**
     * Checks that the given element is invisible or not present on the page.
     * You can also specify expected attributes of this element.
     *
     * ``` php
     * <?php
     * $I->dontSeeElement('.error');
     * $I->dontSeeElement('//form/input[1]');
     * $I->dontSeeElement('input', ['name' => 'login']);
     * $I->dontSeeElement('input', ['value' => '123456']);
     * ?>
     * ```
     *
     * @param string $selector   selector
     * @param array  $attributes attributes
     *
     * @return void
     */
    public function dontSeeElement($selector, $attributes = array())
    {
        $this->runner->dontSeeElement($selector, $attributes);
    }

    /**
     * Checks that the current URI doesn't contain the given string.
     *
     * ``` php
     * <?php
     * $I->dontSeeInCurrentUrl('/users/');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function dontSeeInCurrentUrl($uri)
    {
        $this->runner->dontSeeInCurrentUrl($uri);
    }

    /**
     * Checks that an input field or textarea doesn't contain the given value.
     * For fuzzy locators, the field is matched by label text, CSS and XPath.
     *
     * ``` php
     * <?php
     * $I->dontSeeInField('Body','Type your comment here');
     * $I->dontSeeInField('form textarea[name=body]','Type your comment here');
     * $I->dontSeeInField('form input[type=hidden]','hidden_value');
     * $I->dontSeeInField('#searchform input','Search');
     * $I->dontSeeInField('//form/*[@name=search]','Search');
     * $I->dontSeeInField(['name' => 'search'], 'Search');
     * ?>
     * ```
     *
     * @param string $field field
     * @param string $value value
     *
     * @return void
     */
    public function dontSeeInField($field, $value)
    {
        $this->runner->dontSeeInField($field, $value);
    }

    /**
     * Checks that the page title does not contain the given string.
     *
     * @param string $title title
     *
     * @return void
     */
    public function dontSeeInTitle($title)
    {
        $this->runner->dontSeeInTitle($title);
    }

    /**
     * Checks that the page doesn't contain a link with the given string.
     * If the second parameter is given, only links with a matching "href" attribute will be checked.
     *
     * ``` php
     * <?php
     * $I->dontSeeLink('Logout'); // I suppose user is not logged in
     * $I->dontSeeLink('Checkout now', '/store/cart.php');
     * ?>
     * ```
     *
     * @param string      $text text
     * @param string|null $url  URL
     *
     * @return void
     */
    public function dontSeeLink($text, $url = null)
    {
        $this->runner->dontSeeLink($text, $url);
    }

    /**
     * Checks that the given option is not selected.
     *
     * ``` php
     * <?php
     * $I->dontSeeOptionIsSelected('#form input[name=payment]', 'Visa');
     * ?>
     * ```
     *
     * @param string $selector   selector
     * @param string $optionText option text
     *
     * @return void
     */
    public function dontSeeOptionIsSelected($selector, $optionText)
    {
        $this->runner->dontSeeOptionIsSelected($selector, $optionText);
    }

    /**
     * Fills a text field or textarea with the given string.
     *
     * ``` php
     * <?php
     * $I->fillField("//input[@type='text']", "Hello World!");
     * $I->fillField(['name' => 'email'], 'jon@mail.com');
     * ?>
     * ```
     *
     * @param string $field field
     * @param string $value value
     *
     * @return void
     */
    public function fillField($field, $value)
    {
        $this->runner->fillField($field, $value);
    }

    /**
     * Grabs the value of the given attribute value from the given element.
     * Fails if element is not found.
     *
     * ``` php
     * <?php
     * $I->grabAttributeFrom('#tooltip', 'title');
     * ?>
     * ```
     *
     * @param string $cssOrXpath CSS or XPath
     * @param string $attribute  attribute
     *
     * @internal param $element
     * @return mixed
     */
    public function grabAttributeFrom($cssOrXpath, $attribute)
    {
        return $this->runner->grabAttributeFrom($cssOrXpath, $attribute);
    }

    /**
     * Grabs a cookie value.
     *
     * @param string $name   name
     * @param array  $params params
     *
     * @return mixed
     */
    public function grabCookie($name, array $params = [])
    {
        return $this->runner->grabCookie($name, $params);
    }

    /**
     * Executes the given regular expression against the current URI and returns the first match.
     * If no parameters are provided, the full URI is returned.
     *
     * ``` php
     * <?php
     * $user_id = $I->grabFromCurrentUrl('~$/user/(\d+)/~');
     * $uri = $I->grabFromCurrentUrl();
     * ?>
     * ```
     *
     * @param string|null $uri URI
     *
     * @internal param $url
     * @return mixed
     */
    public function grabFromCurrentUrl($uri = null)
    {
        return $this->runner->grabFromCurrentUrl($uri);
    }

    /**
     * Finds and returns the text contents of the given element.
     * If a fuzzy locator is used, the element is found using CSS, XPath, and by matching the full page source by regular expression.
     *
     * ``` php
     * <?php
     * $heading = $I->grabTextFrom('h1');
     * $heading = $I->grabTextFrom('descendant-or-self::h1');
     * $value = $I->grabTextFrom('~<input value=(.*?)]~sgi'); // match with a regex
     * ?>
     * ```
     *
     * @param string $cssOrXpathOrRegex CSS or XPath or RegEx
     *
     * @return mixed
     */
    public function grabTextFrom($cssOrXpathOrRegex)
    {
        return $this->runner->grabTextFrom($cssOrXpathOrRegex);
    }

    /**
     * Finds the value for the given form field.
     * If a fuzzy locator is used, the field is found by field name, CSS, and XPath.
     *
     * ``` php
     * <?php
     * $name = $I->grabValueFrom('Name');
     * $name = $I->grabValueFrom('input[name=username]');
     * $name = $I->grabValueFrom('descendant-or-self::form/descendant::input[@name = 'username']');
     * $name = $I->grabValueFrom(['name' => 'username']);
     * ?>
     * ```
     *
     * @param string $field field
     *
     * @return mixed
     */
    public function grabValueFrom($field)
    {
        return $this->runner->grabValueFrom($field);
    }

    /**
     * Unsets cookie with the given name.
     *
     * @param string $name   name
     * @param array  $params params
     *
     * @return mixed
     */
    public function resetCookie($name, array $params = [])
    {
        $this->runner->resetCookie($name, $params);
    }

    /**
     * Checks that the current page contains the given string.
     * Specify a locator as the second parameter to match a specific region.
     *
     * ``` php
     * <?php
     * $I->see('Logout'); // I can suppose user is logged in
     * $I->see('Sign Up','h1'); // I can suppose it's a signup page
     * $I->see('Sign Up','//body/h1'); // with XPath
     * ?>
     * ```
     *
     * @param string      $text     text
     * @param string|null $selector selector
     *
     * @return void
     */
    public function see($text, $selector = null)
    {
        $this->runner->see($text, $selector);
    }

    /**
     * Checks that the specified checkbox is checked.
     *
     * ``` php
     * <?php
     * $I->seeCheckboxIsChecked('#agree'); // I suppose user agreed to terms
     * $I->seeCheckboxIsChecked('#signup_form input[type=checkbox]'); // I suppose user agreed to terms, If there is only one checkbox in form.
     * $I->seeCheckboxIsChecked('//form/input[@type=checkbox and @name=agree]');
     * ?>
     * ```
     *
     * @param string $checkbox checkbox
     *
     * @return void
     */
    public function seeCheckboxIsChecked($checkbox)
    {
        $this->runner->seeCheckboxIsChecked($checkbox);
    }

    /**
     * Checks that a cookie with the given name is set.
     *
     * ``` php
     * <?php
     * $I->seeCookie('PHPSESSID');
     * ?>
     * ```
     *
     * @param string $name   name
     * @param array  $params params
     *
     * @return mixed
     */
    public function seeCookie($name, array $params = [])
    {
        $this->runner->seeCookie($name, $params);
    }

    /**
     * Checks that the current URL is equal to the given string.
     * Unlike `seeInCurrentUrl`, this only matches the full URL.
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->seeCurrentUrlEquals('/');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function seeCurrentUrlEquals($uri)
    {
        $this->runner->seeCurrentUrlEquals($uri);
    }

    /**
     * Checks that the current URL matches the given regular expression.
     *
     * ``` php
     * <?php
     * // to match root url
     * $I->seeCurrentUrlMatches('~$/users/(\d+)~');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function seeCurrentUrlMatches($uri)
    {
        $this->runner->seeCurrentUrlMatches($uri);
    }

    /**
     * Checks that the given element exists on the page and is visible.
     * You can also specify expected attributes of this element.
     *
     * ``` php
     * <?php
     * $I->seeElement('.error');
     * $I->seeElement('//form/input[1]');
     * $I->seeElement('input', ['name' => 'login']);
     * $I->seeElement('input', ['value' => '123456']);
     *
     * // strict locator in first arg, attributes in second
     * $I->seeElement(['css' => 'form input'], ['name' => 'login']);
     * ?>
     * ```
     *
     * @param string $selector   selector
     * @param array  $attributes attributes
     *
     * @return void
     */
    public function seeElement($selector, $attributes = array())
    {
        $this->runner->seeElement($selector, $attributes);
    }

    /**
     * Checks that current URI contains the given string.
     *
     * ``` php
     * <?php
     * // to match: /home/dashboard
     * $I->seeInCurrentUrl('home');
     * // to match: /users/1
     * $I->seeInCurrentUrl('/users/');
     * ?>
     * ```
     *
     * @param string $uri URI
     *
     * @return void
     */
    public function seeInCurrentUrl($uri)
    {
        $this->runner->seeInCurrentUrl($uri);
    }

    /**
     * Checks that the given input field or textarea contains the given value.
     * For fuzzy locators, fields are matched by label text, the "name" attribute, CSS, and XPath.
     *
     * ``` php
     * <?php
     * $I->seeInField('Body','Type your comment here');
     * $I->seeInField('form textarea[name=body]','Type your comment here');
     * $I->seeInField('form input[type=hidden]','hidden_value');
     * $I->seeInField('#searchform input','Search');
     * $I->seeInField('//form/*[@name=search]','Search');
     * $I->seeInField(['name' => 'search'], 'Search');
     * ?>
     * ```
     *
     * @param string $field field
     * @param string $value value
     *
     * @return void
     */
    public function seeInField($field, $value)
    {
        $this->runner->seeInField($field, $value);
    }

    /**
     * Checks that the page title contains the given string.
     *
     * ``` php
     * <?php
     * $I->seeInTitle('Blog - Post #1');
     * ?>
     * ```
     *
     * @param string $title title
     *
     * @return void
     */
    public function seeInTitle($title)
    {
        $this->runner->seeInTitle($title);
    }

    /**
     * Checks that there's a link with the specified text.
     * Give a full URL as the second parameter to match links with that exact URL.
     *
     * ``` php
     * <?php
     * $I->seeLink('Logout'); // matches <a href="#">Logout</a>
     * $I->seeLink('Logout','/logout'); // matches <a href="/logout">Logout</a>
     * ?>
     * ```
     *
     * @param string      $text text
     * @param string|null $url  URL
     *
     * @return void
     */
    public function seeLink($text, $url = null)
    {
        $this->runner->seeLink($text, $url);
    }

    /**
     * Checks that there are a certain number of elements matched by the given locator on the page.
     *
     * ``` php
     * <?php
     * $I->seeNumberOfElements('tr', 10);
     * $I->seeNumberOfElements('tr', [0,10]); //between 0 and 10 elements
     * ?>
     * ```
     *
     * @param string $selector selector
     * @param mixed  $expected expected:
     * - string: strict number
     * - array: range of numbers [0,10]
     *
     * @return void
     */
    public function seeNumberOfElements($selector, $expected)
    {
        $this->runner->seeNumberOfElements($selector, $expected);
    }

    /**
     * Checks that the given option is selected.
     *
     * ``` php
     * <?php
     * $I->seeOptionIsSelected('#form input[name=payment]', 'Visa');
     * ?>
     * ```
     *
     * @param string $selector   selector
     * @param string $optionText option text
     *
     * @return void
     */
    public function seeOptionIsSelected($selector, $optionText)
    {
        $this->runner->seeOptionIsSelected($selector, $optionText);
    }

    /**
     * Selects an option in a select tag or in radio button group.
     *
     * ``` php
     * <?php
     * $I->selectOption('form select[name=account]', 'Premium');
     * $I->selectOption('form input[name=payment]', 'Monthly');
     * $I->selectOption('//form/select[@name=account]', 'Monthly');
     * ?>
     * ```
     *
     * Provide an array for the second argument to select multiple options:
     *
     * ``` php
     * <?php
     * $I->selectOption('Which OS do you use?', array('Windows','Linux'));
     * ?>
     * ```
     *
     * @param string $select select
     * @param string $option option
     *
     * @return void
     */
    public function selectOption($select, $option)
    {
        $this->runner->selectOption($select, $option);
    }

    /**
     * Sets a cookie with the given name and value.
     *
     * ``` php
     * <?php
     * $I->setCookie('PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3');
     * ?>
     * ```
     *
     * @param string $name   name
     * @param string $value  value
     * @param array  $params params
     *
     * @return void
     */
    public function setCookie($name, $value, array $params = [])
    {
        $this->runner->setCookie($name, $value, $params);
    }

    /**
     * Submit form
     *
     * @param string      $selector selector
     * @param array       $params   params
     * @param string|null $button   button
     *
     * @return void
     */
    /*public function submitForm($selector, $params, $button = null)
    {
        $this->runner->submitForm($selector, $params, $button);
    }*/

    /**
     * Uncheck option
     *
     * @param string $option option
     *
     * @return void
     */
    public function uncheckOption($option)
    {
        $this->runner->uncheckOption($option);
    }

    /**
     * Am HTTP authenticated
     *
     * PhpBrowser ONLY
     *
     * @param string $username username
     * @param string $password password
     *
     * @throws LogicException
     *
     * @return void
     */
    public function amHttpAuthenticated($username, $password)
    {
        if ($this->isPhpBrowser()) {
            $this->runner->amHttpAuthenticated($username, $password);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Execute in Guzzle
     *
     * PhpBrowser ONLY
     *
     * @param callable $function function
     *
     * @throws LogicException
     *
     * @return mixed
     */
    public function executeInGuzzle(\Closure $function)
    {
        if ($this->isPhpBrowser()) {
            return $this->runner->executeInGuzzle($function);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
        return null;
    }

    /**
     * See page not found
     *
     * PhpBrowser ONLY
     *
     * @throws LogicException
     *
     * @return void
     */
    public function seePageNotFound()
    {
        if ($this->isPhpBrowser()) {
            $this->runner->seePageNotFound();
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * See response code is
     *
     * PhpBrowser ONLY
     *
     * @param string|int $code code
     *
     * @return void
     *
     * @throws LogicException
     */
    public function seeResponseCodeIs($code)
    {
        if ($this->isPhpBrowser()) {
            $this->runner->seeResponseCodeIs($code);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Send AJAX GET request
     *
     * PhpBrowser ONLY
     *
     * @param string $uri    URI
     * @param array  $params params
     *
     * @return void
     *
     * @throws LogicException
     */
    public function sendAjaxGetRequest($uri, $params = array())
    {
        if ($this->isPhpBrowser()) {
            $this->runner->sendAjaxGetRequest($uri, $params);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Send AJAX POST request
     *
     * PhpBrowser ONLY
     *
     * @param string $uri    URI
     * @param array  $params params
     *
     * @return void
     *
     * @throws LogicException
     */
    public function sendAjaxPostRequest($uri, $params = array())
    {
        if ($this->isPhpBrowser()) {
            $this->runner->sendAjaxPostRequest($uri, $params);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Send AJAX request
     *
     * PhpBrowser ONLY
     *
     * @param string $method method
     * @param string $uri    URI
     * @param array  $params params
     *
     * @return void
     *
     * @throws LogicException
     */
    public function sendAjaxRequest($method, $uri, $params = array())
    {
        if ($this->isPhpBrowser()) {
            $this->runner->sendAjaxRequest($method, $uri, $params);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Set header
     *
     * PhpBrowser ONLY
     *
     * @param string $header header
     * @param string $value  value
     *
     * @return void
     *
     * @throws LogicException
     */
    public function setHeader($header, $value)
    {
        if ($this->isPhpBrowser()) {
            $this->runner->setHeader($header, $value);
        } else {
            $this->methodNotImplemented(self::MODULE_WEBDRIVER, __FUNCTION__);
        }
    }

    /**
     * Accept popup
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function acceptPopup()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->acceptPopup();
        }
    }

    /**
     * Append field
     *
     * WebDriver ONLY
     *
     * @param string $field field
     * @param string $value value
     *
     * @return void
     *
     * @throws LogicException
     */
    public function appendField($field, $value)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->appendField($field, $value);
        }
    }

    /**
     * Cancel popup
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function cancelPopup()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->cancelPopup();
        }
    }

    /**
     * Click with right button
     *
     * WebDriver ONLY
     *
     * @param string $cssOrXpath CSS or XPath
     *
     * @return void
     *
     * @throws LogicException
     */
    public function clickWithRightButton($cssOrXpath)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->clickWithRightButton($cssOrXpath);
        }
    }

    /**
     * Don't see element in DOM
     *
     * WebDriver ONLY
     *
     * @param string $selector   selector
     * @param array  $attributes attributes
     *
     * @return void
     *
     * @throws LogicException
     */
    public function dontSeeElementInDOM($selector, $attributes = array())
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->dontSeeElementInDOM($selector, $attributes);
        }
    }

    /**
     * Don't see in page source
     *
     * WebDriver ONLY
     *
     * @param string $text text
     *
     * @return void
     *
     * @throws LogicException
     */
    public function dontSeeInPageSource($text)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->dontSeeInPageSource($text);
        }
    }

    /**
     * Double click
     *
     * WebDriver ONLY
     *
     * @param string $cssOrXpath CSS or XPath
     *
     * @return void
     *
     * @throws LogicException
     */
    public function doubleClick($cssOrXpath)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->doubleClick($cssOrXpath);
        }
    }

    /**
     * Drag and drop
     *
     * WebDriver ONLY
     *
     * @param string $source source
     * @param string $target target
     *
     * @return void
     *
     * @throws LogicException
     */
    public function dragAndDrop($source, $target)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->dragAndDrop($source, $target);
        }
    }

    /**
     * Execute in Selenium
     *
     * WebDriver ONLY
     *
     * @param callable $function function
     *
     * @return mixed
     *
     * @throws LogicException
     */
    public function executeInSelenium(\Closure $function)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            return $this->runner->executeInSelenium($function);
        }
        return null;
    }

    /**
     * Execute JS
     *
     * WebDriver ONLY
     *
     * @param string $script script
     *
     * @return mixed
     *
     * @throws LogicException
     */
    public function executeJS($script)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            return $this->runner->executeJS($script);
        }
        return null;
    }

    /**
     * Get visible text
     *
     * WebDriver ONLY
     *
     * @return string
     *
     * @throws LogicException
     */
    public function getVisibleText()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            return $this->runner->getVisibleText();
        }
        return '';
    }

    /**
     * Make screenshot
     *
     * WebDriver ONLY
     *
     * @param string $name name
     *
     * @return void
     *
     * @throws LogicException
     */
    public function makeScreenshot($name)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->makeScreenshot($name);
        }
    }

    /**
     * Maximize window
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function maximizeWindow()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->maximizeWindow();
        }
    }

    /**
     * Move back
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function moveBack()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->moveBack();
        }
    }

    /**
     * Move forward
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function moveForward()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->moveForward();
        }
    }

    /**
     * Move mouse over
     *
     * WebDriver ONLY
     *
     * @param string   $cssOrXpath CSS or XPath
     * @param null|int $offsetX    X offset
     * @param null|int $offsetY    Y offset
     *
     * @return void
     *
     * @throws LogicException
     */
    public function moveMouseOver($cssOrXpath, $offsetX = null, $offsetY = null)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->moveMouseOver($cssOrXpath, $offsetX, $offsetY);
        }
    }

    /**
     * Pause execution
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function pauseExecution()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->pauseExecution();
        }
    }

    /**
     * Press key
     *
     * WebDriver ONLY
     *
     * @param string $element element
     * @param string $char    char
     *
     * @return void
     *
     * @throws LogicException
     */
    public function pressKey($element, $char)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->pressKey($element, $char);
        }
    }

    /**
     * Reload page
     *
     * WebDriver ONLY
     *
     * @return void
     *
     * @throws LogicException
     */
    public function reloadPage()
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->reloadPage();
        }
    }

    /**
     * Resize window
     *
     * WebDriver ONLY
     *
     * @param int $width  width
     * @param int $height height
     *
     * @return void
     *
     * @throws LogicException
     */
    public function resizeWindow($width, $height)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->resizeWindow($width, $height);
        }
    }

    /**
     * See element in DOM
     *
     * WebDriver ONLY
     *
     * @param string $selector   selector
     * @param array  $attributes attributes
     *
     * @return void
     *
     * @throws LogicException
     */
    public function seeElementInDOM($selector, $attributes = array())
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->seeElementInDOM($selector, $attributes);
        }
    }

    /**
     * See in page source
     *
     * WebDriver ONLY
     *
     * @param string $text text
     *
     * @return void
     *
     * @throws LogicException
     */
    public function seeInPageSource($text)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->seeInPageSource($text);
        }
    }

    /**
     * See in popup
     *
     * WebDriver ONLY
     *
     * @param string $text text
     *
     * @return void
     *
     * @throws LogicException
     */
    public function seeInPopup($text)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->seeInPopup($text);
        }
    }

    /**
     * Switch to Iframe
     *
     * WebDriver ONLY
     *
     * @param null|string $name name
     *
     * @return void
     *
     * @throws LogicException
     */
    public function switchToIFrame($name = null)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->switchToIFrame($name);
        }
    }

    /**
     * Switch to window
     *
     * WebDriver ONLY
     *
     * @param null|string $name name
     *
     * @return void
     *
     * @throws LogicException
     */
    public function switchToWindow($name = null)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->switchToWindow($name);
        }
    }

    /**
     * Type in popup
     *
     * WebDriver ONLY
     *
     * @param string $keys keys
     *
     * @return void
     *
     * @throws LogicException
     */
    public function typeInPopup($keys)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->typeInPopup($keys);
        }
    }

    /**
     * Unselect option
     *
     * WebDriver ONLY
     *
     * @param string $select select
     * @param string $option option
     *
     * @return void
     *
     * @throws LogicException
     */
    public function unselectOption($select, $option)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->unselectOption($select, $option);
        }
    }

    /**
     * Wait
     *
     * @param int $timeout timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function wait($timeout)
    {
        if ($this->isPhpBrowser()) {
            sleep($timeout);
        } else {
            $this->runner->wait($timeout);
        }
    }

    /**
     * Wait for element
     *
     * WebDriver ONLY
     *
     * @param string $element element
     * @param int    $timeout timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForElement($element, $timeout = 10)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForElement($element, $timeout);
        }
    }

    /**
     * Wait for element to change
     *
     * WebDriver ONLY
     *
     * @param string   $element  element
     * @param callable $callback callback
     * @param int      $timeout  timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForElementChange($element, \Closure $callback, $timeout = 30)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForElementChange($element, $callback, $timeout);
        }
    }

    /**
     * Wait for element to not be visible
     *
     * WebDriver ONLY
     *
     * @param string $element element
     * @param int    $timeout timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForElementNotVisible($element, $timeout = 10)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForElementNotVisible($element, $timeout);
        }
    }

    /**
     * Wait for element to be visible
     *
     * WebDriver ONLY
     *
     * @param string $element element
     * @param int    $timeout timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForElementVisible($element, $timeout = 10)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForElementVisible($element, $timeout);
        }
    }

    /**
     * Wait for JS
     *
     * WebDriver only
     *
     * @param string $script  script
     * @param int    $timeout timeout
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForJS($script, $timeout = 5)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForJS($script, $timeout);
        }
    }

    /**
     * Wait for text
     *
     * WebDriver ONLY
     *
     * @param string      $text     text
     * @param int         $timeout  timeout
     * @param string|null $selector selector
     *
     * @return void
     *
     * @throws LogicException
     */
    public function waitForText($text, $timeout = 10, $selector = null)
    {
        if ($this->isPhpBrowser()) {
            $this->methodNotImplemented(self::MODULE_PHPBROWSER, __FUNCTION__);
        } else {
            $this->runner->waitForText($text, $timeout, $selector);
        }
    }

    /**
     * Get URL
     *
     * @return mixed
     */
    public function _getUrl()
    {
        return $this->runner->_getUrl();
    }

    /**
     * Initialize session
     *
     * @return void
     */
    public function _initializeSession()
    {
        $this->runner->_initializeSession();
    }

    /**
     * Load session data
     *
     * @param mixed $data data
     *
     * @return void
     */
    public function _loadSessionData($data)
    {
        $this->runner->_loadSessionData($data);
    }

    /**
     * Backup session data
     *
     * @return array|\RemoteWebDriver
     */
    public function _backupSessionData()
    {
        return $this->runner->_backupSessionData();
    }

    /**
     * Close session
     *
     * @param mixed $data data
     *
     * @return void
     */
    public function _closeSession($data)
    {
        $this->runner->_closeSession($data);
    }


    /**
     * Returns array of Web elements with given selector.
     *
     * @param string $cssSelector - selector to the element which we are looking for
     *
     * @return \WebDriverElement[]
     */
    public function getElements($cssSelector)
    {
        /** @var \WebDriverElement[] $elements */
        $elements = $this->executeInSelenium(function (\RemoteWebDriver $webDriver) use(&$cssSelector) {
            return $webDriver->findElements(\WebDriverBy::cssSelector($cssSelector));
        });
        return count($elements);
    }

    /**
     * Return true if element exists on page
     *
     * @param string $cssSelector selector
     *
     * @return bool
     *
     *
     */
    public function isElementPresent($cssSelector)
    {
        if ($this->isPhpBrowser()) {
            try{
                $this->seeElement($cssSelector);
                return true;
            } catch(\Exception $e){
                return false;
            }
        }
        if ($this->isWebDriver()) {
            return ($this->getElements($cssSelector) > 0);
        }
    }
}
