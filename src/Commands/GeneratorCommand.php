<?php

namespace BoggyBot\LaravelTestGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class GeneratorCommand extends Command
{
	use InteractsWithClass;
	use InteractsWithMethods;

	protected $stubs_path;

	protected $app_namespace;

	protected $test_namespace;

	protected $test_base_path;

	protected $test_path;

	protected $test_count = 0;

	protected $signature = "generate:tests
							{--app-namespace=? : Default: App\Http\Controllers }
							{--test-namespace=Tests\Acceptance : Default: Tests\Acceptance }
							";

	protected $description = 'Generates Laravel HTTP Tests from Registered Routes';

	public function handle()
	{
		$this->stubs_path = is_dir(resource_path('stubs/laravel-test-generator'))
			? resource_path('stubs/laravel-test-generator/')
			: dirname(__DIR__).'/../stubs/';

		$this->app_namespace = $this->option('app-namespace') === '?'
			? app()->getNamespace()
			: $this->option('app-namespace');

		$this->test_namespace = $this->option('test-namespace');

		$this->test_path = str_replace('\\', '/', $this->test_namespace);
		$this->test_base_path = base_path(lcfirst($this->test_path));

		// Get the routes do the magic.
		collect(RouteFacade::getRoutes())
			->filter(function(Route $route) {
				$controller = Str::parseCallback($route->getActionName('uses'));
				$method_exists = method_exists(ltrim($controller[0], '\\'), $controller[1]);

				return Str::startsWith($route->getActionName(), $this->app_namespace)
					&& $method_exists;
			})
			->map(function(Route $route) {
				$controller = Str::parseCallback($route->getActionName('uses'));
				$controller_short = Str::afterLast($controller[0], '\\');

				$test_class = str_replace('Controller', 'Test', $controller_short);
				$test_namespace = str_replace($this->app_namespace, $this->test_namespace, $controller[0]);
				$test_namespace = str_replace($controller_short, $test_class, $test_namespace);

				$test_path = Str::after($test_namespace, $this->test_namespace);
				$test_path = str_replace($test_class, '', $test_path);
				$test_path = $this->test_base_path.str_replace('\\', '/', $test_path);

				return (object) [
					'named_route' => $route->getName(),
					'uri' => $route->uri(),
					'controller_long' => $controller[0],
					'controller' => $controller_short,
					'action' => $route->getActionMethod(),
					'method' => strtolower(current(array_diff($route->methods(), ['head', 'put']))),
					'parameters' => $route->parameterNames(),
					'test_namespace' => $test_namespace,
					'test_class' => $test_class,
					'test_file' => $test_class.'.php',
					'test_path' => $test_path,
					'test_path_file' => $test_path.$test_class.'.php',
				];
			})
			->each(function($item) {
				$this->applyClassStubAndSave($item);
				$this->applyMethodStubsAndSave($item);
			});

		$this->info("Generated {$this->test_count} tests to [{$this->test_path}]");
	}
}
