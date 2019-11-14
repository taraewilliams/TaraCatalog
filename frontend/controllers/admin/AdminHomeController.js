app.controller('AdminHomeController', function($scope,
    $http,
    CONFIG,
    AuthService,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnNonAdmin() ) {
            return;
        }

        $http.get(CONFIG.api + CONFIG.api_routes.get_users)
        .then(function(response) {
            $scope.users = response.data;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

        $http.get(CONFIG.api + CONFIG.api_routes.get_users_inactive)
        .then(function(response) {
            $scope.inactive_users = response.data;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    $scope.user.$promise.then(init);

});
