Test conditions: 2.30 hours to complete

Please see PDF for tasks to be run

Preferred delivery:
Please invite Jack Wall, Backend Team Lead to have read access to a private github repo (username: j4k). 

Alternatively, if you are not able to share via github please send us a zip with all your project code and dependencies, with documentation of how to run your code and tests. This is not ideal so should only be used if there is a blocker to inviting via github. 

Useful information
Environment for the test: Stateless Laravel application that will only require phpunit to run the tests in a 7.4+ php environment with sqlite

Can be run with docker from the project root if you don’t have an applicable local env (bash/zsh)

Copy the .env.example to .env in the project directory

cp .env.example .env

Composer install:

docker run --rm --interactive --tty -v $(pwd):/app composer install

Run tests:

docker container run --rm -v $(pwd):/app/ php:7.4-cli php /app/vendor/bin/phpunit --configuration /app/phpunit.xml


