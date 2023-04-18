var Cleco = angular.module('Cleco', ['ngRoute', 'ngStorage', 'ngResource', 'ngSanitize', 'ui.sortable']);
Cleco.config(function($routeProvider) {
  $routeProvider
    .when('/business', {
      controller: 'businessController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/businessView.html'
    })
    .when('/general', {
      controller: 'generalController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/generalView.html'
    })
    .when('/application', {
      controller: 'applicationController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/applicationView.html'
    })
    .when('/accessories', {
      controller: 'accessoriesController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/accessoriesView.html'
    })
    .when('/solution', {
      controller: 'solutionController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/solutionView.html'
    })
    .when('/complete', {
      controller: 'completeController',
      templateUrl: '/themes/custom/atg/advanced-selector/app/views/completeView.html'
    })
    .otherwise({
      redirectTo: '/business'
    });
});

Cleco.translations = false;
Cleco.language = 'en';
Cleco.nontranslatable = [];
Cleco.formContainer = document.getElementsByClassName('sel-ade')[0];

Cleco.run(function($http) {
  $http.get('/themes/custom/atg/advanced-selector/api/translations.php')
    .success(function(data, status) {
      // console.log({ data, status });
      Cleco.language = data.language;
      Cleco.translations = data.translations;
    })
    .error(function(data, status, headers, config) {
      console.log(data, status, headers, config);
    });
});

Cleco.filter('translate', function() {
    return function(string, lang) {
        lang = lang || Cleco.language;
        let translated = string;

        if (lang != 'en') {
            if (Cleco.translations.hasOwnProperty(string) && Cleco.translations[string].hasOwnProperty(lang)) {
                translated = Cleco.translations[string][lang];
            } else {
                console.log('Nontranslatable', { string, lang });
                Cleco.nontranslatable.push(string);
            }
        }

        return translated;
    }
});

// Cleco.filter('startFrom', function() {
//   return function(input, start) {
//     if (!input || !input.length) {
//       return;
//     }
//     start = +start; //parse to int
//     return input.slice(start);
//   }
// });

// Cleco.filter('index', function() {
//   return function(array, index) {
//     if (array) {
//       if (!index)
//         index = 'index';
//       for (var i = 0; i < array.length; ++i) {
//         array[i][index] = i;
//       }
//       return array;
//     }
//   };
// });

// Cleco.filter('capitalize', function() {
//   return function(input, scope) {
//     if (input) {
//       input = input.toLowerCase();
//       return input.substring(0, 1).toUpperCase() + input.substring(1);
//     }
//   }
// });

// Cleco.filter('splitcharacters', function() {
//   return function(input, chars) {
//     if (isNaN(chars)) return input;
//     if (chars <= 0) return '';
//     if (input && input.length > chars) {
//       var prefix = input.substring(0, chars / 2);
//       var postfix = input.substring(input.length - chars / 2, input.length);
//       return prefix + '...' + postfix;
//     }
//     return input;
//   };
// });

// Cleco.directive('emptyToNull', function() {
//   return {
//     restrict: 'A',
//     require: 'ngModel',
//     link: function(scope, elem, attrs, ctrl) {
//       ctrl.$parsers.push(function(viewValue) {
//         if (viewValue === "") {
//           return null;
//         }
//         return viewValue;
//       });
//     }
//   };
// });
