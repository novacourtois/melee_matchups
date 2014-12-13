angular.module('matchups',['ngRoute'])
.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/work', {
        templateUrl: '/includes/views/work.html',
        controller: 'dataListCtrl'
    }).
    when('/projects', {
        templateUrl: 'includes/views/projects.html',
        controller: 'projectListCtrl'
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
        $http.get('php/matchups.php', {
            params: {
                character : $scope.data.character,
                opponent  : $scope.data.opponent
            }
        })
        .success(function (data, status) {
            console.log('fetching info worked');
            console.log(data);
            console.log(status);
            $scope.data.characterTips = data.characterTips;
            $scope.data.opponentTips = data.opponentTips;
        })
        .error(function (data, status){
            console.log('fetching info failed');
        });
    };
});