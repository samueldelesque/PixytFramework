# *Pixyt* site | pixyt.com app



This directory is more for reference purposes and contains the source code I wrote for pixyt.com, a social network for photographers.

You might find interest in the inc/php/classes/ which contain a simple oop framework.
The structure of this PHP app is as follow:

	class App: General purpose class with initializing functions, routing, 
	and references to all other utility classes such as App::$db etc.

	class Mysql: based on PDO, written to match query styles of WPDP
	or Code Igniter, but orientated towards CRUD operations strictly 
	regarding our internal objects. This class is extended by class Query;

	class Object: abstract default object class that contains methods
	such as Create, Update, Delete etc. Each object class (User, Photo, 
	Site, Sale, Tag, Transaction etc) are simply extending the Object
	class and adding its properties and validation.

	class Collection: Allows you to manipulate Collections of Object classes. 
	This class directly extends the Query class.
		For example:	$myPhotos = new Collection('Photo');
						$myPhotos->where('uid',$_SESSION['uid']);
						$photos = $myPhotos->data();
						//$photos now contains an array of all the user's photos.






### Credits

This project was brought to you by [Samuel Delesque](http://samueldelesque.me).
