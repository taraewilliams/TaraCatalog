app.controller('StatsController', function($scope,
    CONFIG,
    $http,
    AuthService,
    messageCenterService,
    MESSAGE_OPTIONS)
{

    function init(){

        /* Redirect if not logged in or if user is a viewer only */
        if( AuthService.redirectOnUnauthorized() || AuthService.redirectOnViewer() ) {
            return;
        }


        /* Get total media counts */
        $scope.counts = {
            books:0,
            movies:0,
            games:0
        };

        $http.get(CONFIG.api + CONFIG.api_routes.get_book_count)
        .then(function(response) {
            $scope.counts.books = response.data.num;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

        $http.get(CONFIG.api + CONFIG.api_routes.get_movie_count)
        .then(function(response) {
            $scope.counts.movies = response.data.num;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });

        $http.get(CONFIG.api + CONFIG.api_routes.get_game_count)
        .then(function(response) {
            $scope.counts.games = response.data.num;
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });


        /* Make Pie Charts */
        var urls = getCountUrls();
        for(i = 0; i < urls.length; i++){
            $http.get(CONFIG.api + urls[i])
            .then(function(response) {
                makePieChartForData(response.data);
            }, function(response){
                messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
            });
        }


        /* Make Bar Chart */
        $http.get(CONFIG.api + CONFIG.api_routes.get_media_location_count)
        .then(function(response) {

            var dataArray = [['Location', 'Books', 'Movies', 'Games']];
            var counts = response.data;
            for (var key in counts) {
                dataArray.push(counts[key]);
            }

            var title = 'Location (Percentage)';
            var html_element = 'LocationBarChart';
            makeBarChart(dataArray, html_element, title);
        }, function(response){
            messageCenterService.add(MESSAGE_OPTIONS.danger, response.data.message, { timeout: CONFIG.messageTimeout });
        });
    }

    /* Make Pie Charts for Count Data */
    var makePieChartForData = function(items){
        var itemKey = Object.keys(items)[0];
        var chartItems = getPieChartItems(itemKey);
        makePieChart(items[itemKey], chartItems.html_element, chartItems.title, chartItems.dataArray);
    };

    /* Generic Pie Chart */
    var makePieChart = function(items, html_element, title, dataArray)
    {
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

    var makeBarChart = function(dataArray, html_element, title)
    {
        google.charts.setOnLoadCallback(drawBarChart);

        function drawBarChart() {

            var data = google.visualization.arrayToDataTable(dataArray);

            var options = {
                'title': title,
                'height':400,
                'backgroundColor': 'transparent',
                'titleTextStyle': {
                    fontSize: 20
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById(html_element));
            chart.draw(data, options);
        }
    };

    var getCountUrls = function(){
        return [ CONFIG.api_routes.get_book_column_count + 'content_type',
        CONFIG.api_routes.get_book_column_count + 'cover_type',
        CONFIG.api_routes.get_book_column_count + 'genre',
        CONFIG.api_routes.get_movie_column_count + 'format',
        CONFIG.api_routes.get_movie_column_count + 'content_type',
        CONFIG.api_routes.get_movie_column_count + 'mpaa_rating',
        CONFIG.api_routes.get_movie_mpaa_count_grouped,
        CONFIG.api_routes.get_movie_column_count + 'genre',
        CONFIG.api_routes.get_game_column_count + 'esrb_rating',
        CONFIG.api_routes.get_game_column_count + 'platform',
        CONFIG.api_routes.get_game_column_count + 'genre' ];
    };

    var getPieChartItems = function(itemKey){
        /* Book Charts */
        if (itemKey == 'book_content_type'){
            var dataArray = [['Book Content Type', 'Number of Books']];
            var title = 'Content';
            var html_element = 'BookContentTypeChart';
        }else if(itemKey == 'book_cover_type'){
            var dataArray = [['Book Cover Type', 'Number of Books']];
            var title = 'Cover';
            var html_element = 'BookCoverTypeChart';
        }else if(itemKey == 'book_genre'){
            var dataArray = [['Book Genre', 'Number of Books']];
            var title = 'Genre';
            var html_element = 'BookGenreChart';
        /* Movie Charts */
        }else if(itemKey == 'movie_format'){
            var dataArray = [['Movie Format', 'Number of Movies']];
            var title = 'Format';
            var html_element = 'MovieFormatChart';
        }else if(itemKey == 'movie_content_type'){
            var dataArray = [['Movie Content Type', 'Number of Movies']];
            var title = 'Content';
            var html_element = 'MovieContentTypeChart';
        }else if(itemKey == 'movie_mpaa_rating'){
            var dataArray = [['Movie MPAA/TV Rating', 'Number of Movies']];
            var title = 'MPAA/TV Rating';
            var html_element = 'MovieMPAARatingTypeChart';
        }else if(itemKey == 'movie_mpaa_grouped_rating_type'){
            var dataArray = [['Movie MPAA/TV Rating', 'Number of Movies']];
            var title = 'MPAA/TV Rating Under/Over PG';
            var html_element = 'MovieMPAARatingGroupedTypeChart';
        }else if(itemKey == 'movie_genre'){
            var dataArray = [['Movie Genre', 'Number of Movies']];
            var title = 'Genre';
            var html_element = 'MovieGenreChart';
        /* Game Charts */
        }else if(itemKey == "game_esrb_rating"){
            var dataArray = [['Game ESRB Rating', 'Number of Games']];
            var title = 'ESRB Rating';
            var html_element = 'GameESRBRatingTypeChart';
        }else if(itemKey == "game_platform"){
            var dataArray = [['Game Platform Type', 'Number of Games']];
            var title = 'Platform';
            var html_element = 'GamePlatformTypeChart';
        }else{
            var dataArray = [['Game Genre', 'Number of Games']];
            var title = 'Genre';
            var html_element = 'GameGenreChart';
        }

        return {
            dataArray: dataArray,
            title: title,
            html_element: html_element
        };
    };

    $scope.user.$promise.then(init);

});
