app.controller('ResetPasswordController', function($scope,
    AuthService,
    CONFIG,
    RequestService,
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

    $scope.updateUser = function(new_password, new_password_2){

        var new_user = {password: new_password};
        var url = CONFIG.api + CONFIG.api_routes.update_user + $scope.user.id;

        if ($scope.isEmpty(new_password) || $scope.isEmpty(new_password_2)){
            messageCenterService.add(MESSAGE_OPTIONS.warning, "Enter new password", { timeout: CONFIG.messageTimeout });
        } else if(new_password !== new_password_2){
            messageCenterService.add(MESSAGE_OPTIONS.warning, "Passwords do not match", { timeout: CONFIG.messageTimeout });
        }else{
            RequestService.post(url, new_user, function(data) {
                alert("Password was updated.");
                $scope.goToPath('/profile');
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });

        }
    };

    $scope.user.$promise.then(init);
});
