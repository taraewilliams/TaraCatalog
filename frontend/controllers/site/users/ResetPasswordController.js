app.controller('ResetPasswordController', function($scope, AuthService, Session, $http, CONFIG, RequestService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    function init(){

        $http.get(CONFIG.api + '/users/' + Session.userID)
        .then(function(response) {
            $scope.user = response.data;
            $scope.user_clone = $scope.clone($scope.user);
        });

    }

    $scope.updateUser = function(new_password, new_password_2){

        var new_user = {password: new_password};
        var url = CONFIG.api + '/users/' + $scope.user.id;

        if (new_password != new_password_2){
            alert("Passwords do not match");
        }else{
            RequestService.post(url, new_user, function(data) {
                alert("Password was updated.");
                $scope.goToPath('/profile');
            }, function(response){
                alert(response.data.message);
            });

        }
    };

    init();
});
