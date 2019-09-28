## Disclaimer

This software is still in alpha stage. It contains all features planned for version 1 and passed basic testing (using the included example library).  
It is currently in extended testing (testing against my private library to find bugs that may not occur when using the example library).  
This readme will be updated at the time version 1 is released.

ComicLib is a complete rewrite of ComicDB, adding many new features and a better software architecture and usability.

**The ComicLib API Version 1 may still change. Have a look at the** [API documentation](https://documenter.getpostman.com/view/5715403/S1a35U3H) **if something breaks.**

# ComicLib

ComicLib is a self-hosted personal comics library. It is a web-app, so you can view and download your comics collection on your PC, smartphone or tablet via your preferred internet browser.
ComicLib can use any digital comic book format. It can extract and display comic books if they use the CBZ, CBR or pdf file format. You can download your comic files via ComicLib, so you can use another app on your device to open files which ComicLib can not extract.
ComicLib keeps track of which comics you have or have not read, as well as the page you last read (for CBZ, CBR and pdf).
To provide additional informations around your comics, ComicLib makes use of the ComicVine comics database. You need a ComicVine user account to use ComicLib.

Aside from the web-app, ComicLib also exposes a [RESTful API](https://documenter.getpostman.com/view/5715403/S1a35U3H) for 3rd party apps. 

# Screenshots

<table>
<tr>
<td colspan="3">
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_responsive_design-e1569698874702.png"/>
    <figcaption>Responsive Design</figcaption>
</figure>
</td>
</tr>
<tr>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_volumes.png">
    <figcaption>Volumes Overview</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_volume_detail.png">
    <figcaption>Volume Details</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_search.png">
    <figcaption>Search For Volumes By Name</figcaption>
</figure>
</td>
</tr>
<tr>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_publishers.png">
    <figcaption>Publishers Overview</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_publisher_detail.png">
    <figcaption>Publisher Details</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_dashboard.png">
    <figcaption>Admins can add users and every user can change his password and API key.</figcaption>
</figure>
</td>
</tr>
<tr>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_reading_mode.png"/>
    <figcaption>Read your comics in ComicLib.</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_reading_list.png"/>
    <figcaption>Keep track of the comics you started reading.</figcaption>
</figure>
</td>
<td>
<figure>
    <img src="https://blog.ahahn94.de/wp-content/uploads/2019/09/comiclib_update_running.png"/>
    <figcaption>Update status is visible to all users.</figcaption>
</figure>
</td>
</tr>
</table>

# Getting started with ComicLib
- Install `docker` and `docker-compose`. ComicLib uses Docker to run the database- and web-servers as a virtualized environment.
- Install `python3`. ComicLib uses python for the server manager script. If you want to host ComicLib on Linux, python3 is probably pre-installed.
- Copy or link your comic directories to src/comics
- Open config/ComicLib/config.ini and change the following settings:
  - Set safe (and different!) passwords for MYSQL_ROOT_PASSWORD, MYSQL_PASSWORD and AUTH_PASSWORD (which is the password for you web login)
  - Set TZ to your timezone. Make sure you use a valid timezone value.
  - Get an API key for the Comicvine API from [https://comicvine.gamespot.com/api/](https://comicvine.gamespot.com/api/) and use it as API_KEY
- Start your ComicLib server with `./manage.py`

**Your ComicLib instance will now be accessible via http://127.0.0.1:8081 and https://127.0.0.1:8082 on the computer running ComicLib. Have a look into _Security_ to secure your ComicLib instance.**

## Server Control
- start the server by running `manage.py` or `manage.py start`
- to stop the server just run `manage.py stop`

## Security
ComicLib can be accessed via HTTP on port 8081 and via HTTPS via 8082. If using HTTP, your password is send to the ComicLib server in plain text with no additional encryption. This is very insecure and you should really consider using HTTPS instead.  
HTTPS is enabled by default, but you will get a certificate warning every time you open ComicLib. To get rid of the warning, you have to request a TLS certificate that matches your domain name.  

**To get a working TLS certificate, run `manage.py setup-le`. This will start Let's Encrypt Certbot, which will guide you through the rest of the process.**
**To renew your existing certificate, run `manage.py renew-le`.**

## Server Autostart
If you want to automatically start ComicLib when starting your computer, you can e.g. create a cron job that runs manage.py.

## Comics Organization
To get familiar with the way ComicLib expects you to organize your comics, please take a look at the included testing 
data and the ComicVine wiki.

## Supported File Formats
ComicLib can handle any file format you throw at it, but only the file formats CBR, CBZ and pdf can be extracted for the reading mode.
Thus, files of every other format can only be downloaded from ComicLib, but not read in the web-app.
You can e.g. use ComicLib to store your DRM-protected comics and your DRM-free comics in the same collection to get an overview of your
whole digital comics collection and which comics you have or have not read yet.

**PDF files have to be rendered to single pages when first opening them for reading. This takes much longer than opening CBR or CBZ comic files (minutes vs mere seconds).**
**Consider (batch-)converting your PDF comics to CBR or CBZ before adding them to your ComicLib collection to avoid long loading times when opening one of these comic books.**

# Testing Data
For testing purposes, ComicLib includes dummy files that will match to some comics.
You can use these files to test if your connection to the API works as intended.
To prevent these dummies from showing up in your real library after testing, you should delete them 
from the comics directory and run a database update via the admin dashboard.

The dummy files contain white pages with page numbers 1 to 10 on them, so you can open them in ComicLib to test the reading mode.

# Additional Information

## Comicvine API
ComicLib uses the [Comicvine API](https://comicvine.gamespot.com/api/).
Cover images and additional information on your comics will be downloaded from there.

## External Libraries
This project makes use of the following libraries (that are not included in PHP):
- [Bootstrap](https://getbootstrap.com) 
- [FontAwesome](https://fontawesome.com)
- [jQuery](https://jquery.com/)
- [Popper.js](https://popper.js.org/)
- [ImageMagick](https://imagemagick.org/)

## ComicLib Icon
The ComicLib icon is based on the archive.svg icon from [FontAwesome](https://fontawesome.com) (CC BY 4.0 License).

## Legacy Software
For compatibility reasons to ARMHF, this project uses MySQL 5.5.
This may change as newer versions become available as docker images for ARMHF.

# Copyright & License
Copyright (c) 2019 ahahn94.

ComicLib is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.