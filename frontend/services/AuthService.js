app.service('AuthService', function ($rootScope, $http, $location, CONFIG, Session, RequestService, AUTH_EVENTS, User)
{
    this.login = function(credentials) {
        var _this = this;
        var url = CONFIG.api + '/auth/login';

        RequestService.post(url, credentials, function(response) {
            Session.create(response.data.id, response.data.token, response.data.user.id);
            _this.setUser();
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            $location.path('/');
        }, function(error) {
            console.log("Error logging in:", error.data.message);
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            alert(error.data.message);
            $location.path('/login');
        });
    };

    this.logout = function() {
        var url = CONFIG.api + '/auth/logout';

        RequestService.post(url, {session_id: Session.id}, function(response) {
            Session.destroy();
            $location.path('/login');
        }, function(error) {
            console.log("Error logging out:", error.data.message);
            Session.destroy();
        });
    };

    this.isAuthenticated = function() {
        return !!Session.id;
    };

    this.redirectOnUnauthorized = function(path)
    {
        path = (typeof path !== 'undefined') ? path : '/login';

        if( !this.isAuthenticated() ) {
            $location.path(path);
            return true;
        }

        return false;
    };

    this.redirectOnViewer = function(path)
    {
        path = (typeof path !== 'undefined') ? path : '/';

        if( $rootScope.user.role !== 'creator' ) {
            $location.path(path);
            return true;
        }
        return false;
    };

    this.redirectOnAuthorized = function(path)
    {
        path = (typeof path !== 'undefined') ? path : '/';

        if( this.isAuthenticated() ) {
            $location.path(path);
            return true;
        }

        return false;
    };

    this.setUser = function()
    {
        $rootScope.user = Session.userID ? User.get({id: Session.userID}, function(){
            $rootScope.userCopy = angular.copy($rootScope.user);
            $rootScope.color_scheme = $rootScope.userCopy.color_scheme;
        }, function(response){
            alert(response.data.message);
            Session.destroy();
            $location.path('/login');
        }) : null;
    };

});
