
# Installation

Firstly, clone the repo by doing => `git clone https://github.com/nemanja-ivanis/shorten-url.git`

After cloning, run:

install composer packages
`composer install`

install npm packages `npm install`

build javascript(vue) files `npm run build`

Environment variables need to be added, change the file `.env.example` to `.env`. The default values are set, and dont need to be changed(but if you want you can use some other database). For ease of use i have set the database as sqlite.

Run `php artisan key:generate` to generate a new encryption key for laravel. If you get any errors regarding this, please clear the cache `php artisan config:clear` `php artisan config:cache`.

And lastly, run:

`php artisan migrate --seed`

This will migrate all tables, and seed 1 test user and 10 random urls(you can register a new user too). If it asks to create new sqlite file, create it. 

Test user credentials:

username => test@example.com

password => test1234


After all this, to run the web app => `php artisan serve`. It will build up a local server and give you the url you should use to access it.

I have prepared a couple of Unit tests which you can run `php artisan test`. If you do tests, and want to use the default credentials please run seed command again.

If you want to test the API endpoints with postman, after you login to the web app with the credentials you can look up in the requests network and check the request headers, authorization parameter and get the bearer token there and then you can use it in postman.
