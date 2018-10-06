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
- ~~Copy the file "wopi-template.php" from the modules directory to site/templates, rename it there to "wopi.php" and make sure it is readable by the web server~~ (This is now done by the installer)
- ~~Create a template named "wopi". It needs no additional fields~~ (This is now done by the installer)
- ~~Create a page named "wopi" under /home and set it to "hidden"~~ (This is now done by the installer)
- Configure your webserver to use https encryption
- Install Collabora CODE like documented [here](https://www.collaboraoffice.com/code/) in steps 1 to 5 (no need to do dabble with the NextCloud app. Take care to follow all the instructions, especially regarding the docker run syntax and webserver configuration, to the point
- Enable LoolEditor on a file field (go to Setup -> Fields, choose your file field there, change to the "Details" tab and scroll to the bottom. You will find a checkbox there to enable LoolEditor for this field:
  ![Screenshot of enabling LoolEditor](https://bitpoet.github.io/img/LoolEnableField.png)
- Edit a page with a file field in ProcessWire and upload an office document. After saving, you will see an edit icon right of the filename. Click on it.
- Your LibreOffice editor should open in a modal and you should be able to edit the file and save any changes

## ToDo
- Pass the correct locale to the CODE leaflet
- ~~Finish implementing file locking~~ [X] Done
- ~~Make the editor modal look nicer and fit the editor more tightly into the available space~~ [X] Done
- Add a little more error handling and reporting
- Allow more customization
- Add history support (WIP)
- ~~Make editor use configurable on a per-field basis~~ [X] Done

## Screencap
![Screen capture of LoolEditor](https://raw.githubusercontent.com/BitPoet/bitpoet.github.io/master/img/LoolEditorScreenrecording2.gif)

## More on installing CODE

### If the container doesn't start on Ubuntu 16.04

I had a bit of trouble getting my Collabora CODE docker instance to run on Ubuntu 16.04. The container simply failed to spin up with a cryptic error message saying something about an invalid graphdriver. Despite how it sounds, this has nothing to do with graphics at all. The culprit apparently was that a disagreement about storage driver. I could fix this by adding a ```/etc/docker/daemon.js``` as described [here](https://docs.docker.com/storage/storagedriver/device-mapper-driver/).

### Server behind NAT

I'm using my local development machine behind a NAT, so the official server name resolves to a public IP. CODE whoever needs to communicate with ProcessWire on the internal IP, as my router (like most) doesn't allow looping connections.

Thus, I had to tell the docker container to resolve the official hostname to the private 192.168.xxx.xxx IP instead of the public one. Fortunately, there's a command line argument for docker run that does just that. Use

```docker run --add-host=your.public.hostname:192.168.xxx.xxx ...your other arguments...```

## Changelog

### 0.0.10

 - Automatically install the WOPI endpoint when LoolEditor.module is installed and remove it when uninstalling
 - Make LOOL-URL configurable in LoolEditor module config to allow hosting it on another server (untested yet)
 - Fix issue with automatic LOOL URL determination when ProcessWire is installed in a subdirectory

## License
This module is released under MPL 2.0. See the LICENSE file in this repository for details.

## Thanks
Big thanks go to
- The LibreOffice folks who built a viable alternative for office software in a monopolized market and keep improving it
- The folks at Collabora for their efforts in bringing LibreOffice into the web
