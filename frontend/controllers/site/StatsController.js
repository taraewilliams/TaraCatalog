app.controller('StatsController', function($scope, CONFIG, $http, AuthService)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }

        var urls = ['/books/content_type/count', '/books/cover_type/count','/movies/format/count',
        '/movies/content_type/count', '/games/platform/count'];

        for(i = 0; i < urls.length; i++){
            $http.get(CONFIG.api + urls[i])
            .then(function(response) {
                makePieChartForData(response.data);
            });
        }
    }

    /* Make Pie Charts for Count Data */
    var makePieChartForData = function(items){
        var itemKey = Object.keys(items)[0];
        if (itemKey == 'book_content_type'){
            var dataArray = [['Book Content Type', 'Number of Books']];
            var title = 'Content';
            var html_element = 'BookContentTypeChart';
        }else if(itemKey == 'book_cover_type'){
            var dataArray = [['Book Cover Type', 'Number of Books']];
            var title = 'Cover';
            var html_element = 'BookCoverTypeChart';
        }else if(itemKey == 'movie_format_type'){
            var dataArray = [['Movie Format', 'Number of Movies']];
            var title = 'Format';
            var html_element = 'MovieFormatChart';
        }else if(itemKey == 'movie_content_type'){
            var dataArray = [['Movie Content Type', 'Number of Movies']];
            var title = 'Content';
            var html_element = 'MovieContentTypeChart';
        }else{
            var dataArray = [['Game Platform Type', 'Number of Games']];
            var title = 'Platform';
            var html_element = 'GamePlatformTypeChart';
        }
        makePieChart(items[itemKey], html_element, title, dataArray);
    };

    /* Generic Pie Chart */
    var makePieChart = function(items, html_element, title, dataArray){

        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            for (i = 0; i < items.length; i++){
                dataArray.push([items[i].type, parseInt(items[i].num)]);
            }

            var data = google.visualization.arrayToDataTable(dataArray);

            var options = {
                'title': title,
                'height':400,
                'backgroundColor': 'transparent',
                'titleTextStyle': {
                    fontSize: 20
                }
            };

            var chart = new google.visualization.PieChart(document.getElementById(html_element));
            chart.draw(data, options);
        }
    };

    $scope.user.$promise.then(init);

});
