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

		// mock get request for retrieving a collection of "books" owned by a user
		$app->router->get('user_books', function() {
			return [
				[ 'book_id' => 1234 ],
				[ 'book_id' => 5678 ],
				[ 'book_id' => 9012 ],
			];
		});

		// mock get request for retrieving multiple books
		$app->router->get('book', function(Request $request) {

			$bookIDs = $request->input('book_ids');
			$bookIDs = explode(',', $bookIDs);

			$resp = [];
			foreach ($bookIDs as $index => $id) {
				$resp[] = [ 'id' => $id, 'name' => "Book #".($index+1) ];
			}

			return $resp;
		});

		return $app;
	}

}
