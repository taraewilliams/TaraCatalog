app.controller('ProfileController', function($scope,
    AuthService,
    $http,
    CONFIG,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }
    }

    /* Delete item */
    $scope.deleteUser = function(userID){

        if (confirm("Delete your profile?")){
            var url = CONFIG.api + CONFIG.api_routes.delete_user + userID;

            $http.delete(url)
            .then(function(response) {
                $scope.successMessage("The user was deleted.");
                Session.destroy();
                $scope.goToPath("/login");
            }, function(response){
                $scope.errorMessage(response.data.message, response.data.type);
            });
        }
    };

    $scope.user.$promise.then(init);
});
