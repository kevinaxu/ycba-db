YCBA Terms Lookup 
==========

The Yale Center for British Art Terms Lookup Tool is used to query and update terms associated with an object. 

<p align="center">
  <img src="https://github.com/kevinaxu/ycba-db/blob/master/img/query.jpg" alt="YCBA Terms Lookup Landing Page" title="Landing Page" />
</p>

<p align="center">
  <img src="https://github.com/kevinaxu/ycba-db/blob/master/img/terms.jpg" alt="Terms Page" title="Terms Page" />
</p>

Setup
-----

1.  Download the 32-bit version of [WampServer](http://www.wampserver.com/en/). 
	Install WampServer using all the default settings. 

	You might encounter an error that says "Missing mscrvc110.dll". This will be resolved in 
	a later step. 

2. 	Download this repository. Extract the contents and rename the folder to 'ycba'. 
	Open up the directory where WampServer was downloaded (should be in the C drive). 
	Move the ycba repository into the www folder of wamp. The final directory structure should look like this: 

	--> C:\wamp
		--> www
			--> ycba
				--> index.html
				---> ...

3. 	Install the Microsoft Visual C++ Redistributable drivers. 
	This should get rid of the mscrv.dll error. 
	Run the executables depending on your machine. 32-bit machines should run the executables 
	in the x86 folder. 64-bit machines should run the x64. 








	
2.	Run the setup script to create the project directory, modify config files, 
	and install the necessary drivers. 

	Input credentials during the setup script 

'''
	$ ./setup.py 
'''

3.	Manually restart WampServer by going to the icon in the taskbar and clicking "Restart all services". 

Contact
-------

Maintainer: [Kevin Xu](http://github.com/kevinaxu/) (kevin.xu@yale.edu). 

