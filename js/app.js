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
.controller('matchupsCtrl', function($scope, $http, $cookieStore) {
    $scope.data = {};

    $scope.selectedCharacter = "Fox";
    $scope.selectedOpponent  = "Falco";

    var tmp_character = $cookieStore.get('selectedCharacter');
    var tmp_opponent  = $cookieStore.get('selectedCharacter');

    if(tmp_character) {
        console.log('character set');
        $scope.selectedCharacter = tmp_character;
    }
    if(tmp_oppononent) {
        console.log('opponent set');
        $scope.selectedOpponent = tmp_oppononent;
    }

    $scope.data.characters = ["Fox", "Falco", "Sheik", "Marth"];

    $scope.matchups = function() {
        $cookieStore.put('selectedCharacter', $scope.selectedCharacter);
        $cookieStore.put('selectedOpponent',  $scope.selectedOpponent);

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