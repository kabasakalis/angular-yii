# Angular Yii - Gallery Manager.
By [Spiros Kabasakalis](http://iws.kabasakalis.gr/)

## Overview
 A Gallery Manager demo application with [Yii](http://www.yiiframework.com/) REST backend and [AngularJS](http://angularjs.org/) frontend.
 The application is more involved than the typical introductory todo demo used in js frameworks.
 It is recommended that you have a basic knowledge of [AngularJS](http://angularjs.org/),[Grunt](http://gruntjs.com/),and [require.js](http://requirejs.org/) before you dive into the source code.

## [Live Demo](http://kabasakalis.tk/ng-yii)

## Features
* Upload pictures with name and description and choose collections to which they belong.
* Create collections and choose their pictures.
* Pictures and collections share a MANY_MANY relationship.
* Create,Update and Delete images and collections.
* View a gallery of the images,filtered by selected collection.

For best experience,use Chrome.Other browsers may complain here and there.Sorry,no patience to make happy every freaking browser out there!

## Setup
Download and copy the project files to some public folder in your development environment.
Create an empty `assets` folder in project root.
### Backend
* **Create a new database** and import the image,collection,collection-image tables found in `protected/data/angular-yii.sql` file.
Optionally you can import the `user_table.sql` file to have a register/login functionality in your yii backend.
* **Define your local development domain** in `/index.php` 

```php 
   defined('LOCAL_DOMAIN') or define('LOCAL_DOMAIN','[localhost or Virtual Host]');
``` 
This will make your `config.php` functional both in local development and production environment.
* In`/index.php` configure your local and/or production **framework paths**.
* In`config/main.php` configure your **database info**.Point to the database you created earlier.
* In`config/main.php`,in params array fill in the **RESTusername** and **RESTpassword** fields.They are used for basic REST authentication.
**Note**:These credentials are sent from the client with custom headers,so your server must allow this.
* Create an `uploads\images\thumbnails' folder structure in project webroot.You can change the names in `ng-yii\src\scripts\config\constants.js`,and `protected\models\Image.php.`

### Frontend
You will need Command line tools so it would be ideal if you could use these in your IDE.
For example in PHPStorm you can configure  Command line tools in Settings/IDE Settings/Command Line Tool Support.

* Install [node.js](http://nodejs.org/) (at least v0.8.1) with npm (Node Package Manager).
* Install  [Grunt](http://gruntjs.com/) node package globally.In your command line console: `npm install -g grunt-cli`.
* Install project **dependencies** (node packages):go to `/ng-yii` directory.In your command line console: `npm install`.
* Open `ng-yii/src/scripts/config/constants.js` and **configure the constants**.Fill in  `X_REST_USERNAME`,`X_REST_PASSWORD`
with same values as those in `config/main.php`.Note that it's not mandatory to have the frontend base folder `ng-yii` inside the backend base
 folder.You can move it to a public location and just configure the `YII_APP_BASE_URL` accordingly,if you are using a relative url.
 You must serve the frontend from the same domain where the backend resides,or else you may run into security issues.
 In case you have to use a different domain include in your `.htaccess file`  `Access-Control-Allow-Origin` 
  and set the domains that you want to allow.
* In your `.htaccess` file:(already included)

```php
<IfModule mod_headers.c>
#Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization,
X_REST_USERNAME,X_REST_PASSWORD,X-Requested-With"
Header always set Access-Control-Allow-Methods "POST, GET,PUT,DELETE, OPTIONS"
  </IfModule>
```
## Compiling
The frontend source files reside in `/ng-yii/src` folder.These are the files that you edit in your IDE.There are three main grunt-tasks configured in `Gruntfile.js` that
 compile the src folder:
* `grunt default`(or just`grunt`) will compile any html templates in your src folder and and then copy all files to a `dev` folder inside ng-yii.(using a temporary `.temp`
  folder as an intermediary step)`.dev` and `.temp` are cleaned up every time this grunt task runs.In development you will run your app
  in `dev` folder.However it is recommended that you exclude `dev` and `.temp`  folders  from your IDE project directory structure,
  so that they don't appear in your IDE.You don't want to edit files by mistake in there only to find out that they have been deleted when you ran the task.

* `grunt dev` is essentially the same as `grunt default` with the extra addition that it will watch for `src` file changes and re-compile on the fly.
* `grunt prod` is the powerhouse task that really shows the power of grunt as an optimization/automation/workflow tool.This task will squash
 the whole scripts folder structure and all js plugins into a single javascript  file and then minify it.It will do the same for all the css files.
  It will optimize  any images found in img folder.It will bake all the partial html files in `views` folder into a single `views.js` file that will be used as cache during runtime,
 thus avoiding http requests to these partials.It will even minify `index.html` file.All the compiled files will be moved to a `ng-yii/prod folder`,ready for deployment.
 For reasons already explained,make sure you don't edit anything in `prod` (unless you debug of course) because the folder is cleaned up every time the task is ran.


## Running the app.
Provided you use a LAMP stack in your localhost,just point your browser to dev or prod folder,after you have compiled the source.
Optionally you can start a local node.js server with  `grunt server` in command line and then point your browser to `http://localhost:3005/` .
The compiled app in `dev` folder will run.If you use a non-localhost virtual host domain for your yii backend
you must take care of the cross domain security issue already mentioned above.

## Tests.
The [Jasmine](http://pivotal.github.io/jasmine/) framework is used for testing.There are 8 simple demo unit tests in two files.( in `ng-yii/test/scripts/services`).
Just point your browser to  `/ng-yii/test/runner.html` to run the tests.

## [Karma](http://karma-runner.github.io/0.8/index.html) (ex-Testacular):Test Runner.
Karma is a test runner which works with any js testing framework.
In a nutshell,the usefulness of this command line tool is that it can run tests in the browser automatically every time you edit your source code,
so that you always know on the spot if your new additions break something.
To start the Karma server,in command line `grunt karma:unit`.After that,a new command `grunt dev`.Any changes in your `src`
folder will trigger Karma Test Runner and recompile the `dev` folder.

Cheers!

Spiros 'drumaddict' Kabasakalis

## Acknowledgement
[AngularFun](https://github.com/CaryLandholt/AngularFun) by [Cary Landholt](https://github.com/CaryLandholt).

## Resources
* [AngularJS](http://angularjs.org/)
* [John Lindquist's Egghead.io](http://www.youtube.com/playlist?list=PLP6DbQBkn9ymGQh2qpk9ImLHdSH5T7yw7)
* [AngularStrap](http://mgcrea.github.io/angular-strap/)
* [AngularUI](http://angular-ui.github.io/)
* [AngularUI States](https://github.com/angular-ui/ui-router/wiki)
* [Animation in AngularJS](http://www.yearofmoo.com/2013/04/animation-in-angularjs.html)
* [ng-grid](http://angular-ui.github.io/ng-grid/)
* [Restangular,modules for AngularJS](http://ngmodules.org/modules/restangular)
* [AngularFun](https://github.com/CaryLandholt/AngularFun)
* [node.js](http://nodejs.org/)
* [RequireJS](http://requirejs.org/)
* [Grunt](http://gruntjs.com/)
* [Jasmine](http://pivotal.github.io/jasmine/)
* [Karma](http://karma-runner.github.io/0.8/index.html)
* [RESTFullYii](http://evan108108.github.io/RESTFullYii/)
* [ActiveRecord Relation Behavior](https://github.com/yiiext/activerecord-relation-behavior)
* [ephpthumb](https://github.com/Haensel/EPhpThumb)
* [PHPThumb](https://github.com/masterexploder/PHPThumb/wiki/Basic-Usage)
* [FancyBox2](http://fancyapps.com/fancybox/)
* [Noty](http://needim.github.io/noty/)
* [spin.js](http://fgnass.github.io/spin.js/)
* [Select2](http://ivaynberg.github.io/select2/)


