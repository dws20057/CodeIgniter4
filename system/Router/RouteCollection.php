<?php namespace CodeIgniter\Router;

/**
 * Interface RouteCollectionInterface
 *
 * A Route Collection's sole job is to hold a series of routes. The required
 * number of methods is kept very small on purpose, but implementors may
 * add a number of additional methods to customize how the routes are defined.
 *
 * The RouteCollection provides the Router with the routes so that it can determine
 * which controller should be ran.
 *
 * @package CodeIgniter\Router
 */
class RouteCollection implements RouteCollectionInterface
{

	/**
	 * The namespace to be added to any controllers.
	 * Defaults to the global namespaces (\)
	 *
	 * @var string
	 */
	protected $defaultNamespace = '\\';

	/**
	 * The name of the default controller to use
	 * when no other controller is specified.
	 *
	 * @var string
	 */
	protected $defaultController = 'Home';

	/**
	 * The name of the default method to use
	 * when no other method has been specified.
	 *
	 * @var string
	 */
	protected $defaultMethod = 'index';

	/**
	 * Defined placeholders that can be used
	 * within the
	 *
	 * @var array
	 */
	protected $placeholders = [
		'any'      => '.*',
		'segment'  => '[^/]+',
		'num'      => '[0-9]+',
		'alpha'    => '[a-zA-Z]+',
		'alphanum' => '[a-zA-Z0-9]+',
	];

	/**
	 * An array of all routes and their mappings.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * The current method that the script is being called by.
	 *
	 * @var
	 */
	protected $http_verb;

	/**
	 * The default list of HTTP methods (and CLI for command line usage)
	 * that is allowed if no other method is provided.
	 *
	 * @var array
	 */
	protected $default_http_methods = ['options', 'get', 'head', 'post', 'put', 'delete', 'trace', 'connect', 'cli'];

	//--------------------------------------------------------------------

	public function __construct()
	{
		// Get HTTP verb
		$this->http_verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
	}

	//--------------------------------------------------------------------

	/**
	 * Adds a single route to the collection.
	 *
	 * This provides a fairly simplistic solution without a lot of options.
	 * A much more flexible version is to present an array elements and
	 * multiple options to the 'map' method below.
	 *
	 * @param string       $route  The pattern to match against the URI
	 * @param string       $map    The controller or path to map to.
	 * @param string|array $method One of more HTTP methods that are allowed. If one,
	 *                             present as a single string (i.e. 'get').
	 *                             Otherwise, present an array of methods.
	 *
	 * @return void
	 */
	public function add($route, $map, $methods = null)
	{
		// If methods is null, than it should work
		// for any of the available methods.
		if (empty($methods))
		{
			$methods = $this->default_http_methods;
		}

		if ( ! is_array($methods))
		{
			$methods = [$methods];
		}

		// Ensure that all of our methods are lower-cased for compatibility
		array_walk($methods, function (&$item)
		{
			$item = strtolower($item);
		}
		);

		// To save on memory and processing later, we only add
		// the routes that are actually available at this time.
		if ( ! in_array($this->http_verb, $methods))
		{
			return;
		}

		// Replace our regex pattern placeholders with the actual thing
		// so that the Router doesn't need to know about any of this.
		foreach ($this->placeholders as $tag => $pattern)
		{
			$route = str_ireplace(':'.$tag, $pattern, $route);
		}

		// We need to ensure that the current namespace is added to the final mapping
		// so that it won't try to use the current namespace for the class.
		if ( is_string($map) && strpos($map, '\\') === false)
		{
			$map = $this->defaultNamespace.'\\'.$map;

			// Trim out any double back-slashes
			$map = str_replace('\\\\', '\\', $map);

			// To make the map as compatible as possible, we
			// prefix with a backslash to ensure we get out of the current namespace.
			$map = '\\'. ltrim($map, '\\ ');
		}

		$this->routes[$route] = $map;
	}

	//--------------------------------------------------------------------

	/**
	 * Adds an array of routes to the class all at once. This allows additional
	 * settings to be specified for all incoming routes, including:
	 *
	 *  _namespace  Sets the namespace for all routes
	 *  _hostname   Route must be on the set domain
	 *  _prefix     Sets a string that will be prefixed to all routes (left side)
	 *
	 * @param array|null $routes
	 *
	 * @return mixed
	 */
	public function map(array $routes = null)
	{
	}

	//--------------------------------------------------------------------

	/**
	 * Registers a new constraint with the system. Constraints are used
	 * by the routes as placeholders for regular expressions to make defining
	 * the routes more human-friendly.
	 *
	 * Once created, they can be used within curly brackets in routes.
	 *
	 * @param $name
	 * @param $pattern
	 *
	 * @return mixed
	 */
	public function addPlaceholder($name, $pattern)
	{
		$this->placeholders[$name] = $pattern;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default namespace to use for controllers when no other
	 * namespace has been specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultNamespace($value)
	{
		$this->defaultNamespace = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default controller to use when no other controller has been
	 * specified.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultController($value)
	{
		$this->defaultController = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the default method to call on the controller when no other
	 * method has been set in the route.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setDefaultMethod($value)
	{
		$this->defaultMethod = $value;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of available routes.
	 *
	 * @return array
	 */
	public function routes()
	{
		return $this->routes;
	}

	//--------------------------------------------------------------------

}