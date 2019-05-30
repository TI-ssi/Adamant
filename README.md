# Adamant

This project is based on Laravel and Lumen. It aim to make a base code to start new project with an available API faster by having some useful part of code already done.

## Installation

Clone the api project and the frontend one.

On the api one, setup the database connection setting in the .env file and run the migration.
On the frontend one, setup the config/api.php file to link to the url of the api one with the required OAuth2 credential.

Don't forget to run composer install on each used project.

You should now have a helloworld! page with minimal functionnality.

p.s. you may use only one of the project if you intend to only make an api or only consume one.