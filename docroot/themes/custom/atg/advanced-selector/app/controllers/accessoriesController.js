Cleco.controller('accessoriesController',
  function ($scope, $location, $http, $window, $localStorage, $filter) {
    $scope.t = $filter('translate');

    $scope.loadData = function() {
      $scope.loading = true;
      $scope.submitted = false;
      $http.get('/themes/custom/atg/advanced-selector/api/read.php')
        .success(function(data, status) {
          $scope.loading = false;
          $scope.user = data.user;
          $scope.user.apps.refer = 'accessories';
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.saveAccessories = function(data) {
      $scope.loading = true;
      $scope.submitted = true;

      data.refer = 'accessories';

      $http.post('/themes/custom/atg/advanced-selector/api/update.php', data)
        .success(function(result) {
          $scope.loading = false;
          $scope.submitted = false;
          if (result.error) {
            $scope.errors = result.message;
          } else {
            $location.path('/solution');
          }
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //saveAccessories
  }); //accessoriesController
