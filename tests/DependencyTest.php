<?php

namespace Dvanderburg\BatchedRequest\Test;

use Dvanderburg\BatchedRequest\BatchedRequest;
use Dvanderburg\BatchedRequest\Test\TestCase;

class DependencyTest extends TestCase {

	/**
	 * Tests dependency functionality
	 * Mocks a request to retrieve a list of books owned by user, then another request to retrieve those book entities
	 * 		- Number of responses match the number sent in the request
	 * 		- Responses are indexed as expected
	 * 		- Both of the requests succesfully return a 200-OK
	 * 		- The number of books returned in the dependant request match the number of books in the prerequisite request
	 * 		
	 * @return [type] [description]
	 */
	public function testDependencies() {

		$batch = [
			[
				'method' => 'GET',
				'name' => 'get-user-books',
				'relative_url' => '/user_books/?username=larry',
			],
			[
				'method' => 'GET',
				'relative_url' => '/book/?book_ids={result=get-user-books:$.book_id}',
			],
		];

		$batchedRequest = new BatchedRequest($batch);

		$batchedRequest->execute();

		$responses = $batchedRequest->getResponses();

		// the number of returned responses should be exactly 2 and indexed as expected (one numerically, one by name)
		$this->assertCount(2, $responses, "Failed asserting number of responses returned matches number of requests sent when using dependencies.");
		$this->assertArrayHasKey('get-user-books', $responses, "Failed asserting named requests are returned indexed by that name when using dependencies.");
		$this->assertArrayHasKey(1, $responses, "Failed asserting unnamed requests are indexed numerically when using dependencies.");

		$userBookResponse = $responses['get-user-books'];
		$bookResponse = $responses[1];

		// dependencies are not working if either of the responses return a non-200 code
		$this->assertEquals(200, $userBookResponse['code'], "Prerequisite request failed when testing a batch with dependencies.");
		$this->assertEquals(200, $bookResponse['code'], "Dependent request failed when testing a batch with dependencies.");

		$userBookResposneBody = $userBookResponse['body'];
		$bookResponseBody = $bookResponse['body'];

		// number of booked returned by the 'get-user-books' request should match the number books returned in the dependent request
		$this->assertCount(count($bookResponseBody), $bookResponseBody, "Number of entities returned in dependent request did not match expected result.");

	}

}
