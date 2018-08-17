<?php

namespace Dvanderburg\BatchedRequest\Test;

use Illuminate\Http\Request;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {

	/**
	 * Creates the application.
	 *
	 * @return \Laravel\Lumen\Application
	 */
	public function createApplication() {

		$app = new \Laravel\Lumen\Application(
			realpath(__DIR__.'/../')
		);

		// mock get request for retrieving a single entity by id
		$app->router->get('product/{id}', function($id) {
			return [ 'product_id' => $id ];
		});

		// mock post request for creating a new entity
		$app->router->post('user', function(Request $request) {

			$username = $request->input('username', 'johndoe');

			return [ 'username' => $username ];
		});

		return $app;
	}

}
