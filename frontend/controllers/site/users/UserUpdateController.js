app.controller('UserUpdateController', function($scope, AuthService, Session, $http, CONFIG, RequestService)
{

    function init(){

        /* Redirect if not logged in */
        if( AuthService.redirectOnUnauthorized() ) {
            return;
        }

        $scope.user_clone = angular.copy($scope.user);

    }

    $scope.updateUser = function(user_clone){

        var new_user = removeNotUpdatedFields(user_clone, $scope.user);
        var url = CONFIG.api + '/users/' + $scope.user.id;

        RequestService.post(url, new_user, function(data) {
            alert("User was updated.");
        }, function(response){
            alert(response.data.message);
        });
    };

    $scope.hasChanged = function(image){
        return ($scope.user.image != image);
    };

    /* Private Functions */
    var removeNotUpdatedFields = function(user_clone, user){

        var new_user = {};

        for (var prop in user_clone) {

            if(!user_clone.hasOwnProperty(prop)) continue;

            if(user_clone[prop] != user[prop]){
                new_user[prop] = user_clone[prop];
            }
        }

        return new_user;
    };

    $scope.user.$promise.then(init);
});
