## Technical Requirements
- Install PHP 7.2.5 or higher and these PHP extensions (which are installed and enabled by default in most PHP 7 installations): Ctype, iconv, JSON, PCRE, Session, SimpleXML, and Tokenizer;
- Install Composer, which is used to install PHP packages.
- Install Node.js and NPM
- Relation database (i use MariaDB)

## Step-by-step guide
- run `composer install` for installation php vendor
- edit .env file for database url ([reference](https://symfony.com/doc/5.4/doctrine.html#configuring-the-database))
- run `php bin/console doctrine:database:create` for create database
- run `php bin/console doctrine:migrations:migrate` for create database tables ([reference](https://symfony.com/doc/5.4/doctrine.html#migrations-creating-the-database-tables-schema))
- run `php bin/console doctrine:fixtures:load` for create dummy data ([reference](https://symfony.com/doc/5.4/testing.html#load-dummy-data-fixtures))
- run `npm install` for frontend dependency
- run `npm run dev` for frontend build
- run `php -S localhost:8000 -t ./public/` for server
- for admin login name: `admin_1@test.sk` and pass: `password`
- for user login name: `client_1@test.sk` and pass: `password`