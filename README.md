## Dynamic-Steam-Signatures

PHP Based application that can generate steam signatures from the gaming platform steam in a PNG format. The application is very portable, and can run on shared web hosting.

The application is fully functional, but still would require some possible tune up, and tweaking. Any help would be appreciated.

# Screenshots
![Screenshot](http://i.imgur.com/h5cPsb9.png)

# Website
https://www.steamsig.xyz

# Features
Supports caching to reduce load on the server

More then 6 skins already created

Easy to use frontend using bootstrap, and jquery

Does not require a database

Open source!

# Requirements
Tested on Apache2, and Nginx

php7.2

php7.2-curl

php7.2-gd

# Nginx
The application  uses .htaccess for Apache2 to understand how to handle the signatures. But Nginx requires a bit more configuration. Add this to the websites configuration if your using Nginx.
```
rewrite ^/pathtosteamroot/steam/images/(.*)/(.*).png$ /pathtosteamroot/steam/createimage.php?skin=$1&username=$2;
```
