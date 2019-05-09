<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;


$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/books";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get all books */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $books = Book::get_all($user_id);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get a set number of books */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $offset = intval($args['offset']);
            $limit = intval($args['limit']);

            $books = Book::get_all_with_limit($user_id, $offset, $limit);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get all books not on the read list */
        $app->get($resource . '/read/list/{read}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $read = intval($args['read']);
            $books = Book::get_all_on_read_list($user_id, $read);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get all books ordered by a specific field */
        $app->get($resource . '/order_by/{option}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $option = $args['option'];
            $books = Book::get_all_with_order($user_id, $option);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get books for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "location"
            ));

            $book = Book::get_for_filter_params($user_id, $params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($book);
        });

        /* Get books for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $order = $args['order'];
            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "location"
            ));

            $book = Book::get_for_filter_params($user_id, $params, $order);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($book);
        });

        /* Get a single book */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $id = intval($args['id']);
            $book = Book::get_from_id($user_id, $id);

            if($book === false) {
                APIService::response_fail("There was a problem getting book.", 500);
            }
            if($book === null) {
                APIService::response_fail("The requested book does not exist.", 404);
            }
            APIService::response_success($book);
        });

        /* Count books with different content types */
        $app->get($resource . '/content_type/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $books = Book::get_all_content_type_counts($user_id);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Count books with different cover types */
        $app->get($resource . '/cover_type/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $books = Book::get_all_cover_type_counts($user_id);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $books = Book::count_books($user_id);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get all authors */
        $app->get($resource . '/authors/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $authors = Book::get_authors($user_id);
            if($authors === false) {
                APIService::response_fail("There was a problem getting the authors.", 500);
            }
            APIService::response_success($authors);
        });

        /* Get all titles */
        $app->get($resource . '/titles/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            $titles = Book::get_titles($user_id);
            if($titles === false) {
                APIService::response_fail("There was a problem getting the titles.", 500);
            }
            APIService::response_success($titles);
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a book */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, array(
                "title"
            ), array(
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "location",
                "read_list"
            ));
            $params["user_id"] = $user_id;

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Book::set_image($files, $params["title"]);
            }

            $book = Book::create_from_data($params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem creating the book.", 500);
            }
            APIService::response_success($book);
        });

        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Update a book */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $id = intval($args["id"]);
            if (!Book::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem updating the book.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "title",
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "location",
                "read_list"
            ));

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                $title = Book::get_title_for_id($user_id, $id);
            }

            if(isset($files['image'])) {
                $params['image'] = Book::set_image($files, $title);
            }

            $book = Book::update($user_id, $id, $params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem updating the book.", 500);
            }
            APIService::response_success($book);
        });


        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a book */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);

            if (!Book::get_from_id($user_id, $id)){
                APIService::response_fail("There was a problem updating the book.", 500);
            }

            $result = Book::set_active($id, 0);

            if( $result === false ) {
                APIService::response_fail("There was an error setting the active state of that book.", 500);
            }
            if( $result === null ) {
                APIService::response_fail("The requested book does not exist.", 404);
            }
            APIService::response_success(true);
        });
    });
});
