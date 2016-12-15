# HmGestor

- clone repo
- install composer: curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
- `composer install`
- config databse in /user@host.local 
- `./server` to run localhost:3000

# Tests

- `./server` 
- `./selenium`
- `./test`

# Install PhantomJS

- `sudo apt-get update`
- `sudo apt-get install build-essential chrpath libssl-dev libxft-dev`
- `sudo apt-get install libfreetype6 libfreetype6-dev`
- `sudo apt-get install libfontconfig1 libfontconfig1-dev`
- `sudo apt-get install phantomjs`
- `phantomjs --webdriver=8643`
- `bin/behat -p phantomjs`

# Install behat settings

```
phantomjs:
  context:
      class:  'FeatureContext'
  extensions:
    Behat\MinkExtension\Extension:
      goutte: ~
      selenium2:
        wd_host: "http://localhost:8643/wd/hub"
```