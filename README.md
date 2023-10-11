install steps

1-composer install

This command will read the `composer.json` file in the project and install all the required packages.



2-copy .env.example .env
The `.env` file contains configuration settings for your Laravel project.


3-php artisan key:generate

The application key is used to secure user sessions and other encrypted data.

4-php artisan migrate

This assumes that you have already set up a database connection in your `.env` file.

5-php artisan serve
