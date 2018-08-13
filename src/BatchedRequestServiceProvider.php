<?php

namespace Dvanderburg\BatchedRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

use Dvanderburg\BatchedRequest\BatchedRequest;

/**
 * Service provider to accomodate batched requests/subrequests
 *
 * Integration
 * 	Register the service provider in app.php: $app->register(Dvanderburg\BatchedRequest\BatchedRequestServiceProvider::class);
 * 	POST to /batch will process a batch of requests and return an array of responses
 * 
 */
class BatchedRequestServiceProvider extends ServiceProvider {

	/**
	 * Attaches the route "batch" to handle batched requests
	 * Any HTTP POST requests to /batch will execute as a batched request
	 * @return null
	 */
	public function boot() {

		Route::post('batch', function(Request $request) {

			$batch = $request->get('batch', []);

			$batchedRequest = new BatchedRequest($batch, $request);

			$batchedRequest->execute();
				
			return $batchedRequest->getResponses();
		
		});

	}

}
