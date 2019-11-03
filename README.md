# Crossfitbox One.Fit WOD generator
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bruno-farias/wod/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bruno-farias/wod/?branch=master)

## Subject
Generate a workout-of-the-day program for the participants

## Stack used
- PHP 7.3
- Composer for package management and autoload;
  -  libraries:
    - phplucidframe/console-table -> Format output in terminal
    - phpunit/phpunit -> run tests
    - fzaninotto/faker -> fake data generator to use on tests
- Instead of use a database, was chosen to use CSV files since they are flexible
enough for this task without add extra complexity.

## Installation
- Just install using git on command line: git clone git@github.com:bruno-farias/wod.git
- Please make sure that you are running php 7+
- Inside project folder run: `composer install`

## Usage
This project was made to run on terminal only.
- Inside project folder run: `php run.php`
- Result will be displayed in table format on terminal.

## Tests
- For running tests: `./vendor/bin/phpunit tests`
