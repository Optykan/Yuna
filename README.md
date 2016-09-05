#Yuna v0.5.0
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
Yuna::Route('/your/route/here', function(Request $request, Response $response){
	$response->setResponse('Hello, World!');
	return $response;
});
```

The response when visiting `http://your.server/your/route/here` will be a `json_encode`d representation of ~~what is returned from that function~~ the data in the `Response` object.
**Plase return a `Response` object. Otherwise, Yuna will respond with a default `Response` object.** You find this in `net/Response.php`.

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
Yuna::Route('/users/{id}', function(Request $request, Reponse $response){
	$response->setResponse($request->getParam('id'));
	return $response;
});
```

If a user visits `http://your.server/users/123`, Yuna will first check to see if a route for `/users/123` is configured. If it's not, it'll see if a route matching `/users/{VARIABLE_NAME}` is configured. Since we have `/users/{id}` configured, Yuna will run the callback for that route. The `$request` parameter is a `Request` object (find this in `/net/Request.php`), and the available methods can be seen below.

Anyways, the response here is:
```
{
	'response': 123,
	'yuna_meta': ...
	'yuna_warnings': ...
}
```
Yuna will take the variable name within the brackets, and will pass it to the `Request` object. You can access these variables with the `getParam` method.

~~**Yuna does not currently support the addition of more than 1 variable. This might be fixed later.**~~
Go crazy. This was fixed in version 0.3.0.

###Route Prefixes

Look you can group routes too! Simply put them into a:

```
Yuna::Group('/prefix/', function(){
	Yuna::Route('/foo/bar/', function(Request $request, Response $response)){
		return $response;
	});
});
```

The resulting route is `/prefix/foo/bar/`. You can also, of course, pass variables in the prefix like with a normal route:

```
Yuna::Group('/prefix/{foo}/', function(){
	Yuna::Route('/bar/{baz}/', function(Request $request, Response $response)){
		$foo=$request->getParam('foo');
		$baz=$request->getParam('baz');
		return $response;
	});
});
```

If you really wanted to, you could nest groups:

```
Yuna::Group('/prefix/', function(){
	Yuna::Group('/inner/', function(){
		Yuna::Route('/bar/', function(Request $request, Response $response){
			return $response;
		});
	});
	Yuna::Group('/inner2/', function(){
		Yuna::Route('/baz/', function(Request $request, Response $response){
			return $response;
		});
	});
});
```

Look, now we have `/prefix/inner/bar/` and `/prefix/inner2/baz/` set up. As always, Yuna will first look for a matching route, then try to match variables to the path.

You can also use `Yuna::Prefix()` as an alias.

#Miscellaneous

###The Request object
This object contains everything that you could possibly need (and less!) to know about the request.

`public function getType()`        returns the HTTP method used for the request (GET, POST, PUT, etc.)

`public function getHeaders()`     returns the HTTP request headers

`public function getParams()`      returns all the parameters in an array. Right now just an array of 1.

`public function getParam($param)` returns the value of the parameter encoded in the route. Like for the route `/foo/{bar}`, `$request->getParam('bar')` returns the value of `bar`;

###The Response object
This object contains some things that you might find useful when responding to a request.

`public function setStatus($status=200)`  sets the HTTP response code

`public function setHeader($header)`      sets a the HTTP headers. Pass a string to set a single header, or pass an array of headers.

`public function setResponse($response)`  sets the `response` field in the response. You can send whatever type you want. It'll get `json_encode`d anyways. Hopefully.

`public function setWarnings($warn=NULL)` sets the warnings that Yuna responds with. You can touch this if you really want to but preferably not.

`public function setMeta($meta=NULL)`     sets the meta that Yuna responds with. Don't touch this either unless you want to.

`public function getCount()`              returns how many direct children the response body has.

`public function sendResponse()`          `echo`s a `json_encoded` representation of the response. This is called automatically, so you don't have to.


#Thanks!
That's it for now!