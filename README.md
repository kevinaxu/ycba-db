YCBA Terms Lookup 
==========

The Yale Center for British Art Terms Lookup Tool is used to query and update terms associated with an object. 

![YCBA Terms Lookup Landing Page](https://github.com/kevinaxu/ycba-db/raw/master/img/query.jpg "Landing Page")

![Terms Page](https://github.com/kevinaxu/ycba-db/raw/master/img/terms.jpg "Terms Page")


Setup
-----

1.  Download the 32-bit version of [WampServer](http://www.wampserver.com/en/). 
	Install WampServer using all the default settings. 

	[![Foo](https://github.com/kevinaxu/ycba-db/blob/master/img/wamp-error.jpg)]
	
	You might encounter an error that says "Missing mscrvc110.dll". This will be resolved in 
	a later step. 

2. 	Download this repository. Extract the contents and rename the folder to 'ycba'. 
	Open up the directory where WampServer was downloaded (should be in the C drive). 
	Move the ycba repository into the www folder of wamp. The final directory structure should look like this: 
	
	.
	+-- _config.yml
	+-- _drafts
	|   +-- begin-with-the-crazy-ideas.textile
	|   +-- on-simplicity-in-technology.markdown
	+-- _includes
	|   +-- footer.html
	|   +-- header.html
	+-- _layouts
	|   +-- default.html
	|   +-- post.html
	+-- _posts
	|   +-- 2007-10-29-why-every-programmer-should-play-nethack.textile
	|   +-- 2009-04-26-barcamp-boston-4-roundup.textile
	+-- _data
	|   +-- members.yml
	+-- _site
	+-- index.html


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

