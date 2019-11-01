app.controller('UpdateController', function($scope, $routeParams, CONFIG, RequestService, $http, AuthService, $route)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        $scope.variables = {
            media_type:media_type,
            get_put_url:CONFIG.api + CONFIG.api_routes["get_single_" + media_type] + $routeParams.id,
            get_genre_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "_column_values"] + 'genre'
        };

        /* Get the select lists for specific columns */
        if(media_type == "book"){

            $http.get(CONFIG.api + CONFIG.api_routes.get_book_column_values + 'author')
            .then(function(response) {
                $scope.authors = response.data;
            });

            $http.get(CONFIG.api + CONFIG.api_routes.get_book_column_values + 'title')
            .then(function(response) {
                $scope.titles = response.data;
            });

            $http.get(CONFIG.api + CONFIG.api_routes.get_book_column_values + 'series')
            .then(function(response) {
                $scope.series = response.data;
            });

        }else if (media_type == "game"){
            $http.get(CONFIG.api + CONFIG.api_routes.get_game_column_values + 'platform')
            .then(function(response) {
                $scope.platforms = response.data;
            });
        }

        $http.get($scope.variables.get_genre_url)
        .then(function(response) {
            $scope.genres = response.data;
        });

        /* Get the media to be updated */
        $http.get($scope.variables.get_put_url)
        .then(function(response) {
            $scope.media = response.data;
            $scope.media_clone = $scope.clone($scope.media);
            $scope.media_resolved = true;
        }, function(error){
            console.log("Error");
        });

    }

    $scope.updateMedia = function(media_clone){

        if ($scope.variables.media_type == "book"){
            delete media_clone.old_author;
            delete media_clone.old_title;
            delete media_clone.old_series;
        }else if ($scope.variables.media_type == "game"){
            delete media_clone.old_platform;
        }
        delete media_clone.old_genre;

        var new_media = removeNotUpdatedFields(media_clone, $scope.media);
        var url = $scope.variables.get_put_url;

        RequestService.post(url, new_media, function(data) {
            alert($scope.variables.media_type + " was updated.");
        }, function(error, status){
            console.log(error.message);
        });

    };

    $scope.updateForSelectValue = function(old_item, field){
        $scope.media_clone[field] = old_item;
    };

    $scope.hasChanged = function(image){
        return ($scope.media.image != image);
    }

    var removeNotUpdatedFields = function(media_clone, media){

        var new_media = {};

        for (var prop in media_clone) {

            if(!media_clone.hasOwnProperty(prop)) continue;

            if(media_clone[prop] != media[prop]){
                new_media[prop] = media_clone[prop];
            }
        }

        return new_media;
    };

    $scope.user.$promise.then(init);

});
