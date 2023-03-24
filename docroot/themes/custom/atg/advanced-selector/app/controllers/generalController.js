Cleco.controller('generalController',
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
          // Add this line to check if the "apps" property exists before accessing it
          if ($scope.user && $scope.user.apps) {
            $scope.user.apps[0].show = 1;
          }
          if ($scope.user == undefined) {
            // Define the $scope.user object if it is undefined
            $scope.user = {};
          }
          $scope.user.de_inquiry_product_types = '';
          if ($scope.user.de_inquiry_product_types !== undefined) {
            $scope.user.de_inquiry_product_types = $scope.user.de_inquiry_product_types.split(', ');
          }
          if ($scope.user.de_inquiry_product_types !== '') {
            $scope.product_types = [
              $scope.t('Positive Feed'),
              $scope.t('Air/Power Feed'),
              $scope.t('Manual/Hand Drill'),
              $scope.t('Other - Please Specify'),
            ];
            $scope.selected_product_types = $scope.user.de_inquiry_product_types;
          }
          if (
            Array.isArray($scope.user.de_inquiry_product_types)
            && $scope.user.de_inquiry_product_types.length == 1
            && $scope.user.de_inquiry_product_types[0] == '') {
            $scope.user.de_inquiry_product_types = [];
          }
          // console.log($scope.user.de_inquiry_product_types);
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.updateGeneral = function(data) {
      $scope.loading = true;
      $scope.submitted = true;
      data.refer = 'general';
      if ($scope.selected_product_types) {
        data.de_inquiry_product_types = $scope.selected_product_types.filter(function(value) {
          return value !== '' && value !== false;
        }).join(', ')
      }
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
          console.log(data, status, headers, config);
          $scope.loading = false;
        });
    }; //updateBusiness

    $scope.toggleProductType = function toggleProductType(product_type) {
      var index = $scope.selected_product_types.indexOf(product_type);

      // Is currently selected
      if (index !== -1) {
        $scope.selected_product_types.splice(index, 1);
      } else {
        $scope.selected_product_types.push(product_type);
      }
    }; //toggleProductType

    $scope.toggleCheck = function(item, val, index) {
      // console.log('item: ' + item);
      // console.log('value: ' + $scope.user['apps'][index][item]);
      if ($scope.user['apps'][index][item] === val) {
        $scope.user['apps'][index][item] = '';
        if (item == 'dea_product_type_other') {
          $scope.user['apps'][index]['dea_product_type_other_value'] = '';
        }
      } else {
        $scope.user['apps'][index][item] = val;
      }
    }; //toggleCheck

    $scope.clearChildren = function(items) {
      item_array = items.split('|');
      if (item_array.length == 1) {
        $scope.user[items] = '';
        // console.log('Cleared out: '+items);
      } else {
        item_array.forEach(function(item) {
          $scope.user[item] = '';
          // console.log('Cleared out: '+item);
        });
      }
    }; // clearChildren
  }); //generalController
