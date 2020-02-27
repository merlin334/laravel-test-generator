## Laravel Feature Test Generator
If you have not been writing any tests for your Laravel app, 
and need a way to scaffold your integration tests, this tool is for you.

### How it works?
1. Generator scans your registered routes
2. Generates Test for each controller
3. Generates stubs for each one of your controller actions
4. You will need to then fill in the stubs with whatever logic your app needs.

### Sample Result
```
<?php

namespace Tests\Amazing\Dashboard\DashboardTest;

use Tests\TestCase;

class DashboardTest extends TestCase
{
    /** @see \App\Http\Controllers\Dashboard\DashboardController::index() */
    public function test_get_dashboard_index()
    {
        //$response = $this->get(route('dashboard.index', []));

        //$response->assertOk();
    }

    ////
}
```

### How to use
```
composer require --dev boggybot/laravel-test-generator
```
```
php artisan generate:tests
```

#### App Namespace
By default, any matching registered route in the current project namespace qualifies. eg. App\\

You can change which namespace for generator to scan.
```
php artisan generate:tests --app-namespace="AwesomeApp"
```

#### Test Namespace
By default, all tests will be written to 'tests/Acceptance' folder.
```
php artisan generate:tests --test-namespace="Tests\FeatureTests"
```

#### Customize Stubs
```
php artisan vendor:publish --tag=laravel-test-generator-stubs
```
