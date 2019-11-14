# Flutter-app-Wordpress-Plugin
Wordpress plugin for handling settings support for an mobile application.
This project is part of my Graduation Project of the UNED University and it is an Ad Hoc Plugin specificaly elaborated for my Mobile
application developed in Flutter.
The plugin lets the mobile app comunicates with new Wordpress Resp API endpoint and has a panel settings for make setting to the mobile app from
the Wordpress site.

This plugin has the following characteristics:
. Creates new endpoints in the Wordpress Rest API

(Custom Endpoints are)
This endpoint uses [Timetable and Event Schedule] Plugin for Wordpress to show all the events as Json.
//yoursite.com/wp-json/tv/upcoming_events/all-events
This endpoint shows data about posts, it works when user has a network Internet connection (No Wi-Fi)
//yoursite.com/wp-json/mobile/v1
This endpoint shows data about posts, it works when user has Wi-Fi connection.
//yoursite.com\/wp-json\/wifi\/v1
This endpoint shows the data related to the categories the admin decided to show the mobile application.
//yoursite.com\/wp-json/active_categories/v1/categories



