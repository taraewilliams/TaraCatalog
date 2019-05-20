app.controller('ProfileController', function($scope, AuthService, Session, $http, CONFIG, RequestService)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.user_clone = angular.copy($scope.user);
    }

    /* Delete item */
    $scope.deleteUser = function(userID){

        if (confirm("Delete your profile?")){
            var url = CONFIG.api + "/users/" + userID;

            $http.delete(url)
            .then(function(response) {
                alert("The user was deleted.");
                $scope.goToPath("/login");
            }, function(response){
                console.log("Error");
            });
        }
    };

    $scope.user.$promise.then(init);
});
