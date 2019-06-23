app.controller('CreateController', function($scope, CONFIG, RequestService, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }


        /* Set the variables for books/movies/games */
        if($scope.isActive('/books_create')){
            $scope.variables = {
                media_type:"book",
                create_url:CONFIG.api + '/books'
            };

            $http.get(CONFIG.api + '/books/authors/all')
            .then(function(response) {
                $scope.authors = response.data;
            });

            $http.get(CONFIG.api + '/books/titles/all')
            .then(function(response) {
                $scope.titles = response.data;
            });

        }else if ($scope.isActive('/movies_create')){
            $scope.variables = {
                media_type:"movie",
                create_url:CONFIG.api + '/movies'
            };
        }else{
            $scope.variables = {
                media_type:"game",
                create_url:CONFIG.api + '/games'
            };

            $http.get(CONFIG.api + '/games/platforms/all')
            .then(function(response) {
                $scope.platforms = response.data;
            });
        }

        $scope.media = getEmptyMedia();

    }

    $scope.createMedia = function(){

        var url = $scope.variables.create_url;

        if ($scope.variables.media_type == "book"){
            delete $scope.media.old_author;
            delete $scope.media.old_title;
        }else if ($scope.variables.media_type == "game"){
            delete $scope.media.old_platform;
        }

        RequestService.post(url, $scope.media, function(data) {
            alert($scope.variables.media_type + " was created.")
            $scope.media = getEmptyMedia();
            window.scrollTo(0,0);

        }, function(error, status) {
            console.log(error.message);
        });

    };

    $scope.updateForSelectValue = function(old_item, field){
        if (field === "title"){
            $scope.media.title = old_item;
        }else if (field === "author"){
            $scope.media.author = old_item;
        }else if (field === "platform"){
            $scope.media.platform = old_item;
        }
    };

    var getEmptyMedia = function(){
        if ($scope.variables.media_type == "book"){
            return {
                title:"",
                old_title:"",
                author:"",
                old_author:"",
                volume:null,
                isbn:"",
                cover_type:"",
                content_type:"",
                location:"",
                image:""
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
                image:""
            };
        }else{
            return {
                title:"",
                platform:"",
                old_platform:"",
                esrb_rating:"none",
                location:"",
                image:""
            };
        }
    };

    $scope.user.$promise.then(init);

});
