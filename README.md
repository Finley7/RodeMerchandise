
# RodeMerchandise
Merchandise ordering app for official members of the Belgian Red Devil football club.

## Setup
The setup follows basic Symfony guidelines. Clone the repository and run `composer install` to setup and install the application. 

1. Setup the database connection in the `.env` file.
2. Run the migrations using `php bin/console do:mi:mi`
3. Insert the products from `products.sql` (included in the root of the repository).
4. Insert the members into the database using `php bin/console ImportMemberCsvCommand /path/to/file.csv` (club member CSV is included in the root of the repository).
5. Configure a local apache server like MAMP, XAMP or run the Symphony Web Server.
6. Open your browser and follow the applications instructions.

That's it. Any questions can be sent to [finley.siebert@outlook.com](mailto:finley.siebert@outlook.com)
