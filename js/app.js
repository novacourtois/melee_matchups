angular.module('matchups',['ngRoute'])
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
.controller("matchupsCtrl", function($scope, $http) {
    $scope.data = {};

    $scope.selectedCharacter = "Fox";
    $scope.selectedOpponent = "Falco";

    $scope.characterTips = "";
    $scope.opponentTips = "";

    $scope.data.characters = ["Fox", "Falco", "Sheik", "Marth"];

    $scope.matchups = function() {
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
});