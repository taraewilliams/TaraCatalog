app.controller('ApplicationController', function ($scope,
    $route,
    $location,
    AuthService,
    AUTH_EVENTS,
    Session,
    messageCenterService,
    MESSAGE_OPTIONS)
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
        messageCenterService.add(MESSAGE_OPTIONS.danger, "Your session either does not exist or has expired. Please login again.", { timeout: CONFIG.messageTimeout });
    });

    $scope.menuOpen = false;

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

    /* Add alphabetical letters between the titles to organize */
    $scope.addLettersToTitles = function(items, sortVal){

        var items_clone = $scope.clone(items);
        var added_letters = 0;

        for (var i = 0; i < items.length - 1; i++){
            var item = items[i];
            var next_item = items[i + 1];

            if (sortVal === "title"){
                var prev_letter = item[sortVal].charAt(0).toUpperCase();
                var curr_letter = next_item[sortVal].charAt(0).toUpperCase();
            }else{
                var prev_word = $scope.isEmpty(item.series) ? item.title : item.series;
                var prev_letter = prev_word.charAt(0).toUpperCase();

                var curr_word = $scope.isEmpty(next_item.series) ? next_item.title : next_item.series;
                var curr_letter = curr_word.charAt(0).toUpperCase();
            }

            if (prev_letter !== curr_letter){
                var index = (i + 1) + added_letters;
                var letter = {
                    title: curr_letter,
                    isHeader: 1
                };
                items_clone.splice(index, 0, letter);
                added_letters += 1;
            }
        }
        return items_clone;
    };


    /* Mobile function to toggle the hamburger menu */

    $scope.toggleMenu = function(){
        $scope.menuOpen = !$scope.menuOpen;

    };

});
