# Adamant

This project is based on Laravel and Lumen. It aim to make a base code to start new project with an available API faster by having some useful part of code already done.

## Installation

While you have to install each project in order to fully experience Adamant, each part mat be use independently from the other. So you may use only part of the project as needed.

### API

1- Clone or download Adamant-API  
2- Copy the ".env.example" file and rename the copy ".env"  
3- Edit your new ".env" file to add your database credential.  
4- Run ```composer install```  
5- Run ```php artisan migrate```  
6- Run ```php artisan passport:install --force```  

Note the generated keys for further use.  


### Front-End

1- Clone or download Adamant-FrontEnd  
2- If youre connecting to an API, edit "config/api.php" file to add your API credential.  
3- Run ```composer install```  

