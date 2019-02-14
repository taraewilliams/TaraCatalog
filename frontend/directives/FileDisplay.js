app.directive('fileDisplay', ['$parse', function($parse){
    return{
        restrict:'A',
        link: function(scope, element, attrs){
            element.bind('change', function(){
                scope.$apply(function(){
                    if(element[0].files && element[0].files[0]){
                        var reader = new FileReader();

                        reader.onload = function(e){
                            $("#" + attrs.name).attr('src', e.target.result);
                        }

                        reader.readAsDataURL(element[0].files[0]);
                    }
                });
            });
        }
    };
}]);
