app.service('AuthService', function ($rootScope,
    $http,
    $location,
    CONFIG,
    Session,
    RequestService,
    AUTH_EVENTS,
    User,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    this.login = function(credentials) {
        var _this = this;
        var url = CONFIG.api + CONFIG.api_routes.login;

        RequestService.post(url, credentials, function(response) {
            Session.create(response.data.id, response.data.token, response.data.user.id);
            _this.setUser();
            $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
            $location.path('/');
        }, function(response) {
            messageCenterService.add(MESSAGE_OPTIONS.danger, "Error logging in: " + response.data.message, { timeout: CONFIG.messageTimeout });
            $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
            $location.path('/login');
        });
    };

    this.logout = function() {
        var url = CONFIG.api + CONFIG.api_routes.logout;

        RequestService.post(url, {session_id: Session.id}, function(response) {
            Session.destroy();
            $location.path('/login');
        }, function(response) {
            messageCenterService.add(MESSAGE_OPTIONS.danger, "Error logging out: " + response.data.message, { timeout: CONFIG.messageTimeout });
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

    this.redirectOnNonAdmin = function(path)
    {
        path = (typeof path !== 'undefined') ? path : '/';

        if( $rootScope.user.is_admin !== true ) {
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
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            Session.destroy();
            $location.path('/login');
        }) : null;
    };

});
