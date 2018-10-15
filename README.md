Japo
====
Japo (pronounced hapo) it's a project to produce Japanese Language learning tools to non English speakers.

There are plenty of resources for English speakers available on the Internet but it's difficult to find anything from any other languages to Japanese, and it's quite frustrating to non native English speakers to require to a double translation or spend quite a lot of money in physical dictionaries.
 
Japo allows to compile a dictionary, glossary and kanji catalog and make it available to other students. 

The main features are:

- Japanese input (kana and kanji) without any IME installed (only Japanese fonts are required).
- Fast search as you type.
- Dictionary with grammar information, custom notes and custom categories.
- Kanji tool to show kanji information (strokes, readings, related words) supporting different kanji catalogs.
- Kanji test generation.

Japo is based on the tools developed by [maesierra](https://github.com/maesierra) while she was learning Japanese.

The current beta contains only the Kanji Catalog plus an Spanish Database.
Next steps will be:

- Dictionary.
- Kanji Test generation.
- Custom word list creation.
- Central database repository and automatic updates.
- Kanji catalog importer.


  
Requirements
============

- PHP >= 5.6
- PHP Extensions: mb_string, pdo, pdo_mysql, apcu
- MySQL>=  5.6
- npm (only for building)
- Apache 2 
- An Auth0 account to handle users (optional)

Installation
============
Initial Config
1. Create a mysql database
2. Create a copy of src/maesierra/Japo/.env.example as src/maesierra/Japo/.env
  and set up the configuration 
    
  Alternatively, set up those environment properties can be set up in the server's environment instead of using the .env file.
Configuration
* **SERVER_PATH** *URL Path for the backend*
* **HOME_PATH**  *URL Path for the front-end*
* **TEMP_DIR** *Temp dir. Default: system's temp dir*
* **LOG_FOLDER** *Path for the logs*
* **LOG_LEVEL** *Log level (DEBUG, INFO, WARN, ERROR)*
* **MYSQL_HOST** *default localhost*
* **MYSQL_HOST** *default 3306*
* **MYSQL_USER** *default japo*
* **MYSQL_PASSWORD**  
* **DATABASE_NAME** *default japo*

Building
========
To build the application:
   ```
   php composer.phar self-update
   php composer.phar install
   ```
Composer install will:
1. Download and install dependencies
2. ``build-front-end`` Build frontend *Note: npm must be installed* 
3. ``build-webroot`` Adjust .htaccess paths and create webroot folder links
4. ``run db-migration`` runs the db migration

``build-front-end``,  ``build-webroot`` and ``run db-migration`` can be skipped with the ``--no-scripts`` option.

To run just some of those commands use the following syntax:
  
```
php composer.phar run-script <command name>
```

DB Migration
============

On the bin folder an script is provided to run all the db migrations that will create the db
 tables and insert the current dictionary and catalogs. Only data for the configured language will be applied.
 *Note: .env or environment properties must be set up prior running the migration script*
 
   ```
   bin/run-db-migration
   ```

Server set up
=============

Only the following files/directories need to be exposed from the webserver:
```
api/
asset-manifest.json
favicon.ico
.htaccess
index.html
manifest.json
service-worker.js
static/
```
webroot folder contains symlinks to those files and can be safely exposed. Server path must match 
**SERVER_PATH** property value.

Auth0
=====

Auth0 can be used to provide user login and management to the application. By default the application 
doesn't handle users. Auth0 free tier should be enough for most scenarios. 

Steps to set up auth0
1. Create an auth0 account on [https://auth0.com/](https://auth0.com/)
2. Create a regular web application
3. Add https://[yourdomain]/[server_path]/api/auth as **Allowed Callback URLs**
4. Add https://[yourdomain] as **Allowed Web Origins**
5. Add https://[yourdomain]/[server_path] as **Allowed Logout URLs**
6. Add the following rule under Rules (name it's not important)
```javascript
function (user, context, callback) {
   var namespace = 'https://github.com/maesierra/japo/';
   if (context.idToken && user.user_metadata) {
     context.idToken[namespace + 'user_metadata'] = user.user_metadata;
   }
   if (context.idToken && user.app_metadata) {
     context.idToken[namespace + 'app_metadata'] = user.app_metadata;
   }
   callback(null, user, context);
 }
```
7. Set up the following properties on .env:
* **AUTH_MANAGER** = maesierra\Japo\Auth\NoLoginAuthManager 
* **AUTH0_DOMAIN** = mydomain.auth0.com *Copy it from application settings page*
* **AUTH0_CLIENT_ID** = Client ID  *Copy it from application settings page*
* **AUTH0_CLIENT_SECRET** = Client Secret  *Copy it from application settings page*

Login page options can be set under host pages section. Please follow auth0 configuration on 
how to set it up, change the language or the look and feel.  

To give an user admin or editor role (required to allow edit kanji) on the users's section, edit the user and add the following ``app_metadata``
```json
{
  "role": "admin"
}
```

To make auth0 hosted login page match the application language it's required to change the language passed to ``Auth0Lock`` constructor:
```
language: config.extraParams.custom_lang
```

Vagrant
=======

A Vagrant file is provided to create a local enviroment for testing. The provision script is 
``vagrant/bootstrap.sh`` and can also be used as a guide on how to install all the requirements.
  
To run the local environment:
```
vagrant up
```
Japo will be available on [https://localhost:8043/]()
 
