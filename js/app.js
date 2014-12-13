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
        templateUrl: '/views/matchups.html',
        controller: 'matchupsCtrl'
    }).
    otherwise({
        redirectTo: '/'
    });
}])
.controller("projectListCtrl", function($scope, $http) {
    $scope.data = {};

    $scope.data.characters = ["Fox", "Falco", "Sheik", "Marth"];

    $http.get('accept.php', {
        params: {
            source: link,
            category_id: category
        }
     })
     .success(function (data,status) {
          $scope.info_show = data
     });
})
.controller("projectListCtrl", function($scope, $http) {
    $scope.data = {};

    $http.get('http://novacourtois.com/includes/projects.json').
        success(function(data) {
            $scope.data = data;
        });
})
.controller("dataListCtrl", function($scope, $http) {
    $scope.data = {};

    $http.get('http://novacourtois.com/includes/projects.json').
        success(function(data) {
            $scope.data.projects = data.items;
        });

    $http.get('http://novacourtois.com/includes/other.json').
        success(function(data) {
            $scope.data.other = data.items;
        });

    $http.get('http://novacourtois.com/includes/websites.json').
        success(function(data) {
            $scope.data.websites = data.items;
        });
})
.filter('unsafe', function($sce) {
	return function(val) {
		return $sce.trustAsHtml(val);
    };
});