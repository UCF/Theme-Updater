# Custom Theme Updater

## Installation & Use

How to use it?  Good question.



## Code Comments

The flow of the plugin is:

### 1 - Get the Theme's Update URI

Code is a mashup of Wordpress source.  I'm looking at:
* [`get_themes()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L249)
* [`get_theme_data()`](http://core.trac.wordpress.org/browser/trunk/wp-includes/theme.php?rev=17978#L163)

Unfortunately the Theme URI is not available via default [`get_theme_data()`](http://codex.wordpress.org/Function_Reference/get_theme_data), which is probably for the best because I don't want to conflict with standard [wordpress conventions](http://codex.wordpress.org/Theme_Development#Theme_Stylesheet). 


### 2 - Get teh github tags

Pull the tags trough the [Repository Refs API](http://develop.github.com/p/repo.html).

Similar to how [Wordpress updates themes](http://core.trac.wordpress.org/browser/trunk/wp-includes/update.php?rev=17978#L188).




