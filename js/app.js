angular.module('matchups',['ngRoute', 'ngCookies'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/about', {
        templateUrl: 'views/about.html'
    }).
    when('/contact', {
        templateUrl: 'views/contact.html'
    }).
    when('/', {
        templateUrl: 'views/matchups.html',
        controller: 'matchupsCtrl'
    }).
    otherwise({
        redirectTo: '/'
    });
}])
.controller('matchupsCtrl',['$cookies', function($scope, $http, $cookies) {
    $scope.data = {};

    $scope.selectedCharacter = "Fox";
    $scope.selectedOpponent  = "Falco";

    if($cookies.selectedCharacter) {
        console.log('character set');
        $scope.selectedCharacter = $cookies.selectedCharacter;
    }
    if($cookies.selectedOpponent) {
        console.log('opponent set');
        $scope.selectedOpponent = $cookies.selectedOpponent;
    }

    $scope.data.characters = ["Fox", "Falco", "Sheik", "Marth"];

    $scope.matchups = function() {
        $cookies.selectedCharacter = $scope.selectedCharacter;
        $cookies.selectedOpponent  = $scope.selectedOpponent;

        console.log('fetching info');
        $http.get('php/matchups.php?character='+$scope.selectedCharacter+'&opponent='+$scope.selectedOpponent)
        .success(function (data, status) {
            console.log('fetching info worked');
            console.log(data);
            console.log(status);
            $scope.data.characterTips = data.characterTips;
            $scope.data.opponentTips = data.opponentTips;
            $scope.data.characterPercentage = data.percentage;
            $scope.data.opponentPercentage = 100 - data.percentage;
        })
        .error(function (data, status){
            console.log('fetching info failed');
        });
    }

    $scope.matchups();
}]);