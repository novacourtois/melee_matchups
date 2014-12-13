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

    $scope.characterTips = "hello";
    $scope.opponentTips = "Bye";

    $scope.data.characters = ["Fox", "Falco", "Sheik", "Marth"];

    $scope.characterUpdate = function() {
        console.log($scope.selectedCharacter);
        console.log($scope.selectedOpponent);
    };

    $scope.opponentUpdate = function() {
        console.log($scope.selectedCharacter);
        console.log($scope.selectedOpponent);
    };

    $scope.Matchups = function() {
        $http.get('accept.php', {
            params: {
                character: $scope.data.character,
                opponent: $scope.data.opponent
            }
         })
         .success(function (data,status) {
              $scope.data.characterTips = data.characterTips;
              $scope.data.opponentTips = data.opponentTips;
         });
    }

    // $http.get('accept.php', {
    //     params: {
    //         character: $scope.data.character,
    //         opponent: $scope.data.opponent
    //     }
    //  })
    //  .success(function (data,status) {
    //       $scope.info_show = data
    //  });
});