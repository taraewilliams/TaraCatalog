app.controller('LoginController', function($scope, $rootScope, AuthService, AUTH_EVENTS, $location)
{
    function init() {

        if( AuthService.redirectOnAuthorized() ) {
          return;
        }

        $scope.credentials = {
          username:'',
          password:''
        };
    }

    $scope.login = function(credentials) {
        AuthService.login(credentials);
    };

    init();

});
