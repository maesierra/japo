#Japo

Japo (pronounced hapo) it's a project to produce Japanese Language learning tools to non English speakers.

There are plenty of resources for English speakers available on the Internet but it's difficult to find anything from any other languages to Japanese, and it's quite frustrating to non native English speakers to require to a double translation or spend quite a lot of money in physical dictionaries.
 
Japo allows to compile a dictionary, glossary and kanji catalog and make it available to other students. 

The main features are:

- Japanese input (kana and kanji) without any IME installed (only Japanese fonts are required).
- Fast search as you type.
- Dictionary with grammar information, custom notes and custom categories.
- Kanji tool to show kanji information (strokes, readings, related words) supporting different kanji catalogs.
- Kanji test generation.

Japo is baesd on the tools developed by [maesierra](https://github.com/maesierra) while she was learning Japanese.

The current beta contains only the Kanji Catalog plus an Spanish Database.
Next steps will be:

- Dictionary.
- Kanji Test generation.
- Custom word list creation.
- Central database repository and automatic updates.
- Kanji catalog importer.


  
##Requirements

- PHP >= 5.6
- MySQL>=  5.6
- npm (only for building)
- Webserver supporting php (tested only in Apache but nginx should work too)
- An Auth0 account to handle users (optional)

##Installation
###Inital Config
* Unzip or clone source files to a non public folder on the server
* Create a mysql database
* Create a copy of src/maesierra/Japo/.env.example as src/maesierra/Japo/.env
  and set up the configuration
  <Need an example config>
###Building
  <Need a install script>
  
* run bin/create-db --no-lang (use --lang es to install the Spanish database)
* run php composer.phar --self-update    
* run php composer.phar install
*  

