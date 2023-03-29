Cleco.controller('completeController',
  function ($scope, $location, $http, $window, $localStorage, $filter) {
    $scope.t = $filter('translate');

    $scope.loadData = function() {
      $scope.loading = true;
      $scope.submitted = false;
      $http.get('/themes/custom/atg/advanced-selector/api/read.php')
        .success(function(data, status) {
          $scope.loading = false;
          $scope.user = data.user;
          if ($scope.user == undefined) {
            // Define the $scope.user object if it is undefined
            $scope.user = {};
          }
          $scope.user.de_contact_name =''; 
          $scope.friend.from_name = $scope.user.de_contact_name;
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.friend = {
      colleague_email: '',
      brief_msg: '',
      from_name: ''
    };

    $scope.sendToColleague = function(data) {
      $scope.loading = true;
      $scope.submitted = true;
      data.refer = 'colleague';
      $http.post('/themes/custom/atg/advanced-selector/api/update.php', data)
        .success(function(result) {
          $scope.loading = false;
          $scope.submitted = false;
          if (result.error) {
            $scope.errors = result.message;
          } else {
            window.location.assign('/' + $scope.t('tools/advanced-drilling') + '/email ');
          }
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //sendToColleague
  }); //completeController