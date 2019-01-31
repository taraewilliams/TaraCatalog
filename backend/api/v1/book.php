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
            $books = Book::get_all();
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get a set number of books */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);

            $books = Book::get_all_with_limit($offset, $limit);
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get a single book */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $id = intval($args['id']);
            $book = Book::get_from_id($id);

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
            $books = Book::get_all_content_type_counts();
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $books = Book::count_books();
            if($books === false) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            APIService::response_success($books);
        });

        /* Get all authors */
        $app->get($resource . '/authors/all', function ($request, $response, $args) use ($app)
        {
            $authors = Book::get_authors();
            if($authors === false) {
                APIService::response_fail("There was a problem getting the authors.", 500);
            }
            APIService::response_success($authors);
        });

        /* Get all titles */
        $app->get($resource . '/titles/all', function ($request, $response, $args) use ($app)
        {
            $titles = Book::get_titles();
            if($titles === false) {
                APIService::response_fail("There was a problem getting the titles.", 500);
            }
            APIService::response_success($titles);
        });


        /* ========================================================== *
         * POST
         * ========================================================== */
        $app->post($resource, function () use ($app)
        {
            $params = APIService::build_params($_REQUEST, array(
                "title"
            ), array(
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "location"
            ));

            $book = Book::create_from_data($params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem creating the book.", 500);
            }
            APIService::response_success($book);
        });

        /* ========================================================== *
         * PUT
         * ========================================================== */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $params = APIService::build_params($_REQUEST, null, array(
              "title",
              "author",
              "volume",
              "isbn",
              "cover_type",
              "content_type",
              "location"
            ));

            $id = intval($args["id"]);

            $book = Book::update($id, $params);
            if($book === false) {
                APIService::response_fail("There was an error saving the book.", 500);
            }
            if($book === null) {
                APIService::response_fail("The requested book does not exist.", 404);
            }
            APIService::response_success($book);
        });


        /* ========================================================== *
         * DELETE
         * ========================================================== */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $id = intval($args["id"]);
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
