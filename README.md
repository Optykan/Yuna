#Yuna v0.3.0
Yuna is a lightweight PHP API framework (pfft, like we needed any more) that you can do things with.
I'm still squashing bugs and adding features, but if you'd like to add something open an issue ~~or make it yourself and open a pull request you lazy piece of~~ and I'll get to it (eventually)

#Installation
1. Download the repo
2. `require 'path/to/Yuna.php'`
3. Have fun!

#Configuration

###Server
Make sure you're using Apache, or something that can handle .htaccess files. Point all requests to `index.php`, or whatever you're using as the base file.
By default, Yuna takes routes through the GET parameter `request_url`. ~~This will be configurable later.~~ You can configure this through `Yuna::Config`

Here's a sample one:
```
<IfModule mod_rewrite.c>
	RewriteEngine On
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule (.*) index.php?request_url=$1
</IfModule>
```
I think this works.

###Yuna
Hey you can configure things now! Just do:
```
Yuna::Config(array('option_name'=>'option_value');
```

Valid option names are:

`variable_delimiter`, which is an `array` of the beginning delimiter, and ending. Default is `array('{', '}')`. It defines where Yuna should look for variables in the route definition. For example, in route `/foo/{bar}`, Yuna will look for `{bar}`. You can change it to `/foo/:bar:` by doing `'variable_delimiter'=>array(':', ':')` if you wanted to. I don't recommend using alphanumeric characters (or slashes) because everything might explode. You can use brackets `()` but make sure to properly escape them: `\(`.

`request_url`, which is where Yuna should get the request data route from. Default is `$_GET['request_url']`.

`enable_meta`, a boolean that enables/disables the `yuna_meta` output. Default is `true`.

`enable_warnings`, a boolean that enables/disables the `yuna_warnings` output. Default is `true`.

#Usage

###Creating a route

In `index.php`, or your base file, do:
```
Yuna::Route('/your/route/here', function(){
	return 'Hello, World!';
});
```

The response when visiting `http://your.server/your/route/here` will be a `json_encode`d representation of what is returned from that function.
In this case, the response would be:

```
{
	'response': 'Hello, World!',
	'yuna_meta': ...
	'yuna_warnings': ...
}
```
Yeah, some meta stuff gets dumped with it too. ~~You'll be able to turn this off in future versions~~. See `Yuna::Config` for how to turn this off.

###Advanced routing

You wanna do more with Yuna? Of course you do. Let's try a semi-practical application.

```
Yuna::Route('/users/{id}', function(Request $request){
	$user_id=$request->getParam('id');
	return $user_id;
});
```

If a user visits `http://your.server/users/123`, Yuna will first check to see if a route for `/users/123` is configured. If it's not, it'll see if a route matching `/users/{VARIABLE_NAME}` is configured. Since we have `/users/{id}` configured, Yuna will run the callback for that route. The `$request` parameter is a `Request` object (find this in `/net/Request.php`), and the available methods can be seen below.

Anyways, the response here is:
```
{
	'response': {
		'id': 123
	},
	'yuna_meta': ...
	'yuna_warnings': ...
}
```
Yuna will take the variable name within the brackets, and will pass it to the `Request` object. You can access this variable with the `getParam` method.

~~**Yuna does not currently support the addition of more than 1 variable. This might be fixed later.**~~
Go crazy. This was fixed in version 0.3.0.

#Miscellaneous

###The Request object
This object contains everything that you could possibly need (and less!) to know about the request.

`public function getType();`        returns the HTTP method used for the request (GET, POST, PUT, etc.)

`public function getHeaders();`     returns the HTTP request headers

`public function getParams();`      returns all the parameters in an array. Right now just an array of 1.

`public function getParam($param);` returns the value of the parameter encoded in the route. Like for the route `/foo/{bar}`, `$request->getParam('bar')` returns the value of `bar`;



#Thanks!
That's it for now!