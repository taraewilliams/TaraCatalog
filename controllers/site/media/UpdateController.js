app.controller('UpdateController', function($scope,
    $routeParams,
    CONFIG,
    RequestService,
    $http,
    AuthService,
    $route,
    messageCenterService,
    MESSAGE_OPTIONS)
{
    var variables = {};

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        /* Set the variables for the books/movies/games */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        variables = {
            media_type:media_type,
            get_put_url:CONFIG.api + CONFIG.api_routes["get_single_" + media_type] + $routeParams.id,
            get_column_list_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "_column_values"]
        };

        /* Get the select lists for specific columns */
        $scope.selectListVariables = [];
        var columns = getSelectListColumns(media_type);
        columns.forEach(column => {
            $http.get(variables.get_column_list_url + column)
            .then(function(response) {
                $scope.selectListVariables[column] = response.data;
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        });

        /* Get the media to be updated */
        $http.get(variables.get_put_url)
        .then(function(response) {
            $scope.media = response.data;
            $scope.media_clone = $scope.clone($scope.media);
            $scope.media_resolved = true;
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });

    }

    /***************************************/
    /********** Public Functions ***********/
    /***************************************/

    $scope.updateMedia = function(media_clone){

        media_clone = deleteSelectListPlaceholders(media_clone);
        var new_media = removeNotUpdatedFields(media_clone, $scope.media);

        RequestService.post(variables.get_put_url, new_media, function(data) {
            $scope.successMessage(variables.media_type + " was updated.");
        }, function(response){
            $scope.errorMessage(response.data.message, response.data.type);
        });
    };

    $scope.updateForSelectValue = function(old_item, field){
        $scope.media_clone[field] = old_item;
    };

    $scope.hasImageChanged = function(image){
        return ($scope.media.image != image);
    };

    /***************************************/
    /********** Private Functions **********/
    /***************************************/

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

    var deleteSelectListPlaceholders = function(media_clone){
        if (variables.media_type == "book"){
            delete media_clone.old_author;
            delete media_clone.old_title;
            delete media_clone.old_series;
        }else if (variables.media_type == "game"){
            delete media_clone.old_platform;
        }
        delete media_clone.old_genre;
        return media_clone;
    };

    var getSelectListColumns = function(mediaType){
        var selectColumns = {
            book:['author', 'title', 'series', 'genre'],
            movie:['genre'],
            game:['platform', 'genre']
        };
        return selectColumns[mediaType];
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
