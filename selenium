#!/bin/bash

PORT='3000'
SELENIUM_SERVER="selenium-server.jar"
CHROME="chromedriver"

if [ ! -d "./scripts" ]; then
	mkdir ./scripts
fi

cd scripts

if [ ! -f "./$SELENIUM_SERVER" ]; then
	echo -ne "--------------------------------------------------------\n"
	echo -ne "Baixando Selenium Server...\n"
	echo -ne "--------------------------------------------------------\n"
	wget -O ./$SELENIUM_SERVER http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar
fi

if [ ! -f "./$CHROME" ]; then
	echo -ne "--------------------------------------------------------\n"
	echo -ne "Baixando chromedriver...\n"
	echo -ne "--------------------------------------------------------\n"
	wget -O $CHROME.zip http://chromedriver.storage.googleapis.com/2.23/chromedriver_linux64.zip
	unzip $CHROME.zip
	rm $CHROME.zip
fi

cd ../

java -jar ./scripts/$SELENIUM_SERVER -Dwebdriver.chrome.driver=./scripts/$CHROME