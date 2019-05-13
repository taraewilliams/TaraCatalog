app.service('AuthService', function ($rootScope, $http, $location, CONFIG, Session, RequestService, AUTH_EVENTS)
{
    this.login = function(credentials) {
        var _this = this;
        var url = CONFIG.api + '/auth/login';

        RequestService.post(url, credentials, function(response) {
            console.log("Logged in:", response.data);
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
            console.log("Logged out:", response.data);
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
        Session.userID ? $http.get(CONFIG.api + "/users/" + Session.userID)
        .then(function(response) {
            $rootScope.user = response.data;
            $rootScope.color_scheme = response.data.color_scheme;
        }, function(response){
            console.log("Error");
        }) : null;
    };

});
