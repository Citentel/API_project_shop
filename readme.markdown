# API Shop Project

## Project requirements 

1. GIT
1. Mysql 8.x
1. Composer 2.x
1. PHP 7.4.x or 8.0.x

## First steps
```
git clone https://github.com/Citentel/API_project_shop.git
```
```
composer update
composer install
```
Generate JWT keys. The password is in the .env 
```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
```
php bin/console d:d:c
php bin/console d:s:u --force
php bin/console e:c:f
```
Sometimes you need to set permissions for the var and jwt folders. **Remember to be in the parent folder!**
```
chmod -R 777 /var
chmod -R 777 /jwt
```
Now you can try to run the server 
```
php bin/console server:run
or
symfony server:start
```
If the server is working correctly go to url: 127.0.0.1:8000/documentation. There you will find a detailed documentation for the api.

# Author 

### Robert Gontarski
[My portfolio: gontarsky.com](https://gontarsky.com)
