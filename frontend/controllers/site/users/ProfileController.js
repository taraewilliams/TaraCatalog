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

        $scope.user_clone = angular.copy($scope.user);
    }

    /* Delete item */
    $scope.deleteUser = function(userID){

        if (confirm("Delete your profile?")){
            var url = CONFIG.api + CONFIG.api_routes.delete_user + userID;

            $http.delete(url)
            .then(function(response) {
                alert("The user was deleted.");
                $scope.goToPath("/login");
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.user.$promise.then(init);
});
