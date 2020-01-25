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

        /* Set the variables for the books/movies/games */
        var media_string = $route.current.originalPath.split("_")[0];
        var media_type = media_string.substring(1, media_string.length - 1);

        variables = {
            media_type:media_type,
            create_url:CONFIG.api + CONFIG.api_routes["create_" + media_type],
            get_column_list_url:CONFIG.api + CONFIG.api_routes["get_" + media_type + "_column_values"]
        };

        /* Get the select list variables */
        $scope.selectListVariables = getSelectListVariables(media_type);

        /* Get the select lists for specific columns */
        var columns = getSelectListColumns(media_type);
        columns.forEach(column => {
            $http.get(variables.get_column_list_url + column)
            .then(function(response) {
                $scope.selectListVariables[column] = response.data;
            }, function(response){
                messageCenterService.add(response.data.type, response.data.message, { timeout: CONFIG.messageTimeout });
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
            messageCenterService.add(MESSAGE_OPTIONS.success, variables.media_type + " was created.", { timeout: CONFIG.messageTimeout });
            $scope.media = getEmptyMedia();
            window.scrollTo(0,0);
        }, function(response) {
            messageCenterService.add(response.data.type, response.data.message, { timeout: CONFIG.messageTimeout });
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

    var getSelectListVariables = function(mediaType){
        var selectVariables = {
            book:{
                author:[],
                title:[],
                series:[]
            },
            game:{
                platform:[]
            }
        };
        return selectVariables[mediaType];
    };

    var getEmptyMedia = function(){
        var emptyMedia = {
            book:{
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
            },
            movie:{
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
            },
            game:{
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
            }
        };
        return emptyMedia[variables.media_type];
    };

    /***************************************/
    /**************** Init *****************/
    /***************************************/

    $scope.user.$promise.then(init);

});
