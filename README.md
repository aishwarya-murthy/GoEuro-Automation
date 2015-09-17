# GoEuro-Automation
# Install composer and php >5.4
# Checkout project, navigate to codeception folder and run composer update. 
This generates vendor.
# Now run vendor/bin/codepcept build to generate actor classes
# Download selenium server jar, and start selenium server 
java -jar selenium-server-standalone-2.45.0.jar
# Run tests with command, 
SHIM_MODULE="WebDriver" vendor/bin/codecept run  --html --debug --colors sorting PriceFilterSortingCest

The project uses page object model, commons contains page classes and tests are in tests dir
Reports are in build. Html reports are generated with list of failed , passed tests.

These tests can be run on both PhpBrowser and Webdriver modules.

