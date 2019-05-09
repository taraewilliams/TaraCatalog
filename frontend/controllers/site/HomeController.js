app.controller('HomeController', function($scope, RequestService, CONFIG, AuthService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    function init(){
        $scope.searchTerm = "";
    }

    $scope.search = function(searchTerm){

        var search = { search: searchTerm };

        RequestService.post(CONFIG.api + "/search", search, function(response) {
            $scope.items = response.data;
        }, function(error, status){
            console.log(error.message);
        });

    };

    init();

});
