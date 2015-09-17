<?php
/**
 * Created by PhpStorm.
 * User: amurthy
 * Date: 15.09.15
 * Time: 22:48
 */
use \commons\Page\SearchPage;

class PriceFilterSortingCest {


    /**
     * Before to test
     *
     * @param SortingTester $i actor
     *
     * @return void
     */
    public function _before(SortingTester $i)
    {
        SearchPage::of($i)
            ->openSearchPage();

    }

    /**
     * Assert train result prices are price with one page results
     *
     * @param SortingTester $i actor
     */
   public function assertPriceSortingInAscendingOrder(SortingTester $i)
   {
       SearchPage::of($i)
           ->search("Berlin, Germany", "Prague, Czech Republic")
           ->verifyTrainPriceIsSortedInAscendingOrder();
   }

    /**
     * Assert train result prices are price with multiple page results
     *
     * @param SortingTester $i actor
     */
    public function assertPriceSortingInAscendingOrderInMultiplePages(SortingTester $i)
    {
        SearchPage::of($i)
            ->search("Berlin, Germany", "Munich, Germany")
            ->verifyTrainPriceIsSortedInAscendingOrder();
    }
}