<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

/* Requests */


/* GET */

/*

1. books/{id}
    Gets a single book for a user for its ID and user ID.
    Input: id (book ID)
    Output: Book object

2. books
    Gets all books for a user for the user ID.
    Input: none
    Output: Book object array

3. books/todo/list/{todo}
    Gets all books on the todo list or not on the todo list for a user.
    Input: todo (0 or 1)
    Output: Book object array

4. /limit/{offset}/{limit}
    Gets a set number of books with a limit and an offset for a user.
    Input: offset, limit
    Output: Book object array

5. books/order_by/{order}
    Gets all books ordered by a specific field for a user.
    Input: order (the field to order by)
    Output: Book object array

6. books/filter
    Gets books that match the filter options for each field for a user.
    Input: (optional) title, author, volume, isbn, cover_type, content_type, location, genre
    Output: Book object array

7. books/filter/{order}
    Gets books that match the filter options for each field ordered by a specific field for a user.
    Input: (required) order
        (optional) title, author, volume, isbn, cover_type, content_type, location, genre
    Output: Book object array

8. books/count/all
    Gets the count of all books for a user.
    Input: none
    Output: Book count

9. books/column_count/{column}
    Gets the count of all books grouped by distinct column for a user.
    Input: column name
    Output: Book counts

10. books/column_values/{column}
    Gets all distinct column values from all books for a user.
    Input: column name
    Output: Array of column values

*/


/* POST */

/*

1. books
    Creates a new book.
    Input: (required) title
        (optional) author, volume, isbn, cover_type, content_type, notes, location, todo_list, image, genre
    Output: Book object

2. books/{id}
    Updates a book.
    Input: (required) id (book ID)
        (optional) title, author, volume, isbn, cover_type, content_type, notes, location, todo_list, image, genre
    Output: true or false (success or failure)
*/


/* DELETE */

/*

1. books/{id}
    Deletes a book.
    Input: id (book ID)
    Output: true or false (success or failure)
*/

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/books";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* ========================================================== *
        * GET BOOKS
        * ========================================================== */

        /* 1. Get a single book */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);
            $book = Media::get_from_id($user_id, $id, Config::DBTables()->book);
            APIService::response_success($book);
        });

        /* 2. Get all books for a user */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order_by = Constants::default_order()->book;
            $books = Media::get_all($user_id, Config::DBTables()->book, $order_by);
            APIService::response_success($books);
        });

        /* 3. Get all books on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $order_by = Constants::default_order()->book;
            $books = Media::get_all_on_todo_list($user_id, $todo, Config::DBTables()->book, $order_by);
            APIService::response_success($books);
        });

        /* 4. Get a set number of books */
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

        /* 5. Get all books ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $books = Media::get_all_with_order($user_id, Config::DBTables()->book, $order);
            APIService::response_success($books);
        });

        /* 6. Get books for multiple filters */
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
                "location",
                "genre"
            ));

            $order_by = Constants::default_order()->book;
            $enum_keys = Constants::enum_columns()->book;
            $books = Media::get_for_search($user_id, Config::DBTables()->book, $params, $order_by, $enum_keys);
            APIService::response_success($books);
        });

        /* 7. Get books for multiple filters with order */
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
                "location",
                "genre"
            ));

            $order_by = "ORDER BY " . $order;
            $enum_keys = Constants::enum_columns()->book;
            $books = Media::get_for_search($user_id, Config::DBTables()->book, $params, $order_by, $enum_keys);
            APIService::response_success($books);
        });

        /* ========================================================== *
        * GET BOOK COUNTS
        * ========================================================== */

        /* 8. Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Media::count_media($user_id, Config::DBTables()->book);
            APIService::response_success($books);
        });

        /* 9. Count books with different column values */
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

        /* 10. Get all distinct values for a column */
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

        /* 1. Create a book */
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
                "notes",
                "location",
                "todo_list",
                "genre"
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

        /* 2. Update a book */
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
                "author",
                "volume",
                "isbn",
                "cover_type",
                "content_type",
                "notes",
                "location",
                "todo_list",
                "genre"
            ));

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

        /* 1. Delete a book */
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
