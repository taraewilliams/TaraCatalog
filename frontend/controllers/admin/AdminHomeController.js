app.controller('AdminHomeController', function($scope,
    $http,
    CONFIG,
    AuthService,
    RequestService,
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
            $scope.users_resolved = true;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

        $http.get(CONFIG.api + CONFIG.api_routes.get_inactive_users)
        .then(function(response) {
            $scope.inactive_users = response.data;
            $scope.inactive_users_resolved = true;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    /* Make user an admin or remove admin access */
    $scope.updateUser = function(userID, isAdmin){

        var update_field = { is_admin:isAdmin };
        var url = CONFIG.api + CONFIG.api_routes.update_user_admin + userID;

        RequestService.post(url, update_field, function(data) {
            messageCenterService.add(MESSAGE_OPTIONS.success, "User was updated.", { timeout: CONFIG.messageTimeout });
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    };

    /* Delete user */
    $scope.deleteUser= function(userID){

        if (confirm("Delete this user?")){
            var url = CONFIG.api + CONFIG.api_routes.delete_user_admin + userID;

            $http.delete(url)
            .then(function(response) {
                messageCenterService.add(MESSAGE_OPTIONS.success, "User was deleted.", { timeout: CONFIG.messageTimeout });
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }
    };

    $scope.user.$promise.then(init);

});
