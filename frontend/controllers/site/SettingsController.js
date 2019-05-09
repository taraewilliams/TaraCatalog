app.controller('SettingsController', function($scope, AuthService)
{
    /* Redirect if not logged in */
    if( AuthService.redirectOnUnauthorized() ) {
        return;
    }

    $scope.color = $scope.color_scheme;
});
