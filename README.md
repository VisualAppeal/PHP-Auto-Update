[![PHPUnit](https://github.com/VisualAppeal/PHP-Auto-Update/actions/workflows/phpunit.yml/badge.svg)](https://github.com/VisualAppeal/PHP-Auto-Update/actions/workflows/phpunit.yml)

With this library your users can automatically update their instance of your application to the newest version. I created it as a proof of concept and don't know if it is used somewhere. So please use this library with caution because it can potentially make your users software nonfunctional if something goes wrong.

## Installation

* Install the library via composer [visualappeal/php-auto-update](https://packagist.org/packages/visualappeal/php-auto-update)
* Create an update file/method in your application with your update routine (see `example/client/update/index.php`)
* Create a `update.json` or `update.ini` on your server (where the client should get the updates, see `example/server/update.json` or `example/server/update.ini`)

**Important: Please notice that PHP needs write permissions to update the files on the webserver**

## Example

You can start an example docker container via `docker-compose up` and see the example by visiting `http://127.0.0.1:8080/example/client/`

## Client

### Caching

The library supports the `desarrolla2/cache` component, and you should use it! Otherwise, the client will download the update ini/json file on every request.

## Server

Your server needs at least one file which will be downloaded from the client to check for updates. This can be a json or an ini file. See `example/server/` for examples. The ini section key respectively the json key is the version. This library uses semantic versioning to compare the versions. See [semver.org](http://semver.org/) for details. The ini/json value is the absolute url to the update zip file. Since the library supports incremental updates, the zip file only need to contain the changes since the last version. The zip files do not need to be placed on the same server, they can be uploaded to S3 or another cloud storage, too.

## Documentation

For the documentation see the comments in `src/AutoUpdate.php` or the example in the `example` directory.
