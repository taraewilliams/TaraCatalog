<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/books";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* ========================================================== *
        * GET BOOKS
        * ========================================================== */

        /* Get a single book */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $id = intval($args['id']);
                $book = Media::get_from_id($user_id, $id, Config::DBTables()->book);
                APIService::response_success($book);
            } else{
                APIService::response_fail("Must be a creator to get book.", 401);
            }
        });

        /* Get all books for a user */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $order_by = Constants::default_order()->book;
                $books = Media::get_all($user_id, Config::DBTables()->book, $order_by);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* Get all books on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $todo = intval($args['todo']);
                $order_by = Constants::default_order()->book;
                $books = Media::get_all_on_todo_list($user_id, $todo, Config::DBTables()->book, $order_by);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* Get a set number of books */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $offset = intval($args['offset']);
                $limit = intval($args['limit']);
                $order_by = Constants::default_order()->book;
                $books = Media::get_all_with_limit($user_id, Config::DBTables()->book, $order_by, $offset, $limit);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* Get all books ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $order = $args['order'];
                $books = Media::get_all_with_order($user_id, Config::DBTables()->book, $order);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* Get books for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            if ($session->user->role === Constants::user_role()->creator){

                $params = APIService::build_params($_REQUEST, null, array(
                    "title",
                    "series",
                    "author",
                    "volume",
                    "isbn",
                    "cover_type",
                    "content_type",
                    "location",
                    "genre",
                    "complete_series"
                ));

                $order_by = Constants::default_order()->book;
                $enum_keys = Constants::enum_columns()->book;
                $books = Media::get_for_search($user_id, Config::DBTables()->book, $params, $order_by, $enum_keys);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* Get books for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            if ($session->user->role === Constants::user_role()->creator){

                $order = $args['order'];
                $params = APIService::build_params($_REQUEST, null, array(
                    "title",
                    "series",
                    "author",
                    "volume",
                    "isbn",
                    "cover_type",
                    "content_type",
                    "location",
                    "genre",
                    "complete_series"
                ));

                $order_by = "ORDER BY " . $order;
                $enum_keys = Constants::enum_columns()->book;
                $books = Media::get_for_search($user_id, Config::DBTables()->book, $params, $order_by, $enum_keys);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to get books.", 401);
            }
        });

        /* ========================================================== *
        * GET BOOK COUNTS
        * ========================================================== */

        /* Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $books = Media::count_media($user_id, Config::DBTables()->book);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to count books.", 401);
            }
        });

        /* Count books with different column values */
        $app->get($resource . '/column_count/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $column_name = $args["column"];
                $header = "book_" . $column_name;
                $books = Media::get_counts_for_column(Config::DBTables()->book, $user_id, $column_name, $header);
                APIService::response_success($books);
            }else{
                APIService::response_fail("Must be a creator to count books.", 401);
            }
        });

        /* ========================================================== *
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all distinct values for a column */
        $app->get($resource . '/column_values/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            if ($session->user->role === Constants::user_role()->creator){
                $column_name = $args["column"];
                $column_values = Media::get_distinct_for_column($user_id, Config::DBTables()->book, $column_name);
                APIService::response_success($column_values);
            }else{
                APIService::response_fail("Must be a creator to get book column values.", 401);
            }
        });

        /* ========================================================== *
        * POST
        * ========================================================== */

        /* Create a book */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;
            $username = $session->user->username;

            if ($session->user->role === Constants::user_role()->creator){

                $params = APIService::build_params($_REQUEST, array(
                    "title"
                ), array(
                    "series",
                    "author",
                    "volume",
                    "isbn",
                    "cover_type",
                    "content_type",
                    "notes",
                    "location",
                    "todo_list",
                    "genre",
                    "complete_series"
                ));
                $params["user_id"] = $user_id;

                /* Check that enums are set to valid values */
                $enum_property_list = array(
                    array("property" => "cover_type", "enum" => Constants::book_cover_type()),
                    array("property" => "content_type", "enum" => Constants::book_content_type()),
                    array("property" => "location", "enum" => Constants::media_location()),
                    array("property" => "complete_series", "enum" => Constants::media_complete_series())
                );

                if(!Media::are_valid_enums($enum_property_list, $params)){
                    APIService::response_fail("There was a problem setting the enums.", 500);
                }

                /* Set image */
                $files = APIService::build_files($_FILES, null, array( "image" ));

                if(isset($files['image'])) {
                    $params['image'] = Media::set_image($files, $params["title"], '/' . $username . '/books');
                }

                /* Create book */
                $book = Book::create_from_data($params);
                APIService::response_success($book);

            }else{
                APIService::response_fail("Must be a creator to create a book.", 401);
            }
        });

        /* ========================================================== *
        * PUT
        * ========================================================== */

        /* Update a book */
        $app->post($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;
            $username = $session->user->username;

            if ($session->user->role === Constants::user_role()->creator){

                $id = intval($args["id"]);
                if (!Media::get_from_id($user_id, $id, Config::DBTables()->book)){
                    APIService::response_fail("There was a problem updating the book.", 500);
                }

                $params = APIService::build_params($_REQUEST, null, array(
                    "title",
                    "series",
                    "author",
                    "volume",
                    "isbn",
                    "cover_type",
                    "content_type",
                    "notes",
                    "location",
                    "todo_list",
                    "genre",
                    "complete_series"
                ));

                /* Check that enums are set to valid values */
                $enum_property_list = array(
                    array("property" => "cover_type", "enum" => Constants::book_cover_type()),
                    array("property" => "content_type", "enum" => Constants::book_content_type()),
                    array("property" => "location", "enum" => Constants::media_location()),
                    array("property" => "complete_series", "enum" => Constants::media_complete_series())
                );

                if(!Media::are_valid_enums($enum_property_list, $params)){
                    APIService::response_fail("There was a problem setting the enums.", 500);
                }

                /* Set image */
                $files = APIService::build_files($_FILES, null, array( "image" ));

                if(isset($params["title"])){
                    $title = $params["title"];
                }else{
                    /* Get the book's title for it's ID */
                    $title = Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->book, "title");
                }

                if(isset($files['image'])) {
                    $params['image'] = Media::set_image($files, $title, '/' . $username . '/books');
                }

                /* Update book */
                $book = Media::update($user_id, $id, $params, Config::DBTables()->book);
                APIService::response_success($book);
            }else{
                APIService::response_fail("Must be a creator to update a book.", 401);
            }
        });


        /* ========================================================== *
        * DELETE
        * ========================================================== */

        /* Delete a book */
        $app->delete($resource . '/{id}', function ($response, $request, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;

            if ($session->user->role === Constants::user_role()->creator){

                $id = intval($args['id']);

                if (!Media::get_from_id($user_id, $id, Config::DBTables()->book)){
                    APIService::response_fail("There was a problem deleting the book.", 500);
                }

                $result = Media::delete_for_id($id, $user_id, Config::DBTables()->book);
                APIService::response_success(true);
            }else{
                APIService::response_fail("Must be a creator to delete a book.", 401);
            }
        });
    });
});
