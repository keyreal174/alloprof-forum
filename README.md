_Vanilla uses Composer and NPM!
You cannot clone this repo right into a web directory - it requires a build step. [Learn more](https://open.vanillaforums.com/discussion/36708/the-git-version-of-vanilla-no-longer-ships-pre-built-fronted-assets/p1?new=1) or just [download the latest stable build](https://open.vanillaforums.com/addon/vanilla-core) instead_.

![Vanilla](http://images.v-cdn.net/vanilla-black-logo-400.svg)

## Howdy, Stranger!

Vanilla was born out of the desire to create flexible, customizable, and downright entertaining
community solutions. Vanilla has been used to power tens of thousands of community forums around the world
and we couldn't be happier if you've decided to use Vanilla to grow yours.

Every community is unique. Vanilla is a finely-crafted platform on which designers and developers
can build a custom-tailored environment that meets your community's particular needs.

## 5 reasons Vanilla is the sweetest forum

1. We've reimagined traditional forums for mass-appeal.
1. Our theming flexibility is second-to-none.
1. Impossibly good integration options with single sign-ons and embedding.
1. The best tools available for community management.
1. Curated features with great plugin options, not the kitchen sink.

## Open Source

Vanilla is free, open source software distributed under the GNU GPL2.
We accept and encourage contributions from our community and sometimes give hugs in return.
You can join us on the [Vanilla Community Forums](https://open.vanillaforums.com/discussions) to be part of that discussion.

The latest stable release is always [listed here](https://open.vanillaforums.com/addon/vanilla-core). Currently, it is the `release/2.6` branch.

New plugins and themes can be listed in the [Official Addon Directory](https://open.vanillaforums.com/addon/).
We encourage addon developers to release their code under the GPL as well, but do not require it.

## Cloud Solution

Vanilla Forums provides an official cloud hosting solution at [vanillaforums.com](https://vanillaforums.com)
with a 1-click install, automatic upgrades, amazing professional support, incredible scalability,
integration assistance, theming and migration services, and exclusive features. For the very best Vanilla forum experience,
you can skip the rest of this technical stuff and [go there directly](https://vanillaforums.com/plans).

If you professionally run a large community or enterprise forum, our cloud solution will make the best technical and economic sense by far.

## Self-Hosting Basics

If you're new to self-hosting, read this section. Otherwise, skip to the [requirements](https://github.com/vanilla/vanilla/blob/master/README.md#self-hosting-requirements) and [installation](https://github.com/vanilla/vanilla/blob/master/README.md#installation).

Vanilla requires a server with PHP, MySQL, and web server software (like Apache or nginx). You'll probably need to own a domain, and already have it configured on your server with DNS. You usually need to create a database for it via your hosting provider, often via phpMyAdmin. Then you'll need to visit that domain after uploading Vanilla to continue installation. Up to this point, your web host is responsible for all these things. You should consult their docs and contact their support with any questions to getting as far as that.

Say you bought some web space and someone told you how to access it. It might be that you bought a domain like `example.com` or you received only a cryptic sub domain of your provider like `abc123456789.yourwebhoster.com` or you might only have received an IP address, so that you can reach your web space with `1.2.3.4`.

When you enter one of those to reach your web host, your web server shows what is in a folder of your web space. Often, that folder is called `public_html`. Say you own `example.com` and that's what you set up on your server. You have created a subfolder called `forum` (always use lowercase letters!) and uploaded Vanilla to it (see [installation below]((https://github.com/vanilla/vanilla/blob/master/README.md#installation)). Then, you will be able to see the Vanilla installer when you visit `example.com/forum` in your browser.

## Self-Hosting Requirements

We strongly recommend:

*  **PHP 7.2** or higher.
*  MySQL 5.7 or higher (or Percona/MariaDB equivalent).
* SSL encyrption (check out [LetsEncrypt](https://letsencrypt.org/)).

If your server is not running PHP 7.1 or higher, **you should address this soon**. PHP 7.0 has reached end of life and will no longer receive security patches. **Vanilla's support for PHP 7.0 will end soon**.

Our _minimum_ requirements are now:

* PHP 7.0 or newer.
* PHP extensions mbstring (`--enable-mbstring`), cURL (`--with-curl`), and PDO (on by default).
* To [import into Vanilla](#migrating-to-vanilla) you need MySQLi (`--with-mysqli`).
* To use our social plugins you need [OpenSSL](http://php.net/manual/en/openssl.installation.php).
* MySQL 5.0 or newer (or Percona/MariaDB equivalent).
* MySQL strict mode [disabled](https://www.liquidweb.com/kb/how-to-disable-mysql-strict-mode/).

Vanilla ships with a `.htaccess` file required for Apache support. Using nginx or IIS may require additional configuration. We have [docs for nginx](https://docs.vanillaforums.com/developer/backend/server-nginx/).

On the client side, Vanilla should run & look good in just about any modern browser.
Still using IE? How exotic. You'll want IE11 or greater. IE8 *might* work if you squint hard and click gently, but we make no promises.

We've been natively mobile since before it was cool. Vanilla ships with a mobile-optimized theme enabled
by default for all smartphones & tablets. Heck, it even works on the PlayStation Vita.

## Installation

Vanilla is built to be simple, and its installation is no exception.

1. Upload Vanilla's [pre-built version](https://open.vanillaforums.com/addon/vanilla-core) to your server.
1. Using nginx? [See our nginx guide](https://docs.vanillaforums.com/developer/backend/server-nginx/).
1. Confirm the `cache`, `conf`, and `uploads` folders are writable by PHP.
1. Navigate to the folder where you uploaded Vanilla in your web browser.
1. Follow the instructions on screen.

If you run into a problem, see [Getting Help](#getting-help) below.

## Upgrading

Follow these steps to upgrade Vanilla when a new stable release is announced. These instructions assume you're using SFTP to manually copy files to a server.

Please consider using [maintenance mode](#using-maintenance-mode) before running database updates if your database is very large (millions of users or comments).

1. Backup your database, `.htaccess` and `conf/config.php` file somewhere safe.
1. Upload the new release's files so they overwrite the old ones.
1. Delete all files in `/cache` (except `.htaccess` if you use Apache).
1. Follow all version-specific instructions below. It is **critcal** you delete the listed files.
1. Go to `example.com/utility/update` to run any database updates needed. (404? See next paragraph.) If it fails, try it a second time by refreshing the page.

If you run into a problem, see [Getting Help](#getting-help) below.

### From Vanilla 2.5 or earlier:

* Delete `plugins/HtmLawed`. (This is now in core.)
* Delete `plugins/Tagging`. (This is now in core.)

### From Vanilla 2.3 or earlier:

* Delete `/applications/vanilla/controllers/class.settingscontroller.php`.

If your forum still uses URLs including `?p=`, support for this URL structure has ended. Follow these steps to switch to the simpler format:

1. Confirm your server is setup to handle rewrites. On Apache, using the `.htaccess` file provided will accomplish this. Additional setup is required on [nginx](https://docs.vanillaforums.com/developer/backend/server-nginx/) and other platforms. 
2. Test whether it is working by visiting `/discussions` - if you see a discussions list (rather than a 404), it is likely setup correctly. 
3. Open `/conf/config.php` and find the line with `$Configuration['Garden']['RewriteUrls'] = false;` and **delete the entire line**. 

Your site should immediately switch to "pretty" URL paths instead of using the 'p' parameter. If there is a problem, re-add the line to your config and do further troubleshooting.

### From Vanilla 2.1 or earlier:

* Update ALL locales you have installed (in `/locales`).
* Apache users must update their `.htaccess` file.
* Delete `/themes/mobile/views/discussions/helper_functions.php`
* Delete `/applications/dashboard/views/default.master.php`

### From Vanilla 1.0:

Upgrading from 1.0 (any version) requires a full migration (see next section). Themes and plugins are not compatible. Backup your Vanilla 1 data and files completely, then delete them from your server before attempting to install Vanilla 2.

## Migrating to Vanilla

1. Get [Vanilla Porter](https://open.vanillaforums.com/addon/porter-core) and verify it supports your platform.
1. Read the Advanced Uses notes on that page.
1. Upload it to your current server.
1. Navigate to the file in your web browser & run it.
1. Take the file it produces and import it to Vanilla via the Dashboard's "Import" option.

If you run into a problem, see [Getting Help](#getting-help) below.

## Using Maintenance Mode

You can temporarily halt all access to your forum by putting it into maintenance mode. Users currently signed in with owner privileges (the user who created the forum) will still be able to use the site.

To put your site in maintenance mode, add this to `/conf/config.php` and save it:

`$Configuration['Garden']['UpdateMode'] = true;`

To end maintenance mode, delete it and save.

## Getting Help

* [Troubleshooting upgrades & installs](http://docs.vanillaforums.com/developers/troubleshooting/)
* [Official documentation](http://docs.vanillaforums.com)
* [Vanilla community forums](https://open.vanillaforums.com/discussions)
* [Official cloud hosting with professional support & migration services](https://vanillaforums.com/plans)

## Building with Phing

Vanilla includes a buildfile for [Phing](https://www.phing.info/), a build system for PHP, in the build directory. Running the `phing` command from the build directory will create a deploy-ready copy of Vanilla. This process automatically fetches dependencies with Composer, filters out any unnecessary developer files (Git files/directories, .editorconfig, unit tests, etc.) and compresses the result into an archive.

The phing build requires `NodeJS` and `yarn` to build frontend assets for packaging. See [build tool documentation](https://docs.vanillaforums.com/developer/tools/building-frontend/) for installation instructions.

## Version Control Strategy

We've adopted the [git flow branching model](http://nvie.com/posts/a-successful-git-branching-model) in our projects.
The creators of git flow released a [short intro video](http://vimeo.com/16018419) to explain the model.

The `master` branch is production-ready for our cloud product but is not yet vetted for open source release (alternate platforms & configurations).
Reviewed, stable changes land against `master` via pull-request.

Our open source release branches are named by version number, e.g. `release/2.6`.
We begin release branches with a beta (b1) designation and progress them thru release candidate to stable.
All open source releases (included pre-releases) are tagged.

## Reporting Security Issues

Please disclose security issues responsibly by emailing support@vanillaforums.com with a full description.
We'll work on releasing an updated version as quickly as possible.
Please do not email non-security issues; use the [issue tracker](https://github.com/vanilla/vanilla/issues) instead.

## Legal Stuff
Copyright &copy; 2009-2018 Vanilla Forums Inc.

Vanilla Forums is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.
Vanilla Forums is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details. You should have received a copy of the GNU General Public License
along with Vanilla Forums.  If not, see <http://www.gnu.org/licenses/>.
Contact Vanilla Forums Inc. at support [at] vanillaforums [dot] com

## Bonk!

Just kidding, everything's awesome. ![dance](http://images.v-cdn.net/dance.gif)
