app.controller('HomeController', function($scope, RequestService, CONFIG, AuthService, $http)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.searchTerm = "";
    }

    $scope.search = function(searchTerm){

        var search = { search: searchTerm };

        RequestService.post(CONFIG.api + CONFIG.api_routes.get_media_search, search, function(response) {
            $scope.items = response.data;
        }, function(error, status){
            console.log(error.message);
        });

    };

    init();

});
