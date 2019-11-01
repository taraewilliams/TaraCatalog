app.controller('CreateController', function($scope, CONFIG, RequestService, $http, AuthService, $route)
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
            create_url:CONFIG.api + CONFIG.api_routes["create_" + media_type],
            get_genre_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "_column_values"] + 'genre'
        };

        console.log($scope.variables);

        /* Get the select lists for specific columns */
        if($scope.variables.media_type == "book"){

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

        }else if ($scope.variables.media_type == "game"){
            $http.get(CONFIG.api + CONFIG.api_routes.get_game_column_values + 'platform')
            .then(function(response) {
                $scope.platforms = response.data;
            });
        }

        $http.get($scope.variables.get_genre_url)
        .then(function(response) {
            $scope.genres = response.data;
        });

        $scope.media = getEmptyMedia();

    }

    $scope.createMedia = function(){

        var url = $scope.variables.create_url;

        if ($scope.variables.media_type == "book"){
            delete $scope.media.old_author;
            delete $scope.media.old_title;
            delete $scope.media.old_series;
        }else if ($scope.variables.media_type == "game"){
            delete $scope.media.old_platform;
        }
        delete $scope.media.old_genre;

        RequestService.post(url, $scope.media, function(data) {
            alert($scope.variables.media_type + " was created.")
            $scope.media = getEmptyMedia();
            window.scrollTo(0,0);

        }, function(data) {
            console.log(data);
        });

    };

    $scope.updateForSelectValue = function(old_item, field){
        $scope.media[field] = old_item;
    };

    var getEmptyMedia = function(){
        if ($scope.variables.media_type == "book"){
            return {
                title:"",
                old_title:"",
                series:"",
                old_series:"",
                author:"",
                old_author:"",
                volume:null,
                isbn:"",
                cover_type:"",
                content_type:"",
                location:"",
                image:"",
                notes:"",
                genre:"",
                old_genre:""
            };
        }else if ($scope.variables.media_type == "movie"){
            return {
                title:"",
                edition:"",
                season:"",
                format:"",
                content_type:"",
                mpaa_rating:"none",
                location:"",
                image:"",
                notes:"",
                genre:"",
                old_genre:""
            };
        }else{
            return {
                title:"",
                platform:"",
                old_platform:"",
                esrb_rating:"none",
                location:"",
                image:"",
                notes:"",
                genre:"",
                old_genre:""
            };
        }
    };

    $scope.user.$promise.then(init);

});
