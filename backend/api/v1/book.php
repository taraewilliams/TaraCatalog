<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;

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
    Input: (optional) title, author, volume, isbn, cover_type, content_type, location
    Output: Book object array

7. books/filter/{order}
    Gets books that match the filter options for each field ordered by a specific field for a user.
    Input: (required) order
        (optional) title, author, volume, isbn, cover_type, content_type, location
    Output: Book object array

8. books/count/all
    Gets the count of all books for a user.
    Input: none
    Output: Book count

9. books/content_type/count
    Gets the count of all books grouped by distinct content type for a user.
    Input: none
    Output: Book counts

10. books/cover_type/count
    Gets the count of all books grouped by distinct cover type for a user.
    Input: none
    Output: Book counts

11. books/authors/all
    Gets all distinct authors from all books for a user.
    Input: none
    Output: Array of authors

12. books/titles/all
    Gets all distinct titles from all books for a user.
    Input: none
    Output: Array of titles
*/


/* POST */

/*

1. books
    Creates a new book.
    Input: (required) title
        (optional) author, volume, isbn, cover_type, content_type, notes, location, todo_list, image
    Output: Book object

2. books/{id}
    Updates a book.
    Input: (required) id (book ID)
        (optional) title, author, volume, isbn, cover_type, content_type, notes, location, todo_list, image
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

        /* Get a single book */
        $app->get($resource . '/{id}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $id = intval($args['id']);
            $book = Book::get_from_id($user_id, $id);
            APIService::response_success($book);
        });

        /* Get all books for a user */
        $app->get($resource, function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Book::get_all($user_id);
            APIService::response_success($books);
        });

        /* Get all books on the todo list or not on the todo list */
        $app->get($resource . '/todo/list/{todo}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $todo = intval($args['todo']);
            $books = Book::get_all_on_todo_list($user_id, $todo);
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
            APIService::response_success($books);
        });

        /* Get all books ordered by a specific field */
        $app->get($resource . '/order_by/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $order = $args['order'];
            $books = Book::get_all_with_order($user_id, $order);
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

            $book = Book::get_for_search($user_id, $params);
            APIService::response_success($book);
        });

        /* Get books for multiple filters with order */
        $app->post($resource. '/filter/{order}', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $conj = "AND";

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

            $book = Book::get_for_search($user_id, $params, $conj, $order);
            APIService::response_success($book);
        });

        /* ========================================================== *
        * GET BOOK COUNTS
        * ========================================================== */

        /* Count all books */
        $app->get($resource . '/count/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Book::count_books($user_id);
            APIService::response_success($books);
        });

        /* Count books with different content types */
        $app->get($resource . '/content_type/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Book::get_all_content_type_counts($user_id);
            APIService::response_success($books);
        });

        /* Count books with different cover types */
        $app->get($resource . '/cover_type/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $books = Book::get_all_cover_type_counts($user_id);
            APIService::response_success($books);
        });

        /* ========================================================== *
        * GET ALL DISTINCT VALUES FOR A COLUMN
        * ========================================================== */

        /* Get all authors */
        $app->get($resource . '/authors/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $authors = Book::get_authors($user_id);
            APIService::response_success($authors);
        });

        /* Get all titles */
        $app->get($resource . '/titles/all', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $user_id = $session->user->id;
            $titles = Book::get_titles($user_id);
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
                "notes",
                "location",
                "todo_list"
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
                "notes",
                "location",
                "todo_list"
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
                $params['image'] = Media::set_image($files, $title, '/books');
            }

            $book = Book::update($user_id, $id, $params);
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
                APIService::response_fail("There was a problem deleting the book.", 500);
            }

            $result = Book::set_active($id, 0);
            APIService::response_success(true);
        });
    });
});
