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
            $id = intval($args['id']);
            $book = Media::get_from_id($user_id, $id, Config::DBTables()->book);
            APIService::response_success($book);
        });

        /* Get all books for a user */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order_by = Constants::default_order()->book;
            $books = Media::get_all($user_id, Config::DBTables()->book, $order_by);
            APIService::response_success($books);
        });

        /* Get all books on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $order_by = Constants::default_order()->book;
            $books = Media::get_all_on_todo_list($user_id, $todo, Config::DBTables()->book, $order_by);
            APIService::response_success($books);
        });

        /* Get a set number of books */
        $app->get($resource . '/limit/{offset}/{limit}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $offset = intval($args['offset']);
            $limit = intval($args['limit']);
            $order_by = Constants::default_order()->book;
            $books = Media::get_all_with_limit($user_id, Config::DBTables()->book, $order_by, $offset, $limit);
            APIService::response_success($books);
        });

        /* Get all books ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $books = Media::get_all_with_order($user_id, Config::DBTables()->book, $order);
            APIService::response_success($books);
        });

        /* Get books for multiple filters */
        $app->post($resource. '/filter', function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

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
        });

        /* Get books for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

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
        });

        /* ========================================================== *
        * GET BOOK COUNTS
        * ========================================================== */

        /* Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Media::count_media($user_id, Config::DBTables()->book);
            APIService::response_success($books);
        });

        /* Count books with different column values */
        $app->get($resource . '/column_count/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $header = "book_" . $column_name;
            $books = Media::get_counts_for_column(Config::DBTables()->book, $user_id, $column_name, $header);
            APIService::response_success($books);
        });

        /* ========================================================== *
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all distinct values for a column */
        $app->get($resource . '/column_values/{column}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $column_name = $args["column"];
            $column_values = Media::get_distinct_for_column($user_id, Config::DBTables()->book, $column_name);
            APIService::response_success($column_values);
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

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $params["title"], '/books');
            }

            $book = Book::create_from_data($params);
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

            /* Check that enums are valid */
            if(isset($params["cover_type"])){
                if(!Media::is_valid_enum(Constants::book_cover_type(), $params["cover_type"])){
                    APIService::response_fail("Must choose a valid value for cover type.", 400);
                }
            }
            if(isset($params["content_type"])){
                if(!Media::is_valid_enum(Constants::book_content_type(), $params["content_type"])){
                    APIService::response_fail("Must choose a valid value for content type.", 400);
                }
            }
            if(isset($params["location"])){
                if(!Media::is_valid_enum(Constants::media_location(), $params["location"])){
                    APIService::response_fail("Must choose a valid value for location.", 400);
                }
            }
            if(isset($params["complete_series"])){
                if(!Media::is_valid_enum(Constants::media_complete_series(), $params["complete_series"])){
                    APIService::response_fail("Must choose a valid value for complete series.", 400);
                }
            }

            $files = APIService::build_files($_FILES, null, array(
                "image"
            ));

            if(isset($params["title"])){
                $title = $params["title"];
            }else{
                /* Get the book's title for it's ID */
                $title = Media::get_column_value_for_id($user_id, $id, CONFIG::DBTables()->book, "title");
            }

            if(isset($files['image'])) {
                $params['image'] = Media::set_image($files, $title, '/books');
            }

            $book = Media::update($user_id, $id, $params, Config::DBTables()->book);
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

            if (!Media::get_from_id($user_id, $id, Config::DBTables()->book)){
                APIService::response_fail("There was a problem deleting the book.", 500);
            }

            $result = Media::set_active($id, 0, Config::DBTables()->book);
            APIService::response_success(true);
        });
    });
});
