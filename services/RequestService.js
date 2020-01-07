app.service('RequestService', function ($http)
{
    this.post = function(url, data, successCallback, errorCallback)
    {
        var fd = new FormData();
        for(var key in data) {
            fd.append(key, data[key]);
        }

        return $http.post(url, fd, {
            transformRequest: angular.identity,
            headers: {'Content-Type': undefined}

        }).then(successCallback, errorCallback);
    };

});
