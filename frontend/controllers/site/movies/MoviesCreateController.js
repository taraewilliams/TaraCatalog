app.controller('MoviesCreateController', function($scope, CONFIG, RequestService, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        $scope.movie = {
            title:"",
            edition:"",
            season:"",
            format:"",
            content_type:"",
            mpaa_rating:"none",
            location:"",
            image:""
        };
    }

    $scope.createMovie = function(){

        var url = CONFIG.api + '/movies';

        if(!$scope.isEmpty($scope.movie.title) && !$scope.isEmpty($scope.movie.format))
        {
            RequestService.post(url, $scope.movie, function(data) {
                alert("Movie was created.")
                clearMovie();
                window.scrollTo(0,0);

            }, function(error, status) {
                console.log(error.message);
            });
        }else{
            alert("Title and format are required.")
        }
    };

    var clearMovie = function(){
        $scope.movie = {
            title:"",
            edition:"",
            season:"",
            format:"",
            content_type:"",
            mpaa_rating:"none",
            location:"",
            image:""
        };
    };

    $scope.user.$promise.then(init);

});
