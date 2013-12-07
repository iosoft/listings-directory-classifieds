=== Directory & Classifieds ===
Contributors: Mihail Chepovskiy
Donate link: http://www.salephpscripts.com/
Tags: directory, wordpress directory, directory plugin, classifieds, listings directory, listings, ads directory, advertisement, yellow pages, directory theme, classifieds directory, locations directory, address, classified ads, directory plugin, business directory plugin, classifieds plugin, church directory, member directory, members directory, city portal, city portal plugin, events directory, cars directory, bikes directory, boats directory, vehicles dealers directory, pets directory, real estate portal, google maps
Requires at least: 3.6.0
Tested up to: 3.7.1
Last Updated: 2013-Nov-15
Stable tag: tags/1.1.5
License: GPLv2 or later

Build Directory or Classifieds site in some minutes. The plugin combines flexibility of WordPress and functionality of Directory and Classifieds

== Description ==

The plugin provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal.
In other words - whatever you want.

Look at our [demo](http://www.salephpscripts.com/wordpress_directory/demo/)

= Features of the plugin =
* Fully customizable and easy in configuration
* Restrict ads by listings levels
* Sticky and featured listings options
* Ability to raise up directory listings
* Ability to renew expired listings manually
* Customizable content fields of different types
* Icons for custom content fields
* Category-based content fields
* Order directory listings by content fields
* Powerful search by content fields (in premium module)
* Search by categories and locations
* SEO friendly - fully compatible with Yoast SEO plugin
* Locations search in radius - results displaying on map (in premium module)
* Set up any number of locations for one listing (in premium module)
* Google Maps integrated
* Custom map markers (in premium module)
* YouTube videos attachments for listings
* Images AJAX uploading
* 2 types of images gallery on listings pages
* Contact listing owner form
* Favourites list functionality
* 'Print listing' option
* 'Get listing in PDF' option
* Adapted for reCaptcha

= Plugin conception =
Levels of listings control the functionality amount of listings and their directory/classifieds conception.
Each listing may belong to different levels, some may have eternal active period, have sticky status and enabled
google maps, other may have greater number of allowed attached images or videos. It is perfect base for business model of your directory site.

Each content field field belongs to the type that defines its behaviour and display. You may hide field name, select custom field icon,
set field as required, manage visibility on pages. Also listings may be ordered by some fields.
Note that you may assign fields for specific categories. This is important feature allows to build category-specific content fields.
For instance: there may be special *'price'* field especially in *'Classifieds/For sale'* category and all its subcategories, so this field will appear
only in listings, those were assigned with this category.

= Premium modules =
Right now we have 3 additional premium modules:

**Frontend submission** - allow users to submit new directory/classified ads at the frontend side of your site. Ability to select one of available login modes:

1. user must be logged in before submission
2. new user will be created during submission (contact info required)
3. new user will be created during submission (contact info doesn't required)
4. create listings by anonymous users

**Enhanced search** - allows to search by categories and content fields, also additional feature for locations search in radius (in miles or kilometers)

**Enhanced locations** - allows to set up any number of locations for one listing, also users may select map markers icons for their locations on Google map


== Installation ==

1. Upload files to the `/wp-content/plugins/w2dc/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create new page with [webdirectory] shortcode

== Screenshots ==

1. search blocks

2. Google maps and map markers

3. Different types of content fields

4. Management of listing Images and Videos

5. Listing at frontend

6. Listing at frontend

== Changelog ==

= Version 1.1.5 =
* new settings was added 'Default map zoom'
* core content fields bug was fixed
* creation of new listing with empty title now renders error message and saves draft instead of unknown action

= Version 1.1.4 =
* favourites list functionality was implemented: Put in/Out button on listings pages and 'My favourites list' special page
* new 'Print listing' option
* new 'Get listing in PDF' option
* 'Edit listing' button was placed on listing page, visible only for users, those can edit current listing

= Version 1.1.3 =
* javascript code for dependencies of content fields from selected categories was improved
* the bug that causes problems when some of content fields change its types was fixed
* special condition for edit listing link was added in 'listing_single.tpl.php' template

= Version 1.1.2 =
* 2 new settings were added: ability to hide contact form option, ability to disable rendering of listings on directory home page
* Yoast SEO plugin compatibility bug was fixed
* recaptcha bug on contact form was fixed
* checkboxes content field bug when all checkboxes unchecked was fixed
* the plugin fully adapted for customizations in css and template files
* the plugin fully adapted for new 'Frontend submission' premium module

= Version 1.1.1 =
* locations metabox bug was fixed

= Version 1.1.0 =
* the structure of plugin was redesigned to be compatible with most of wordpress themes
* compatibility with Yoast SEO plugin was added
* 2 unnecessary settings were removed

= Version 1.0.7 =
* new setting was added to manage width of HTML content part of all directory pages

= Version 1.0.6 =
* listings title layout bug fixed - esc_attr() added
* 2 new settings for search panel added

= Version 1.0.5 =
* default installation content fields added

= Version 1.0.4 =
* added support of SSL for https sites when YouTube videos attached

= Version 1.0.3 =
* added support of SSL for https sites
* fixed bug with locations number during new levels creation

= Version 1.0.2 =
* default installation locations terms added

= Version 1.0.1 =
* fixed bug that appears during new content fields creation