<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Book;
use TaraCatalog\Model\Movie;
use TaraCatalog\Model\Game;
use TaraCatalog\Model\Viewer;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/search";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get items for search */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "search"
            ));

            $searchTerm = isset($params["search"]) ? $params["search"] : null;

            $book_params = array(
                "title"          => $searchTerm,
                "author"         => $searchTerm,
                "volume"         => $searchTerm,
                "isbn"           => $searchTerm,
                "cover_type"     => $searchTerm,
                "content_type"   => $searchTerm,
                "location"       => $searchTerm
            );

            $movie_params = array(
                "title"         => $searchTerm,
                "format"        => $searchTerm,
                "edition"       => $searchTerm,
                "content_type"  => $searchTerm,
                "location"      => $searchTerm,
                "season"        => $searchTerm
            );

            $game_params = array(
                "title"         => $searchTerm,
                "platform"      => $searchTerm,
                "location"      => $searchTerm
            );

            $book = Book::get_for_search($user_id, $book_params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            $movie = Movie::get_for_search($user_id, $movie_params);
            if($movie === false || $movie === null) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            $game = Game::get_for_search($user_id, $game_params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }

            $merge = array_merge($book, $movie);
            $items = array_merge($merge, $game);
            usort($items, array("TaraCatalog\Model\Book", "sort_all"));

            APIService::response_success($items);
        });

        /* Get items for viewer search */
        $app->post($resource . "/{id}", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $viewer_id = $session->user->id;
            $creator_id = intval($args['id']);

            /* Check that the viewer has permission to view the creator's games */
            $viewer = Viewer::get_for_creator_and_viewer_id($creator_id, $viewer_id);
            if($viewer === false || $viewer === null) {
                APIService::response_fail("There was a problem getting the items.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "search"
            ));

            $searchTerm = isset($params["search"]) ? $params["search"] : null;

            $book_params = array(
                "title"          => $searchTerm
            );

            $movie_params = array(
                "title"         => $searchTerm
            );

            $game_params = array(
                "title"         => $searchTerm
            );

            $book = Book::get_for_search($creator_id, $book_params);
            if($book === false || $book === null) {
                APIService::response_fail("There was a problem getting the books.", 500);
            }
            $movie = Movie::get_for_search($creator_id, $movie_params);
            if($movie === false || $movie === null) {
                APIService::response_fail("There was a problem getting the movies.", 500);
            }
            $game = Game::get_for_search($creator_id, $game_params);
            if($game === false || $game === null) {
                APIService::response_fail("There was a problem getting the games.", 500);
            }

            $merge = array_merge($book, $movie);
            $items = array_merge($merge, $game);
            usort($items, array("TaraCatalog\Model\Book", "sort_all"));

            APIService::response_success($items);
        });

    });
});
