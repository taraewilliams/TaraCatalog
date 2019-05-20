app.controller('ApplicationController', function ($scope, $route, $location, AuthService, AUTH_EVENTS, Session)
{
    /* Set the user */
    AuthService.setUser();

    /* Redirect to login page on unauthorized */
    $scope.$on(AUTH_EVENTS.notAuthenticated, function(event, args) {
        if( !Session.id ) {
            return;
        }

        Session.destroy();
        $location.path('/login');
        alert("Your session either does not exist or has expired. Please login again.");
    });

    $scope.changeStyle = function(color){
        $scope.color_scheme = color;
    }

    $scope.goToPath = function(path, param=null){
        if(param != null){
            path = path + param;
        }
        $location.path(path);
    };

    $scope.isActive = function (paths) {
        if( !Array.isArray(paths) ) {
            paths = [paths]
        }

        for(var i in paths ) {
            var path = paths[i];

            if ($route.current && $route.current.regexp) {
                if( $route.current.regexp.test(path) ) {
                    return true;
                }
            }
        }
        return false;
    };

    $scope.clone = function(obj) {
        if (null == obj || "object" != typeof obj) return obj;
        var copy = obj.constructor();
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
        }
        return copy;
    };

    $scope.isEmpty = function(obj){
      return (obj == '' || typeof(obj) == "undefined" || obj == null);
    };

    $scope.isEmptyObj = function(obj) {
        for(var prop in obj) {
            if(obj.hasOwnProperty(prop)) return false;
        }
        return true;
    };

});
