<?php
/**
 * Created by PhpStorm.
 * User: amurthy
 * Date: 15.09.15
 * Time: 16:59
 */
namespace commons\Page;

class ResultsPage extends Base {

    const SORT_BY_PRICE       = "#sortby-price";
    const SORT_BY_TRAVEL_TIME = "#sortby-traveltime";
    const PAGE_NUMBER         = ".pagination li:nth-of-type(x) a";

    /**
     * @return bool
     */
    public function isElementNextPagePresent()
    {
        $i           = $this->actor;
        $nextElement = array();
        $i->executeInSelenium(function (\RemoteWebDriver $webDriver) use(&$nextElement) {
            $nextElement = $webDriver->findElements(\WebDriverBy::linkText('Next'));
        });
        if(count($nextElement) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Go to next result page
     */
    public function goToNextResultPage()
    {
        $i = $this->actor;
        $i->executeInSelenium(function (\RemoteWebDriver $webDriver) {
                $webDriver->findElement(\WebDriverBy::linkText('Next'))->click();
            });
    }

    /**
     * @return int
     */
    public function noOfTrainResultsOnPage()
    {
        $i = $this->actor;
        $elements = array();
        $i->waitForElementVisible('div[id="results-train"] div[class="result"]');
        $i->executeInSelenium(function (\RemoteWebDriver $webDriver) use(&$elements) {
            $elements = $webDriver->findElements(\WebDriverBy::cssSelector('div[id="results-train"] div[class="result"]'));
        });
        return count($elements);
    }

    /**
     * Get train fare
     *
     * @param $counter
     *
     * @return float
     */
    public function getTrainFare($counter)
    {
        $i = $this->actor;
        $price   = $i->grabTextFrom('div[id="results-train"] div[class="result"]:nth-child('. $counter . ') .currency-beforecomma');
        $decimal = $i->grabTextFrom('div[id="results-train"] div[class="result"]:nth-child(' .$counter .') .currency-decimals:nth-of-type(4)');
        $formattedPrice = $price. '.' .$decimal;
        return (float) $formattedPrice;
    }

    public function verifyTrainPriceIsSortedInAscendingOrder()
    {
        $i = $this->actor;
        $amount1 = 0;
        $pageCount = 1;
    do{
        if ($pageCount !=1) {
            // Go to next result page
            ResultsPage::goToNextResultPage();
        }
        //Get number of	result trains on given page
            $count = ResultsPage::noOfTrainResultsOnPage();
            for($k=1 ;$k<=$count;$k++) {
                //Get train fare for each result
                $amount2 = self::getTrainFare($k);

                //Assert results are sorted in terms of price (cheapest first)
                $i->assertTrue($amount1<=$amount2);
                $amount1 = $amount2;
            }
        $pageCount++;
        } while (self::isElementNextPagePresent());
    }

    /**
     * Assert results page with results is loaded
     */
    public function assertResultsPage()
    {
        $i = $this->actor;
        $i->amOnPage('');
        $i->seeElement();

    }
}