<?php defined('_JEXEC') or die(); ?>

=====================================================================
Hotspots 6.0.13 - Released 19-March-2023
=====================================================================
# Fix galleria not loading on some Joomla 4 installations


=====================================================================
Hotspots 6.0.12 - Released 19-March-2023
=====================================================================
# Fix centering by user location (one needs account with api https://ipapi.com/ to use this feature)


=====================================================================
Hotspots 6.0.11 - Released 5-March-2023
=====================================================================
# Fix table structure on some migrated sites from Joomla 3 to Joomla 4
# Fix warnings on PHP 8.1
# Map was not properly centered when using "center by marker bounds"
# Fix CB plugin not saving a hotspot when the user was updating the address on his profile


=====================================================================
Hotspots 6.0.10 - Released 5-Feb-2023
=====================================================================
# Properly fix table structure for old Hotspots installations


=====================================================================
Hotspots 6.0.9 - Released 24-Jan-2023
=====================================================================
# fix crash when accessing the KML screen in backend
# fix CB plugin not installing on Joomla 4


=====================================================================
Hotspots 6.0.8 - Released 15-Jan-2023
=====================================================================
# fix Single Hotspot view was not working on Joomla 4
# fix filters misbehaving when all categories were turned on


=====================================================================
Hotspots 6.0.7 - Released 19-Dec-2022
=====================================================================
# fix crash on PHP 8.0.25
# fix crash when un-publishing a custom field on Joomla 4
+ Add schema PostalAddress to the hotspots address in the menu

=====================================================================
Hotspots 6.0.6 - Released 28-Nov-2022
=====================================================================
# Fix openweather API - it now works on https websites

=====================================================================
Hotspots 6.0.5 - Released 24-Apr-2022
=====================================================================
# Fix failure to save category when category alias collide PHP 8
# Fix tiles not being generated on PHP8 and joomla 4.1

=====================================================================
Hotspots 6.0.4 - Released 22-Mar-2022
=====================================================================
# Fix crash when un-publishing hotspots on Joomla 4.1
# Fix crash when deleting hotspots on Joomla 4.1

=====================================================================
Hotspots 6.0.3 - Released 05-Feb-2022
=====================================================================
# Fix failing KML import on joomla 4.1

=====================================================================
Hotspots 6.0.2 - Released 05-Feb-2022
=====================================================================
# Fix failing KML import

=====================================================================
Hotspots 6.0.1 - Released 21-Dec-2021
=====================================================================
# Fix crash on PHP8 when trying to create a hotspot

=====================================================================
Hotspots 6.0.0 - Released 20-Aug-2021
=====================================================================
+ Joomla4 compatible
~ Updates to Javascript scripts to use jQuery instead of Mootools

=====================================================================
Hotspots 5.4.14 - Released 22-May-2020
=====================================================================
# Image upload on iOS > 13.4 was rotating it in the wrong direction

=====================================================================
Hotspots 5.4.13 - Released 11-May-2020
=====================================================================
# Submit form contained mootools code that was breaking when no mootools was loaded on the page

=====================================================================
Hotspots 5.4.12 - Released 10-Apr-2020
=====================================================================
~ improve address search usability - "clear search" is displayed whenever something is entered in the form
~ improve address search usability - entered address is treated as destination and not origin anymore.
# no feedback was given to a user when a direction search was failing ( e.g. due to a typo in the address)

=====================================================================
Hotspots 5.4.11 - Released 29-March-2020
=====================================================================
# map on dashboard in backend was not loading properly
+ added option to set the height of the map on a small device
+ added option to unpublish a hotspot from the frontend (edit state & edit own permissions required)

=====================================================================
Hotspots 5.4.10 - Released 27-Jan-2020
=====================================================================
# the delivered lib_compojoom package was missing font-awesome

=====================================================================
Hotspots 5.4.9 - Released 15-Dec-2019
=====================================================================
+ added an option to set default directions unit (METRIC/IMPERIAL)

=====================================================================
Hotspots 5.4.8 - Released 6-may-2019
=====================================================================
# the k2 plugin was not respecting the provided height and zoom setting

=====================================================================
Hotspots 5.4.7 - Released 15-Apr-2019
=====================================================================
# updated the k2 hotspots plugin - now it works without mootools and doesn't crash with latest gmaps version

=====================================================================
Hotspots 5.4.6 - Released 10-Feb-2019
=====================================================================
~ fixing the google maps version of the api to 3.34 as 3.35 introduced some incompatibilities
~ better handling of aliases when creating hotspots
# wrong category description in the CB plugin
# zip was not copied over from the CB plugin
# geocoding requests in CB, k2 plugins were failing because of a missing API key for the google APIs

=====================================================================
Hotspots 5.4.5 - Released 6-Jan-2019
=====================================================================
+ added a css class to the div that holds the language in the frontend form
+ added an option to specify a default category for the hotspots submit form in the frontend

=====================================================================
LEGEND
=====================================================================
! Note
+ New feature or addition
^ Major change
~ Small change
$ Language change
* Security fix
# Bug fix
- Feature removal
