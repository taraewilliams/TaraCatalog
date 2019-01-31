app.controller('ApplicationController', function ($scope, $route, $location)
{

    $scope.goToPath = function(path){
        $location.path(path);
    };

});
