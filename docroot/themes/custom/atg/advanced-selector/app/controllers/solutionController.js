Cleco.controller('solutionController',
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
          if (!data.user.de_solution_issue_sortingLog) {
            data.user.de_solution_issue_sortingLog = 'Hole Quality, Ergonomics, Drill Cycle Time, Overall Productivity, Error Proofing, Other';
          }
          sort_items = data.user.de_solution_issue_sortingLog;
          $scope.items = sort_items.split(', ');
          for (i = 0; i < $scope.items.length; i += 1) {
            if ($scope.items[i].indexOf('Other') === 0) {
              $scope.items[i] = 'Other';
              break;
            }
          }
          $scope.sortableOptions = {
            stop: function(e, ui) {
              var logEntry = $scope.items.map(function(i) {
                if (i.toLowerCase() == 'other' && data.user.de_solution_issue_other_value) {
                  i = i + ' (' + data.user.de_solution_issue_other_value + ')';
                }
                return i;
              }).join(', ');
              $scope.user.de_solution_issue_sortingLog = logEntry;
              console.log({ logEntry });
            },
            axis: 'y'
          };
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
      });
    }; //loadData

    //initial load
    $scope.loadData();

    $scope.saveSolutions = function(data) {
      $scope.loading = true;
      $scope.submitted = true;
      data.refer = 'solutions';
      if (data.de_solution_issue_other_value && data.de_solution_issue_sortingLog.indexOf(data.de_solution_issue_other_value) == -1) {
        data.de_solution_issue_sortingLog = data.de_solution_issue_sortingLog.replace('Other', 'Other (' + data.de_solution_issue_other_value + ')');
      }
      $http.post('/themes/custom/atg/advanced-selector/api/update.php', data)
        .success(function(result) {
          $scope.loading = false;
          $scope.submitted = false;
          if (result.error) {
            $scope.errors = result.message;
          } else {
            $location.path('/complete');
          }
        })
        .error(function(data, status, headers, config) {
          $scope.loading = false;
          console.log(data, status, headers, config);
        });
    }; //saveSolutions
  }); //solutionController
