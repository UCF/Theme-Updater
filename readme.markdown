# Theme Updater plugin for GitHub hosted Wordpress themes

## Installation & Use

### 1- Publish Theme to a public GitHub Repository

### 2- Update Your Theme's `style.css`

Add `Github Theme URI` to your `style.css` header, this will be where the plugin looks for updates.  I also recommend using [semantic versioning](http://semver.org/) for the version number.

example header:

    Theme Name: Example  
    Theme URI: http://example.com/  
    Github Theme URI: https://github.com/username/repo
    Description: My Example Theme
    Author: person
    Version: v1.0.0

Push these changes back to the project

### 3- Create a new tag

    $ git tag v1.0.0
    $ git push origin v1.0.0

note, your tag numbers and theme version numbers should be in accord.

### 4- Download and install the plugin

The next time you push a new tag, it will be recognized by the plugin and will be notified in the wp-admin.

## Code Comments

The flow of the plugin is:

### 1 - Get the Theme's Update URI

Code is a mashup of Wordpress source.  I'm looking at:

* [`get_themes()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L249)  
* [`get_theme_data()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L163)

Unfortunately `Theme URI` is not available via default [`get_theme_data()`](http://codex.wordpress.org/Function_Reference/get_theme_data), which is probably for the best because I don't want to conflict with standard [wordpress conventions](http://codex.wordpress.org/Theme_Development#Theme_Stylesheet). 


### 2 - Get the github tags

Pull the tags trough the [Repository Refs API](http://develop.github.com/p/repo.html).

### 3 - Notify Worpress of the Update

Publish the update details to the `response` array in the `update_themes` transient, similar to how [Wordpress updates themes](http://core.trac.wordpress.org/browser/trunk/wp-includes/update.php?rev=17978#L188).


