YCBA Terms Lookup 
==========

The Yale Center for British Art Terms Lookup Tool is used to query and update terms associated with an object. 

![YCBA Terms Lookup Landing Page](https://github.com/kevinaxu/ycba-db/raw/master/img/query.jpg "Landing Page")

![Terms Page](https://github.com/kevinaxu/ycba-db/raw/master/img/terms.jpg "Terms Page")


Setup
-----

1.  Download the 32-bit version of [WampServer](http://www.wampserver.com/en/). Install WampServer using all the default settings. Be sure to remember which version of PHP is installed. 

	![wampserver-error](https://github.com/kevinaxu/ycba-db/raw/master/img/wamp-error.jpg "Missing MSVCR100.dll")
	
	During installation, you may encounter an error about missing `MSVCR100.dll`, which is a Microsoft Visual C++ Redistributable driver. This will be resolved in a later step. 
	
2. 	Download this repository. Extract the contents and rename the folder to `ycba`. In the `ycba\config` folder, rename `app.ini.example` to `app.ini`. Enter in the proper credentials for `user_name`, `pw`, and `server_name`. Use `bac5-dev` for testing and `bac5-prod` for production. 

3. Open up the directory where WampServer was downloaded (should be in the `C:` drive). Move the ycba repository into the `www` folder of wamp. The final directory structure should look like this: 

   ```
   C:
   └─wamp
	   ├─www
	   │   └─ycba
	   │       ├─config
	   │       │   └─app.ini
	   │       ├─drivers
	   │       └─img
	   ├─alias
	   ├─apps
	   ├─bin
	   .
	   .
	   .
   ```
   
4. Install the Microsoft Visual C++ Redistributable driver by going into `ycba\drivers` and running the executables. 

   32-bit machines should run all the executables in the `x84` folder, while 64-bit machines should run all the ones in `x64`. 

   If you are unsure what architecture your machine uses, open the `Start` menu, right-click `My Computer`, then click `Properties`. The version of Windows should be displayed under `System`. 

   NOTE: If the error still occurs, run all executables in the other folder as well. 

5. The next step is to configure the Microsoft SQL Server drivers with PHP. Go to `ycba\drivers\sqlsrv`, find the sqlsrv drivers that match your version of PHP, and drop them into `www\bin\php\phpX\ext`, where `X` is the PHP version.

   For example, if the version of PHP that WampServer uses is 5.5, then move all drivers containing `55` into the directory. There should be two files moved. 

6. To let WampServer know that extensions were added, we have to modify the `php.ini` configuration file. This can be done in two ways. 

    1. Click on the WampServer icon in the system tray. Go into the `PHP` folder and click on `php.ini` to edit it. 

      ![Editing php.ini](https://github.com/kevinaxu/ycba-db/raw/master/img/php-ini.jpg "Edit php.ini")
   
    2. Directly edit `www\bin\php\phpX.X\php.ini`. 

   Whichever method you choose, add the following lines to the end of the file `www\bin\php\phpX\ext`. Make sure you change the file name to correspond to the extension that you added. 

   ![Adding extensions to php.ini](https://github.com/kevinaxu/ycba-db/raw/master/img/php-ini2.jpg "Adding extensions to php.ini")

   Save the file. 
   
7. The last step is to install the ODBC Driver 11 for SQL Server. Go into `ycba\drivers\ODBC Driver 11` and run `msodbcsqlx86.msi` for 32-bit machines and `msodbcsqlx64.msi` for 64-bit machines. 

8.	Restart WampServer by going to the icon in the system tray and clicking `Restart all services`. 


Congrats! The YCBA Terms Lookup tool should be successfully installed! Point your browser to `localhost/ycba/index.html` to get started. 

If you still run into errors, look up the errors in the stack trace. It's most likely a driver configuration error. 

Contact
-------

Maintainer: [Kevin Xu](http://github.com/kevinaxu/) (kevin.xu@yale.edu). 

