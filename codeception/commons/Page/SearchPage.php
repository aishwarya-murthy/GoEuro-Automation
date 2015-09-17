<?php
/**
 * Created by PhpStorm.
 * User: amurthy
 * Date: 15.09.15
 * Time: 16:59
 */
namespace commons\Page;
class SearchPage extends Base{

    const FROM_FIELD    = "input[id='from_filter']";
    const TO_FIELD      = "input[id='to_filter']";
    const DEPARTURE_DATE = "#departure_date";
    const PERSON_COUNTER = "div[id='person-counter']";
    const SEARCH_BUTTON = "#search-form__submit-btn";
    const HOTEL_CHKBOX  = '.hotel-checkboxes';
    const AIRBNB_CHKBOX = '[data-partner="airbnb"] span';

    /**
     * Opens Home page
     *
     * @return $this
     */
    public function openSearchPage()
    {
        //Open Home page
        $this->openPage('');
        //assert there are no Http errors on page
        $this->assertNoHttpErrorsDisplayed();
        return $this;
    }

    /**
     * Search with default date
     *
     * @param $from
     * @param $to
     * @return ResultsPage
     */
    public function Search($from, $to){
        $i = $this->actor;
        //Fill from and to fields and click search button
        $i->fillField(self::FROM_FIELD, $from);
        $i->fillField(self::TO_FIELD, $to);
        $i->click('#search');
        $i->click(self::SEARCH_BUTTON);
        //Wait for page to load
        $i->waitForJS('return document.readyState === \'complete\'');
        return ResultsPage::of($i);
    }
}