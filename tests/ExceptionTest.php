<?php

namespace Tests;

use Dmn\Exceptions\ServiceProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\Example\Controllers\TestController;
use Tests\Example\MergeMetaException;
use Tests\Example\Models\TestModel;
use Tests\Example\Models\TestModelWithResourceName;
use Tests\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class, \Dmn\Exceptions\Handler::class
        );

    }



    /**
     * Load package service provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        Config::set('validation', include __DIR__ . '/../config/validation.php');
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Run the database migrations for the application.
     *
     * @return void
     */
    public function runDatabaseMigrations(): void
    {
        $migrationPath = __DIR__ . '/database/migrations';

        $this->artisan(
            'migrate:fresh --realpath --path="'
            . $migrationPath
            . '"'
        );

        $this->beforeApplicationDestroyed(function () use ($migrationPath) {
            $this->artisan(
                'migrate:rollback --realpath --path="'
                . $migrationPath
                . '"'
            );
        });
    }

    /**
     * @test
     * @testdox Http not found
     *
     * @return void
     */
    public function httpNotFound(): void
    {
        $response = $this->getJson('/');

        $response->assertNotFound();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->json('status_code'));
        $this->assertEquals('http_not_found', $response->json('error'));
        $this->assertEquals('The route / could not be found.', $response->json('message'));
        $this->assertEquals('Route not found. Please check the URI.', $response->json('description'));
    }

    /**
     * @test
     * @testdox Method not allowed
     *
     * @return void
     */
    public function methodNotAllowed(): void
    {
        Route::get('/');

        $response = $this->postJson('/');

        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->json('status_code'));
        $this->assertEquals('method_not_allowed', $response->json('error'));
        $this->assertEquals('Method not allowed.', $response->json('message'));
        $this->assertEquals('The POST method is not supported for route /. Supported methods: GET, HEAD.', $response->json('description'));

    }

    /**
     * @test
     * @testdox Forbidden
     *
     * @return void
     */
    public function forbidden(): void
    {
        Route::get('/', function () {
            throw new AuthenticationException();
        });

        $response = $this->getJson('/');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->json('status_code'));
        $this->assertEquals('forbidden', $response->json('error'));
        $this->assertEquals("You don't have permission to access this resource.", $response->json('message'));
        $this->assertEquals("You don't have permission to access this resource.", $response->json('description'));
    }

    /**
     * @test
     * @testdox Model not found
     *
     * @return void
     */
    public function modelNotFond(): void
    {
        $this->runDatabaseMigrations();

        Route::get('/', function () {
            return TestModel::findOrFail(1);
        });

        $response = $this->getJson('/');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->json('status_code'));
        $this->assertEquals('resource_not_found', $response->json('error'));
        $this->assertEquals('Resource not found.', $response->json('message'));
        $this->assertEquals('Resource not found.', $response->json('description'));
    }

    /**
     * @test
     * @testdox Model not found with resource name
     *
     * @return void
     */
    public function modelNotFoundWithResourceName(): void
    {
        $this->runDatabaseMigrations();

        Route::get('/', function () {
            return TestModelWithResourceName::findOrFail(1);
        });

        $response = $this->getJson('/');
        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->json('status_code'));
        $this->assertEquals('resource_not_found', $response->json('error'));
        $this->assertEquals('Test not found.', $response->json('message'));
        $this->assertEquals('Test not found.', $response->json('description'));
    }

    /**
     * @test
     * @testdox Unprocessable entity
     *
     * @return void
     */
    public function unprocessableEntity(): void
    {
        Route::post('/', [
            'as' => 'reference.group1.field',
            'uses' => TestController::class . '@test'
        ]);

        $response = $this->postJson('/');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors($response->json('errors'));
        $this->assertIsArray($response->json('meta'));
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->json('status_code'));
        $this->assertEquals('unprocessable_entity', $response->json('error'));
        $this->assertEquals('The field field is required.', $response->json('message'));
        $this->assertEquals('The field field is required.', $response->json('description'));
    }

    /**
     * @test
     * @testdox Unexpected Error
     *
     * @return void
     */
    public function unexpectedError()
    {
        Route::post('/', function () {
            throw new MergeMetaException();
        });

        $response = $this->postJson('/');

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->json('status_code'));
        $this->assertEquals('unexpected_error', $response->json('error'));
        $this->assertEquals('Unexpected error.', $response->json('message'));
        $this->assertEquals('Unexpected error.', $response->json('description'));
    }

    /**
     * @test
     * @testdox Throttle request
     *
     * @return void
     */
    public function throttle(): void
    {
        Route::post('/', function () {
            throw new ThrottleRequestsException('message');
        });

        $response = $this->postJson('/');

        $this->assertEquals(Response::HTTP_TOO_MANY_REQUESTS, $response->json('status_code'));
        $this->assertEquals('too_many_requests', $response->json('error'));
    }

    /**
     * @test
     * @testdox It can get meta reference
     *
     * @return void
     */
    public function metaReference(): void
    {
        $response = $this->getJson(route('reference.group1.field'));

        $this->assertEquals(
            config('validation.references.group1.field'),
            $response->json('data')
        );
    }
}
