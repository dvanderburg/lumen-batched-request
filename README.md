# Lumen Batched Requests
[![Build Status](https://travis-ci.org/dvanderburg/lumen-batched-request.svg?branch=master)](https://travis-ci.org/dvanderburg/lumen-batched-request)
[![Latest Stable Version](https://poser.pugx.org/dvanderburg/lumen-batched-request/v/stable)](https://packagist.org/packages/dvanderburg/lumen-batched-request)
[![Total Downloads](https://poser.pugx.org/dvanderburg/lumen-batched-request/downloads)](https://packagist.org/packages/dvanderburg/lumen-batched-request)
[![Latest Unstable Version](https://poser.pugx.org/dvanderburg/lumen-batched-request/v/unstable)](https://packagist.org/packages/dvanderburg/lumen-batched-request)
[![License](https://poser.pugx.org/dvanderburg/lumen-batched-request/license)](https://packagist.org/packages/dvanderburg/lumen-batched-request)

Lumen service provider to perform batched requests. Usage and implementation roughly based on [making batch requests](https://developers.facebook.com/docs/graph-api/making-multiple-requests) with Facebook's Graph API.

Batching requests allows you to send multiple requests at once, allowing you to perform multiple operations in a single HTTP request. Each request in the batch is processed in sequence unless dependencies are specified.

Requests within the batch can list dependancies on other requests within the batch, and access their responses via JSONP syntax. Again, similar to Facebook's Graph API. Using [JSONP syntax](https://code.google.com/archive/p/jsonpath/), a request in the batch can use the response from another request in the batch. More information is provided in the [Usage and Examples](#Usage-and-Examples) section.

Once all requests have been completed, an array of responses will be returned and the HTTP connection closed.

Built on PHP 7.1.13 and Laravel Framework Lumen (5.6.3).


## Dependancies

* __PHP >=7.1__ Built and tested on PHP 7.1.13
* __Laravel/Lumen ~5.6__ Originally built using Laravel Lumen version 5.6.3 (https://github.com/laravel/lumen)
* __FlowCommunications/JSONPath ~0.4.0__ Accommodates JSONP parsing for specifying dependancies (https://github.com/FlowCommunications/JSONPath)


## Installation and Setup

Install the package via [composer](https://getcomposer.org/).
```bash
composer install dvanderburg/lumen-batched-request
```

Register the service provider in app.php
```php
$app->register(Dvanderburg\BatchedRequest\BatchedRequestServiceProvider::class);
```


## Simple Batched Requests

Send a basic batched request by sending an HTTP post to `/batch/`. This example will perform two GET requests and a POST, returning an array of responses.

HTTP POST Example:
```
POST /batch HTTP/1.1
Content-Type: application/json
batch: [
	{ method: "GET",	relative_url: "/product/1234" },
	{ method: "GET",	relative_url: "/user/?ids=larry,jill,sally" },
	{ method: "POST",	'content-type': "application/x-www-form-urlencoded", name: 'create-user', relative_url: "/user/?username=john" body: "password=admin"},
```

[Axios](https://github.com/axios/axios) XHR Example:
```javascript
axios.post('/batch', {
	batch: [
		{ method: "GET",	relative_url: "/product/1234" },
		{ method: "GET",	relative_url: "/user/?ids=larry,jill,sally" },
		{ method: "POST",	'content-type': "application/x-www-form-urlencoded", name: 'create-user', relative_url: "/user/?username=john" body: "password=admin"},
	]
});
```

[JQuery](https://github.com/jquery/jquery) XHR Example:
```javascript
$.ajax({
	method: "POST",
	dataType: "json",
	data: {
		batch: [
			{ method: "GET",	relative_url: "/product/1234" },
			{ method: "GET",	relative_url: "/users/?ids=larry,jill,sally" },
			{ method: "POST",	'content-type': "application/x-www-form-urlencoded", name: 'create-user', relative_url: "/user/?username=john" body: "password=admin" },
		]
	}
})
```

For the examples above, the expected response format would be:
```javascript
[
	{
		code: 200,
		body: { product_id: 1234 }
	},
	{
		code: 200,
		body: [{ username: "larry" }, { username: "jill" }, { username: "sally" }]
	},
	{
		code: 200,
		body: { username: "john" }
	},
]
```


## Errors

If a specific request in the batch fails, its response within the array of responses will contain a non-200 code. However, the actual HTTP request to process the batch will still return a 200-OK.


## Specifying Dependencies with JSONP

Sometimes the operation of one request in the batch is dependant on the response of another. This dependancy can be created by specifying a name for a request and then accessing the response of that request using JSONP syntax.

The following example retrieves a user's collection of books which are represented with a `book_id`. The book IDs are then used by a second request in the batch to retrieve information about those books.

HTTP POST Example:
```
POST /batch HTTP/1.1
Content-Type: application/json
batch: [
	{ "method": "GET", "name": "get-user-books", "relative_url": "/user_books/?username=larry" },
	{ "method": "GET", "relative_url": "/book/?book_ids={result=get-user-books:$.book_id}" },
```

In the example above, the first request in the batch is named get-user-books and the second request references that request by name and uses JSONP syntax to extract all the book_ids from the first request in order to know what books to retrieve.

The result of the JSONP expression is exported in csv format. In the example above that may look like: `1234,5678,9012`.

If a request is a dependancy of another request and fails, it will cause the request which is dependant on it to also fail. In the above example if the get-user-books request fails, the request to retrieve books will also fail.


## Headers, Cookies, and Files

Headers, cookies, and files associated with the HTTP request to `/batch/` will be available to all requests in the batch. For example: Sending bearer-token authentication as a header will set that header on all subrequests in the batch, meaning any authenticaiton middleware will be executed for each request in the batch.
