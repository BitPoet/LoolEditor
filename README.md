# LoolEditor
ProcessWire module - inline editing for office documents using Collabora CODE

## About
This module adds inline editing capabilities for office documents to the [ProcessWire OpenSource CMS](https://processwire.com). It uses the free [Collabora CODE](https://www.collaboraoffice.com/code/) server that is used in other projects like [OwnCloud](https://owncloud.org/) or [NextCloud](https://www.nextcloud.com/).

## Module Status
Alpha

## Requirements
- An Installation of ProcessWire CMS with Apache or NGINX on a docker-capable server
- Docker runtime
- HTTPS with a certificate trusted by the browser (e.g. from [LetsEncrypt](https://letsencrypt.org/))
- A working installation of the Collabora CODE docker image
- This module, apparently

## Step by step
- Install ProcessWire 3 as documented [here](https://processwire.com/docs/install/new/). If you are new to ProcessWire, you may want to select a site profile other than "blank"
- Download this module through the green icon at the top right and extract the contents into the site/modules directory of your ProcessWire installation
- Enter the ProcessWire admin backend, go to "Modules" -> "Refresh" and install ProcessLoolEditor (titled "LibreOffice Online Editor")
- Copy the file "wopi-template.php" from the modules directory to site/templates, rename it there to "wopi.php" and make sure it is readable by the web server
- Create a template named "wopi". It needs no additional fields
- Create a page named "wopi" under /home and set it to "hidden"
- Configure your webserver to use https encryption
- Install Collabora CODE like documented [here](https://www.collaboraoffice.com/code/) in steps 1 to 5 (no need to do dabble with the NextCloud app. Take care to follow all the instructions, especially regarding the docker run syntax and webserver configuration, to the point
- Enable LoolEditor on a file field (go to Setup -> Fields, choose your file field there, change to the "Details" tab and scroll to the bottom. You will find a checkbox there to enable LoolEditor for this field:
  ![Screenshot of enabling LoolEditor](https://bitpoet.github.io/img/LoolEnableField.png)
- Edit a page with a file field in ProcessWire and upload an office document. After saving, you will see an edit icon right of the filename. Click on it.
- Your LibreOffice editor should open in a modal and you should be able to edit the file and save any changes

## ToDo
- Pass the correct locale to the CODE leaflet
- ~~Finish implementing file locking~~ [X] Done
- Make the editor modal look nicer and fit the editor more tightly into the available space
- Add a little more error handling and reporting
- Allow more customization
- ~~Make editor use configurable on a per-field basis~~ [X] Done

## Screencap
![Screen capture of LoolEditor](https://raw.githubusercontent.com/BitPoet/bitpoet.github.io/master/img/LoolEditor1.gif)

## License
This module is released under MPL 2.0. See the LICENSE file in this repository for details.

## Thanks
Big thanks go to
- The LibreOffice folks who built a viable alternative for office software in a monopolized market and keep improving it
- The folks at Collabora for their efforts in bringing LibreOffice into the web
