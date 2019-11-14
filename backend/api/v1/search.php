<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;
use TaraCatalog\Model\Viewer;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/search";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Get media for search */
        $app->post($resource, function () use ($app)
        {
            $session = APIService::authenticate_request($_REQUEST);
            $user_id = $session->user->id;

            $params = APIService::build_params($_REQUEST, null, array(
                "search"
            ));

            $searchTerm = isset($params["search"]) ? $params["search"] : null;

            $book_params = array(
                "title"           => $searchTerm,
                "series"          => $searchTerm,
                "author"          => $searchTerm,
                "volume"          => $searchTerm,
                "isbn"            => $searchTerm,
                "cover_type"      => $searchTerm,
                "content_type"    => $searchTerm,
                "location"        => $searchTerm,
                "genre"           => $searchTerm,
                "notes"           => $searchTerm,
                "complete_series" => $searchTerm
            );

            $movie_params = array(
                "title"           => $searchTerm,
                "format"          => $searchTerm,
                "edition"         => $searchTerm,
                "content_type"    => $searchTerm,
                "location"        => $searchTerm,
                "season"          => $searchTerm,
                "mpaa_rating"     => $searchTerm,
                "genre"           => $searchTerm,
                "notes"           => $searchTerm,
                "running_time"    => $searchTerm,
                "complete_series" => $searchTerm
            );

            $game_params = array(
                "title"           => $searchTerm,
                "platform"        => $searchTerm,
                "location"        => $searchTerm,
                "esrb_rating"     => $searchTerm,
                "genre"           => $searchTerm,
                "notes"           => $searchTerm,
                "complete_series" => $searchTerm
            );

            $conj = "OR";

            $books = Media::get_for_search($user_id, Config::DBTables()->book, $book_params, Constants::default_order()->book, Constants::enum_columns()->book, $conj);
            $movies = Media::get_for_search($user_id, Config::DBTables()->movie, $movie_params, Constants::default_order()->movie, Constants::enum_columns()->movie, $conj);
            $games = Media::get_for_search($user_id, Config::DBTables()->game, $game_params, Constants::default_order()->game, Constants::enum_columns()->game, $conj);

            $merge = array_merge($books, $movies);
            $media = array_merge($merge, $games);
            usort($media, array("TaraCatalog\Model\Media", "sort_all"));

            APIService::response_success($media);
        });

        /* Get media for viewer search */
        $app->post($resource . "/{id}", function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request($_GET);
            $viewer_id = $session->user->id;
            $creator_id = intval($args['id']);
            $status = "approved";

            /* Check that the viewer has permission to view the creator's media */
            if(!Viewer::exists_for_creator_and_viewer_id($creator_id, $viewer_id, $status)){
                APIService::response_fail("You don't have permission to view this list.", 500);
            }

            $params = APIService::build_params($_REQUEST, null, array(
                "search"
            ));

            $searchTerm = isset($params["search"]) ? $params["search"] : null;

            $book_params = array( "title" => $searchTerm );
            $movie_params = array( "title" => $searchTerm );
            $game_params = array( "title" => $searchTerm );

            $conj = "OR";

            $books = Media::get_for_search($creator_id, Config::DBTables()->book, $book_params, Constants::default_order()->book, Constants::enum_columns()->book, $conj);
            $movies = Media::get_for_search($creator_id, Config::DBTables()->movie, $movie_params, Constants::default_order()->movie, Constants::enum_columns()->movie, $conj);
            $games = Media::get_for_search($creator_id, Config::DBTables()->game, $game_params, Constants::default_order()->game, Constants::enum_columns()->game, $conj);

            $merge = array_merge($books, $movies);
            $media = array_merge($merge, $games);
            usort($media, array("TaraCatalog\Model\Media", "sort_all"));

            APIService::response_success($media);
        });

    });
});
