<<<<<<< HEAD
=== Theme Updater ===
Contributors: blurback
Tags: github, theme, upgrader, updater
Requires at least: 3.1.2
Tested up to: 3.1.3
Stable tag: 1.3.3
=======
# Wordpress plugin: a theme updater for GitHub-hosted Wordpress themes

Do you wish that you could somehow get update notifications within WordPress for _custom_ themes that you use for your site? Perhaps a custom theme that you had developed specifically for your site? Or a theme you developed for a client site?  And do you wish you could do "automatic updates" to those custom themes just like you can for _public_ themes available from WordPress.org?

This WordPress plugin lets you host a custom theme in a Github _public_ repository (private repos are not supported) and then notify sites when a new version of the theme is available.  Those sites can then perform an auto-update just as with publicly available themes.  The plugin also allows you to roll back to a previous version of the theme.

This plugin works with WordPress in both a standalone and MultiSite mode and has been tested up to WordPress 3.3.x.  It can be found in the WordPress plugin directory at:

* http://wordpress.org/extend/plugins/theme-updater/

Bugs, feature requests or other suggestions should be filed as [issues at the plugin's Github repository](https://github.com/UCF/Theme-Updater/issues)
>>>>>>> master

A theme updater for GitHub hosted WordPress themes

== Description ==

A theme updater for GitHub hosted WordPress themes.  This plugin automatically checks GitHub for new project tags and enables automatic install.

== Installation ==

#### For the Impatient

1. Install and activate the plugin.
1. [Here](https://github.com/UCF/Theme-Updater-Demo) is a sample theme.  [Download (.zip)](https://github.com/UCF/Theme-Updater-Demo/zipball/v1.1.0)

<<<<<<< HEAD
#### Theme Prep

##### 1- Publish Theme to a public GitHub Repository  
=======
---

## Installation
>>>>>>> master

Note: lacks support for private repositories.

<<<<<<< HEAD
##### 2- Update Your Theme's `style.css`
=======
### 1 - Publish your theme to a public GitHub Repository

### 2 - Update Your theme's `style.css`
>>>>>>> master

Add `Github Theme URI` to your `style.css` header, this will be where the plugin looks for updates.  I also recommend using [semantic versioning](http://semver.org/) for the version number. (Note that the version number does _not_ need to start with "v" as shown in the examples below. You can simply use a number such as "1.2.0". You just need to be consistent with how you create version numbers.)

Example header:

    Theme Name: Example  
    Theme URI: http://example.com/  
    Github Theme URI: https://github.com/username/repo
    Description: My Example Theme
    Author: person
    Version: v1.0.0

Push these changes back to the project.

<<<<<<< HEAD
#####  3- Create a new tag
=======
### 3 - Create a new tag and push the change back to the repo
>>>>>>> master

    $ git tag v1.0.0
    $ git push origin v1.0.0

<<<<<<< HEAD
Note: your tag numbers and theme version numbers should be in accord.

##### 4- Download and install the plugin

The next time you push a new tag, it will be recognized by the plugin and you will be notified in the wp-admin.


== Screenshots ==

1. A new update is available.
2. Clicked "Update automatically".
3. Everything is up-to-date.

== Changelog ==

= v1.3.2 =
* Stable, import from git project

== Upgrade Notice ==

= v1.3.2 =
Because git > svn
=======
Note, your tag numbers and theme version numbers should match.  If you want to increment the version number, be sure to update and commit your `style.css` prior to creating the new git tag.

### 4 - Upload your modified theme to your WordPress site

Before the plugin can work, your theme with the `Github Theme URI` addition needs to be uploaded to our WordPress site. 

* Create a ZIP file of your theme on your local computer.
* Inside your WordPress admin menu (standalone) or network admin menu (MultiSite) go to the Install Themes panel and click on "Upload".
* Choose your ZIP file and press "Install Now".

Your theme will now be installed inside of WordPress and can be activated for your site.  From this point forward all updates will be installed automatically once the plugin is activated.

### 5 - Download and install the plugin

Inside your WordPress admin menu (standalone) or network admin menu (MultiSite) choose "Add New" under the Plugins menu.  Search for "Theme Updater" and this will bring you to [the plugin's WordPress page](http://wordpress.org/extend/plugins/theme-updater/) where you can install the plugin directly into your WordPress installation. (Alternatively you can visit that page, download the plugin as a zip file and upload it to your WordPress install, but why go through all that work?)

With the plugin installed and activated on your site and the theme uploaded, the next time you push a new tag to your Github repository, it will be recognized by the plugin and an update notice will appear in your admin menu.

---

## Updating The Theme

The process of updating your theme and generating auto-update notifications is now simply this:

### 1 - Make your changes to the theme and commit those changes to your local git repository.

### 2 - **IMPORTANT** - Update your `style.css` file with a new version number and commit that change to your local repository.

### 3 - Push your changes to the Github repository

    $ git push origin master

### 4 - Create a new tag and push the change back to the repo

    $ git tag v1.1.0
    $ git push origin v1.1.0

Note, you should use the **identical** number for the tag that you did for a version number in `style.css` in step #2. 

That's it. Now any sites with your theme installed will receive an update notification the next time their WordPress installation checks for updates.

---
>>>>>>> master

== Code Comments ==

The flow of the plugin is:

##### Get the Theme's Update URI

Code is a mashup of Wordpress source.  I'm looking at:

* [`get_themes()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L249)  
* [`get_theme_data()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L163)


##### Get the github tags

Pull the tags trough the [Repository Refs API](http://develop.github.com/p/repo.html).

##### Notify Worpress of the Update

Publish the update details to the `response` array in the `update_themes` transient, similar to how [Wordpress updates themes](http://core.trac.wordpress.org/browser/trunk/wp-includes/update.php?rev=17978#L188).

## Changelog

### v1.3.4 - February 8, 2012
* Fix to [SSL issue](https://github.com/UCF/Theme-Updater/issues/3). Code by Github user bainternet. Added by Github user danyork.
