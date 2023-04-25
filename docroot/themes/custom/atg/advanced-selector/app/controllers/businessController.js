Cleco.controller('businessController',
  function ($scope, $location, $http, $window, $localStorage, $filter) {
    $scope.t = $filter('translate');

    $scope.loadData = function() {
      $scope.loading = true;
      $scope.submitted = false;
      $http.get('/themes/custom/atg/advanced-selector/api/read.php')
        .success(function(data, status) {
          // console.log({ data, status });
          $scope.loading = false;
          $scope.user = data.user;
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.updateBusiness = function(data) {
      $scope.loading = true;
      $scope.submitted = true;
      if (!$scope.businessform.$valid) return false;

      data.refer = 'business';
      $http.post('/themes/custom/atg/advanced-selector/api/update.php', data)
        .success(function(result) {
          $scope.loading = false;
          $scope.submitted = false;
          if (result.error) {
            $scope.errors = result.message;
          } else {
            if ($scope.user.de_inquiry.toLowerCase() === 'general information' || $scope.user.de_inquiry_application_info.toLowerCase() === 'please contact me') {
              $location.path('/general');
            } else {
              $location.path('/application');
            }

          }
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //updateBusiness

    $scope.clearChildren = function(items) {
      item_array = items.split(',');
      if (item_array.length == 1) {
        $scope.user[items] = '';
        // console.log('Cleared out: '+items);
      } else {
        item_array.forEach(function(item, index) {
          $scope.user[item] = '';
          // console.log('Cleared out: '+item);
        });
      }
    }; // clearChildren
  }); //businessController
