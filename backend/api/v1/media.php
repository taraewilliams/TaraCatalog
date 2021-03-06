<?php

use TaraCatalog\Service\APIService;
use TaraCatalog\Model\Media;
use TaraCatalog\Config\Config;
use TaraCatalog\Config\Constants;

$app->group('/api', function () use ($app) {
    $app->group('/v1', function () use ($app) {
        $resource = "/media";

        /* ========================================================== *
        * GET
        * ========================================================== */

        /* Count media with different locations */
        $app->get($resource . '/location/count', function ($request, $response, $args) use ($app)
        {
            $session = APIService::authenticate_request_creator($_GET);
            $user_id = $session->user->id;

            $media_counts = Media::get_all_media_location_counts($user_id);
            $total_books = intval(Media::count_media($user_id, Config::DBTables()->book)["num"]);
            $total_movies = intval(Media::count_media($user_id, Config::DBTables()->movie)["num"]);
            $total_games = intval(Media::count_media($user_id, Config::DBTables()->game)["num"]);

            $location_strings = array();
            $row_order = array("book", "movie", "game");
            $book_index = 0;
            $movie_index = 1;
            $game_index = 2;

            foreach($media_counts["media_locations"] as $count){
                $location_type = $count["type"];
                if (!in_array($location_type, $location_strings)){
                    array_push($location_strings, $location_type);
                    $location_types[$location_type] = array($location_type, 0, 0, 0);
                }
                if ($count["media"] == $row_order[$book_index]){
                    $location_types[$location_type][$book_index+1] = intval($count["num"])/$total_books * 100;
                }else if ($count["media"] == $row_order[$movie_index]){
                    $location_types[$location_type][$movie_index+1] = intval($count["num"])/$total_movies * 100;
                }else{
                    $location_types[$location_type][$game_index+1] = intval($count["num"])/$total_games * 100;
                }
            }
            APIService::response_success($location_types);
        });

    });
});
