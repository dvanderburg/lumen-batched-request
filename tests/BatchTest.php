<?php

namespace Dvanderburg\BatchedRequest\Test;

use Dvanderburg\BatchedRequest\BatchedRequest;
use Dvanderburg\BatchedRequest\Test\TestCase;

class BatchTest extends TestCase {

	/**
	 * Tests basic batch functionality
	 * 		- Number of responses returned matches number of requests sent in the batch
	 * 		- Requests returned are indexed in the order they were sent or by their name
	 * 		- Responses returned include an http status code and a response body
	 *
	 * @return void
	 */
	public function testBatch() {

		$batch = [
			[
				'method' => 'GET',
				'relative_url' => '/product/1234',
			],
			[
				'method' => 'POST',
				'name' => 'create-user',
				'relative_url' => '/user/?username=john',
				'content-type' => 'application/x-www-form-urlencoded',
				'body' => 'password=admin',
			],
			[
				'method' => 'GET',
				'relative_url' => '/fourohfour',
			]
		];

		$batchedRequest = new BatchedRequest($batch);

		$batchedRequest->execute();

		$responses = $batchedRequest->getResponses();

		$this->assertCount(3, $responses, "Failed asserting number of responses returned matches number of requests sent.");
		$this->assertArrayHasKey(0, $responses, "Failed asserting unnamed requests are indexed numerically.");
		$this->assertArrayHasKey('create-user', $responses, "Failed asserting named requests are returned indexed by that name.");
		$this->assertArrayHasKey(2, $responses, "Failed asserting unnamed requests are indexed numerically.");
		$this->assertArrayHasKey('code', $responses[0], "Failed asserting responses include an HTTP status code.");
		$this->assertArrayHasKey('body', $responses[0], "Failed asserting responses include a body");

	}

}
