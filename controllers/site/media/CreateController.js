app.controller('CreateController', function(
    $scope,
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

        /* Get the media type from the URL */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        /* Set the variables for the books/movies/games */
        variables = {
            media_type:media_type,
            create_url:CONFIG.api + CONFIG.api_routes["create_" + media_type],
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

        $scope.media = getEmptyMedia();
    }

    /***************************************/
    /********** Public Functions ***********/
    /***************************************/

    $scope.createMedia = function(){

        deleteSelectListPlaceholders();

        RequestService.post(variables.create_url, $scope.media, function(data) {
            $scope.successMessage(variables.media_type + " was created.");
            $scope.media = getEmptyMedia();
            window.scrollTo(0,0);
        }, function(response) {
            $scope.errorMessage(response.data.message, response.data.type);
        });
    };

    $scope.updateForSelectValue = function(old_item, field){
        $scope.media[field] = old_item;
    };

    /***************************************/
    /********** Private Functions **********/
    /***************************************/

    var deleteSelectListPlaceholders = function(){
        if (variables.media_type == "book"){
            delete $scope.media.old_author;
            delete $scope.media.old_title;
            delete $scope.media.old_series;
        }else if (variables.media_type == "game"){
            delete $scope.media.old_platform;
        }
        delete $scope.media.old_genre;
    };

    var getSelectListColumns = function(mediaType){
        var selectColumns = {
            book:['author', 'title', 'series', 'genre'],
            movie:['genre'],
            game:['platform', 'genre']
        };
        return selectColumns[mediaType];
    };

    var getEmptyMedia = function(){
        if (variables.media_type === 'book'){
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
                old_genre:"",
                complete_series:""
            };
        } else if (variables.media_type === 'movie'){
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
                old_genre:"",
                complete_series:"",
                running_time:null
            };
        } else {
            return {
                title:"",
                platform:"",
                old_platform:"",
                esrb_rating:"none",
                location:"",
                image:"",
                notes:"",
                genre:"",
                old_genre:"",
                complete_series:""
            };
        }
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
